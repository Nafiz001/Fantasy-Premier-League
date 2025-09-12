<?php

namespace App\Services;

use App\Models\PlayerGameweekStats;
use App\Models\Fixture;
use App\Models\Player;

class FPLMatchProcessor
{
    private FPLScoringService $scoringService;
    private FPLBonusPointsService $bonusService;

    public function __construct()
    {
        $this->scoringService = new FPLScoringService();
        $this->bonusService = new FPLBonusPointsService();
    }

    /**
     * Process a completed fixture and calculate all points
     */
    public function processFixture(int $fixtureId): array
    {
        $fixture = Fixture::findOrFail($fixtureId);
        
        if (!$fixture->finished) {
            throw new \Exception("Fixture {$fixtureId} is not yet finished");
        }

        // Get all player stats for this fixture
        $allStats = PlayerGameweekStats::where('fixture_id', $fixtureId)->get();
        
        if ($allStats->isEmpty()) {
            throw new \Exception("No player stats found for fixture {$fixtureId}");
        }

        $results = [
            'fixture' => $fixture,
            'players_processed' => 0,
            'bonus_points_awarded' => 0,
            'top_bps_players' => []
        ];

        // Step 1: Calculate base points and BPS for all players
        foreach ($allStats as $playerStats) {
            // Calculate base points
            $playerStats->calculateTotalPoints();
            
            // Calculate BPS
            $playerStats->calculateBPS();
            
            $results['players_processed']++;
        }

        // Step 2: Calculate and award bonus points
        $bonusResults = $this->awardBonusPoints($fixtureId);
        $results['bonus_points_awarded'] = $bonusResults['total_bonus_awarded'];
        $results['top_bps_players'] = $bonusResults['top_players'];

        // Step 3: Recalculate total points including bonus
        foreach ($allStats->fresh() as $playerStats) {
            $playerStats->calculateTotalPoints();
        }

        return $results;
    }

    /**
     * Award bonus points for a fixture based on BPS
     */
    public function awardBonusPoints(int $fixtureId): array
    {
        $playerStats = PlayerGameweekStats::where('fixture_id', $fixtureId)
            ->where('minutes', '>', 0) // Only players who played
            ->orderBy('bps', 'desc')
            ->get();

        if ($playerStats->count() < 3) {
            return [
                'total_bonus_awarded' => 0,
                'top_players' => []
            ];
        }

        // Get unique BPS values in descending order
        $uniqueBPS = $playerStats->pluck('bps')->unique()->sort()->reverse()->values();
        
        $bonusAwarded = 0;
        $topPlayers = [];
        
        // Award 3 points to highest BPS
        if (isset($uniqueBPS[0])) {
            $firstPlace = $playerStats->where('bps', $uniqueBPS[0]);
            foreach ($firstPlace as $player) {
                $player->update(['bonus' => 3]);
                $bonusAwarded += 3;
                $topPlayers[] = [
                    'player' => $player->player->web_name,
                    'bps' => $player->bps,
                    'bonus' => 3
                ];
            }
        }

        // Award 2 points to second highest BPS (if different from first)
        if (isset($uniqueBPS[1]) && $uniqueBPS[1] != $uniqueBPS[0]) {
            $secondPlace = $playerStats->where('bps', $uniqueBPS[1]);
            foreach ($secondPlace as $player) {
                $player->update(['bonus' => 2]);
                $bonusAwarded += 2;
                $topPlayers[] = [
                    'player' => $player->player->web_name,
                    'bps' => $player->bps,
                    'bonus' => 2
                ];
            }
        }

        // Award 1 point to third highest BPS (if different from first two)
        if (isset($uniqueBPS[2]) && $uniqueBPS[2] != $uniqueBPS[1] && $uniqueBPS[2] != $uniqueBPS[0]) {
            $thirdPlace = $playerStats->where('bps', $uniqueBPS[2]);
            foreach ($thirdPlace as $player) {
                $player->update(['bonus' => 1]);
                $bonusAwarded += 1;
                $topPlayers[] = [
                    'player' => $player->player->web_name,
                    'bps' => $player->bps,
                    'bonus' => 1
                ];
            }
        }

        return [
            'total_bonus_awarded' => $bonusAwarded,
            'top_players' => $topPlayers
        ];
    }

    /**
     * Process all fixtures for a gameweek
     */
    public function processGameweek(int $gameweek): array
    {
        $fixtures = Fixture::where('event', $gameweek)
            ->where('finished', true)
            ->get();

        $results = [
            'gameweek' => $gameweek,
            'fixtures_processed' => 0,
            'total_players_processed' => 0,
            'total_bonus_awarded' => 0,
            'fixture_results' => []
        ];

        foreach ($fixtures as $fixture) {
            try {
                $fixtureResult = $this->processFixture($fixture->id);
                
                $results['fixtures_processed']++;
                $results['total_players_processed'] += $fixtureResult['players_processed'];
                $results['total_bonus_awarded'] += $fixtureResult['bonus_points_awarded'];
                $results['fixture_results'][] = $fixtureResult;
                
            } catch (\Exception $e) {
                // Log error but continue processing other fixtures
                error_log("Error processing fixture {$fixture->id}: " . $e->getMessage());
            }
        }

        return $results;
    }

    /**
     * Get gameweek summary with top performers
     */
    public function getGameweekSummary(int $gameweek): array
    {
        $topScorers = PlayerGameweekStats::with(['player', 'player.team'])
            ->gameweek($gameweek)
            ->orderBy('total_points', 'desc')
            ->limit(10)
            ->get();

        $mostBonusPoints = PlayerGameweekStats::with(['player', 'player.team'])
            ->gameweek($gameweek)
            ->where('bonus', '>', 0)
            ->orderBy('bonus', 'desc')
            ->orderBy('bps', 'desc')
            ->limit(10)
            ->get();

        $captainPicks = PlayerGameweekStats::with(['player', 'player.team'])
            ->gameweek($gameweek)
            ->where('total_points', '>=', 8) // Good captain candidates
            ->orderBy('total_points', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($stats) {
                return [
                    'player' => $stats->player->web_name,
                    'team' => $stats->player->team->short_name,
                    'points' => $stats->total_points,
                    'captain_points' => $stats->total_points * 2,
                    'position' => $this->getPositionName($stats->player->element_type)
                ];
            });

        return [
            'gameweek' => $gameweek,
            'top_scorers' => $topScorers->map(function ($stats) {
                return [
                    'player' => $stats->player->web_name,
                    'team' => $stats->player->team->short_name,
                    'position' => $this->getPositionName($stats->player->element_type),
                    'points' => $stats->total_points,
                    'breakdown' => $stats->getPointsBreakdown()
                ];
            }),
            'most_bonus' => $mostBonusPoints->map(function ($stats) {
                return [
                    'player' => $stats->player->web_name,
                    'team' => $stats->player->team->short_name,
                    'bonus_points' => $stats->bonus,
                    'bps' => $stats->bps,
                    'total_points' => $stats->total_points
                ];
            }),
            'captain_recommendations' => $captainPicks
        ];
    }

    /**
     * Simulate team points for a given gameweek
     */
    public function calculateTeamPoints(array $playerIds, int $gameweek, int $captainId = null, int $viceCaptainId = null): array
    {
        if (count($playerIds) !== 15) {
            throw new \Exception("Team must have exactly 15 players");
        }

        // Get player stats for the gameweek
        $playerStats = PlayerGameweekStats::with('player')
            ->whereIn('player_id', $playerIds)
            ->gameweek($gameweek)
            ->get()
            ->keyBy('player_id');

        $teamPoints = 0;
        $playingXI = [];
        $bench = [];
        $breakdown = [];

        // Sort players by position and select starting XI
        $positions = [1 => [], 2 => [], 3 => [], 4 => []]; // GK, DEF, MID, FWD

        foreach ($playerIds as $playerId) {
            $stats = $playerStats->get($playerId);
            if ($stats && $stats->minutes > 0) {
                $position = $stats->player->element_type;
                $positions[$position][] = [
                    'id' => $playerId,
                    'stats' => $stats,
                    'points' => $stats->total_points
                ];
            }
        }

        // Select starting XI based on formation constraints
        $formation = $this->selectFormation($positions);
        
        foreach ($formation['starting_xi'] as $player) {
            $points = $player['points'];
            
            // Apply captain multiplier
            if ($captainId && $player['id'] == $captainId) {
                $points *= 2;
                $breakdown[] = [
                    'player' => $player['stats']->player->web_name,
                    'base_points' => $player['points'],
                    'multiplier' => '(C)',
                    'final_points' => $points
                ];
            } else {
                $breakdown[] = [
                    'player' => $player['stats']->player->web_name,
                    'base_points' => $player['points'],
                    'multiplier' => '',
                    'final_points' => $points
                ];
            }
            
            $teamPoints += $points;
            $playingXI[] = $player;
        }

        return [
            'total_points' => $teamPoints,
            'playing_xi' => $playingXI,
            'bench' => $formation['bench'],
            'breakdown' => $breakdown,
            'captain' => $captainId,
            'vice_captain' => $viceCaptainId
        ];
    }

    /**
     * Select optimal formation from available players
     */
    private function selectFormation(array $positions): array
    {
        // Sort each position by points
        foreach ($positions as &$positionPlayers) {
            usort($positionPlayers, function ($a, $b) {
                return $b['points'] <=> $a['points'];
            });
        }

        $startingXI = [];
        $bench = [];

        // Always select best GK
        if (!empty($positions[1])) {
            $startingXI[] = array_shift($positions[1]);
            $bench = array_merge($bench, $positions[1]); // Rest go to bench
        }

        // Select best defenders (minimum 3, maximum 5)
        $defCount = min(5, max(3, count($positions[2])));
        for ($i = 0; $i < $defCount && !empty($positions[2]); $i++) {
            $startingXI[] = array_shift($positions[2]);
        }
        $bench = array_merge($bench, $positions[2]);

        // Select midfielders (minimum 3, maximum 5)
        $midCount = min(5, max(3, count($positions[3])));
        for ($i = 0; $i < $midCount && !empty($positions[3]); $i++) {
            $startingXI[] = array_shift($positions[3]);
        }
        $bench = array_merge($bench, $positions[3]);

        // Select forwards (minimum 1, maximum 3)
        $fwdCount = min(3, max(1, count($positions[4])));
        for ($i = 0; $i < $fwdCount && !empty($positions[4]); $i++) {
            $startingXI[] = array_shift($positions[4]);
        }
        $bench = array_merge($bench, $positions[4]);

        return [
            'starting_xi' => $startingXI,
            'bench' => $bench
        ];
    }

    /**
     * Get position name from element type
     */
    private function getPositionName(int $elementType): string
    {
        return match($elementType) {
            1 => 'Goalkeeper',
            2 => 'Defender',
            3 => 'Midfielder',
            4 => 'Forward',
            default => 'Unknown'
        };
    }
}
