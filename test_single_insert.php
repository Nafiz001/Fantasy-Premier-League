<?php

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

try {
    // Test inserting data for just one player from GW1
    $csvData = file_get_contents('c:\xampp\htdocs\fantasy-premier-league\FPL-Elo-Insights\data\2025-2026\By Gameweek\GW1\player_gameweek_stats.csv');
    $lines = explode("\n", trim($csvData));
    $headers = str_getcsv($lines[0]);

    if (isset($lines[1])) {
        $row = str_getcsv($lines[1]);
        $data = array_combine($headers, $row);

        $playerId = (int)($data['id'] ?? 0);
        echo "Player ID: $playerId\n";

        // Get the actual database player_id from our players table
        $dbPlayer = DB::table('players')->where('fpl_id', $playerId)->first();
        if ($dbPlayer) {
            echo "Found DB player: " . $dbPlayer->id . "\n";

            $testRecord = [
                'player_id' => $dbPlayer->id,
                'gameweek' => 1,
                'minutes' => (int)($data['minutes'] ?? 0),
                'goals_scored' => (int)($data['goals_scored'] ?? 0),
                'assists' => (int)($data['assists'] ?? 0),
                'clean_sheets' => (int)($data['clean_sheets'] ?? 0),
                'goals_conceded' => (int)($data['goals_conceded'] ?? 0),
                'own_goals' => (int)($data['own_goals'] ?? 0),
                'penalties_saved' => (int)($data['penalties_saved'] ?? 0),
                'penalties_missed' => (int)($data['penalties_missed'] ?? 0),
                'yellow_cards' => (int)($data['yellow_cards'] ?? 0),
                'red_cards' => (int)($data['red_cards'] ?? 0),
                'saves' => (int)($data['saves'] ?? 0),
                'bonus' => (int)($data['bonus'] ?? 0),
                'bps' => (int)($data['bps'] ?? 0),
                'influence' => (float)($data['influence'] ?? 0),
                'creativity' => (float)($data['creativity'] ?? 0),
                'threat' => (float)($data['threat'] ?? 0),
                'ict_index' => (float)($data['ict_index'] ?? 0),
                'starts' => (int)($data['starts'] ?? 0),
                'expected_goals' => (float)($data['expected_goals'] ?? 0),
                'expected_assists' => (float)($data['expected_assists'] ?? 0),
                'expected_goal_involvements' => (float)($data['expected_goal_involvements'] ?? 0),
                'expected_goals_conceded' => (float)($data['expected_goals_conceded'] ?? 0),
                'total_points' => (int)($data['total_points'] ?? 0),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            echo "Attempting to insert test record...\n";
            var_dump($testRecord);

            DB::table('player_gameweek_stats')->insert($testRecord);
            echo "Successfully inserted test record!\n";

        } else {
            echo "Player not found in database\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
