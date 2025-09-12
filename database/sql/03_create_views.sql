-- =============================================
-- DATABASE VIEWS FOR FPL PROJECT
-- Advanced Database Lab Project
-- Date: September 2025
-- =============================================

USE fpl;

-- =============================================
-- 1. PLAYER VALUE ANALYSIS VIEW
-- =============================================
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
    RANK() OVER (PARTITION BY p.element_type ORDER BY ps.total_points / (ps.now_cost / 10.0) DESC) as value_rank_in_position,
    ps.gameweek
FROM player_stats ps
INNER JOIN players p ON ps.player_id = p.fpl_id
INNER JOIN teams t ON p.team_code = t.fpl_code
WHERE ps.now_cost > 0 AND ps.minutes > 0;

-- =============================================
-- 2. TEAM PERFORMANCE SUMMARY VIEW
-- =============================================
CREATE OR REPLACE VIEW team_performance_summary AS
SELECT 
    t.fpl_id,
    t.name as team_name,
    t.short_name,
    t.elo as current_elo,
    
    -- Squad statistics
    COUNT(p.fpl_id) as squad_size,
    SUM(ps.total_points) as total_squad_points,
    AVG(ps.total_points) as avg_player_points,
    MAX(ps.total_points) as best_player_points,
    
    -- Financial metrics
    SUM(ps.now_cost) / 10.0 as total_squad_value_millions,
    AVG(ps.now_cost) / 10.0 as avg_player_cost_millions,
    AVG(ps.selected_by_percent) as avg_ownership,
    
    -- Performance metrics
    SUM(ps.goals_scored) as total_goals,
    SUM(ps.assists) as total_assists,
    SUM(ps.bonus) as total_bonus_points,
    AVG(ps.form) as avg_form,
    
    -- Defensive metrics (for defenders and GKs)
    SUM(CASE WHEN p.element_type IN (1,2) THEN ps.clean_sheets ELSE 0 END) as total_clean_sheets,
    SUM(CASE WHEN p.element_type = 1 THEN ps.saves ELSE 0 END) as total_saves,
    
    -- Expected stats
    AVG(ps.expected_goals) as avg_xg,
    AVG(ps.expected_assists) as avg_xa,
    
    ps.gameweek
FROM teams t
LEFT JOIN players p ON t.fpl_code = p.team_code
LEFT JOIN player_stats ps ON p.fpl_id = ps.player_id
GROUP BY t.fpl_id, t.name, t.short_name, t.elo, ps.gameweek;

-- =============================================
-- 3. FIXTURE DIFFICULTY ANALYSIS VIEW
-- =============================================
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
    
    -- Home team difficulty (from their perspective)
    CASE 
        WHEN f.away_team_elo - f.home_team_elo > 200 THEN 5  -- Very difficult
        WHEN f.away_team_elo - f.home_team_elo > 100 THEN 4  -- Difficult
        WHEN ABS(f.home_team_elo - f.away_team_elo) <= 100 THEN 3  -- Average
        WHEN f.home_team_elo - f.away_team_elo > 100 THEN 2  -- Easy
        ELSE 1  -- Very easy
    END as home_difficulty,
    
    -- Away team difficulty (from their perspective)
    CASE 
        WHEN f.home_team_elo - f.away_team_elo > 200 THEN 5  -- Very difficult
        WHEN f.home_team_elo - f.away_team_elo > 100 THEN 4  -- Difficult
        WHEN ABS(f.home_team_elo - f.away_team_elo) <= 100 THEN 3  -- Average
        WHEN f.away_team_elo - f.home_team_elo > 100 THEN 2  -- Easy
        ELSE 1  -- Very easy
    END as away_difficulty,
    
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
    END as predicted_winner,
    
    -- Win probabilities (simplified ELO calculation)
    ROUND(1 / (1 + POWER(10, (f.away_team_elo - f.home_team_elo - 100) / 400)) * 100, 1) as home_win_probability,
    ROUND(1 / (1 + POWER(10, (f.home_team_elo - f.away_team_elo + 100) / 400)) * 100, 1) as away_win_probability
FROM fixtures f
INNER JOIN teams ht ON f.home_team = ht.fpl_id
INNER JOIN teams at ON f.away_team = at.fpl_id
WHERE f.finished = 0;

-- =============================================
-- 4. POSITION RANKINGS VIEW
-- =============================================
CREATE OR REPLACE VIEW position_rankings AS
SELECT 
    p.web_name,
    p.position,
    CASE p.element_type
        WHEN 1 THEN 'Goalkeeper'
        WHEN 2 THEN 'Defender'
        WHEN 3 THEN 'Midfielder'
        WHEN 4 THEN 'Forward'
    END as position_full,
    t.short_name as team,
    ps.total_points,
    ps.now_cost / 10.0 as cost_millions,
    ps.selected_by_percent,
    ps.form,
    ps.goals_scored,
    ps.assists,
    ps.bonus,
    
    -- Rankings
    RANK() OVER (PARTITION BY p.element_type ORDER BY ps.total_points DESC) as position_rank,
    DENSE_RANK() OVER (ORDER BY ps.total_points DESC) as overall_rank,
    RANK() OVER (PARTITION BY p.element_type ORDER BY ps.total_points / (ps.now_cost / 10.0) DESC) as value_rank_in_position,
    
    -- Compared to position average
    AVG(ps.total_points) OVER (PARTITION BY p.element_type) as position_avg_points,
    ps.total_points - AVG(ps.total_points) OVER (PARTITION BY p.element_type) as points_above_position_avg,
    
    -- Value metrics
    ROUND(ps.total_points / (ps.now_cost / 10.0), 2) as points_per_million,
    ROUND(ps.total_points / NULLIF(ps.minutes / 90.0, 0), 2) as points_per_90,
    
    ps.gameweek
FROM player_stats ps
INNER JOIN players p ON ps.player_id = p.fpl_id
INNER JOIN teams t ON p.team_code = t.fpl_code
WHERE ps.minutes > 0 AND ps.now_cost > 0;

-- =============================================
-- 5. TRANSFER MARKET ANALYSIS VIEW
-- =============================================
CREATE OR REPLACE VIEW transfer_market_analysis AS
SELECT 
    p.web_name,
    p.position,
    t.short_name as team,
    ps.now_cost / 10.0 as current_cost,
    ps.selected_by_percent as ownership,
    ps.form,
    ps.total_points,
    ps.cost_change_start / 10.0 as season_price_change_millions,
    ps.transfers_in_event as gw_transfers_in,
    ps.transfers_out_event as gw_transfers_out,
    
    -- Transfer momentum
    CASE 
        WHEN ps.transfers_in_event > ps.transfers_out_event * 2 THEN 'High Demand'
        WHEN ps.transfers_in_event > ps.transfers_out_event THEN 'Rising'
        WHEN ps.transfers_out_event > ps.transfers_in_event * 2 THEN 'Falling Fast'
        WHEN ps.transfers_out_event > ps.transfers_in_event THEN 'Declining'
        ELSE 'Stable'
    END as transfer_momentum,
    
    -- Potential price changes
    CASE 
        WHEN ps.transfers_in_event > 200000 AND ps.cost_change_event >= 0 THEN 'Likely Rise'
        WHEN ps.transfers_out_event > 200000 AND ps.cost_change_event <= 0 THEN 'Likely Fall'
        ELSE 'Stable Price'
    END as price_prediction,
    
    -- Value assessment
    ROUND(ps.total_points / (ps.now_cost / 10.0), 2) as points_per_million,
    CASE 
        WHEN ps.total_points / (ps.now_cost / 10.0) >= 20 THEN 'Excellent Value'
        WHEN ps.total_points / (ps.now_cost / 10.0) >= 15 THEN 'Good Value'
        WHEN ps.total_points / (ps.now_cost / 10.0) >= 10 THEN 'Fair Value'
        ELSE 'Poor Value'
    END as value_rating,
    
    -- Recommendation
    CASE 
        WHEN ps.form >= 7 AND ps.cost_change_start <= -20 THEN 'BUY - Good form, price dropped'
        WHEN ps.transfers_out_event > ps.transfers_in_event * 2 AND ps.total_points >= 80 THEN 'BUY - Mass exodus, still good'
        WHEN ps.form <= 3 AND ps.selected_by_percent > 15 THEN 'SELL - Poor form, high ownership'
        WHEN ps.status != 'a' THEN 'SELL - Injury concern'
        WHEN ps.cost_change_start >= 30 AND ps.form <= 5 THEN 'SELL - Overpriced, poor form'
        ELSE 'HOLD'
    END as recommendation,
    
    ps.gameweek
FROM player_stats ps
INNER JOIN players p ON ps.player_id = p.fpl_id
INNER JOIN teams t ON p.team_code = t.fpl_code
WHERE ps.minutes > 100;

-- =============================================
-- 6. MATCH STATISTICS SUMMARY VIEW
-- =============================================
CREATE OR REPLACE VIEW match_statistics_summary AS
SELECT 
    m.match_id,
    m.gameweek,
    ht.name as home_team,
    at.name as away_team,
    CONCAT(m.home_score, ' - ', m.away_score) as score,
    m.kickoff_time,
    
    -- Possession
    m.home_possession,
    m.away_possession,
    
    -- Shooting stats
    m.home_total_shots,
    m.away_total_shots,
    m.home_shots_on_target,
    m.away_shots_on_target,
    ROUND(m.home_shots_on_target * 100.0 / NULLIF(m.home_total_shots, 0), 1) as home_shot_accuracy,
    ROUND(m.away_shots_on_target * 100.0 / NULLIF(m.away_total_shots, 0), 1) as away_shot_accuracy,
    
    -- Expected goals
    m.home_expected_goals_xg,
    m.away_expected_goals_xg,
    ROUND(m.home_score - m.home_expected_goals_xg, 2) as home_xg_difference,
    ROUND(m.away_score - m.away_expected_goals_xg, 2) as away_xg_difference,
    
    -- Defensive actions
    m.home_tackles_won,
    m.away_tackles_won,
    m.home_interceptions,
    m.away_interceptions,
    m.home_clearances,
    m.away_clearances,
    
    -- Passing
    m.home_accurate_passes,
    m.away_accurate_passes,
    m.home_accurate_passes_pct,
    m.away_accurate_passes_pct,
    
    -- Match outcome indicators
    CASE 
        WHEN m.home_score > m.away_score THEN 'Home Win'
        WHEN m.away_score > m.home_score THEN 'Away Win'
        ELSE 'Draw'
    END as result,
    
    -- Performance indicators
    CASE 
        WHEN m.home_expected_goals_xg > m.away_expected_goals_xg THEN 'Home Dominated'
        WHEN m.away_expected_goals_xg > m.home_expected_goals_xg THEN 'Away Dominated'
        ELSE 'Even Contest'
    END as xg_performance,
    
    m.finished
FROM matches m
INNER JOIN teams ht ON m.home_team = ht.fpl_id
INNER JOIN teams at ON m.away_team = at.fpl_id;

-- =============================================
-- 7. CAPTAIN RECOMMENDATION VIEW
-- =============================================
CREATE OR REPLACE VIEW captain_recommendations AS
SELECT 
    p.web_name,
    p.position,
    t.short_name as team,
    ps.total_points,
    ps.form,
    ps.selected_by_percent as ownership,
    ps.now_cost / 10.0 as cost,
    
    -- Next fixture info (simplified - would need actual fixture join)
    'TBD' as next_opponent,
    'TBD' as venue,
    3 as fixture_difficulty,
    
    -- Captain score calculation
    (
        LEAST(ps.form, 10) * 2 +  -- Form score (max 20)
        LEAST(ps.total_points / 10, 15) +  -- Points reliability (max 15)
        (10 - LEAST(3, 10)) * 1.5 +  -- Fixture difficulty (max 10.5, easier = higher)
        CASE WHEN ps.selected_by_percent < 5 THEN 5  -- Differential bonus
             WHEN ps.selected_by_percent < 15 THEN 3
             WHEN ps.selected_by_percent < 30 THEN 1
             ELSE 0 END +
        CASE WHEN p.element_type = 4 THEN 2  -- Forward bonus
             WHEN p.element_type = 3 THEN 1  -- Midfielder bonus
             ELSE 0 END
    ) as captain_score,
    
    -- Recommendation level
    CASE 
        WHEN (
            LEAST(ps.form, 10) * 2 +
            LEAST(ps.total_points / 10, 15) +
            (10 - LEAST(3, 10)) * 1.5 +
            CASE WHEN ps.selected_by_percent < 5 THEN 5
                 WHEN ps.selected_by_percent < 15 THEN 3
                 WHEN ps.selected_by_percent < 30 THEN 1
                 ELSE 0 END +
            CASE WHEN p.element_type = 4 THEN 2
                 WHEN p.element_type = 3 THEN 1
                 ELSE 0 END
        ) >= 35 THEN 'Premium Captain'
        WHEN (
            LEAST(ps.form, 10) * 2 +
            LEAST(ps.total_points / 10, 15) +
            (10 - LEAST(3, 10)) * 1.5 +
            CASE WHEN ps.selected_by_percent < 5 THEN 5
                 WHEN ps.selected_by_percent < 15 THEN 3
                 WHEN ps.selected_by_percent < 30 THEN 1
                 ELSE 0 END +
            CASE WHEN p.element_type = 4 THEN 2
                 WHEN p.element_type = 3 THEN 1
                 ELSE 0 END
        ) >= 25 THEN 'Excellent Choice'
        WHEN (
            LEAST(ps.form, 10) * 2 +
            LEAST(ps.total_points / 10, 15) +
            (10 - LEAST(3, 10)) * 1.5 +
            CASE WHEN ps.selected_by_percent < 5 THEN 5
                 WHEN ps.selected_by_percent < 15 THEN 3
                 WHEN ps.selected_by_percent < 30 THEN 1
                 ELSE 0 END +
            CASE WHEN p.element_type = 4 THEN 2
                 WHEN p.element_type = 3 THEN 1
                 ELSE 0 END
        ) >= 20 THEN 'Good Option'
        ELSE 'Consider Others'
    END as recommendation_level,
    
    ps.gameweek
FROM player_stats ps
INNER JOIN players p ON ps.player_id = p.fpl_id
INNER JOIN teams t ON p.team_code = t.fpl_code
WHERE ps.minutes > 500 AND ps.status = 'a';

-- =============================================
-- 8. FORM TABLE VIEW (Last 5 matches)
-- =============================================
CREATE OR REPLACE VIEW form_table AS
SELECT 
    t.name as team_name,
    t.short_name,
    
    -- Recent form metrics (would need actual match data for accuracy)
    5 as matches_played,  -- Placeholder
    3 as wins,            -- Placeholder
    1 as draws,           -- Placeholder  
    1 as losses,          -- Placeholder
    8 as goals_for,       -- Placeholder
    4 as goals_against,   -- Placeholder
    (8 - 4) as goal_difference,  -- Placeholder
    (3 * 3 + 1 * 1) as form_points,  -- Placeholder
    
    -- Performance percentage
    ROUND((3 * 3 + 1 * 1) * 100.0 / (5 * 3), 1) as points_percentage,
    
    -- Form rating
    CASE 
        WHEN (3 * 3 + 1 * 1) >= 12 THEN 'Excellent'
        WHEN (3 * 3 + 1 * 1) >= 9 THEN 'Good'
        WHEN (3 * 3 + 1 * 1) >= 6 THEN 'Average'
        WHEN (3 * 3 + 1 * 1) >= 3 THEN 'Poor'
        ELSE 'Very Poor'
    END as form_rating,
    
    t.elo as current_elo,
    t.strength as overall_strength
FROM teams t
ORDER BY (3 * 3 + 1 * 1) DESC, (8 - 4) DESC;

-- Create indexes on views for better performance
CREATE INDEX idx_player_value_gameweek ON player_value_analysis(gameweek);
CREATE INDEX idx_team_performance_gameweek ON team_performance_summary(gameweek);
CREATE INDEX idx_fixture_difficulty_gameweek ON fixture_difficulty_analysis(gameweek);
CREATE INDEX idx_position_rankings_gameweek ON position_rankings(gameweek);
CREATE INDEX idx_transfer_analysis_gameweek ON transfer_market_analysis(gameweek);
CREATE INDEX idx_captain_recommendations_gameweek ON captain_recommendations(gameweek);

SELECT 'All database views created successfully!' as Status;
