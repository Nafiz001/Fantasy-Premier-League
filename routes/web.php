<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\FPLAnalysisController;
use App\Http\Controllers\FPLDataController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\SquadController;
use App\Http\Controllers\FixturesController;
use App\Http\Controllers\PointsController;
use App\Http\Controllers\LeagueController;

Route::get('/', function () {
    return view('welcome');
});

// Manual data refresh route (for development/admin use)
Route::get('/refresh-data', function () {
    try {
        // Run GitHub data seeder (GW2-38)
        Artisan::call('db:seed', [
            '--class' => 'FPLDataSeeder',
            '--force' => true
        ]);

        $output = Artisan::output();

        // Import GW1 data from FPL API
        $gw1Service = new \App\Services\FPLGameweek1Service();
        $gw1Service->importGameweek1Data();

        return response()->json([
            'success' => true,
            'message' => 'FPL data refreshed successfully! GitHub data (GW2-38) and FPL API data (GW1) imported.',
            'output' => $output
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error refreshing data: ' . $e->getMessage()
        ], 500);
    }
})->name('refresh.data');

// Manual gameweek status update route (for development/admin use)
Route::get('/update-gameweek-status', function () {
    try {
        Artisan::call('gameweek:update-status');

        return response()->json([
            'success' => true,
            'message' => 'Gameweek status updated successfully!',
            'output' => Artisan::output()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error updating gameweek status: ' . $e->getMessage()
        ], 500);
    }
})->name('update.gameweek.status');

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/signup', [AuthController::class, 'showRegistrationForm'])->name('signup');
Route::post('/signup', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Squad Selection Routes (for users who haven't selected a squad yet)
Route::middleware(['auth'])->group(function () {
    Route::get('/squad/selection', [SquadController::class, 'showSelection'])->name('squad.selection');
    Route::post('/squad/save', [SquadController::class, 'saveSquad'])->name('squad.save');
    Route::post('/squad/auto-pick', [SquadController::class, 'autoPickSquad'])->name('squad.auto-pick');
});

// Protected Routes (require completed squad selection)
Route::middleware(['auth', \App\Http\Middleware\CheckSquadSelection::class])->group(function () {

    Route::get('/dashboard', [SquadController::class, 'dashboard'])->name('dashboard');

    // Squad management routes
    Route::get('/squad/view', [SquadController::class, 'viewSquad'])->name('squad.view');
    Route::get('/pick-team', [SquadController::class, 'pickTeam'])->name('pick.team');
    Route::post('/pick-team/save', [SquadController::class, 'saveTeamSelection'])->name('pick.team.save');

    // Transfers routes
    Route::get('/transfers', [SquadController::class, 'showTransfers'])->name('transfers');
    Route::post('/transfers/make', [SquadController::class, 'makeTransfers'])->name('transfers.make');
    Route::post('/transfers/reset', [SquadController::class, 'resetTransfers'])->name('transfers.reset');

    // Fixtures routes
    Route::get('/fixtures/{gameweek?}', [App\Http\Controllers\FixturesController::class, 'index'])->name('fixtures')->where('gameweek', '[0-9]+');
    Route::post('/fixtures/import', [App\Http\Controllers\FixturesController::class, 'importFixtures'])->name('fixtures.import');

    // Points routes
    Route::get('/points/{gameweek?}', [PointsController::class, 'index'])->name('points')->where('gameweek', '[0-9]+');
    Route::get('/api/points/data', [PointsController::class, 'getPointsData'])->name('points.data');
    Route::get('/api/points/gameweek/{gameweekId}', [PointsController::class, 'getGameweekPoints'])->name('points.gameweek');

    // Leagues routes
    Route::prefix('leagues')->name('leagues.')->group(function () {
        Route::get('/', [LeagueController::class, 'index'])->name('index');
        Route::get('/create', [LeagueController::class, 'create'])->name('create');
        Route::post('/create', [LeagueController::class, 'store'])->name('store');
        Route::get('/join', [LeagueController::class, 'join'])->name('join');
        Route::post('/join', [LeagueController::class, 'joinWithCode'])->name('join-code');
        Route::get('/{league}', [LeagueController::class, 'show'])->name('show');
        Route::delete('/{league}/leave', [LeagueController::class, 'leave'])->name('leave');
        Route::delete('/{league}', [LeagueController::class, 'destroy'])->name('destroy');
        Route::get('/{league}/settings', [LeagueController::class, 'settings'])->name('settings');
        Route::put('/{league}/settings', [LeagueController::class, 'updateSettings'])->name('update-settings');
    });

    // FPL Data Management Routes
    Route::prefix('fpl/data')->name('fpl.data.')->group(function () {
        Route::get('/', [FPLDataController::class, 'index'])->name('dashboard');
        Route::post('/import/all', [FPLDataController::class, 'importAll'])->name('import.all');
        Route::post('/import/specific', [FPLDataController::class, 'importSpecific'])->name('import.specific');
        Route::post('/update/gameweek', [FPLDataController::class, 'updateGameweek'])->name('update.gameweek');
        Route::get('/status', [FPLDataController::class, 'checkStatus'])->name('status');
        Route::post('/command', [FPLDataController::class, 'runCommand'])->name('command');
        Route::get('/stats', [FPLDataController::class, 'getStats'])->name('stats');
    });

    // FPL Analysis Dashboard Routes
    Route::get('/fpl/dashboard', [FPLAnalysisController::class, 'dashboard'])->name('fpl.dashboard');

    // SQL Operations Demo Routes
    Route::post('/fpl/crud', [FPLAnalysisController::class, 'crudDemo'])->name('fpl.crud');
    Route::get('/fpl/joins', [FPLAnalysisController::class, 'joinQueries'])->name('fpl.joins');
    Route::get('/fpl/subqueries', [FPLAnalysisController::class, 'subqueriesAndWindows'])->name('fpl.subqueries');
    Route::get('/fpl/groupby', [FPLAnalysisController::class, 'groupByHaving'])->name('fpl.groupby');
    Route::get('/fpl/aggregates', [FPLAnalysisController::class, 'aggregateFunctions'])->name('fpl.aggregates');
    Route::post('/fpl/views', [FPLAnalysisController::class, 'manageViews'])->name('fpl.views');

    // Query Explorer
    Route::post('/fpl/query', [FPLAnalysisController::class, 'queryExplorer'])->name('fpl.query');
    Route::get('/fpl/schema', [FPLAnalysisController::class, 'schemaInfo'])->name('fpl.schema');
});

// Debug routes
Route::get('/debug/teams', function () {
    $teams = DB::table('teams')
        ->select('fpl_code', 'name', 'short_name')
        ->orderBy('fpl_code')
        ->get();

    return response()->json($teams);
});

Route::get('/debug/players', function () {
    $players = DB::table('players')
        ->join('teams', 'players.team_code', '=', 'teams.fpl_code')
        ->select('players.web_name', 'players.position', 'teams.name as team_name', 'teams.fpl_code')
        ->limit(20)
        ->get();

    return response()->json($players);
});
