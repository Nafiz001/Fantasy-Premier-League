<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\FPLAnalysisController;
use App\Http\Controllers\FPLDataController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\SquadController;

Route::get('/', function () {
    return view('welcome');
});

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
});

// Protected Routes (require completed squad selection)
Route::middleware(['auth', \App\Http\Middleware\CheckSquadSelection::class])->group(function () {
    
    Route::get('/dashboard', [SquadController::class, 'dashboard'])->name('dashboard');
    
    // Squad management routes
    Route::get('/squad/view', [SquadController::class, 'viewSquad'])->name('squad.view');
    Route::get('/pick-team', [SquadController::class, 'pickTeam'])->name('pick.team');
    Route::post('/pick-team/save', [SquadController::class, 'saveTeamSelection'])->name('pick.team.save');

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

    // Advanced Analysis Routes
    Route::get('/fpl/captains/{gameweek?}', [FPLAnalysisController::class, 'captainRecommendations'])->name('fpl.captains');
    Route::get('/fpl/differentials', [FPLAnalysisController::class, 'differentialPlayers'])->name('fpl.differentials');
    Route::get('/fpl/clean-sheets/{gameweek?}', [FPLAnalysisController::class, 'cleanSheetProbabilities'])->name('fpl.clean-sheets');
    Route::get('/fpl/transfers', [FPLAnalysisController::class, 'transferRecommendations'])->name('fpl.transfers');
    Route::get('/fpl/fixtures', [FPLAnalysisController::class, 'fixtureDifficulty'])->name('fpl.fixtures');

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
