<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class FPLQueryService
{
    /**
     * CRUD OPERATIONS
     */

    /**
     * Create a new player transfer tracking record
     */
    public function createPlayerTransfer(array $data): bool
    {
        try {
            DB::table('player_transfers')->insert([
                'player_id' => $data['player_id'],
                'from_team_code' => $data['from_team'],
                'to_team_code' => $data['to_team'],
                'transfer_date' => $data['transfer_date'] ?? now(),
                'transfer_fee' => $data['fee'] ?? null,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to create transfer record: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update team performance metrics
     */
    public function updateTeamMetrics(int $teamId, array $metrics): bool
    {
        return DB::table('teams')
            ->where('fpl_id', $teamId)
            ->update($metrics + ['updated_at' => now()]);
    }

    /**
     * Delete old gameweek data (soft delete approach)
     */
    public function archiveGameweekData(int $gameweek): int
    {
        return DB::table('player_gameweek_stats')
            ->where('gameweek', $gameweek)
            ->update(['archived_at' => now()]);
    }

    /**
     * COMPLEX QUERIES WITH JOINS
     */

    /**
     * Get top performing players with team and position info
     */
    public function getTopPerformersWithTeamInfo(int $gameweek = null, int $limit = 20): array
    {
        $query = "
            SELECT 
                p.web_name,
                p.position,
                t.name as team_name,
                t.short_name as team_short,
                ps.total_points,
                ps.now_cost / 10.0 as cost_millions,
                ps.selected_by_percent,
                ps.form,
                ps.goals_scored,
                ps.assists,
                ps.bonus,
                CASE p.element_type
                    WHEN 1 THEN 'Goalkeeper'
                    WHEN 2 THEN 'Defender'
                    WHEN 3 THEN 'Midfielder'
                    WHEN 4 THEN 'Forward'
                END as position_full
            FROM player_stats ps
            INNER JOIN players p ON ps.player_id = p.fpl_id
            INNER JOIN teams t ON p.team_code = t.fpl_code
        ";
        
        if ($gameweek) {
            $query .= " WHERE ps.gameweek = ?";
            $params = [$gameweek];
        } else {
            $query .= " WHERE ps.gameweek = (SELECT MAX(gameweek) FROM player_stats)";
            $params = [];
        }
        
        $query .= " ORDER BY ps.total_points DESC LIMIT ?";
        $params[] = $limit;
        
        return DB::select($query, $params);
    }

    /**
     * Get team vs team head-to-head record with detailed stats
     */
    public function getHeadToHeadRecord(int $team1Id, int $team2Id): array
    {
        return DB::select("
            WITH head_to_head AS (
                SELECT 
                    m.*,
                    CASE 
                        WHEN m.home_team = ? THEN 'home'
                        ELSE 'away'
                    END as team1_venue,
                    CASE 
                        WHEN m.home_team = ? THEN 'home'
                        ELSE 'away'  
                    END as team2_venue,
                    CASE 
                        WHEN (m.home_team = ? AND m.home_score > m.away_score) OR 
                             (m.away_team = ? AND m.away_score > m.home_score) THEN 'team1_win'
                        WHEN (m.home_team = ? AND m.home_score < m.away_score) OR 
                             (m.away_team = ? AND m.away_score < m.home_score) THEN 'team2_win'
                        ELSE 'draw'
                    END as result
                FROM matches m 
                WHERE (m.home_team = ? AND m.away_team = ?) 
                   OR (m.home_team = ? AND m.away_team = ?)
                   AND m.finished = 1
            )
            SELECT 
                COUNT(*) as total_matches,
                SUM(CASE WHEN result = 'team1_win' THEN 1 ELSE 0 END) as team1_wins,
                SUM(CASE WHEN result = 'team2_win' THEN 1 ELSE 0 END) as team2_wins,
                SUM(CASE WHEN result = 'draw' THEN 1 ELSE 0 END) as draws,
                AVG(CASE WHEN team1_venue = 'home' THEN home_score ELSE away_score END) as team1_avg_goals,
                AVG(CASE WHEN team2_venue = 'home' THEN home_score ELSE away_score END) as team2_avg_goals,
                MAX(kickoff_time) as last_meeting,
                AVG(home_expected_goals_xg + away_expected_goals_xg) as avg_total_xg
            FROM head_to_head
        ", [$team1Id, $team2Id, $team1Id, $team1Id, $team1Id, $team1Id, $team1Id, $team2Id, $team2Id, $team1Id]);
    }

    /**
     * Get player performance against specific teams
     */
    public function getPlayerVsTeamStats(int $playerId, int $opponentTeamId): array
    {
        return DB::select("
            SELECT 
                p.web_name,
                COUNT(pms.id) as matches_played,
                SUM(pms.goals) as total_goals,
                SUM(pms.assists) as total_assists,
                SUM(pms.total_points) as total_points,
                AVG(pms.total_points) as avg_points_per_match,
                SUM(pms.bonus_points) as total_bonus,
                MAX(pms.total_points) as highest_score,
                SUM(CASE WHEN pms.total_points >= 6 THEN 1 ELSE 0 END) as good_performances,
                t.name as opponent_name
            FROM player_match_stats pms
            INNER JOIN players p ON pms.player_id = p.fpl_id
            INNER JOIN matches m ON pms.match_id = m.match_id
            INNER JOIN teams t ON t.fpl_id = ?
            WHERE pms.player_id = ? 
              AND ((m.home_team = ? AND m.away_team != p.team_code) 
                   OR (m.away_team = ? AND m.home_team != p.team_code))
            GROUP BY p.web_name, t.name
        ", [$opponentTeamId, $playerId, $opponentTeamId, $opponentTeamId]);
    }

    /**
     * SUBQUERIES AND WINDOW FUNCTIONS
     */

    /**
     * Get players' rank by points in their position with running totals
     */
    public function getPositionalRankingsWithTrends(): array
    {
        return DB::select("
            SELECT 
                p.web_name,
                p.position,
                t.short_name as team,
                ps.total_points,
                ps.now_cost / 10.0 as cost,
                RANK() OVER (PARTITION BY p.element_type ORDER BY ps.total_points DESC) as position_rank,
                DENSE_RANK() OVER (ORDER BY ps.total_points DESC) as overall_rank,
                LAG(ps.total_points, 1) OVER (PARTITION BY p.fpl_id ORDER BY ps.gameweek) as prev_total_points,
                ps.total_points - LAG(ps.total_points, 1) OVER (PARTITION BY p.fpl_id ORDER BY ps.gameweek) as gameweek_points,
                AVG(ps.total_points) OVER (PARTITION BY p.element_type) as position_avg,
                ps.total_points - AVG(ps.total_points) OVER (PARTITION BY p.element_type) as points_above_avg
            FROM player_stats ps
            INNER JOIN players p ON ps.player_id = p.fpl_id
            INNER JOIN teams t ON p.team_code = t.fpl_code
            WHERE ps.gameweek = (SELECT MAX(gameweek) FROM player_stats)
            ORDER BY ps.total_points DESC
        ");
    }

    /**
     * Get form table with last 5 gameweeks using subqueries
     */
    public function getFormTable(int $gameweeks = 5): array
    {
        return DB::select("
            SELECT 
                t.name,
                t.short_name,
                form_stats.matches_played,
                form_stats.wins,
                form_stats.draws,
                form_stats.losses,
                form_stats.goals_for,
                form_stats.goals_against,
                form_stats.goal_difference,
                form_stats.points as form_points,
                ROUND(form_stats.points / NULLIF(form_stats.matches_played, 0) * 100, 1) as points_percentage
            FROM teams t
            INNER JOIN (
                SELECT 
                    team_id,
                    COUNT(*) as matches_played,
                    SUM(CASE WHEN result = 'W' THEN 1 ELSE 0 END) as wins,
                    SUM(CASE WHEN result = 'D' THEN 1 ELSE 0 END) as draws,
                    SUM(CASE WHEN result = 'L' THEN 1 ELSE 0 END) as losses,
                    SUM(goals_for) as goals_for,
                    SUM(goals_against) as goals_against,
                    SUM(goals_for) - SUM(goals_against) as goal_difference,
                    SUM(CASE WHEN result = 'W' THEN 3 WHEN result = 'D' THEN 1 ELSE 0 END) as points
                FROM (
                    SELECT 
                        CASE WHEN m.home_team = t_check.fpl_id THEN m.home_team ELSE m.away_team END as team_id,
                        CASE 
                            WHEN m.home_team = t_check.fpl_id AND m.home_score > m.away_score THEN 'W'
                            WHEN m.away_team = t_check.fpl_id AND m.away_score > m.home_score THEN 'W'
                            WHEN m.home_score = m.away_score THEN 'D'
                            ELSE 'L'
                        END as result,
                        CASE WHEN m.home_team = t_check.fpl_id THEN m.home_score ELSE m.away_score END as goals_for,
                        CASE WHEN m.home_team = t_check.fpl_id THEN m.away_score ELSE m.home_score END as goals_against
                    FROM matches m
                    CROSS JOIN teams t_check
                    WHERE (m.home_team = t_check.fpl_id OR m.away_team = t_check.fpl_id)
                      AND m.finished = 1
                      AND m.gameweek > (SELECT MAX(gameweek) FROM matches WHERE finished = 1) - ?
                ) recent_matches
                GROUP BY team_id
            ) form_stats ON t.fpl_id = form_stats.team_id
            ORDER BY form_stats.points DESC, form_stats.goal_difference DESC
        ", [$gameweeks]);
    }

    /**
     * GROUP BY AND HAVING CLAUSES
     */

    /**
     * Get teams with highest average possession
     */
    public function getHighPossessionTeams(float $minPossession = 50.0): array
    {
        return DB::select("
            SELECT 
                t.name,
                t.short_name,
                COUNT(m.match_id) as matches_played,
                ROUND(AVG(CASE WHEN m.home_team = t.fpl_id THEN m.home_possession ELSE m.away_possession END), 1) as avg_possession,
                ROUND(AVG(CASE WHEN m.home_team = t.fpl_id THEN m.home_accurate_passes ELSE m.away_accurate_passes END), 0) as avg_passes,
                ROUND(AVG(CASE WHEN m.home_team = t.fpl_id THEN m.home_accurate_passes_pct ELSE m.away_accurate_passes_pct END), 1) as pass_accuracy
            FROM teams t
            INNER JOIN matches m ON (m.home_team = t.fpl_id OR m.away_team = t.fpl_id)
            WHERE m.finished = 1 
              AND ((m.home_team = t.fpl_id AND m.home_possession IS NOT NULL) 
                   OR (m.away_team = t.fpl_id AND m.away_possession IS NOT NULL))
            GROUP BY t.fpl_id, t.name, t.short_name
            HAVING AVG(CASE WHEN m.home_team = t.fpl_id THEN m.home_possession ELSE m.away_possession END) >= ?
            ORDER BY avg_possession DESC
        ", [$minPossession]);
    }

    /**
     * Get players who score regularly (goals in multiple games)
     */
    public function getConsistentGoalScorers(int $minGames = 3): array
    {
        return DB::select("
            SELECT 
                p.web_name,
                p.position,
                t.short_name as team,
                COUNT(pms.id) as games_with_goals,
                SUM(pms.goals) as total_goals,
                AVG(pms.goals) as avg_goals_when_scoring,
                ps.total_points,
                ps.now_cost / 10.0 as cost
            FROM players p
            INNER JOIN player_match_stats pms ON p.fpl_id = pms.player_id
            INNER JOIN teams t ON p.team_code = t.fpl_code
            INNER JOIN player_stats ps ON p.fpl_id = ps.player_id
            WHERE pms.goals > 0
              AND ps.gameweek = (SELECT MAX(gameweek) FROM player_stats)
            GROUP BY p.fpl_id, p.web_name, p.position, t.short_name, ps.total_points, ps.now_cost
            HAVING COUNT(pms.id) >= ?
            ORDER BY games_with_goals DESC, total_goals DESC
        ", [$minGames]);
    }

    /**
     * AGGREGATE FUNCTIONS WITH COMPLEX CONDITIONS
     */

    /**
     * Get comprehensive team attacking stats
     */
    public function getTeamAttackingStats(): array
    {
        return DB::select("
            SELECT 
                t.name,
                COUNT(m.match_id) as matches,
                SUM(CASE WHEN m.home_team = t.fpl_id THEN m.home_score ELSE m.away_score END) as goals_scored,
                SUM(CASE WHEN m.home_team = t.fpl_id THEN m.home_total_shots ELSE m.away_total_shots END) as total_shots,
                SUM(CASE WHEN m.home_team = t.fpl_id THEN m.home_shots_on_target ELSE m.away_shots_on_target END) as shots_on_target,
                SUM(CASE WHEN m.home_team = t.fpl_id THEN m.home_big_chances ELSE m.away_big_chances END) as big_chances,
                SUM(CASE WHEN m.home_team = t.fpl_id THEN m.home_expected_goals_xg ELSE m.away_expected_goals_xg END) as total_xg,
                ROUND(AVG(CASE WHEN m.home_team = t.fpl_id THEN m.home_score ELSE m.away_score END), 2) as avg_goals,
                ROUND(AVG(CASE WHEN m.home_team = t.fpl_id THEN m.home_total_shots ELSE m.away_total_shots END), 1) as avg_shots,
                ROUND(AVG(CASE WHEN m.home_team = t.fpl_id THEN m.home_expected_goals_xg ELSE m.away_expected_goals_xg END), 2) as avg_xg,
                ROUND(
                    SUM(CASE WHEN m.home_team = t.fpl_id THEN m.home_shots_on_target ELSE m.away_shots_on_target END) * 100.0 / 
                    NULLIF(SUM(CASE WHEN m.home_team = t.fpl_id THEN m.home_total_shots ELSE m.away_total_shots END), 0), 
                    1
                ) as shot_accuracy,
                ROUND(
                    SUM(CASE WHEN m.home_team = t.fpl_id THEN m.home_score ELSE m.away_score END) * 100.0 / 
                    NULLIF(SUM(CASE WHEN m.home_team = t.fpl_id THEN m.home_big_chances ELSE m.away_big_chances END), 0),
                    1
                ) as big_chance_conversion
            FROM teams t
            LEFT JOIN matches m ON (m.home_team = t.fpl_id OR m.away_team = t.fpl_id) AND m.finished = 1
            GROUP BY t.fpl_id, t.name
            HAVING COUNT(m.match_id) > 0
            ORDER BY avg_goals DESC
        ");
    }

    /**
     * Get defensive stats with clean sheet analysis
     */
    public function getTeamDefensiveStats(): array
    {
        return DB::select("
            SELECT 
                t.name,
                t.short_name,
                COUNT(m.match_id) as matches,
                SUM(CASE WHEN m.home_team = t.fpl_id THEN m.away_score ELSE m.home_score END) as goals_conceded,
                SUM(CASE 
                    WHEN (m.home_team = t.fpl_id AND m.away_score = 0) OR 
                         (m.away_team = t.fpl_id AND m.home_score = 0) THEN 1 
                    ELSE 0 
                END) as clean_sheets,
                SUM(CASE WHEN m.home_team = t.fpl_id THEN m.home_tackles_won ELSE m.away_tackles_won END) as total_tackles,
                SUM(CASE WHEN m.home_team = t.fpl_id THEN m.home_interceptions ELSE m.away_interceptions END) as total_interceptions,
                SUM(CASE WHEN m.home_team = t.fpl_id THEN m.home_blocks ELSE m.away_blocks END) as total_blocks,
                SUM(CASE WHEN m.home_team = t.fpl_id THEN m.home_clearances ELSE m.away_clearances END) as total_clearances,
                ROUND(AVG(CASE WHEN m.home_team = t.fpl_id THEN m.away_score ELSE m.home_score END), 2) as avg_goals_conceded,
                ROUND(
                    SUM(CASE 
                        WHEN (m.home_team = t.fpl_id AND m.away_score = 0) OR 
                             (m.away_team = t.fpl_id AND m.home_score = 0) THEN 1 
                        ELSE 0 
                    END) * 100.0 / NULLIF(COUNT(m.match_id), 0), 
                    1
                ) as clean_sheet_percentage
            FROM teams t
            LEFT JOIN matches m ON (m.home_team = t.fpl_id OR m.away_team = t.fpl_id) AND m.finished = 1
            GROUP BY t.fpl_id, t.name, t.short_name  
            HAVING COUNT(m.match_id) > 0
            ORDER BY clean_sheet_percentage DESC, avg_goals_conceded ASC
        ");
    }

    /**
     * VIEWS CREATION
     */

    /**
     * Create view for player value analysis
     */
    public function createPlayerValueView(): bool
    {
        try {
            DB::statement("
                CREATE OR REPLACE VIEW player_value_analysis AS
                SELECT 
                    p.fpl_id,
                    p.web_name,
                    p.position,
                    t.short_name as team,
                    ps.now_cost / 10.0 as cost_millions,
                    ps.total_points,
                    ps.selected_by_percent,
                    ps.form,
                    ROUND(ps.total_points / (ps.now_cost / 10.0), 2) as points_per_million,
                    ROUND(ps.total_points / NULLIF(ps.minutes / 90.0, 0), 2) as points_per_90,
                    CASE 
                        WHEN ps.total_points / (ps.now_cost / 10.0) >= 20 THEN 'Excellent Value'
                        WHEN ps.total_points / (ps.now_cost / 10.0) >= 15 THEN 'Good Value'
                        WHEN ps.total_points / (ps.now_cost / 10.0) >= 10 THEN 'Fair Value'
                        ELSE 'Poor Value'
                    END as value_rating,
                    RANK() OVER (PARTITION BY p.element_type ORDER BY ps.total_points / (ps.now_cost / 10.0) DESC) as value_rank_in_position
                FROM player_stats ps
                INNER JOIN players p ON ps.player_id = p.fpl_id
                INNER JOIN teams t ON p.team_code = t.fpl_code
                WHERE ps.gameweek = (SELECT MAX(gameweek) FROM player_stats)
                  AND ps.now_cost > 0
                  AND ps.minutes > 0
            ");
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to create player value view: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Create view for fixture difficulty analysis
     */
    public function createFixtureDifficultyView(): bool
    {
        try {
            DB::statement("
                CREATE OR REPLACE VIEW fixture_difficulty_analysis AS
                SELECT 
                    f.fixture_id,
                    f.gameweek,
                    f.kickoff_time,
                    ht.name as home_team,
                    ht.short_name as home_short,
                    at.name as away_team,
                    at.short_name as away_short,
                    f.home_team_elo,
                    f.away_team_elo,
                    ABS(f.home_team_elo - f.away_team_elo) as elo_difference,
                    ht.strength_overall_home + 50 as home_adjusted_strength,
                    at.strength_overall_away as away_strength,
                    CASE 
                        WHEN ABS(f.home_team_elo - f.away_team_elo) < 50 THEN 'Very Close'
                        WHEN ABS(f.home_team_elo - f.away_team_elo) < 100 THEN 'Close'
                        WHEN ABS(f.home_team_elo - f.away_team_elo) < 200 THEN 'Moderate'
                        WHEN ABS(f.home_team_elo - f.away_team_elo) < 300 THEN 'Clear Favorite'
                        ELSE 'Huge Favorite'
                    END as match_competitiveness,
                    CASE 
                        WHEN f.home_team_elo > f.away_team_elo + 100 THEN ht.short_name
                        WHEN f.away_team_elo > f.home_team_elo + 100 THEN at.short_name
                        ELSE 'Toss-up'
                    END as predicted_winner
                FROM fixtures f
                INNER JOIN teams ht ON f.home_team = ht.fpl_id
                INNER JOIN teams at ON f.away_team = at.fpl_id
                WHERE f.finished = 0
            ");
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to create fixture difficulty view: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * COMPLEX ANALYTICAL QUERIES
     */

    /**
     * Get captain recommendations based on multiple factors
     */
    public function getCaptainRecommendations(int $gameweek): array
    {
        return DB::select("
            WITH player_fixture_info AS (
                SELECT 
                    p.fpl_id,
                    p.web_name,
                    p.position,
                    t.short_name as team,
                    ps.form,
                    ps.total_points,
                    ps.selected_by_percent,
                    f.fixture_id,
                    CASE WHEN f.home_team = t.fpl_id THEN 'Home' ELSE 'Away' END as venue,
                    opponent.short_name as opponent,
                    opponent.strength_overall_home + opponent.strength_overall_away as opponent_total_strength,
                    ABS(f.home_team_elo - f.away_team_elo) as elo_diff
                FROM players p
                INNER JOIN teams t ON p.team_code = t.fpl_code
                INNER JOIN player_stats ps ON p.fpl_id = ps.player_id
                LEFT JOIN fixtures f ON (f.home_team = t.fpl_id OR f.away_team = t.fpl_id) AND f.gameweek = ?
                LEFT JOIN teams opponent ON (CASE WHEN f.home_team = t.fpl_id THEN f.away_team ELSE f.home_team END) = opponent.fpl_id
                WHERE ps.gameweek = (SELECT MAX(gameweek) FROM player_stats)
                  AND ps.minutes > 500  -- Regular starters only
            ),
            captain_scores AS (
                SELECT 
                    *,
                    -- Form score (0-10)
                    LEAST(form, 10) as form_score,
                    -- Fixture difficulty score (0-10, higher is easier)
                    10 - LEAST(opponent_total_strength / 2.0, 10) as fixture_score,
                    -- Home advantage (0-2)
                    CASE WHEN venue = 'Home' THEN 2 ELSE 0 END as home_score,
                    -- Total points reliability (0-5)
                    LEAST(total_points / 50.0, 5) as reliability_score,
                    -- Low ownership bonus for differentials (0-3)
                    CASE 
                        WHEN selected_by_percent < 5 THEN 3
                        WHEN selected_by_percent < 15 THEN 2
                        WHEN selected_by_percent < 30 THEN 1
                        ELSE 0
                    END as differential_score
                FROM player_fixture_info
                WHERE fixture_id IS NOT NULL
            )
            SELECT 
                web_name,
                position,
                team,
                opponent,
                venue,
                form,
                selected_by_percent,
                (form_score + fixture_score + home_score + reliability_score + differential_score) as captain_score,
                form_score,
                fixture_score,
                home_score,
                reliability_score,
                differential_score,
                CASE 
                    WHEN (form_score + fixture_score + home_score + reliability_score + differential_score) >= 25 THEN 'Premium'
                    WHEN (form_score + fixture_score + home_score + reliability_score + differential_score) >= 20 THEN 'Excellent'
                    WHEN (form_score + fixture_score + home_score + reliability_score + differential_score) >= 15 THEN 'Good'
                    ELSE 'Consider'
                END as recommendation_level
            FROM captain_scores
            ORDER BY captain_score DESC
            LIMIT 15
        ", [$gameweek]);
    }

    /**
     * Get differential players (low ownership, high potential)
     */
    public function getDifferentialPlayers(float $maxOwnership = 10.0, int $minPoints = 50): array
    {
        return DB::select("
            SELECT 
                p.web_name,
                p.position,
                t.short_name as team,
                ps.selected_by_percent as ownership,
                ps.total_points,
                ps.form,
                ps.now_cost / 10.0 as cost,
                ROUND(ps.total_points / (ps.now_cost / 10.0), 2) as points_per_million,
                -- Recent gameweek performances
                (
                    SELECT COALESCE(AVG(pgs.total_points), 0)
                    FROM player_gameweek_stats pgs 
                    WHERE pgs.player_id = p.fpl_id 
                      AND pgs.gameweek > (SELECT MAX(gameweek) FROM player_gameweek_stats) - 5
                ) as avg_last_5_gw,
                -- Upcoming fixture difficulty
                (
                    SELECT COALESCE(AVG(
                        CASE WHEN f.home_team = t.fpl_id 
                        THEN (SELECT strength_overall_away FROM teams WHERE fpl_id = f.away_team)
                        ELSE (SELECT strength_overall_home FROM teams WHERE fpl_id = f.home_team)
                        END
                    ), 3)
                    FROM fixtures f 
                    WHERE (f.home_team = t.fpl_id OR f.away_team = t.fpl_id)
                      AND f.gameweek BETWEEN (SELECT COALESCE(MAX(gameweek), 1) FROM gameweeks WHERE is_current = 1) 
                                         AND (SELECT COALESCE(MAX(gameweek), 1) FROM gameweeks WHERE is_current = 1) + 3
                      AND f.finished = 0
                ) as next_3_fixture_difficulty,
                -- Injury status
                CASE 
                    WHEN ps.status = 'a' AND ps.chance_of_playing_next_round >= 75 THEN 'Fit'
                    WHEN ps.chance_of_playing_next_round >= 50 THEN 'Doubt'
                    ELSE 'Injury Risk'
                END as injury_status
            FROM players p
            INNER JOIN teams t ON p.team_code = t.fpl_code  
            INNER JOIN player_stats ps ON p.fpl_id = ps.player_id
            WHERE ps.gameweek = (SELECT MAX(gameweek) FROM player_stats)
              AND ps.selected_by_percent <= ?
              AND ps.total_points >= ?
              AND ps.status = 'a'
              AND ps.minutes > 200  -- Has played reasonable minutes
            ORDER BY (ps.total_points / (ps.now_cost / 10.0)) DESC, ps.form DESC
            LIMIT 20
        ", [$maxOwnership, $minPoints]);
    }

    /**
     * Get team clean sheet probability based on historical data
     */
    public function getCleanSheetProbabilities(int $nextGameweek): array
    {
        return DB::select("
            WITH team_defensive_stats AS (
                SELECT 
                    t.fpl_id,
                    t.name,
                    t.short_name,
                    COUNT(m.match_id) as matches_played,
                    SUM(CASE 
                        WHEN (m.home_team = t.fpl_id AND m.away_score = 0) OR 
                             (m.away_team = t.fpl_id AND m.home_score = 0) 
                        THEN 1 ELSE 0 
                    END) as clean_sheets,
                    AVG(CASE WHEN m.home_team = t.fpl_id THEN m.away_score ELSE m.home_score END) as avg_goals_conceded,
                    -- Home vs Away defensive record
                    AVG(CASE WHEN m.home_team = t.fpl_id THEN m.away_score ELSE NULL END) as home_goals_conceded_avg,
                    AVG(CASE WHEN m.away_team = t.fpl_id THEN m.home_score ELSE NULL END) as away_goals_conceded_avg
                FROM teams t
                LEFT JOIN matches m ON (m.home_team = t.fpl_id OR m.away_team = t.fpl_id) AND m.finished = 1
                GROUP BY t.fpl_id, t.name, t.short_name
                HAVING COUNT(m.match_id) > 0
            ),
            fixture_analysis AS (
                SELECT 
                    tds.*,
                    f.fixture_id,
                    f.gameweek,
                    CASE WHEN f.home_team = tds.fpl_id THEN 'Home' ELSE 'Away' END as venue,
                    opp.short_name as opponent,
                    -- Opponent attacking strength
                    AVG(CASE WHEN om.home_team = opp.fpl_id THEN om.home_score ELSE om.away_score END) as opp_avg_goals_scored
                FROM team_defensive_stats tds
                INNER JOIN fixtures f ON (f.home_team = tds.fpl_id OR f.away_team = tds.fpl_id) 
                INNER JOIN teams opp ON (CASE WHEN f.home_team = tds.fpl_id THEN f.away_team ELSE f.home_team END) = opp.fpl_id
                LEFT JOIN matches om ON (om.home_team = opp.fpl_id OR om.away_team = opp.fpl_id) AND om.finished = 1
                WHERE f.gameweek = ? AND f.finished = 0
                GROUP BY tds.fpl_id, tds.name, tds.short_name, tds.matches_played, tds.clean_sheets, 
                         tds.avg_goals_conceded, tds.home_goals_conceded_avg, tds.away_goals_conceded_avg,
                         f.fixture_id, f.gameweek, venue, opp.short_name
            )
            SELECT 
                name,
                short_name,
                opponent,
                venue,
                clean_sheets,
                matches_played,
                ROUND(clean_sheets * 100.0 / matches_played, 1) as cs_percentage,
                ROUND(avg_goals_conceded, 2) as avg_conceded,
                ROUND(opp_avg_goals_scored, 2) as opp_avg_scored,
                -- Adjusted clean sheet probability
                ROUND(
                    GREATEST(0, LEAST(100,
                        (clean_sheets * 100.0 / matches_played) * 
                        CASE WHEN venue = 'Home' THEN 1.2 ELSE 0.8 END *
                        (2.0 / GREATEST(opp_avg_goals_scored, 0.5))
                    ))
                , 1) as adjusted_cs_probability,
                CASE 
                    WHEN ROUND(
                        GREATEST(0, LEAST(100,
                            (clean_sheets * 100.0 / matches_played) * 
                            CASE WHEN venue = 'Home' THEN 1.2 ELSE 0.8 END *
                            (2.0 / GREATEST(opp_avg_goals_scored, 0.5))
                        ))
                    , 1) >= 40 THEN 'High'
                    WHEN ROUND(
                        GREATEST(0, LEAST(100,
                            (clean_sheets * 100.0 / matches_played) * 
                            CASE WHEN venue = 'Home' THEN 1.2 ELSE 0.8 END *
                            (2.0 / GREATEST(opp_avg_goals_scored, 0.5))
                        ))
                    , 1) >= 25 THEN 'Medium'
                    ELSE 'Low'
                END as cs_likelihood_rating
            FROM fixture_analysis
            ORDER BY adjusted_cs_probability DESC
        ", [$nextGameweek]);
    }

    /**
     * Get transfer recommendations based on price changes and form
     */
    public function getTransferRecommendations(): array
    {
        return DB::select("
            SELECT 
                p.web_name,
                p.position,
                t.short_name as team,
                ps.now_cost / 10.0 as current_cost,
                ps.selected_by_percent as ownership,
                ps.form,
                ps.total_points,
                ps.cost_change_start as season_price_change,
                ps.transfers_in_event as gw_transfers_in,
                ps.transfers_out_event as gw_transfers_out,
                -- Form trend (last 5 vs previous 5)
                (
                    SELECT AVG(pgs1.total_points) 
                    FROM player_gameweek_stats pgs1 
                    WHERE pgs1.player_id = p.fpl_id 
                      AND pgs1.gameweek > (SELECT MAX(gameweek) FROM player_gameweek_stats) - 5
                ) as avg_last_5,
                (
                    SELECT AVG(pgs2.total_points) 
                    FROM player_gameweek_stats pgs2 
                    WHERE pgs2.player_id = p.fpl_id 
                      AND pgs2.gameweek BETWEEN (SELECT MAX(gameweek) FROM player_gameweek_stats) - 10
                                            AND (SELECT MAX(gameweek) FROM player_gameweek_stats) - 6
                ) as avg_prev_5,
                -- Injury concerns
                CASE 
                    WHEN ps.status != 'a' OR ps.chance_of_playing_next_round < 75 THEN 1
                    ELSE 0
                END as has_injury_concern,
                -- Recommendation type
                CASE 
                    WHEN ps.form >= 7 AND ps.cost_change_start <= -2 THEN 'BUY - Good form, price dropped'
                    WHEN ps.transfers_out_event > ps.transfers_in_event * 2 AND ps.total_points >= 50 THEN 'BUY - Mass exodus, still good'
                    WHEN ps.form <= 3 AND ps.selected_by_percent > 15 THEN 'SELL - Poor form, high ownership'
                    WHEN ps.status != 'a' OR ps.chance_of_playing_next_round < 50 THEN 'SELL - Injury concern'
                    WHEN ps.cost_change_start >= 3 AND ps.form <= 5 THEN 'SELL - Overpriced, poor form'
                    ELSE 'HOLD'
                END as recommendation
            FROM players p
            INNER JOIN teams t ON p.team_code = t.fpl_code
            INNER JOIN player_stats ps ON p.fpl_id = ps.player_id
            WHERE ps.gameweek = (SELECT MAX(gameweek) FROM player_stats)
              AND ps.minutes > 100  -- Has played some minutes
            HAVING recommendation != 'HOLD'
            ORDER BY 
                CASE recommendation 
                    WHEN 'BUY - Good form, price dropped' THEN 1
                    WHEN 'BUY - Mass exodus, still good' THEN 2
                    WHEN 'SELL - Injury concern' THEN 3
                    WHEN 'SELL - Poor form, high ownership' THEN 4
                    WHEN 'SELL - Overpriced, poor form' THEN 5
                    ELSE 6
                END,
                ps.form DESC
        ");
    }

    /**
     * Get team fixtures difficulty rating for next 5 gameweeks
     */
    public function getTeamFixtureDifficulty(int $fromGameweek, int $gameweeks = 5): array
    {
        return DB::select("
            WITH team_fixtures AS (
                SELECT 
                    t.fpl_id,
                    t.name,
                    t.short_name,
                    f.gameweek,
                    f.fixture_id,
                    CASE WHEN f.home_team = t.fpl_id THEN 'H' ELSE 'A' END as venue,
                    CASE WHEN f.home_team = t.fpl_id THEN at.short_name ELSE ht.short_name END as opponent,
                    CASE WHEN f.home_team = t.fpl_id 
                         THEN at.strength_overall_away 
                         ELSE ht.strength_overall_home 
                    END as opponent_strength,
                    CASE 
                        WHEN (CASE WHEN f.home_team = t.fpl_id THEN at.strength_overall_away ELSE ht.strength_overall_home END) <= 2 THEN 2
                        WHEN (CASE WHEN f.home_team = t.fpl_id THEN at.strength_overall_away ELSE ht.strength_overall_home END) <= 3 THEN 3  
                        WHEN (CASE WHEN f.home_team = t.fpl_id THEN at.strength_overall_away ELSE ht.strength_overall_home END) <= 4 THEN 4
                        ELSE 5
                    END as difficulty_rating
                FROM teams t
                LEFT JOIN fixtures f ON (f.home_team = t.fpl_id OR f.away_team = t.fpl_id)
                LEFT JOIN teams ht ON f.home_team = ht.fpl_id
                LEFT JOIN teams at ON f.away_team = at.fpl_id
                WHERE f.gameweek BETWEEN ? AND ? + ? - 1
                  AND f.finished = 0
            )
            SELECT 
                name,
                short_name,
                COUNT(fixture_id) as fixtures_count,
                GROUP_CONCAT(CONCAT(opponent, '(', venue, ')') SEPARATOR ', ') as fixtures_list,
                ROUND(AVG(opponent_strength), 2) as avg_opponent_strength,
                ROUND(AVG(difficulty_rating), 2) as avg_difficulty_rating,
                SUM(CASE WHEN difficulty_rating = 2 THEN 1 ELSE 0 END) as easy_fixtures,
                SUM(CASE WHEN difficulty_rating = 3 THEN 1 ELSE 0 END) as moderate_fixtures,
                SUM(CASE WHEN difficulty_rating = 4 THEN 1 ELSE 0 END) as hard_fixtures,
                SUM(CASE WHEN difficulty_rating = 5 THEN 1 ELSE 0 END) as very_hard_fixtures,
                CASE 
                    WHEN AVG(difficulty_rating) <= 2.5 THEN 'Very Easy'
                    WHEN AVG(difficulty_rating) <= 3.0 THEN 'Easy'
                    WHEN AVG(difficulty_rating) <= 3.5 THEN 'Moderate'
                    WHEN AVG(difficulty_rating) <= 4.0 THEN 'Hard'
                    ELSE 'Very Hard'
                END as overall_difficulty
            FROM team_fixtures
            GROUP BY fpl_id, name, short_name
            ORDER BY avg_difficulty_rating ASC, fixtures_count DESC
        ", [$fromGameweek, $fromGameweek, $gameweeks]);
    }

    /**
     * Initialize all database views
     */
    public function initializeViews(): array
    {
        $results = [];
        
        $results['player_value_view'] = $this->createPlayerValueView();
        $results['fixture_difficulty_view'] = $this->createFixtureDifficultyView();
        
        return $results;
    }
}
