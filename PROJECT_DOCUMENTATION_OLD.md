# Fantasy Premier League Project Documentation

## Project Overview

This is a comprehensive Fantasy Premier League (FPL) clone built with Laravel 12.x, featuring user authentication, squad management, live FPL data integration, points calculation, and league system.

### Key Features

- 🔐 **User Authentication**: Register, login, and manage accounts
- ⚽ **Squad Selection**: Build teams with 15 players and £100M budget
- 🎯 **Auto-Pick**: AI-powered team selection based on player stats
- 📊 **Live FPL Data**: Real-time player stats, fixtures, and gameweek data
- 💯 **Points Calculation**: Accurate 2025/26 FPL scoring system
- 🏆 **League System**: Create and join classic leagues with leaderboards
- 📈 **Gameweek Navigation**: View points breakdown across all gameweeks
- 🎨 **Responsive Design**: Beautiful UI that works on all devices

## Architecture (MVC Pattern)

The project follows Laravel's Model-View-Controller architecture:

### Models (app/Models/)

Models represent database tables and handle data logic:

**User Model** (`app/Models/User.php`)
- **Properties**: `$fillable`, `$hidden`, `$casts`
- **Relationships**: `leagues()`, `adminLeagues()`, `leagueMembers()`
- **Usage**: User authentication, squad management, league participation

**Player Model** (`app/Models/Player.php`)
- **Relationships**: `team()`, `playerStats()`, `gameweekStats()`
- **Usage**: Player data management, statistics tracking

**Team Model** (`app/Models/Team.php`)
- **Relationships**: `players()`, `fixtures()`
- **Usage**: Team information, fixtures management

**Gameweek Model** (`app/Models/Gameweek.php`)
- **Properties**: Gameweek status flags (`finished`, `is_current`, `is_next`)
- **Usage**: Gameweek progression tracking

**League Model** (`app/Models/League.php`)
- **Relationships**: `admin()`, `members()`, `leagueMembers()`
- **Usage**: League creation and management

### Controllers (app/Http/Controllers/)

Controllers handle HTTP requests and business logic:

**SquadController** (`app/Http/Controllers/SquadController.php`)
- `showSelection()` - Display player selection interface
- `saveSquad()` - Save user's selected squad
- `autoPickSquad()` - AI-powered squad selection
- `dashboard()` - Show user dashboard with squad and points
- `viewSquad()` - Display current squad
- `pickTeam()` - Show team selection interface
- `saveTeamSelection()` - Save starting 11 and captain selections

**AuthController** (`app/Http/Controllers/Auth/AuthController.php`)
- `showLoginForm()` - Display login form
- `login()` - Process user login
- `showRegistrationForm()` - Display registration form
- `register()` - Process user registration
- `logout()` - Process user logout

**LeagueController** (`app/Http/Controllers/LeagueController.php`)
- `index()` - List user's leagues
- `create()` - Show league creation form
- `store()` - Create new league
- `show()` - Display league details and standings
- `join()` - Show league join form
- `joinWithCode()` - Join league with code
- `leave()` - Leave league
- `destroy()` - Delete league (admin only)

**PointsController** (`app/Http/Controllers/PointsController.php`)
- `index()` - Show points for specific gameweek
- `getPointsData()` - AJAX endpoint for points data
- `getGameweekPoints()` - Get points for specific gameweek

**FixturesController** (`app/Http/Controllers/FixturesController.php`)
- `index()` - Show fixtures for specific gameweek
- `importFixtures()` - Import fixture data from FPL API

### Views (resources/views/)

Views are Blade templates that render the UI:

**Layout Templates**
- `welcome.blade.php` - Landing page with FPL branding
- `dashboard.blade.php` - User dashboard with squad overview

**Authentication Views** (`resources/views/auth/`)
- `login.blade.php` - User login form
- `register.blade.php` - User registration form

**Squad Management** (`resources/views/squad/`)
- `selection.blade.php` - Player selection interface
- `view.blade.php` - Current squad display

**League System** (`resources/views/leagues/`)
- `index.blade.php` - List of user's leagues
- `create.blade.php` - League creation form
- `show.blade.php` - League details and standings
- `settings.blade.php` - League admin settings

**Points & Fixtures** (`resources/views/points/`, `resources/views/fixtures/`)
- `index.blade.php` - Points/fixtures display by gameweek

**Shared Components** (`resources/views/partials/`)
- Navigation, modals, and reusable UI components

## Database Schema & Migrations

### Core Tables Migration

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Teams table
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->integer('fpl_code')->unique();
            $table->integer('fpl_id')->unique();
            $table->string('name');
            $table->string('short_name');
            $table->integer('strength');
            $table->integer('strength_overall_home');
            $table->integer('strength_overall_away');
            $table->integer('strength_attack_home');
            $table->integer('strength_attack_away');
            $table->integer('strength_defence_home');
            $table->integer('strength_defence_away');
            $table->integer('pulse_id')->nullable();
            $table->decimal('elo', 8, 2)->nullable();
            $table->timestamps();

            $table->index(['fpl_id', 'fpl_code']);
        });

        // Players table
        Schema::create('players', function (Blueprint $table) {
            $table->id();
            $table->integer('fpl_code')->unique();
            $table->integer('fpl_id')->unique();
            $table->string('first_name');
            $table->string('second_name');
            $table->string('web_name');
            $table->integer('team_code');
            $table->string('position');
            $table->integer('element_type');
            $table->timestamps();

            $table->foreign('team_code')->references('fpl_code')->on('teams');
            $table->index(['fpl_id', 'element_type', 'team_code']);
        });

        // Gameweeks table
        Schema::create('gameweeks', function (Blueprint $table) {
            $table->id();
            $table->integer('gameweek_id')->unique();
            $table->string('name');
            $table->dateTime('deadline_time');
            $table->boolean('finished')->default(false);
            $table->boolean('is_current')->default(false);
            $table->boolean('is_next')->default(false);
            $table->timestamps();
        });

        // And more tables for fixtures, player stats, etc.
    }

    public function down()
    {
        Schema::dropIfExists('players');
        Schema::dropIfExists('teams');
        Schema::dropIfExists('gameweeks');
    }
};
```

### Middleware
```

### SQL History Table (Admin Panel)

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sql_histories', function (Blueprint $table) {
            $table->id();
            $table->string('operation_type'); // DDL, DML, SELECT, etc.
            $table->text('sql_query');
            $table->json('parameters')->nullable();
            $table->integer('execution_time_ms')->nullable();
            $table->integer('rows_affected')->nullable();
            $table->text('error_message')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();

            $table->index(['operation_type', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('sql_histories');
    }
};
```

## CRUD Operations

### Create (Insert)

```php
// In controller
public function store(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users',
        'password' => 'required|min:8',
    ]);

    $user = User::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'password' => Hash::make($validated['password']),
        'has_selected_squad' => false,
        'budget_remaining' => 1000.0,
        'points' => 0,
    ]);

    return redirect()->route('dashboard')->with('success', 'Account created successfully!');
}
```

## Middleware

Middleware provides filtering logic for HTTP requests:

```php
<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckSquadSelection
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && !Auth::user()->has_selected_squad) {
            // User is logged in but hasn't selected a squad
            if (!$request->routeIs('squad.*') && !$request->routeIs('pick.team') && !$request->routeIs('logout')) {
                return redirect()->route('pick.team');
            }
        }

        return $next($request);
    }
}
```

### Middleware Registration (bootstrap/app.php)

```php
<?php
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'check.squad' => \App\Http\Middleware\CheckSquadSelection::class,
            'auto.seed' => \App\Http\Middleware\AutoSeedFPLData::class,
            'auto.update' => \App\Http\Middleware\AutoUpdateGameweekStatus::class,
        ]);
    })
    ->create();
```

## Services

Services contain business logic and reusable functionality:

```php
<?php
namespace App\Services;

use App\Models\Gameweek;
use App\Models\PlayerStat;
use App\Models\Player;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class FPLPointsService
{
    /**
     * Get the most recent finished gameweek
     */
    public function getLatestFinishedGameweekId()
    {
        $gameweek = Gameweek::where('finished', true)
            ->orderBy('deadline_time', 'desc')
            ->first();

        return $gameweek ? $gameweek->gameweek_id : null;
    }

    /**
     * Calculate FPL points for a player in a specific gameweek
     */
    public function calculatePlayerPoints($playerId, $gameweekId)
    {
        $stat = DB::table('player_gameweek_stats')
            ->where('player_id', $playerId)
            ->where('gameweek', $gameweekId)
            ->first();

        if (!$stat) {
            return 0;
        }

        // Points calculation logic
        $points = 0;

        // Minutes played
        if ($stat->minutes >= 60) $points += 2;
        elseif ($stat->minutes >= 1) $points += 1;

        // Goals
        if ($stat->position === 'Goalkeeper' || $stat->position === 'Defender') {
            $points += $stat->goals_scored * 6;
        } elseif ($stat->position === 'Midfielder') {
            $points += $stat->goals_scored * 5;
        } elseif ($stat->position === 'Forward') {
            $points += $stat->goals_scored * 4;
        }

        // Assists
        $points += $stat->assists * 3;

        // Clean sheets
        if (($stat->position === 'Goalkeeper' || $stat->position === 'Defender') && $stat->clean_sheets) {
            $points += $stat->position === 'Goalkeeper' ? 4 : 2;
        }

        // And more scoring rules...

        return $points;
    }

    /**
     * Get squad points for a specific gameweek
     */
    public function getSquadPointsForGameweek($userId, $gameweekId)
    {
        $user = User::find($userId);
        $startingXi = $user->starting_xi ?? [];
        $captainId = $user->captain_id;
        $viceCaptainId = $user->vice_captain_id;

        $totalPoints = 0;
        $captainPoints = 0;
        $viceCaptainPoints = 0;

        foreach ($startingXi as $playerId) {
            $playerPoints = $this->calculatePlayerPoints($playerId, $gameweekId);

            if ($playerId == $captainId) {
                $captainPoints = $playerPoints * 2;
                $totalPoints += $captainPoints;
            } elseif ($playerId == $viceCaptainId) {
                $viceCaptainPoints = $playerPoints;
                $totalPoints += $viceCaptainPoints;
            } else {
                $totalPoints += $playerPoints;
            }
        }

        return [
            'total_points' => $totalPoints,
            'captain_points' => $captainPoints,
            'vice_captain_points' => $viceCaptainPoints,
            'gameweek' => $gameweekId
        ];
    }
}
```

## Routes

### Web Routes (routes/web.php)

```php
<?php
use Illuminate\Support\Facades\Route;
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

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::login']);
Route::get('/signup', [AuthController::class, 'showRegistrationForm'])->name('signup');
Route::post('/signup', [AuthController::register]);
Route::post('/logout', [AuthController::logout])->name('logout');

// Squad Selection Routes
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

    // Fixtures routes
    Route::get('/fixtures/{gameweek?}', [FixturesController::class, 'index'])->name('fixtures');

    // Points routes
    Route::get('/points/{gameweek?}', [PointsController::class, 'index'])->name('points');
    Route::get('/api/points/data', [PointsController::class, 'getPointsData'])->name('points.data');

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
    Route::get('/fpl/captains/{gameweek?}', [FPLAnalysisController::class, 'captainRecommendations'])->name('fpl.captains');
    Route::get('/fpl/differentials', [FPLAnalysisController::class, 'differentialPlayers'])->name('fpl.differentials');
    Route::get('/fpl/clean-sheets/{gameweek?}', [FPLAnalysisController::class, 'cleanSheetProbabilities'])->name('fpl.clean-sheets');
    Route::get('/fpl/transfers', [FPLAnalysisController::class, 'transferRecommendations'])->name('fpl.transfers');
    Route::get('/fpl/fixtures', [FPLAnalysisController::class, 'fixtureDifficulty'])->name('fpl.fixtures');
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
        ->select('players.web_name', 'players.position', 'teams.name as team_name')
        ->limit(20)
        ->get();
    return response()->json($players);
});
```

## Application Workflow

### User Registration & Authentication Flow

```php
<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SQLExecutorService
{
    /**
     * Execute SQL query with logging
     */
    public function executeSQL(string $sql, array $params = [], string $operationType = 'UNKNOWN')
    {
        $startTime = microtime(true);

        try {
            // Execute the query
            if (strtoupper(substr(trim($sql), 0, 6)) === 'SELECT') {
                $result = DB::select($sql, $params);
                $rowsAffected = count($result);
            } else {
                $rowsAffected = DB::statement($sql, $params);
                $result = null;
            }

            $executionTime = (microtime(true) - $startTime) * 1000; // Convert to milliseconds

            // Log successful execution
            DB::table('sql_histories')->insert([
                'operation_type' => $operationType,
                'sql_query' => $sql,
                'parameters' => json_encode($params),
                'execution_time_ms' => round($executionTime),
                'rows_affected' => $rowsAffected,
                'error_message' => null,
                'ip_address' => request()->ip(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return [
                'success' => true,
                'data' => $result,
                'rows_affected' => $rowsAffected,
                'execution_time' => round($executionTime, 2),
            ];

        } catch (\Exception $e) {
            $executionTime = (microtime(true) - $startTime) * 1000;

            // Log failed execution
            DB::table('sql_histories')->insert([
                'operation_type' => $operationType,
                'sql_query' => $sql,
                'parameters' => json_encode($params),
                'execution_time_ms' => round($executionTime),
                'rows_affected' => null,
                'error_message' => $e->getMessage(),
                'ip_address' => request()->ip(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            Log::error('SQL Execution Error: ' . $e->getMessage(), [
                'sql' => $sql,
                'params' => $params,
                'operation_type' => $operationType,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'execution_time' => round($executionTime, 2),
            ];
        }
    }

    /**
     * Get database statistics
     */
    public function getDatabaseStats()
    {
        $databaseName = DB::getDatabaseName();

        // Get table count
        $tableCount = DB::select("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = ?", [$databaseName])[0]->count;

        // Get total records
        $tables = DB::select("SHOW TABLES");
        $totalRecords = 0;
        foreach ($tables as $table) {
            $tableName = $table->{'Tables_in_' . $databaseName};
            $count = DB::table($tableName)->count();
            $totalRecords += $count;
        }

        // Get database size
        $sizeResult = DB::select("
            SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) as size_mb
            FROM information_schema.tables
            WHERE table_schema = ?
        ", [$databaseName]);

        $databaseSize = $sizeResult[0]->size_mb ?? 0;

        return [
            'table_count' => $tableCount,
            'total_records' => $totalRecords,
            'database_size_mb' => $databaseSize,
        ];
    }
}
```

### SQLHistory Model

```php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SQLHistory extends Model
{
    use HasFactory;

    protected $table = 'sql_histories'; // Explicit table name to avoid pluralization issues

    protected $fillable = [
        'operation_type',
        'sql_query',
        'parameters',
        'execution_time_ms',
        'rows_affected',
        'error_message',
        'ip_address',
    ];

    protected $casts = [
        'parameters' => 'array',
        'execution_time_ms' => 'integer',
        'rows_affected' => 'integer',
    ];

    /**
     * Scope for filtering by operation type
     */
    public function scopeByOperationType($query, $type)
    {
        return $query->where('operation_type', $type);
    }

    /**
     * Scope for successful queries only
     */
    public function scopeSuccessful($query)
    {
        return $query->whereNull('error_message');
    }

    /**
     * Scope for failed queries only
     */
    public function scopeFailed($query)
    {
        return $query->whereNotNull('error_message');
    }
}
```

## Security Features
- **Database Indexing**: Strategic indexes on frequently queried columns
    <style>
        .sql-query {
            font-family: 'Courier New', monospace;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            padding: 1rem;
        }
        .sidebar {
            min-height: 100vh;
            background-color: #343a40;
        }
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.75);
        }
        .sidebar .nav-link:hover {
            color: #fff;
        }
        .sidebar .nav-link.active {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.1);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-2 sidebar p-3">
                <h5 class="text-white mb-4">Database Admin</h5>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="collapse" href="#ddlMenu" role="button">
                            <i class="fas fa-database me-2"></i>DDL Operations
                        </a>
                        <div class="collapse" id="ddlMenu">
                            <ul class="nav flex-column ms-3">
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('admin.create-table') ? 'active' : '' }}" href="{{ route('admin.create-table') }}">
                                        Create Table
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('admin.alter-table') ? 'active' : '' }}" href="{{ route('admin.alter-table') }}">
                                        Alter Table
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('admin.drop-table') ? 'active' : '' }}" href="{{ route('admin.drop-table') }}">
                                        Drop Table
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="collapse" href="#dmlMenu" role="button">
                            <i class="fas fa-edit me-2"></i>DML Operations
                        </a>
                        <div class="collapse" id="dmlMenu">
                            <ul class="nav flex-column ms-3">
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('admin.insert') ? 'active' : '' }}" href="{{ route('admin.insert') }}">
                                        Insert Data
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('admin.update') ? 'active' : '' }}" href="{{ route('admin.update') }}">
                                        Update Data
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('admin.delete') ? 'active' : '' }}" href="{{ route('admin.delete') }}">
                                        Delete Data
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="collapse" href="#queryMenu" role="button">
                            <i class="fas fa-search me-2"></i>Query Interface
                        </a>
                        <div class="collapse" id="queryMenu">
                            <ul class="nav flex-column ms-3">
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('admin.select') ? 'active' : '' }}" href="{{ route('admin.select') }}">
                                        SELECT Queries
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('admin.joins') ? 'active' : '' }}" href="{{ route('admin.joins') }}">
                                        JOIN Queries
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('admin.aggregates') ? 'active' : '' }}" href="{{ route('admin.aggregates') }}">
                                        Aggregates
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('admin.subqueries') ? 'active' : '' }}" href="{{ route('admin.subqueries') }}">
                                        Subqueries
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.history') ? 'active' : '' }}" href="{{ route('admin.history') }}">
                            <i class="fas fa-history me-2"></i>SQL History
                        </a>
                    </li>
                </ul>
            </nav>

            <!-- Main content -->
            <main class="col-md-10 p-4">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Prism.js for syntax highlighting -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/prism.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-sql.min.js"></script>

    @yield('scripts')
</body>
</html>
```

#### Dashboard View (resources/views/admin/dashboard.blade.php)

```blade
@extends('admin.layout')

@section('title', 'Dashboard')

@section('content')
<div class="row">
    <div class="col-md-12">
        <h1 class="mb-4">Database Administration Panel</h1>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h5 class="card-title">Tables</h5>
                <h2>{{ $tableCount }}</h2>
                <small>Total tables in database</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h5 class="card-title">Records</h5>
                <h2>{{ number_format($totalRecords) }}</h2>
                <small>Total records across all tables</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h5 class="card-title">Database Size</h5>
                <h2>{{ number_format($databaseSize, 2) }} MB</h2>
                <small>Current database size</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <h5 class="card-title">Queries Today</h5>
                <h2>{{ $recentQueries->where('created_at', '>=', today())->count() }}</h2>
                <small>SQL queries executed today</small>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5>Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <a href="{{ route('admin.create-table') }}" class="btn btn-primary btn-lg w-100 mb-2">
                            <i class="fas fa-plus-circle me-2"></i>Create Table
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('admin.insert') }}" class="btn btn-success btn-lg w-100 mb-2">
                            <i class="fas fa-plus me-2"></i>Insert Data
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('admin.select') }}" class="btn btn-info btn-lg w-100 mb-2">
                            <i class="fas fa-search me-2"></i>Query Data
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('admin.history') }}" class="btn btn-secondary btn-lg w-100 mb-2">
                            <i class="fas fa-history me-2"></i>View History
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent SQL History -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>Recent SQL Operations</h5>
                <a href="{{ route('admin.history') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                @if($recentQueries->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Operation</th>
                                    <th>Query</th>
                                    <th>Status</th>
                                    <th>Execution Time</th>
                                    <th>Executed At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentQueries as $query)
                                    <tr>
                                        <td>
                                            <span class="badge bg-{{ $query->operation_type === 'DDL' ? 'primary' : ($query->operation_type === 'DML' ? 'success' : 'info') }}">
                                                {{ $query->operation_type }}
                                            </span>
                                        </td>
                                        <td>
                                            <code class="text-truncate d-inline-block" style="max-width: 300px;" title="{{ $query->sql_query }}">
                                                {{ Str::limit($query->sql_query, 50) }}
                                            </code>
                                        </td>
                                        <td>
                                            @if($query->error_message)
                                                <span class="badge bg-danger">Failed</span>
                                            @else
                                                <span class="badge bg-success">Success</span>
                                            @endif
                                        </td>
                                        <td>{{ $query->execution_time_ms }}ms</td>
                                        <td>{{ $query->created_at->diffForHumans() }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted mb-0">No SQL operations have been executed yet.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
```

## Application Workflow

### User Registration & Authentication Flow

1. **User visits homepage** (`/`)
   - Sees welcome page with FPL branding
   - Can register or login

2. **Registration Process** (`/signup`)
   - User fills registration form
   - Validates input (name, email, password)
   - Creates user account with default values
   - Redirects to squad selection

3. **Login Process** (`/login`)
   - User enters credentials
   - Authenticates against database
   - Redirects based on squad selection status

### Squad Selection & Management Flow

1. **Squad Selection** (`/squad/selection`)
   - Middleware checks if user has selected squad
   - Displays available players grouped by position
   - User selects 15 players within £100M budget
   - Validates team composition rules

2. **Auto-Pick Feature** (`/squad/auto-pick`)
   - AI algorithm selects optimal squad
   - Considers player form, fixtures, and statistics
   - Ensures budget and position constraints

3. **Team Management** (`/pick-team`)
   - User selects starting 11 from squad
   - Chooses captain and vice-captain
   - Sets formation

### Gameweek Processing Flow

1. **Points Calculation**
   - FPLPointsService calculates player points
   - Considers minutes played, goals, assists, clean sheets
   - Applies captain/vice-captain multipliers

2. **League Updates**
   - Updates user total points
   - Recalculates league standings
   - Updates league member rankings

### Database Administration Workflow

1. **Admin Dashboard** (`/admin/dashboard`)
   - Shows database statistics
   - Displays recent SQL operations
   - Provides quick action buttons

2. **DDL Operations**
   - Create Table: Dynamic form for column definition
   - Alter Table: Modify existing table structure
   - Drop Table: Remove tables with confirmation

3. **DML Operations**
   - Insert: Form-based data insertion
   - Update: Modify existing records
   - Delete: Remove records with confirmation

4. **Query Interface**
   - SELECT queries with table selection
   - JOIN operations with visual builders
   - Aggregate functions and grouping
   - Subquery construction

5. **SQL History Tracking**
   - Logs all operations with execution time
   - Tracks success/failure status
   - Stores query parameters and results

## Installation & Setup

### Prerequisites

- PHP 8.2 or higher
- Composer
- Node.js and NPM
- MySQL 8.0 or PostgreSQL
- Git

### Installation Steps

1. **Clone Repository**
   ```bash
   git clone https://github.com/Nafiz001/Fantasy-Premier-League.git
   cd fantasy-premier-league
   ```

2. **Install Dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Environment Configuration**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database Setup**
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=fpl
   DB_USERNAME=root
   DB_PASSWORD=your_password
   ```

5. **Run Migrations**
   ```bash
   php artisan migrate
   ```

6. **Import FPL Data** (Optional)
   ```bash
   php artisan fpl:import-all
   ```

7. **Build Assets**
   ```bash
   npm run dev
   ```

8. **Start Development Server**
   ```bash
   php artisan serve
   ```

### Admin Panel Setup

1. **Create Admin Database**
   ```sql
   CREATE DATABASE fpl_admin_panel;
   ```

2. **Update Environment**
   ```env
   DB_DATABASE=fpl_admin_panel
   ```

3. **Run Admin Migrations**
   ```bash
   php artisan migrate
   ```

4. **Access Admin Panel**
   - Visit `http://localhost:8001/admin/dashboard`
   - Use the navigation to access different features

## Key Technologies & Libraries

- **Laravel 12.x**: PHP framework for MVC architecture
- **Bootstrap 5**: Responsive frontend framework
- **MySQL/PostgreSQL**: Database systems
- **Eloquent ORM**: Database abstraction layer
- **Blade Templates**: Server-side templating
- **Prism.js**: SQL syntax highlighting
- **Font Awesome**: Icon library
- **Tailwind CSS**: Utility-first CSS framework

## Security Features

- **CSRF Protection**: All forms protected against cross-site request forgery
- **Input Validation**: Comprehensive validation using Laravel's validation rules
- **SQL Injection Prevention**: Parameterized queries and Eloquent ORM
- **Authentication Middleware**: Route protection for authenticated users
- **Squad Selection Middleware**: Ensures users complete squad setup
- **Authorization Checks**: League admin permissions and ownership validation

## Performance Optimizations

- **Database Indexing**: Strategic indexes on frequently queried columns
- **Eager Loading**: N+1 query prevention with Eloquent relationships
- **Caching**: Laravel's caching system for expensive operations
- **Pagination**: Efficient data loading for large datasets
- **Query Optimization**: Efficient SQL queries with proper joins and selects

This documentation provides a comprehensive overview of the Fantasy Premier League project, covering the main application features with detailed code examples and workflow explanations.</content>
<parameter name="filePath">c:\xampp\htdocs\fantasy-premier-league\PROJECT_DOCUMENTATION.md
