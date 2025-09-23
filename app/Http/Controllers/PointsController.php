<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\FPLPointsService;

class PointsController extends Controller
{
    protected $pointsService;

    public function __construct(FPLPointsService $pointsService)
    {
        $this->pointsService = $pointsService;
    }

    /**
     * Display the points page with current squad points
     */
    public function index(Request $request, $gameweek = null)
    {
        $user = Auth::user();

        // Get gameweek from route parameter, request query, or use latest finished gameweek
        $gameweekId = $gameweek ?: $request->get('gameweek');
        if (!$gameweekId) {
            $gameweekId = $this->pointsService->getLatestFinishedGameweekId();
        }

        if (!$gameweekId) {
            return view('points.index', [
                'error' => 'No finished gameweeks available',
                'user' => $user
            ]);
        }

        // Get squad points for the specified gameweek
        $squadPoints = $this->pointsService->getSquadPointsForGameweek($user->id, $gameweekId);

        // Get gameweek info
        $currentGameweek = $this->pointsService->getGameweekStatistics($gameweekId);

        // Get navigation info (previous/next gameweeks)
        // Allow navigation to finished gameweeks and current gameweek
        $previousGameweek = \App\Models\Gameweek::where(function($query) {
                $query->where('finished', true)->orWhere('is_current', true);
            })
            ->where('gameweek_id', '<', $gameweekId)
            ->orderBy('gameweek_id', 'desc')
            ->first();

        $nextGameweek = \App\Models\Gameweek::where(function($query) {
                $query->where('finished', true)->orWhere('is_current', true);
            })
            ->where('gameweek_id', '>', $gameweekId)
            ->orderBy('gameweek_id', 'asc')
            ->first();

        return view('points.index', compact(
            'squadPoints',
            'currentGameweek',
            'previousGameweek',
            'nextGameweek',
            'gameweekId',
            'user'
        ));
    }

    /**
     * Get points data as JSON (for AJAX requests)
     */
    public function getPointsData()
    {
        $user = Auth::user();

        $squadPoints = $this->pointsService->getSquadPoints($user->id);
        $pointsHistory = $this->pointsService->getUserPointsHistory($user->id, 10);
        $gameweekStats = $this->pointsService->getGameweekStatistics();

        return response()->json([
            'squad_points' => $squadPoints,
            'points_history' => $pointsHistory,
            'gameweek_stats' => $gameweekStats
        ]);
    }

    /**
     * Get points for a specific gameweek
     */
    public function getGameweekPoints($gameweekId)
    {
        $user = Auth::user();

        $squadPoints = $this->pointsService->getSquadPointsForGameweek($user->id, $gameweekId);
        $gameweekStats = $this->pointsService->getGameweekStatistics($gameweekId);

        return response()->json([
            'squad_points' => $squadPoints,
            'gameweek_stats' => $gameweekStats,
            'gameweek_id' => $gameweekId
        ]);
    }
}
