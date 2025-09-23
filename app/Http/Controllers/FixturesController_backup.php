<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FixturesController extends Controller
{
    public function index()
    {
        // Get current gameweek (you may need to adjust this logic)
        $currentGameweek = 4; // Hardcoded for now, you can get this from gameweeks table or settings
        
        // Get fixtures for current gameweek
        $fixtures = $this->getFixturesForGameweek($currentGameweek);
        
        return view('fixtures.index', compact('fixtures', 'currentGameweek'));
    }
    
    private function getFixturesForGameweek($gameweek)
    {
        // Try to get from fixtures table first
        $fixtures = DB::table('fixtures')
            ->join('teams as home_team', 'fixtures.home_team', '=', 'home_team.fpl_code')
            ->join('teams as away_team', 'fixtures.away_team', '=', 'away_team.fpl_code')
            ->where('fixtures.gameweek', $gameweek)
            ->select(
                'fixtures.*',
                'home_team.name as home_team_name',
                'home_team.short_name as home_team_short',
                'home_team.fpl_code as home_team_code',
                'away_team.name as away_team_name', 
                'away_team.short_name as away_team_short',
                'away_team.fpl_code as away_team_code'
            )
            ->orderBy('fixtures.kickoff_time')
            ->get();
            
        // If no fixtures, create sample data for demonstration
        if ($fixtures->isEmpty()) {
            $fixtures = $this->getSampleFixtures($gameweek);
        }
        
        return $fixtures;
    }
    
    private function getSampleFixtures($gameweek)
    {
        // Premier League team mappings for correct logo display
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
                'home_team' => 9, // Everton
                'away_team' => 2, // Aston Villa
                'home_team_name' => 'Everton',
                'home_team_short' => 'EVE',
                'home_team_code' => 9,
                'away_team_name' => 'Aston Villa',
                'away_team_short' => 'AVL',
                'away_team_code' => 2,
                'finished' => 0
            ],
            [
                'fixture_id' => 5,
                'gameweek' => $gameweek,
                'kickoff_time' => Carbon::createFromFormat('Y-m-d H:i:s', '2025-09-13 20:00:00'),
                'home_team' => 11, // Fulham
                'away_team' => 13, // Leeds
                'home_team_name' => 'Fulham',
                'home_team_short' => 'FUL',
                'home_team_code' => 11,
                'away_team_name' => 'Leeds',
                'away_team_short' => 'LEE',
                'away_team_code' => 13,
                'finished' => 0
            ],
            [
                'fixture_id' => 6,
                'gameweek' => $gameweek,
                'kickoff_time' => Carbon::createFromFormat('Y-m-d H:i:s', '2025-09-13 20:00:00'),
                'home_team' => 14, // Newcastle
                'away_team' => 19, // Wolves
                'home_team_name' => 'Newcastle',
                'home_team_short' => 'NEW',
                'home_team_code' => 14,
                'away_team_name' => 'Wolves',
                'away_team_short' => 'WOL',
                'away_team_code' => 19,
                'finished' => 0
            ],
            [
                'fixture_id' => 7,
                'gameweek' => $gameweek,
                'kickoff_time' => Carbon::createFromFormat('Y-m-d H:i:s', '2025-09-13 22:30:00'),
                'home_team' => 18, // West Ham
                'away_team' => 16, // Spurs
                'home_team_name' => 'West Ham',
                'home_team_short' => 'WHU',
                'home_team_code' => 18,
                'away_team_name' => 'Tottenham',
                'away_team_short' => 'TOT',
                'away_team_code' => 16,
                'finished' => 0
            ],
            [
                'fixture_id' => 8,
                'gameweek' => $gameweek,
                'kickoff_time' => Carbon::createFromFormat('Y-m-d H:i:s', '2025-09-14 01:00:00'),
                'home_team' => 3, // Brentford
                'away_team' => 6, // Chelsea
                'home_team_name' => 'Brentford',
                'home_team_short' => 'BRE',
                'home_team_code' => 3,
                'away_team_name' => 'Chelsea',
                'away_team_short' => 'CHE',
                'away_team_code' => 6,
                'finished' => 0
            ],
            [
                'fixture_id' => 9,
                'gameweek' => $gameweek,
                'kickoff_time' => Carbon::createFromFormat('Y-m-d H:i:s', '2025-09-14 19:00:00'),
                'home_team' => 3, // Burnley
                'away_team' => 12, // Liverpool
                'home_team_name' => 'Burnley',
                'home_team_short' => 'BUR',
                'home_team_code' => 3,
                'away_team_name' => 'Liverpool',
                'away_team_short' => 'LIV',
                'away_team_code' => 12,
                'finished' => 0
            ],
            [
                'fixture_id' => 10,
                'gameweek' => $gameweek,
                'kickoff_time' => Carbon::createFromFormat('Y-m-d H:i:s', '2025-09-14 21:30:00'),
                'home_team' => 15, // Man City
                'away_team' => 10, // Man Utd
                'home_team_name' => 'Manchester City',
                'home_team_short' => 'MCI',
                'home_team_code' => 15,
                'away_team_name' => 'Manchester United',
                'away_team_short' => 'MUN',
                'away_team_code' => 10,
                'finished' => 0
            ]
        ]);
        
        return $sampleFixtures->map(function($fixture) {
            return (object) $fixture;
        });
    }
    
    public function importFixtures()
    {
        // This method can be used to import fixtures from FPL API
        // For now, it's a placeholder
        return response()->json([
            'success' => true,
            'message' => 'Fixtures import feature coming soon!'
        ]);
    }
}