<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FPLSampleDataSeeder extends Seeder
{
    public function run(): void
    {
        echo "Creating complete FPL sample data...\n";

        // Sample Teams
        $teams = [
            ['fpl_id' => 1, 'fpl_code' => 1, 'name' => 'Arsenal', 'short_name' => 'ARS', 'strength' => 85, 'strength_overall_home' => 85, 'strength_overall_away' => 80, 'strength_attack_home' => 85, 'strength_attack_away' => 80, 'strength_defence_home' => 85, 'strength_defence_away' => 80, 'pulse_id' => 1, 'elo' => 1650.5],
            ['fpl_id' => 2, 'fpl_code' => 2, 'name' => 'Manchester City', 'short_name' => 'MCI', 'strength' => 90, 'strength_overall_home' => 90, 'strength_overall_away' => 88, 'strength_attack_home' => 90, 'strength_attack_away' => 88, 'strength_defence_home' => 90, 'strength_defence_away' => 88, 'pulse_id' => 2, 'elo' => 1750.8],
            ['fpl_id' => 3, 'fpl_code' => 3, 'name' => 'Liverpool', 'short_name' => 'LIV', 'strength' => 88, 'strength_overall_home' => 88, 'strength_overall_away' => 85, 'strength_attack_home' => 88, 'strength_attack_away' => 85, 'strength_defence_home' => 88, 'strength_defence_away' => 85, 'pulse_id' => 3, 'elo' => 1720.3],
            ['fpl_id' => 4, 'fpl_code' => 4, 'name' => 'Chelsea', 'short_name' => 'CHE', 'strength' => 80, 'strength_overall_home' => 80, 'strength_overall_away' => 78, 'strength_attack_home' => 80, 'strength_attack_away' => 78, 'strength_defence_home' => 80, 'strength_defence_away' => 78, 'pulse_id' => 4, 'elo' => 1580.2],
            ['fpl_id' => 5, 'fpl_code' => 5, 'name' => 'Newcastle United', 'short_name' => 'NEW', 'strength' => 82, 'strength_overall_home' => 82, 'strength_overall_away' => 78, 'strength_attack_home' => 82, 'strength_attack_away' => 78, 'strength_defence_home' => 82, 'strength_defence_away' => 78, 'pulse_id' => 5, 'elo' => 1620.7],
        ];

        foreach ($teams as $team) {
            DB::table('teams')->insert(array_merge($team, [
                'created_at' => now(),
                'updated_at' => now()
            ]));
        }

        // Sample Gameweeks
        for ($i = 1; $i <= 5; $i++) {
            DB::table('gameweeks')->insert([
                'gameweek_id' => $i,
                'name' => "Gameweek {$i}",
                'deadline_time' => now()->addWeeks($i - 1)->startOfDay(),
                'deadline_time_epoch' => now()->addWeeks($i - 1)->startOfDay()->timestamp,
                'deadline_time_game_offset' => 0,
                'is_previous' => $i < 3,
                'is_current' => $i == 3,
                'is_next' => $i > 3,
                'finished' => $i < 3,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        // Sample Players
        $players = [
            ['fpl_id' => 1, 'fpl_code' => 1, 'web_name' => 'Haaland', 'first_name' => 'Erling', 'second_name' => 'Haaland', 'element_type' => 4, 'team_code' => 2, 'position' => 'Forward'],
            ['fpl_id' => 2, 'fpl_code' => 2, 'web_name' => 'Salah', 'first_name' => 'Mohamed', 'second_name' => 'Salah', 'element_type' => 3, 'team_code' => 3, 'position' => 'Midfielder'],
            ['fpl_id' => 3, 'fpl_code' => 3, 'web_name' => 'Son', 'first_name' => 'Heung-Min', 'second_name' => 'Son', 'element_type' => 3, 'team_code' => 4, 'position' => 'Midfielder'],
            ['fpl_id' => 4, 'fpl_code' => 4, 'web_name' => 'Alexander-Arnold', 'first_name' => 'Trent', 'second_name' => 'Alexander-Arnold', 'element_type' => 2, 'team_code' => 3, 'position' => 'Defender'],
            ['fpl_id' => 5, 'fpl_code' => 5, 'web_name' => 'De Bruyne', 'first_name' => 'Kevin', 'second_name' => 'De Bruyne', 'element_type' => 3, 'team_code' => 2, 'position' => 'Midfielder'],
            ['fpl_id' => 6, 'fpl_code' => 6, 'web_name' => 'Alisson', 'first_name' => 'Alisson', 'second_name' => 'Becker', 'element_type' => 1, 'team_code' => 3, 'position' => 'Goalkeeper'],
            ['fpl_id' => 7, 'fpl_code' => 7, 'web_name' => 'Saka', 'first_name' => 'Bukayo', 'second_name' => 'Saka', 'element_type' => 3, 'team_code' => 1, 'position' => 'Midfielder'],
            ['fpl_id' => 8, 'fpl_code' => 8, 'web_name' => 'Odegaard', 'first_name' => 'Martin', 'second_name' => 'Ødegaard', 'element_type' => 3, 'team_code' => 1, 'position' => 'Midfielder'],
        ];

        foreach ($players as $player) {
            DB::table('players')->insert(array_merge($player, [
                'created_at' => now(),
                'updated_at' => now()
            ]));
        }

        // Complete Player Stats
        $playerStats = [
            ['player_id' => 1, 'gameweek' => 3, 'first_name' => 'Erling', 'second_name' => 'Haaland', 'web_name' => 'Haaland', 'status' => 'a', 'chance_of_playing_next_round' => 100, 'now_cost' => 150, 'selected_by_percent' => 65.2, 'total_points' => 125, 'event_points' => 12, 'points_per_game' => 8.5, 'form' => 8.5, 'minutes' => 2340, 'goals_scored' => 18, 'assists' => 5, 'bonus' => 12, 'starts' => 28, 'transfers_in_event' => 163350, 'transfers_out_event' => 37149, 'expected_goals' => 15.2],
            ['player_id' => 2, 'gameweek' => 3, 'first_name' => 'Mohamed', 'second_name' => 'Salah', 'web_name' => 'Salah', 'status' => 'a', 'chance_of_playing_next_round' => 100, 'now_cost' => 130, 'selected_by_percent' => 55.8, 'total_points' => 115, 'event_points' => 8, 'points_per_game' => 7.8, 'form' => 7.8, 'minutes' => 2580, 'goals_scored' => 12, 'assists' => 8, 'bonus' => 15, 'starts' => 30, 'transfers_in_event' => 85000, 'transfers_out_event' => 42000, 'expected_goals' => 10.5],
            ['player_id' => 3, 'gameweek' => 3, 'first_name' => 'Heung-Min', 'second_name' => 'Son', 'web_name' => 'Son', 'status' => 'a', 'chance_of_playing_next_round' => 100, 'now_cost' => 100, 'selected_by_percent' => 35.4, 'total_points' => 98, 'event_points' => 6, 'points_per_game' => 6.5, 'form' => 6.5, 'minutes' => 2280, 'goals_scored' => 8, 'assists' => 7, 'bonus' => 8, 'starts' => 26, 'transfers_in_event' => 45000, 'transfers_out_event' => 25000, 'expected_goals' => 8.3],
            ['player_id' => 4, 'gameweek' => 3, 'first_name' => 'Trent', 'second_name' => 'Alexander-Arnold', 'web_name' => 'Alexander-Arnold', 'status' => 'a', 'chance_of_playing_next_round' => 100, 'now_cost' => 75, 'selected_by_percent' => 42.1, 'total_points' => 89, 'event_points' => 9, 'points_per_game' => 6.8, 'form' => 6.8, 'minutes' => 2520, 'goals_scored' => 2, 'assists' => 12, 'bonus' => 18, 'starts' => 28, 'clean_sheets' => 8, 'transfers_in_event' => 65000, 'transfers_out_event' => 35000, 'expected_assists' => 8.2],
            ['player_id' => 5, 'gameweek' => 3, 'first_name' => 'Kevin', 'second_name' => 'De Bruyne', 'web_name' => 'De Bruyne', 'status' => 'a', 'chance_of_playing_next_round' => 100, 'now_cost' => 95, 'selected_by_percent' => 28.5, 'total_points' => 92, 'event_points' => 7, 'points_per_game' => 7.2, 'form' => 7.2, 'minutes' => 2180, 'goals_scored' => 5, 'assists' => 15, 'bonus' => 10, 'starts' => 25, 'transfers_in_event' => 38000, 'transfers_out_event' => 22000, 'expected_assists' => 12.8],
            ['player_id' => 6, 'gameweek' => 3, 'first_name' => 'Alisson', 'second_name' => 'Becker', 'web_name' => 'Alisson', 'status' => 'a', 'chance_of_playing_next_round' => 100, 'now_cost' => 55, 'selected_by_percent' => 18.7, 'total_points' => 78, 'event_points' => 5, 'points_per_game' => 5.8, 'form' => 5.8, 'minutes' => 2700, 'goals_scored' => 0, 'assists' => 1, 'bonus' => 8, 'starts' => 30, 'clean_sheets' => 8, 'saves' => 45, 'transfers_in_event' => 12000, 'transfers_out_event' => 8000],
            ['player_id' => 7, 'gameweek' => 3, 'first_name' => 'Bukayo', 'second_name' => 'Saka', 'web_name' => 'Saka', 'status' => 'a', 'chance_of_playing_next_round' => 100, 'now_cost' => 85, 'selected_by_percent' => 38.9, 'total_points' => 102, 'event_points' => 7, 'points_per_game' => 7.5, 'form' => 7.5, 'minutes' => 2460, 'goals_scored' => 9, 'assists' => 6, 'bonus' => 12, 'starts' => 28, 'transfers_in_event' => 55000, 'transfers_out_event' => 28000, 'expected_goals' => 7.5],
            ['player_id' => 8, 'gameweek' => 3, 'first_name' => 'Martin', 'second_name' => 'Ødegaard', 'web_name' => 'Odegaard', 'status' => 'a', 'chance_of_playing_next_round' => 100, 'now_cost' => 80, 'selected_by_percent' => 22.3, 'total_points' => 88, 'event_points' => 6, 'points_per_game' => 6.9, 'form' => 6.9, 'minutes' => 2340, 'goals_scored' => 7, 'assists' => 9, 'bonus' => 9, 'starts' => 26, 'transfers_in_event' => 32000, 'transfers_out_event' => 18000, 'expected_goals' => 5.8],
        ];

        foreach ($playerStats as $stat) {
            DB::table('player_stats')->insert(array_merge($stat, [
                'cost_change_event' => 0,
                'cost_change_start' => rand(-3, 5),
                'clean_sheets' => $stat['clean_sheets'] ?? 0,
                'goals_conceded' => 0,
                'own_goals' => 0,
                'penalties_saved' => 0,
                'penalties_missed' => 0,
                'yellow_cards' => rand(0, 3),
                'red_cards' => 0,
                'saves' => $stat['saves'] ?? 0,
                'bps' => rand(150, 400),
                'value_form' => 10.5,
                'value_season' => 15.8,
                'dreamteam_count' => rand(1, 5),
                'transfers_in' => 50000,
                'transfers_out' => 25000,
                'expected_assists' => $stat['expected_assists'] ?? 2.0,
                'expected_goal_involvements' => 4.3,
                'expected_goals_conceded' => 0.5,
                'created_at' => now(),
                'updated_at' => now()
            ]));
        }

        // Sample Matches
        $matches = [
            ['match_id' => 1, 'gameweek' => 1, 'home_team' => 1, 'away_team' => 2, 'home_score' => 1, 'away_score' => 3, 'finished' => 1, 'kickoff_time' => now()->subWeeks(2), 'home_possession' => 45, 'away_possession' => 55, 'home_total_shots' => 12, 'away_total_shots' => 18, 'home_shots_on_target' => 4, 'away_shots_on_target' => 8, 'home_big_chances' => 3, 'away_big_chances' => 6, 'home_expected_goals_xg' => 1.2, 'away_expected_goals_xg' => 2.8, 'home_team_elo' => 1650.5, 'away_team_elo' => 1750.8],
            ['match_id' => 2, 'gameweek' => 1, 'home_team' => 3, 'away_team' => 4, 'home_score' => 2, 'away_score' => 0, 'finished' => 1, 'kickoff_time' => now()->subWeeks(2), 'home_possession' => 65, 'away_possession' => 35, 'home_total_shots' => 16, 'away_total_shots' => 8, 'home_shots_on_target' => 6, 'away_shots_on_target' => 2, 'home_big_chances' => 5, 'away_big_chances' => 2, 'home_expected_goals_xg' => 2.1, 'away_expected_goals_xg' => 0.8, 'home_team_elo' => 1720.3, 'away_team_elo' => 1580.2],
            ['match_id' => 3, 'gameweek' => 2, 'home_team' => 2, 'away_team' => 3, 'home_score' => 4, 'away_score' => 1, 'finished' => 1, 'kickoff_time' => now()->subWeeks(1), 'home_possession' => 58, 'away_possession' => 42, 'home_total_shots' => 20, 'away_total_shots' => 11, 'home_shots_on_target' => 9, 'away_shots_on_target' => 4, 'home_big_chances' => 7, 'away_big_chances' => 3, 'home_expected_goals_xg' => 3.5, 'away_expected_goals_xg' => 1.4, 'home_team_elo' => 1750.8, 'away_team_elo' => 1720.3],
        ];

        foreach ($matches as $match) {
            DB::table('matches')->insert(array_merge($match, [
                'home_tackles_won' => rand(15, 25),
                'away_tackles_won' => rand(15, 25),
                'home_interceptions' => rand(8, 15),
                'away_interceptions' => rand(8, 15),
                'home_blocks' => rand(3, 8),
                'away_blocks' => rand(3, 8),
                'home_clearances' => rand(20, 40),
                'away_clearances' => rand(20, 40),
                'home_accurate_passes' => rand(400, 700),
                'away_accurate_passes' => rand(400, 700),
                'home_accurate_passes_pct' => rand(80, 95),
                'away_accurate_passes_pct' => rand(80, 95),
                'created_at' => now(),
                'updated_at' => now()
            ]));
        }

        // Sample Fixtures
        $fixtures = [
            ['fixture_id' => 10, 'gameweek' => 4, 'home_team' => 1, 'away_team' => 3, 'finished' => 0, 'kickoff_time' => now()->addDays(3), 'home_team_elo' => 1650.5, 'away_team_elo' => 1720.3],
            ['fixture_id' => 11, 'gameweek' => 4, 'home_team' => 2, 'away_team' => 4, 'finished' => 0, 'kickoff_time' => now()->addDays(3), 'home_team_elo' => 1750.8, 'away_team_elo' => 1580.2],
            ['fixture_id' => 12, 'gameweek' => 5, 'home_team' => 3, 'away_team' => 5, 'finished' => 0, 'kickoff_time' => now()->addDays(10), 'home_team_elo' => 1720.3, 'away_team_elo' => 1620.7],
        ];

        foreach ($fixtures as $fixture) {
            DB::table('fixtures')->insert(array_merge($fixture, [
                'created_at' => now(),
                'updated_at' => now()
            ]));
        }

        echo "✅ Sample FPL data seeded successfully!\n";
        echo "   → " . count($teams) . " teams created\n";
        echo "   → 5 gameweeks created\n";
        echo "   → " . count($players) . " players created\n";
        echo "   → " . count($playerStats) . " player stat records created\n";
        echo "   → " . count($matches) . " matches created\n";  
        echo "   → " . count($fixtures) . " fixtures created\n";
    }
}
