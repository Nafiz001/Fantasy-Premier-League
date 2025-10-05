<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Services\TeamLogoService;

class FixturesController extends Controller
{
    public function index(Request $request, $gameweek = null)
    {
        // Get gameweek from route parameter, request query, or use current gameweek
        $currentGameweek = $gameweek ?: $request->get('gameweek');

        if (!$currentGameweek) {
            // Get current gameweek (the one that's happening now)
            $currentGameweek = DB::table('gameweeks')
                ->where('is_current', true)
                ->value('gameweek_id');

            // If no current gameweek is set, get the next unfinished gameweek
            if (!$currentGameweek) {
                $currentGameweek = DB::table('gameweeks')
                    ->where('finished', false)
                    ->orderBy('gameweek_id', 'asc')
                    ->value('gameweek_id');
            }

            // Fallback to gameweek 6 if nothing found
            if (!$currentGameweek) {
                $currentGameweek = 6;
            }
        }

        // Get navigation info (previous/next gameweeks)
        $previousGameweek = \App\Models\Gameweek::where('gameweek_id', '<', $currentGameweek)
            ->orderBy('gameweek_id', 'desc')
            ->first();

        $nextGameweek = \App\Models\Gameweek::where('gameweek_id', '>', $currentGameweek)
            ->orderBy('gameweek_id', 'asc')
            ->first();

        // Get gameweek data for deadline
        $gameweekData = \App\Models\Gameweek::where('gameweek_id', $currentGameweek)->first();

        // Get fixtures for current gameweek
        $fixtures = $this->getFixturesForGameweek($currentGameweek);

        return view('fixtures.index', compact('fixtures', 'currentGameweek', 'previousGameweek', 'nextGameweek', 'gameweekData'));
    }    private function getFixturesForGameweek($gameweek)
    {
        // Try to get from fixtures table first
        $fixtures = DB::table('fixtures')
            ->join('teams as home_team', 'fixtures.home_team', '=', 'home_team.fpl_id')
            ->join('teams as away_team', 'fixtures.away_team', '=', 'away_team.fpl_id')
            ->where('fixtures.gameweek', $gameweek)
            ->select(
                'fixtures.*',
                'home_team.name as home_team_name',
                'home_team.short_name as home_team_short',
                'home_team.fpl_id as home_team_code',
                'away_team.name as away_team_name',
                'away_team.short_name as away_team_short',
                'away_team.fpl_id as away_team_code'
            )
            ->orderBy('fixtures.kickoff_time')
            ->get();

        // Add team logo URLs to each fixture
        foreach ($fixtures as $fixture) {
            $fixture->home_team_logo = TeamLogoService::getLogoUrl($fixture->home_team_short, 'api-football');
            $fixture->away_team_logo = TeamLogoService::getLogoUrl($fixture->away_team_short, 'api-football');
        }

        // If no fixtures, create sample data for demonstration
        if ($fixtures->isEmpty()) {
            $fixtures = $this->getSampleFixtures($gameweek);
        }

        return $fixtures;
    }

    private function getSampleFixtures($gameweek)
    {
        // Premier League team mappings for correct logo display (Football-Data.org IDs)
        $teamMappings = [
            'ARS' => ['name' => 'Arsenal', 'logo_id' => 57],
            'AVL' => ['name' => 'Aston Villa', 'logo_id' => 58],
            'BOU' => ['name' => 'Bournemouth', 'logo_id' => 1044],
            'BRE' => ['name' => 'Brentford', 'logo_id' => 402],
            'BHA' => ['name' => 'Brighton', 'logo_id' => 397],
            'CHE' => ['name' => 'Chelsea', 'logo_id' => 61],
            'CRY' => ['name' => 'Crystal Palace', 'logo_id' => 354],
            'EVE' => ['name' => 'Everton', 'logo_id' => 62],
            'FUL' => ['name' => 'Fulham', 'logo_id' => 63],
            'IPS' => ['name' => 'Ipswich', 'logo_id' => 349],
            'LEI' => ['name' => 'Leicester', 'logo_id' => 338],
            'LIV' => ['name' => 'Liverpool', 'logo_id' => 64],
            'MCI' => ['name' => 'Manchester City', 'logo_id' => 65],
            'MUN' => ['name' => 'Manchester United', 'logo_id' => 66],
            'NEW' => ['name' => 'Newcastle', 'logo_id' => 67],
            'NFO' => ['name' => 'Nottingham Forest', 'logo_id' => 351],
            'SOU' => ['name' => 'Southampton', 'logo_id' => 340],
            'TOT' => ['name' => 'Tottenham', 'logo_id' => 73],
            'WHU' => ['name' => 'West Ham', 'logo_id' => 563],
            'WOL' => ['name' => 'Wolverhampton', 'logo_id' => 76]
        ];

        // Create sample fixtures for gameweek 4 (Sat 13 Sep, Sun 14 Sep)
        $sampleFixtures = collect([
            [
                'fixture_id' => 1,
                'gameweek' => $gameweek,
                'kickoff_time' => Carbon::createFromFormat('Y-m-d H:i:s', '2025-09-13 17:30:00'),
                'home_team' => 1,
                'away_team' => 20,
                'home_team_name' => $teamMappings['ARS']['name'],
                'home_team_short' => 'ARS',
                'home_team_code' => $teamMappings['ARS']['logo_id'],
                'away_team_name' => $teamMappings['NFO']['name'],
                'away_team_short' => 'NFO',
                'away_team_code' => $teamMappings['NFO']['logo_id'],
                'finished' => 0
            ],
            [
                'fixture_id' => 2,
                'gameweek' => $gameweek,
                'kickoff_time' => Carbon::createFromFormat('Y-m-d H:i:s', '2025-09-13 20:00:00'),
                'home_team' => 7,
                'away_team' => 4,
                'home_team_name' => $teamMappings['BOU']['name'],
                'home_team_short' => 'BOU',
                'home_team_code' => $teamMappings['BOU']['logo_id'],
                'away_team_name' => $teamMappings['BHA']['name'],
                'away_team_short' => 'BHA',
                'away_team_code' => $teamMappings['BHA']['logo_id'],
                'finished' => 0
            ],
            [
                'fixture_id' => 3,
                'gameweek' => $gameweek,
                'kickoff_time' => Carbon::createFromFormat('Y-m-d H:i:s', '2025-09-13 20:00:00'),
                'home_team' => 8,
                'away_team' => 17,
                'home_team_name' => $teamMappings['CRY']['name'],
                'home_team_short' => 'CRY',
                'home_team_code' => $teamMappings['CRY']['logo_id'],
                'away_team_name' => $teamMappings['EVE']['name'],
                'away_team_short' => 'EVE',
                'away_team_code' => $teamMappings['EVE']['logo_id'],
                'finished' => 0
            ],
            [
                'fixture_id' => 4,
                'gameweek' => $gameweek,
                'kickoff_time' => Carbon::createFromFormat('Y-m-d H:i:s', '2025-09-13 20:00:00'),
                'home_team' => 9,
                'away_team' => 2,
                'home_team_name' => $teamMappings['LIV']['name'],
                'home_team_short' => 'LIV',
                'home_team_code' => $teamMappings['LIV']['logo_id'],
                'away_team_name' => $teamMappings['MUN']['name'],
                'away_team_short' => 'MUN',
                'away_team_code' => $teamMappings['MUN']['logo_id'],
                'finished' => 0
            ],
            [
                'fixture_id' => 5,
                'gameweek' => $gameweek,
                'kickoff_time' => Carbon::createFromFormat('Y-m-d H:i:s', '2025-09-14 16:00:00'),
                'home_team' => 11,
                'away_team' => 13,
                'home_team_name' => $teamMappings['FUL']['name'],
                'home_team_short' => 'FUL',
                'home_team_code' => $teamMappings['FUL']['logo_id'],
                'away_team_name' => $teamMappings['LEI']['name'],
                'away_team_short' => 'LEI',
                'away_team_code' => $teamMappings['LEI']['logo_id'],
                'finished' => 0
            ],
            [
                'fixture_id' => 6,
                'gameweek' => $gameweek,
                'kickoff_time' => Carbon::createFromFormat('Y-m-d H:i:s', '2025-09-14 16:00:00'),
                'home_team' => 14,
                'away_team' => 15,
                'home_team_name' => $teamMappings['MCI']['name'],
                'home_team_short' => 'MCI',
                'home_team_code' => $teamMappings['MCI']['logo_id'],
                'away_team_name' => $teamMappings['TOT']['name'],
                'away_team_short' => 'TOT',
                'away_team_code' => $teamMappings['TOT']['logo_id'],
                'finished' => 0
            ],
            [
                'fixture_id' => 7,
                'gameweek' => $gameweek,
                'kickoff_time' => Carbon::createFromFormat('Y-m-d H:i:s', '2025-09-14 16:00:00'),
                'home_team' => 16,
                'away_team' => 18,
                'home_team_name' => $teamMappings['NEW']['name'],
                'home_team_short' => 'NEW',
                'home_team_code' => $teamMappings['NEW']['logo_id'],
                'away_team_name' => $teamMappings['CHE']['name'],
                'away_team_short' => 'CHE',
                'away_team_code' => $teamMappings['CHE']['logo_id'],
                'finished' => 0
            ],
            [
                'fixture_id' => 8,
                'gameweek' => $gameweek,
                'kickoff_time' => Carbon::createFromFormat('Y-m-d H:i:s', '2025-09-14 18:30:00'),
                'home_team' => 19,
                'away_team' => 3,
                'home_team_name' => $teamMappings['WHU']['name'],
                'home_team_short' => 'WHU',
                'home_team_code' => $teamMappings['WHU']['logo_id'],
                'away_team_name' => $teamMappings['WOL']['name'],
                'away_team_short' => 'WOL',
                'away_team_code' => $teamMappings['WOL']['logo_id'],
                'finished' => 0
            ]
        ]);

        return $sampleFixtures->map(function ($fixture) {
            $fixtureObj = (object) $fixture;
            // Add team logo URLs using TeamLogoService
            $fixtureObj->home_team_logo = TeamLogoService::getLogoUrl($fixtureObj->home_team_short, 'api-football');
            $fixtureObj->away_team_logo = TeamLogoService::getLogoUrl($fixtureObj->away_team_short, 'api-football');
            return $fixtureObj;
        });
    }

    /**
     * Import fixtures from FPL API
     */
    public function importFixtures(Request $request)
    {
        try {
            // For now, return a sample response
            // In the future, this would call the actual FPL API
            return response()->json([
                'success' => true,
                'message' => 'Sample fixtures imported successfully. Real API integration coming soon!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to import fixtures: ' . $e->getMessage()
            ]);
        }
    }
}
