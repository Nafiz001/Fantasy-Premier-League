<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Services\FPLPointsService;

class SquadController extends Controller
{
    protected $pointsService;

    public function __construct(FPLPointsService $pointsService)
    {
        $this->pointsService = $pointsService;
    }
    public function showSelection()
    {
        // Get players grouped by position with their current stats
        $goalkeepers = $this->getPlayersByPosition('Goalkeeper');
        $defenders = $this->getPlayersByPosition('Defender');
        $midfielders = $this->getPlayersByPosition('Midfielder');
        $forwards = $this->getPlayersByPosition('Forward');

        return view('squad.selection', compact('goalkeepers', 'defenders', 'midfielders', 'forwards'));
    }

    public function dashboard()
    {
        $user = Auth::user();

        // Get user's team data
        $startingXI = $user->starting_xi ?? [];

        // Ensure it's always an array (handle both JSON string and array cases)
        if (is_string($startingXI)) {
            $startingXI = json_decode($startingXI, true) ?? [];
        }

        // Get latest points data
        $squadPoints = $this->pointsService->getSquadPoints($user->id);
        $currentGameweek = $this->pointsService->getCurrentGameweek();

        $teamData = [
            'starting_xi' => $startingXI,
            'captain_id' => $user->captain_id,
            'vice_captain_id' => $user->vice_captain_id,
            'formation' => $user->formation ?? '4-4-2',
            'active_chip' => $user->active_chip,
            'used_chips' => $user->used_chips ?? [],
            'points' => $user->points ?? 0,
            'free_transfers' => $user->free_transfers ?? 1,
            'budget_remaining' => $user->budget_remaining ?? 1000,
            'latest_points' => $squadPoints['total_points'] ?? 0,
            'latest_gameweek' => $squadPoints['gameweek_name'] ?? null,
        ];

        return view('dashboard', compact('user', 'teamData', 'squadPoints', 'currentGameweek'));
    }

    private function getPlayersByPosition($position)
    {
        return DB::table('players')
            ->join('teams', 'players.team_code', '=', 'teams.fpl_code')
            ->join('player_stats', function($join) {
                $join->on('players.fpl_id', '=', 'player_stats.player_id')
                     ->where('player_stats.gameweek', '=', 3); // Current gameweek
            })
            ->select(
                'players.*',
                'teams.name as team_name',
                'teams.short_name as team_short',
                'teams.fpl_code as team_id',
                'player_stats.now_cost',
                'player_stats.total_points',
                'player_stats.selected_by_percent',
                'player_stats.form'
            )
            ->where('players.position', $position)
            ->whereNotNull('player_stats.now_cost')
            ->where('player_stats.now_cost', '>', 0)
            ->orderBy('player_stats.total_points', 'desc')
            ->get()
            ->map(function($player) {
                $player->price = ($player->now_cost ?? 50) / 10; // Convert to £m
                $player->jersey_url = $this->getJerseyUrl($player->team_id);
                $player->photo_url = $this->getPlayerPhotoUrl($player->fpl_id);
                return $player;
            });
    }

    private function getJerseyUrl($teamId)
    {
        // Try multiple jersey URL formats for better compatibility
        $jerseyUrls = [
            "https://fantasy.premierleague.com/dist/img/shirts/standard/shirt_{$teamId}-110.png",
            "https://fantasy.premierleague.com/dist/img/shirts/standard/shirt_{$teamId}.png",
            "https://resources.premierleague.com/premierleague/badges/25/t{$teamId}.png",
            "https://resources.premierleague.com/premierleague/badges/50/t{$teamId}@x2.png"
        ];

        // Return the first URL (we could add logic to test which works)
        return $jerseyUrls[0];
    }

    private function getPlayerPhotoUrl($playerId)
    {
        // Official FPL player photo URLs
        return "https://resources.premierleague.com/premierleague/photos/players/250x250/p{$playerId}.png";
    }

    public function saveSquad(Request $request)
    {
        $request->validate([
            'team_name' => 'required|string|max:255',
            'players' => 'required|array|size:15',
            'formation' => 'required|string',
        ]);

        $user = Auth::user();

        // Update user with squad completion
        $user->update([
            'has_selected_squad' => true,
            'team_name' => $request->team_name,
        ]);

        // Here you would save the actual squad selection
        // For now, we'll just mark as completed

        return response()->json([
            'success' => true,
            'message' => 'Squad saved successfully!',
            'redirect' => route('dashboard')
        ]);
    }

    public function autoPickSquad()
    {
        try {
            $targetBudget = 1000; // £100.0m in FPL format

            // Get all available players grouped by position
            $goalkeepers = $this->getPlayersByPosition('Goalkeeper');
            $defenders = $this->getPlayersByPosition('Defender');
            $midfielders = $this->getPlayersByPosition('Midfielder');
            $forwards = $this->getPlayersByPosition('Forward');

            // Check if we have enough players
            if ($goalkeepers->count() < 2 || $defenders->count() < 5 ||
                $midfielders->count() < 5 || $forwards->count() < 3) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not enough players available in database. Please check data import.'
                ]);
            }

            // Strategy: Build squad that uses exactly £100m (within £1m)
            $maxAttempts = 200;
            $bestSquad = null;
            $bestDifference = PHP_INT_MAX;

            for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
                // Different budget distribution strategies
                $strategies = [
                    ['gk' => 100, 'def' => 270, 'mid' => 400, 'fwd' => 230], // Balanced
                    ['gk' => 90, 'def' => 250, 'mid' => 450, 'fwd' => 210],  // Mid-heavy
                    ['gk' => 110, 'def' => 290, 'mid' => 350, 'fwd' => 250], // Forward-heavy
                    ['gk' => 95, 'def' => 300, 'mid' => 380, 'fwd' => 225],  // Defense-heavy
                ];

                $strategy = $strategies[$attempt % count($strategies)];

                // Add some randomness to budget allocation
                $gkBudget = $strategy['gk'] + rand(-10, 10);
                $defBudget = $strategy['def'] + rand(-20, 20);
                $midBudget = $strategy['mid'] + rand(-30, 30);
                $fwdBudget = $strategy['fwd'] + rand(-15, 15);

                // Ensure total budget equals target
                $totalAllocated = $gkBudget + $defBudget + $midBudget + $fwdBudget;
                $adjustment = $targetBudget - $totalAllocated;
                $fwdBudget += $adjustment; // Adjust forwards budget to hit exact target

                try {
                    $selectedGKs = $this->selectPlayersByBudget($goalkeepers, 2, $gkBudget);
                    $selectedDefs = $this->selectPlayersByBudget($defenders, 5, $defBudget);
                    $selectedMids = $this->selectPlayersByBudget($midfielders, 5, $midBudget);
                    $selectedFwds = $this->selectPlayersByBudget($forwards, 3, $fwdBudget);

                    $selectedSquad = collect()
                        ->merge($selectedGKs)
                        ->merge($selectedDefs)
                        ->merge($selectedMids)
                        ->merge($selectedFwds);

                    // Check if we have exactly 15 players
                    if ($selectedSquad->count() !== 15) {
                        continue;
                    }

                    $totalCost = $selectedSquad->sum('now_cost');
                    $difference = abs($totalCost - $targetBudget);

                    // If this is closer to £100m, keep it
                    if ($difference < $bestDifference) {
                        $bestDifference = $difference;
                        $bestSquad = $selectedSquad;

                        // If we're within £0.5m of target, that's perfect
                        if ($difference <= 5) {
                            break;
                        }
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }

            if (!$bestSquad || $bestSquad->count() !== 15) {
                // Last resort: pick players to hit exactly £100m
                $bestSquad = $this->buildExactBudgetSquad($goalkeepers, $defenders, $midfielders, $forwards, $targetBudget);
            }

            $totalCost = $bestSquad->sum('now_cost');

            // Save the auto-picked squad to the database
            $user = Auth::user();
            $playerIds = $bestSquad->pluck('fpl_id')->toArray();

            \Log::info('Auto-pick saving squad for user:', [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'player_ids' => $playerIds,
                'total_cost' => $totalCost,
                'budget_remaining' => (1000 - $totalCost) / 10
            ]);

            try {
                $user->update([
                    'has_selected_squad' => true,
                    'squad_completed' => true,
                    'team_name' => $user->name . "'s Auto Squad",
                    'budget_remaining' => (1000 - $totalCost) / 10,
                    'starting_xi' => $playerIds,
                    'formation' => '4-4-2'
                ]);

                \Log::info('Auto-pick squad saved successfully for user:', ['user_id' => $user->id]);

            } catch (\Exception $e) {
                \Log::error('Error saving auto-pick squad:', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
                throw $e;
            }

            // Convert to the format expected by frontend
            $squad = $bestSquad->map(function($player) {
                return [
                    'id' => $player->fpl_id,
                    'name' => $player->web_name,
                    'position' => $player->position,
                    'team' => $player->team_short,
                    'team_id' => $player->team_id,
                    'price' => $player->price,
                    'jersey_url' => $player->jersey_url,
                    'photo_url' => $player->photo_url,
                    'total_points' => $player->total_points ?? 0
                ];
            });

            return response()->json([
                'success' => true,
                'squad' => $squad,
                'total_cost' => $totalCost / 10,
                'budget_remaining' => (1000 - $totalCost) / 10,
                'message' => sprintf('Squad auto-picked and saved! £%.1fm used, £%.1fm remaining',
                    $totalCost / 10, (1000 - $totalCost) / 10),
                'redirect' => route('dashboard')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating squad: ' . $e->getMessage()
            ], 500);
        }
    }

    private function buildExactBudgetSquad($goalkeepers, $defenders, $midfielders, $forwards, $targetBudget)
    {
        // Start with cheapest possible squad
        $squad = collect()
            ->merge($goalkeepers->sortBy('now_cost')->take(2))
            ->merge($defenders->sortBy('now_cost')->take(5))
            ->merge($midfielders->sortBy('now_cost')->take(5))
            ->merge($forwards->sortBy('now_cost')->take(3));

        $currentCost = $squad->sum('now_cost');
        $remaining = $targetBudget - $currentCost;

        // If we're under budget, try to upgrade players
        if ($remaining > 0) {
            // Get all available players sorted by price difference potential
            $allPlayers = collect()
                ->merge($goalkeepers->sortByDesc('now_cost'))
                ->merge($defenders->sortByDesc('now_cost'))
                ->merge($midfielders->sortByDesc('now_cost'))
                ->merge($forwards->sortByDesc('now_cost'));

            // Try to spend remaining budget by upgrading players
            foreach ($allPlayers as $expensivePlayer) {
                if ($remaining <= 0) break;

                // Find current player in same position to replace
                $currentPlayers = $squad->where('position', $expensivePlayer->position);
                foreach ($currentPlayers as $currentPlayer) {
                    $priceDiff = $expensivePlayer->now_cost - $currentPlayer->now_cost;
                    if ($priceDiff > 0 && $priceDiff <= $remaining) {
                        // Replace with more expensive player
                        $squad = $squad->reject(function($p) use ($currentPlayer) {
                            return $p->fpl_id === $currentPlayer->fpl_id;
                        })->push($expensivePlayer);
                        $remaining -= $priceDiff;
                        break 2; // Break both loops
                    }
                }
            }
        }

        return $squad;
    }

    private function selectPlayersByBudget($players, $count, $budget)
    {
        if ($players->count() < $count) {
            return $players->take($count);
        }

        $sorted = $players->sortBy('now_cost');
        $selected = collect();

        // Strategy: Try to use most of the budget allocated
        $avgPrice = $budget / $count;
        $minPrice = $sorted->first()->now_cost ?? 40;
        $maxPrice = $sorted->last()->now_cost ?? 150;

        // First pass: try to select players around average price
        $remainingBudget = $budget;
        $remainingCount = $count;

        // Select players trying to hit the budget target
        for ($i = 0; $i < $count && $remainingCount > 0; $i++) {
            $targetPrice = $remainingBudget / $remainingCount;

            // Find player closest to target price
            $candidatePlayer = $sorted
                ->whereNotIn('fpl_id', $selected->pluck('fpl_id'))
                ->sortBy(function($player) use ($targetPrice) {
                    return abs($player->now_cost - $targetPrice);
                })
                ->first();

            if ($candidatePlayer && $candidatePlayer->now_cost <= $remainingBudget) {
                $selected->push($candidatePlayer);
                $remainingBudget -= $candidatePlayer->now_cost;
                $remainingCount--;
            } else {
                // If can't find suitable player, pick cheapest available
                $cheapest = $sorted
                    ->whereNotIn('fpl_id', $selected->pluck('fpl_id'))
                    ->first();
                if ($cheapest) {
                    $selected->push($cheapest);
                    $remainingBudget -= $cheapest->now_cost;
                    $remainingCount--;
                }
            }
        }

        // Fill remaining slots with cheapest available if needed
        while ($selected->count() < $count) {
            $available = $sorted->whereNotIn('fpl_id', $selected->pluck('fpl_id'))->first();
            if ($available) {
                $selected->push($available);
            } else {
                break;
            }
        }

        return $selected->take($count);
    }

    public function viewSquad()
    {
        $user = Auth::user();

        // Get user's actual selected team
        $startingXI = $user->starting_xi ?? [];

        // Ensure it's always an array (handle both JSON string and array cases)
        if (is_string($startingXI)) {
            $startingXI = json_decode($startingXI, true) ?? [];
        }

        if (empty($startingXI)) {
            // If no team selected yet, redirect to pick team
            return redirect()->route('pick.team')->with('message', 'Please select your team first');
        }

        // Get the actual players from the starting XI
        $selectedPlayers = DB::table('players')
            ->join('teams', 'players.team_code', '=', 'teams.fpl_code')
            ->leftJoin('player_stats', function($join) {
                $join->on('players.fpl_id', '=', 'player_stats.player_id')
                     ->where('player_stats.gameweek', '=', 3);
            })
            ->select(
                'players.*',
                'teams.name as team_name',
                'teams.short_name as team_short',
                'teams.fpl_code as team_id',
                'player_stats.now_cost',
                'player_stats.total_points',
                'player_stats.selected_by_percent',
                'player_stats.form'
            )
            ->whereIn('players.fpl_id', $startingXI)
            ->get();

        // Add jersey URLs to each player
        foreach ($selectedPlayers as $player) {
            $player->jersey_url = $this->getJerseyUrl($player->team_id);
            $player->price = $player->now_cost ? $player->now_cost / 10 : 0; // Convert to proper price format
        }

        // Get substitute players (top players not in starting XI)
        $substituteGK = $this->getTopPlayersByPosition('Goalkeeper', 3)
            ->whereNotIn('fpl_id', $startingXI)->first();
        $substituteDEF = $this->getTopPlayersByPosition('Defender', 6)
            ->whereNotIn('fpl_id', $startingXI)->first();
        $substituteMID = $this->getTopPlayersByPosition('Midfielder', 6)
            ->whereNotIn('fpl_id', $startingXI)->first();
        $substituteFWD = $this->getTopPlayersByPosition('Forward', 4)
            ->whereNotIn('fpl_id', $startingXI)->first();

        // Group players by position for the squad view
        $startingPlayers = [
            'goalkeepers' => $selectedPlayers->where('position', 'Goalkeeper')->values(),
            'defenders' => $selectedPlayers->where('position', 'Defender')->values(),
            'midfielders' => $selectedPlayers->where('position', 'Midfielder')->values(),
            'forwards' => $selectedPlayers->where('position', 'Forward')->values()
        ];

        // Create full squad with substitutes at correct positions
        $allGoalkeepers = collect([$startingPlayers['goalkeepers']->first(), $substituteGK])->filter()->values();
        $allDefenders = $startingPlayers['defenders']->push($substituteDEF)->filter()->values();
        $allMidfielders = $startingPlayers['midfielders']->push($substituteMID)->filter()->values();
        $allForwards = $startingPlayers['forwards']->push($substituteFWD)->filter()->values();

        $squad = [
            'goalkeepers' => $allGoalkeepers,
            'defenders' => $allDefenders,
            'midfielders' => $allMidfielders,
            'forwards' => $allForwards
        ];

        // Get additional team data
        $teamData = [
            'captain_id' => $user->captain_id,
            'vice_captain_id' => $user->vice_captain_id,
            'formation' => $user->formation ?? '4-4-2',
            'active_chip' => $user->active_chip,
        ];

        return view('squad.view', compact('squad', 'user', 'teamData'));
    }

    private function getTopPlayersByPosition($position, $limit)
    {
        return DB::table('players')
            ->join('teams', 'players.team_code', '=', 'teams.fpl_code')
            ->leftJoin('player_stats', function($join) {
                $join->on('players.fpl_id', '=', 'player_stats.player_id')
                     ->where('player_stats.gameweek', '=', 3);
            })
            ->select(
                'players.*',
                'teams.name as team_name',
                'teams.short_name as team_short',
                'teams.fpl_code as team_id',
                'player_stats.now_cost',
                'player_stats.total_points',
                'player_stats.selected_by_percent',
                'player_stats.form'
            )
            ->where('players.position', $position)
            ->orderBy('player_stats.total_points', 'desc')
            ->limit($limit)
            ->get()
            ->map(function($player) {
                $player->price = ($player->now_cost ?? 50) / 10;
                $player->jersey_url = "https://fantasy.premierleague.com/dist/img/shirts/standard/shirt_{$player->team_id}-110.png";
                return $player;
            });
    }

    public function pickTeam()
    {
        $user = Auth::user();

        // Get user's starting XI
        $startingXI = $user->starting_xi ?? [];

        // Ensure it's always an array (handle both JSON string and array cases)
        if (is_string($startingXI)) {
            $startingXI = json_decode($startingXI, true) ?? [];
        }

        // If user has a saved squad, load those specific players
        if (!empty($startingXI)) {
            // Get the user's actual selected players
            $selectedPlayers = DB::table('players')
                ->join('teams', 'players.team_code', '=', 'teams.fpl_code')
                ->leftJoin('player_stats', function($join) {
                    $join->on('players.fpl_id', '=', 'player_stats.player_id')
                         ->where('player_stats.gameweek', '=', 3);
                })
                ->select(
                    'players.*',
                    'teams.name as team_name',
                    'teams.short_name as team_short',
                    'teams.fpl_code as team_id',
                    'player_stats.now_cost',
                    'player_stats.total_points',
                    'player_stats.selected_by_percent',
                    'player_stats.form'
                )
                ->whereIn('players.fpl_id', $startingXI)
                ->get();

            // Add jersey URLs and price formatting
            foreach ($selectedPlayers as $player) {
                $player->jersey_url = $this->getJerseyUrl($player->team_id);
                $player->price = $player->now_cost ? $player->now_cost / 10 : 0;
            }

            // Group selected players by position
            $squad = [
                'goalkeepers' => $selectedPlayers->where('position', 'Goalkeeper')->values(),
                'defenders' => $selectedPlayers->where('position', 'Defender')->values(),
                'midfielders' => $selectedPlayers->where('position', 'Midfielder')->values(),
                'forwards' => $selectedPlayers->where('position', 'Forward')->values()
            ];
        } else {
            // If no saved squad, load top players for selection
            $squad = [
                'goalkeepers' => $this->getTopPlayersByPosition('Goalkeeper', 2),
                'defenders' => $this->getTopPlayersByPosition('Defender', 5),
                'midfielders' => $this->getTopPlayersByPosition('Midfielder', 5),
                'forwards' => $this->getTopPlayersByPosition('Forward', 3)
            ];
        }

        $teamData = [
            'starting_xi' => $startingXI,
            'captain_id' => $user->captain_id,
            'vice_captain_id' => $user->vice_captain_id,
            'formation' => $user->formation ?? '4-4-2',
            'active_chip' => $user->active_chip,
            'used_chips' => $user->used_chips ?? [],
            'points' => $user->points ?? 0,
            'free_transfers' => $user->free_transfers ?? 1,
            'budget_remaining' => $user->budget_remaining ?? 1000,
        ];

        return view('pick-team', compact('squad', 'user', 'teamData'));
    }

    public function saveTeamSelection(Request $request)
    {
        $request->validate([
            'starting_xi' => 'required|array|size:11',
            'captain' => 'required',
            'vice_captain' => 'required',
            'formation' => 'required|string',
            'chip' => 'nullable|string',
        ]);

        $user = Auth::user();

        try {
            // Update user's team selection
            $user->update([
                'starting_xi' => $request->starting_xi,
                'captain_id' => $request->captain,
                'vice_captain_id' => $request->vice_captain,
                'formation' => $request->formation,
                'active_chip' => $request->chip,
                'squad_completed' => true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Team selection saved successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error saving team selection: ' . $e->getMessage()
            ], 500);
        }
    }

    public function showTransfers()
    {
        $user = Auth::user();

        // Get user's actual selected team (same as viewSquad method)
        $startingXI = $user->starting_xi ?? [];

        // Ensure it's always an array (handle both JSON string and array cases)
        if (is_string($startingXI)) {
            $startingXI = json_decode($startingXI, true) ?? [];
        }

        if (empty($startingXI)) {
            // If no team selected yet, redirect to pick team
            return redirect()->route('squad.selection')->with('message', 'Please select your team first');
        }

        // Get the actual players from the starting XI (same query as viewSquad)
        $selectedPlayers = DB::table('players')
            ->join('teams', 'players.team_code', '=', 'teams.fpl_code')
            ->leftJoin('player_stats', function($join) {
                $join->on('players.fpl_id', '=', 'player_stats.player_id')
                     ->where('player_stats.gameweek', '=', 3);
            })
            ->select(
                'players.*',
                'teams.name as team_name',
                'teams.short_name as team_short',
                'teams.fpl_code as team_id',
                'player_stats.now_cost',
                'player_stats.total_points',
                'player_stats.selected_by_percent',
                'player_stats.form'
            )
            ->whereIn('players.fpl_id', $startingXI)
            ->get();

        // Add jersey URLs and price formatting to each player
        foreach ($selectedPlayers as $player) {
            $player->jersey_url = $this->getJerseyUrl($player->team_id);
            $player->price = $player->now_cost ? $player->now_cost / 10 : 0; // Convert to proper price format
        }

        // Get substitute players (same logic as viewSquad)
        $substituteGK = $this->getTopPlayersByPosition('Goalkeeper', 3)
            ->whereNotIn('fpl_id', $startingXI)->first();
        $substituteDEF = $this->getTopPlayersByPosition('Defender', 6)
            ->whereNotIn('fpl_id', $startingXI)->first();
        $substituteMID = $this->getTopPlayersByPosition('Midfielder', 6)
            ->whereNotIn('fpl_id', $startingXI)->first();
        $substituteFWD = $this->getTopPlayersByPosition('Forward', 4)
            ->whereNotIn('fpl_id', $startingXI)->first();

        // Group players by position for the squad view (same as viewSquad)
        $startingPlayers = [
            'goalkeepers' => $selectedPlayers->where('position', 'Goalkeeper')->values(),
            'defenders' => $selectedPlayers->where('position', 'Defender')->values(),
            'midfielders' => $selectedPlayers->where('position', 'Midfielder')->values(),
            'forwards' => $selectedPlayers->where('position', 'Forward')->values()
        ];

        // Create full squad with substitutes at correct positions (same as viewSquad)
        $allGoalkeepers = collect([$startingPlayers['goalkeepers']->first(), $substituteGK])->filter()->values();
        $allDefenders = $startingPlayers['defenders']->push($substituteDEF)->filter()->values();
        $allMidfielders = $startingPlayers['midfielders']->push($substituteMID)->filter()->values();
        $allForwards = $startingPlayers['forwards']->push($substituteFWD)->filter()->values();

        $currentSquad = [
            'goalkeepers' => $allGoalkeepers,
            'defenders' => $allDefenders,
            'midfielders' => $allMidfielders,
            'forwards' => $allForwards
        ];

        // Get all available players for transfers (remove the ->take(10) limit)
        $allPlayers = [
            'Goalkeeper' => $this->getPlayersByPosition('Goalkeeper'),
            'Defender' => $this->getPlayersByPosition('Defender'),
            'Midfielder' => $this->getPlayersByPosition('Midfielder'),
            'Forward' => $this->getPlayersByPosition('Forward')
        ];

        // Calculate transfer data
        $transferData = [
            'free_transfers' => $user->free_transfers ?? 1,
            'budget_remaining' => ($user->budget_remaining ?? 0) / 10, // Convert to pounds
            'gameweek' => $this->getCurrentGameweek(),
            'transfers_made' => 0,
            'point_penalty' => 0,
            'total_cost' => 0
        ];

        // Additional data for the view
        $teamData = [
            'captain_id' => $user->captain_id,
            'vice_captain_id' => $user->vice_captain_id,
            'formation' => $user->formation ?? '4-4-2',
        ];

        return view('transfers.index', compact('currentSquad', 'allPlayers', 'transferData', 'user', 'teamData'));
    }

    public function makeTransfers(Request $request)
    {
        $user = Auth::user();

        $transfersOut = $request->input('transfers_out', []);
        $transfersIn = $request->input('transfers_in', []);
        $transferCount = count($transfersOut);

        // Validate transfers
        if ($transferCount === 0) {
            return response()->json([
                'success' => false,
                'message' => 'No transfers selected.'
            ]);
        }

        if (count($transfersOut) !== count($transfersIn)) {
            return response()->json([
                'success' => false,
                'message' => 'Number of players transferred in must equal number transferred out.'
            ]);
        }

        // Calculate transfer cost
        $freeTransfers = $user->free_transfers ?? 1;
        $extraTransfers = max(0, $transferCount - $freeTransfers);
        $pointPenalty = $extraTransfers * 4;

        // Get player prices for budget calculation
        $outPlayers = DB::table('players')
            ->join('player_stats', function($join) {
                $join->on('players.fpl_id', '=', 'player_stats.player_id')
                     ->where('player_stats.gameweek', '=', 3); // Current gameweek
            })
            ->whereIn('players.id', $transfersOut)
            ->select('players.id', 'player_stats.now_cost as price')
            ->get();

        $inPlayers = DB::table('players')
            ->join('player_stats', function($join) {
                $join->on('players.fpl_id', '=', 'player_stats.player_id')
                     ->where('player_stats.gameweek', '=', 3); // Current gameweek
            })
            ->whereIn('players.id', $transfersIn)
            ->select('players.id', 'player_stats.now_cost as price')
            ->get();

        $outValue = $outPlayers->sum('price');
        $inValue = $inPlayers->sum('price');
        $budgetChange = $outValue - $inValue;
        $newBudget = ($user->budget_remaining ?? 0) + $budgetChange;

        // Check budget constraints
        if ($newBudget < 0) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient budget for these transfers.'
            ]);
        }

        // Validate squad composition after transfers
        $currentSquad = $user->starting_xi ?? [];
        if (is_string($currentSquad)) {
            $currentSquad = json_decode($currentSquad, true) ?? [];
        }

        // Remove transferred out players and add transferred in players
        $newSquad = array_diff($currentSquad, $transfersOut);
        $newSquad = array_merge($newSquad, $transfersIn);

        // Check squad composition
        if (!$this->validateSquadComposition(array_values($newSquad))) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid squad composition after transfers.'
            ]);
        }

        try {
            // Apply transfers
            $user->update([
                'starting_xi' => array_values($newSquad),
                'budget_remaining' => $newBudget,
                'free_transfers' => max(0, $freeTransfers - $transferCount),
                'points' => ($user->points ?? 0) - $pointPenalty
            ]);

            return response()->json([
                'success' => true,
                'message' => "Transfers completed! {$transferCount} transfer(s) made.",
                'point_penalty' => $pointPenalty,
                'new_budget' => $newBudget / 10,
                'transfers_remaining' => max(0, $freeTransfers - $transferCount)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error processing transfers: ' . $e->getMessage()
            ], 500);
        }
    }

    public function resetTransfers()
    {
        // This would reset any pending transfers without saving them
        return response()->json([
            'success' => true,
            'message' => 'Transfers reset successfully.'
        ]);
    }

    private function getCurrentGameweek()
    {
        // For now, return a static gameweek. In a real app, this would be dynamic
        return 1;
    }

    private function validateSquadComposition($playerIds)
    {
        if (count($playerIds) !== 15) {
            return false;
        }

        $players = DB::table('players')->whereIn('id', $playerIds)->get()->groupBy('position');

        $gk = $players->get('Goalkeeper', collect())->count();
        $def = $players->get('Defender', collect())->count();
        $mid = $players->get('Midfielder', collect())->count();
        $fwd = $players->get('Forward', collect())->count();

        return $gk === 2 && $def === 5 && $mid === 5 && $fwd === 3;
    }
}
