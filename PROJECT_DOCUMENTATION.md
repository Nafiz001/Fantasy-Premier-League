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
- **Relationships**: `leagues()`, `leagueMembers()`
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
- **Relationships**: `members()`, `leagueMembers()`
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

## Application Workflow

### User Registration & Authentication Flow

1. **Registration**: User submits registration form with name, email, password
2. **Validation**: Server validates input data and checks for uniqueness
3. **Account Creation**: User record created with default squad settings
4. **Email Verification**: (Optional) Send verification email
5. **Auto-Login**: User automatically logged in after registration
6. **Squad Selection**: Redirect to squad selection if not completed

### Squad Selection & Management Flow

1. **Player Selection**: User selects 15 players within £100M budget
2. **Position Limits**: Enforce FPL position constraints (2 GK, 5 DEF, 5 MID, 3 FWD)
3. **Budget Validation**: Ensure total cost doesn't exceed budget
4. **Team Validation**: Check for duplicate players, valid formations
5. **Save Squad**: Persist squad to database with user association
6. **Captain Selection**: Allow selection of captain and vice-captain

### Points Calculation Flow

1. **Gameweek Processing**: System processes completed gameweeks
2. **Player Stats Import**: Import player statistics from FPL API
3. **Points Calculation**: Calculate points based on FPL scoring rules
4. **Bonus Points**: Apply bonus points for goals, assists, clean sheets
5. **Captain Points**: Double points for captain if they score
6. **Total Calculation**: Sum all points for user's squad

### League System Flow

1. **League Creation**: User creates league with name and settings
2. **Code Generation**: Generate unique join code for private leagues
3. **Member Management**: Handle join requests and approvals
4. **Standings Calculation**: Calculate league positions based on points
5. **Prize Distribution**: Handle prize money for league winners

## Middleware

Middleware provides filtering logic for HTTP requests:

**CheckSquadSelection** (`app/Http/Middleware/CheckSquadSelection.php`)
- **Purpose**: Ensures users complete squad selection before accessing main features
- **Usage**: Applied to routes requiring complete squad (dashboard, leagues, etc.)
- **Logic**: Redirect to squad selection if `has_selected_squad` is false

**Authentication Middleware**
- **Purpose**: Protects routes requiring user authentication
- **Usage**: Applied to all authenticated routes
- **Logic**: Redirect to login if user not authenticated

## Services

Services contain business logic and external API integrations:

**FPLDataImportService** (`app/Services/FPLDataImportService.php`)
- **Purpose**: Imports live FPL data from official API
- **Methods**: `importTeams()`, `importPlayers()`, `importFixtures()`
- **Usage**: Scheduled job to keep data synchronized

**FPLScoringService** (`app/Services/FPLScoringService.php`)
- **Purpose**: Calculates FPL points based on player performance
- **Methods**: `calculatePlayerPoints()`, `calculateBonusPoints()`
- **Usage**: Called during gameweek processing

**FPLQueryService** (`app/Services/FPLQueryService.php`)
- **Purpose**: Complex database queries for FPL data
- **Methods**: `getPlayerStats()`, `getLeagueStandings()`
- **Usage**: Used by controllers for data retrieval

## Security Features

- **CSRF Protection**: All forms protected against cross-site request forgery
- **Input Validation**: Comprehensive validation using Laravel's validation rules
- **SQL Injection Prevention**: Parameterized queries and Eloquent ORM
- **Authentication Middleware**: Route protection for authenticated users
- **Squad Selection Middleware**: Ensures users complete squad setup
- **Authorization Checks**: League permissions and ownership validation

## Performance Optimizations

- **Database Indexing**: Strategic indexes on frequently queried columns
- **Eager Loading**: N+1 query prevention with Eloquent relationships
- **Caching**: Laravel's caching system for expensive operations
- **Pagination**: Efficient data loading for large datasets
- **Query Optimization**: Efficient SQL queries with proper joins and selects

This documentation provides a comprehensive overview of the Fantasy Premier League project, covering the main application features with detailed code examples and workflow explanations.
