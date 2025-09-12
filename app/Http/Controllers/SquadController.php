<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SquadController extends Controller
{
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
        $teamData = [
            'starting_xi' => $user->starting_xi ?? [],
            'captain_id' => $user->captain_id,
            'vice_captain_id' => $user->vice_captain_id,
            'formation' => $user->formation ?? '4-4-2',
            'active_chip' => $user->active_chip,
            'used_chips' => $user->used_chips ?? [],
            'points' => $user->points ?? 0,
            'free_transfers' => $user->free_transfers ?? 1,
            'budget_remaining' => $user->budget_remaining ?? 1000,
        ];

        return view('dashboard', compact('user', 'teamData'));
    }

    private function getPlayersByPosition($position)
    {
        return DB::table('players')
            ->join('teams', 'players.team_code', '=', 'teams.fpl_code')
            ->leftJoin('player_stats', function($join) {
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
        // Official FPL jersey URLs
        return "https://fantasy.premierleague.com/dist/img/shirts/standard/shirt_{$teamId}-110.png";
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

    public function viewSquad()
    {
        $user = Auth::user();
        
        // Get user's actual selected team
        $startingXI = $user->starting_xi ?? [];
        
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
        
        // Get user's full squad
        $squad = [
            'goalkeepers' => $this->getTopPlayersByPosition('Goalkeeper', 2),
            'defenders' => $this->getTopPlayersByPosition('Defender', 5),
            'midfielders' => $this->getTopPlayersByPosition('Midfielder', 5),
            'forwards' => $this->getTopPlayersByPosition('Forward', 3)
        ];

        // Get user's current team data
        $teamData = [
            'starting_xi' => $user->starting_xi ?? [],
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
}
