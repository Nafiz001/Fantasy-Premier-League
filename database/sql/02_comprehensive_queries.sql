-- =============================================
-- COMPREHENSIVE SQL QUERIES FOR FPL DATABASE
-- Database Lab Project - All SQL Operations Demo
-- Date: September 2025
-- =============================================

USE fpl;

-- =============================================
-- 1. BASIC CRUD OPERATIONS
-- =============================================

-- CREATE Operations
-- Insert a new player transfer
INSERT INTO player_transfers (player_id, from_team_code, to_team_code, transfer_date, transfer_fee, season)
SELECT 1, 4, 2, CURDATE(), 50000000, '2025/26'
WHERE NOT EXISTS (SELECT 1 FROM player_transfers WHERE player_id = 1 AND transfer_date = CURDATE());

-- Insert sample players
INSERT IGNORE INTO players (fpl_code, fpl_id, first_name, second_name, web_name, team_code, position, element_type) VALUES
(1, 1, 'Erling', 'Haaland', 'Haaland', 2, 'Forward', 4),
(2, 2, 'Mohamed', 'Salah', 'Salah', 3, 'Midfielder', 3),
(3, 3, 'Kevin', 'De Bruyne', 'De Bruyne', 2, 'Midfielder', 3),
(4, 4, 'Bukayo', 'Saka', 'Saka', 1, 'Midfielder', 3),
(5, 5, 'Trent', 'Alexander-Arnold', 'Alexander-Arnold', 3, 'Defender', 2),
(6, 6, 'Alisson', 'Becker', 'Alisson', 3, 'Goalkeeper', 1);

-- READ Operations with various WHERE clauses
-- Simple SELECT
SELECT * FROM teams WHERE strength > 80;

-- SELECT with LIKE
SELECT web_name, position FROM players WHERE web_name LIKE 'H%';

-- SELECT with IN
SELECT name, short_name FROM teams WHERE fpl_id IN (1, 2, 3);

-- UPDATE Operations
-- Update team form based on recent performance
UPDATE teams 
SET form = 4.5, points = points + 3 
WHERE fpl_id = 1;

-- Update player status
UPDATE player_stats 
SET status = 'i', news = 'Hamstring injury' 
WHERE player_id = 2 AND gameweek = 3;

-- DELETE Operations
-- Delete old transfer records (older than 2 years)
DELETE FROM player_transfers 
WHERE transfer_date < DATE_SUB(CURDATE(), INTERVAL 2 YEAR);

-- Soft delete (archive) old gameweek data
UPDATE player_gameweek_stats 
SET archived_at = NOW() 
WHERE gameweek < 2;

-- =============================================
-- 2. COMPLEX JOINS (Inner, Left, Right, Cross)
-- =============================================

-- INNER JOIN - Players with their team information and current stats
SELECT 
    p.web_name,
    p.position,
    t.name as team_name,
    t.short_name as team_short,
    ps.total_points,
    ps.now_cost / 10.0 as cost_millions,
    ps.selected_by_percent,
    ps.form
FROM players p
INNER JOIN teams t ON p.team_code = t.fpl_code
INNER JOIN player_stats ps ON p.fpl_id = ps.player_id
WHERE ps.gameweek = 3 AND ps.minutes > 500
ORDER BY ps.total_points DESC;

-- LEFT JOIN - All teams with their top scorer (if any)
SELECT 
    t.name as team_name,
    t.short_name,
    p.web_name as top_scorer,
    ps.goals_scored,
    ps.total_points
FROM teams t
LEFT JOIN players p ON t.fpl_code = p.team_code
LEFT JOIN player_stats ps ON p.fpl_id = ps.player_id AND ps.gameweek = 3
LEFT JOIN (
    SELECT team_code, MAX(goals_scored) as max_goals
    FROM players p2
    JOIN player_stats ps2 ON p2.fpl_id = ps2.player_id
    WHERE ps2.gameweek = 3
    GROUP BY team_code
) max_goals ON t.fpl_code = max_goals.team_code AND ps.goals_scored = max_goals.max_goals;

-- SELF JOIN - Players from the same team
SELECT 
    p1.web_name as player1,
    p2.web_name as player2,
    t.name as team_name
FROM players p1
JOIN players p2 ON p1.team_code = p2.team_code AND p1.fpl_id < p2.fpl_id
JOIN teams t ON p1.team_code = t.fpl_code
WHERE p1.element_type = p2.element_type  -- Same position
ORDER BY t.name, p1.web_name;

-- Multiple JOINs - Complete fixture information
SELECT 
    f.fixture_id,
    f.gameweek,
    ht.name as home_team,
    ht.short_name as home_short,
    at.name as away_team,
    at.short_name as away_short,
    f.kickoff_time,
    f.home_team_elo,
    f.away_team_elo,
    ABS(f.home_team_elo - f.away_team_elo) as elo_difference
FROM fixtures f
INNER JOIN teams ht ON f.home_team = ht.fpl_id
INNER JOIN teams at ON f.away_team = at.fpl_id
WHERE f.finished = 0
ORDER BY f.gameweek, f.kickoff_time;

-- =============================================
-- 3. SUBQUERIES (Correlated and Non-correlated)
-- =============================================

-- Non-correlated subquery - Players above average points
SELECT 
    p.web_name,
    t.short_name as team,
    ps.total_points,
    (SELECT AVG(total_points) FROM player_stats WHERE gameweek = 3) as avg_points
FROM players p
JOIN teams t ON p.team_code = t.fpl_code
JOIN player_stats ps ON p.fpl_id = ps.player_id
WHERE ps.gameweek = 3 
  AND ps.total_points > (
      SELECT AVG(total_points) 
      FROM player_stats 
      WHERE gameweek = 3
  )
ORDER BY ps.total_points DESC;

-- Correlated subquery - Each team's top scorer
SELECT 
    t.name as team_name,
    p.web_name,
    ps.goals_scored
FROM teams t
JOIN players p ON t.fpl_code = p.team_code
JOIN player_stats ps ON p.fpl_id = ps.player_id
WHERE ps.gameweek = 3
  AND ps.goals_scored = (
      SELECT MAX(ps2.goals_scored)
      FROM players p2
      JOIN player_stats ps2 ON p2.fpl_id = ps2.player_id
      WHERE p2.team_code = p.team_code AND ps2.gameweek = 3
  )
ORDER BY ps.goals_scored DESC;

-- EXISTS subquery - Teams with at least one player over 100 points
SELECT t.name, t.short_name
FROM teams t
WHERE EXISTS (
    SELECT 1 
    FROM players p 
    JOIN player_stats ps ON p.fpl_id = ps.player_id
    WHERE p.team_code = t.fpl_code 
      AND ps.gameweek = 3 
      AND ps.total_points > 100
);

-- Subquery in FROM clause - Team performance summary
SELECT 
    team_summary.team_name,
    team_summary.total_team_points,
    team_summary.avg_player_points,
    team_summary.player_count
FROM (
    SELECT 
        t.name as team_name,
        SUM(ps.total_points) as total_team_points,
        AVG(ps.total_points) as avg_player_points,
        COUNT(ps.player_id) as player_count
    FROM teams t
    JOIN players p ON t.fpl_code = p.team_code
    JOIN player_stats ps ON p.fpl_id = ps.player_id
    WHERE ps.gameweek = 3
    GROUP BY t.fpl_id, t.name
) team_summary
WHERE team_summary.avg_player_points > 60
ORDER BY team_summary.total_team_points DESC;

-- =============================================
-- 4. GROUP BY with HAVING clauses
-- =============================================

-- Team statistics grouped by team
SELECT 
    t.name as team_name,
    COUNT(p.fpl_id) as player_count,
    SUM(ps.total_points) as total_points,
    AVG(ps.total_points) as avg_points,
    MAX(ps.total_points) as highest_scorer_points,
    MIN(ps.total_points) as lowest_scorer_points,
    SUM(ps.goals_scored) as total_goals,
    SUM(ps.assists) as total_assists
FROM teams t
JOIN players p ON t.fpl_code = p.team_code
JOIN player_stats ps ON p.fpl_id = ps.player_id
WHERE ps.gameweek = 3
GROUP BY t.fpl_id, t.name
HAVING AVG(ps.total_points) > 70
ORDER BY avg_points DESC;

-- Position-wise analysis
SELECT 
    p.position,
    p.element_type,
    COUNT(*) as player_count,
    AVG(ps.total_points) as avg_points,
    AVG(ps.now_cost / 10.0) as avg_cost_millions,
    AVG(ps.selected_by_percent) as avg_ownership,
    SUM(ps.goals_scored) as total_goals,
    SUM(ps.assists) as total_assists
FROM players p
JOIN player_stats ps ON p.fpl_id = ps.player_id
WHERE ps.gameweek = 3 AND ps.minutes > 200
GROUP BY p.position, p.element_type
HAVING COUNT(*) >= 3 AND AVG(ps.total_points) > 50
ORDER BY avg_points DESC;

-- Monthly transfer activity analysis
SELECT 
    YEAR(transfer_date) as transfer_year,
    MONTH(transfer_date) as transfer_month,
    COUNT(*) as transfer_count,
    AVG(transfer_fee) as avg_transfer_fee,
    MAX(transfer_fee) as highest_transfer,
    MIN(transfer_fee) as lowest_transfer
FROM player_transfers
WHERE transfer_fee IS NOT NULL
GROUP BY YEAR(transfer_date), MONTH(transfer_date)
HAVING COUNT(*) > 1 AND AVG(transfer_fee) > 10000000
ORDER BY transfer_year DESC, transfer_month DESC;

-- =============================================
-- 5. AGGREGATE FUNCTIONS
-- =============================================

-- Comprehensive player statistics
SELECT 
    COUNT(*) as total_players,
    COUNT(DISTINCT team_code) as teams_represented,
    SUM(ps.total_points) as total_points_all_players,
    AVG(ps.total_points) as average_points,
    STDDEV(ps.total_points) as points_standard_deviation,
    MAX(ps.total_points) as highest_points,
    MIN(ps.total_points) as lowest_points,
    SUM(ps.goals_scored) as total_goals,
    SUM(ps.assists) as total_assists,
    AVG(ps.now_cost / 10.0) as avg_cost_millions,
    SUM(ps.transfers_in_event) as total_transfers_in,
    SUM(ps.transfers_out_event) as total_transfers_out
FROM players p
JOIN player_stats ps ON p.fpl_id = ps.player_id
WHERE ps.gameweek = 3;

-- Team attacking vs defensive stats
SELECT 
    t.name,
    -- Attacking stats
    SUM(ps.goals_scored) as goals_scored,
    SUM(ps.assists) as assists,
    AVG(ps.expected_goals) as avg_xg,
    AVG(ps.expected_assists) as avg_xa,
    
    -- Defensive stats (for defenders and GKs)
    SUM(CASE WHEN p.element_type IN (1,2) THEN ps.clean_sheets ELSE 0 END) as clean_sheets,
    SUM(CASE WHEN p.element_type = 1 THEN ps.saves ELSE 0 END) as saves,
    AVG(CASE WHEN p.element_type IN (1,2) THEN ps.expected_goals_conceded ELSE NULL END) as avg_xgc,
    
    -- Overall performance
    AVG(ps.total_points) as avg_team_points,
    SUM(ps.bonus) as total_bonus_points,
    COUNT(*) as squad_size
FROM teams t
JOIN players p ON t.fpl_code = p.team_code
JOIN player_stats ps ON p.fpl_id = ps.player_id
WHERE ps.gameweek = 3
GROUP BY t.fpl_id, t.name
ORDER BY avg_team_points DESC;

-- Value analysis using multiple aggregates
SELECT 
    p.position,
    COUNT(*) as players_in_position,
    MIN(ps.now_cost / 10.0) as cheapest_player,
    MAX(ps.now_cost / 10.0) as most_expensive_player,
    AVG(ps.now_cost / 10.0) as avg_cost,
    AVG(ps.total_points / (ps.now_cost / 10.0)) as avg_points_per_million,
    MAX(ps.total_points / (ps.now_cost / 10.0)) as best_value_ratio,
    STDDEV(ps.total_points / (ps.now_cost / 10.0)) as value_consistency
FROM players p
JOIN player_stats ps ON p.fpl_id = ps.player_id
WHERE ps.gameweek = 3 AND ps.now_cost > 0
GROUP BY p.position
ORDER BY avg_points_per_million DESC;

-- =============================================
-- 6. WINDOW FUNCTIONS (Advanced Analytics)
-- =============================================

-- Player ranking within their position
SELECT 
    p.web_name,
    p.position,
    t.short_name as team,
    ps.total_points,
    ps.now_cost / 10.0 as cost,
    RANK() OVER (PARTITION BY p.element_type ORDER BY ps.total_points DESC) as position_rank,
    DENSE_RANK() OVER (ORDER BY ps.total_points DESC) as overall_rank,
    ROW_NUMBER() OVER (PARTITION BY p.element_type ORDER BY ps.total_points DESC) as position_row_num,
    AVG(ps.total_points) OVER (PARTITION BY p.element_type) as position_avg_points,
    ps.total_points - AVG(ps.total_points) OVER (PARTITION BY p.element_type) as points_above_position_avg
FROM players p
JOIN teams t ON p.team_code = t.fpl_code
JOIN player_stats ps ON p.fpl_id = ps.player_id
WHERE ps.gameweek = 3
ORDER BY ps.total_points DESC;

-- Running totals and moving averages
SELECT 
    p.web_name,
    pgs.gameweek,
    pgs.total_points as gw_points,
    SUM(pgs.total_points) OVER (PARTITION BY p.fpl_id ORDER BY pgs.gameweek) as running_total,
    AVG(pgs.total_points) OVER (PARTITION BY p.fpl_id ORDER BY pgs.gameweek ROWS BETWEEN 2 PRECEDING AND CURRENT ROW) as last_3_gw_avg,
    LAG(pgs.total_points, 1) OVER (PARTITION BY p.fpl_id ORDER BY pgs.gameweek) as previous_gw_points,
    LEAD(pgs.total_points, 1) OVER (PARTITION BY p.fpl_id ORDER BY pgs.gameweek) as next_gw_points
FROM players p
JOIN player_gameweek_stats pgs ON p.fpl_id = pgs.player_id
WHERE p.fpl_id IN (1, 2, 3, 4, 5)
ORDER BY p.web_name, pgs.gameweek;

-- =============================================
-- 7. COMMON TABLE EXPRESSIONS (CTEs)
-- =============================================

-- Recursive CTE for league table calculation
WITH RECURSIVE team_results AS (
    -- Base case: initial team data
    SELECT 
        t.fpl_id,
        t.name,
        0 as matches_played,
        0 as wins,
        0 as draws,
        0 as losses,
        0 as goals_for,
        0 as goals_against,
        0 as points
    FROM teams t
    
    UNION ALL
    
    -- Recursive case: add match results
    SELECT 
        tr.fpl_id,
        tr.name,
        tr.matches_played + 1,
        tr.wins + CASE 
            WHEN (m.home_team = tr.fpl_id AND m.home_score > m.away_score) OR 
                 (m.away_team = tr.fpl_id AND m.away_score > m.home_score) THEN 1 
            ELSE 0 
        END,
        tr.draws + CASE 
            WHEN m.home_score = m.away_score THEN 1 
            ELSE 0 
        END,
        tr.losses + CASE 
            WHEN (m.home_team = tr.fpl_id AND m.home_score < m.away_score) OR 
                 (m.away_team = tr.fpl_id AND m.away_score < m.home_score) THEN 1 
            ELSE 0 
        END,
        tr.goals_for + CASE 
            WHEN m.home_team = tr.fpl_id THEN m.home_score 
            ELSE m.away_score 
        END,
        tr.goals_against + CASE 
            WHEN m.home_team = tr.fpl_id THEN m.away_score 
            ELSE m.home_score 
        END,
        tr.points + CASE 
            WHEN (m.home_team = tr.fpl_id AND m.home_score > m.away_score) OR 
                 (m.away_team = tr.fpl_id AND m.away_score > m.home_score) THEN 3
            WHEN m.home_score = m.away_score THEN 1
            ELSE 0 
        END
    FROM team_results tr
    JOIN matches m ON (m.home_team = tr.fpl_id OR m.away_team = tr.fpl_id)
    WHERE m.finished = 1 AND tr.matches_played < 10  -- Limit recursion
),

-- Multiple CTEs for complex analysis
top_performers AS (
    SELECT 
        p.fpl_id,
        p.web_name,
        t.short_name as team,
        ps.total_points,
        RANK() OVER (ORDER BY ps.total_points DESC) as rank
    FROM players p
    JOIN teams t ON p.team_code = t.fpl_code
    JOIN player_stats ps ON p.fpl_id = ps.player_id
    WHERE ps.gameweek = 3
),

team_averages AS (
    SELECT 
        t.fpl_id,
        t.name,
        AVG(ps.total_points) as avg_points
    FROM teams t
    JOIN players p ON t.fpl_code = p.team_code
    JOIN player_stats ps ON p.fpl_id = ps.player_id
    WHERE ps.gameweek = 3
    GROUP BY t.fpl_id, t.name
)

SELECT 
    tp.web_name,
    tp.team,
    tp.total_points,
    tp.rank,
    ta.avg_points as team_avg,
    tp.total_points - ta.avg_points as points_above_team_avg
FROM top_performers tp
JOIN team_averages ta ON tp.team = (SELECT short_name FROM teams WHERE fpl_id = ta.fpl_id)
WHERE tp.rank <= 10;

-- =============================================
-- 8. ADVANCED ANALYTICAL QUERIES
-- =============================================

-- Fixture difficulty analysis
SELECT 
    f.gameweek,
    ht.name as home_team,
    at.name as away_team,
    f.home_team_elo,
    f.away_team_elo,
    ABS(f.home_team_elo - f.away_team_elo) as elo_difference,
    CASE 
        WHEN ABS(f.home_team_elo - f.away_team_elo) < 50 THEN 'Very Close'
        WHEN ABS(f.home_team_elo - f.away_team_elo) < 100 THEN 'Close'
        WHEN ABS(f.home_team_elo - f.away_team_elo) < 200 THEN 'Moderate'
        WHEN ABS(f.home_team_elo - f.away_team_elo) < 300 THEN 'Clear Favorite'
        ELSE 'Huge Favorite'
    END as match_competitiveness,
    CASE 
        WHEN f.home_team_elo > f.away_team_elo + 100 THEN CONCAT(ht.short_name, ' (H)')
        WHEN f.away_team_elo > f.home_team_elo + 100 THEN CONCAT(at.short_name, ' (A)')
        ELSE 'Toss-up'
    END as predicted_winner
FROM fixtures f
JOIN teams ht ON f.home_team = ht.fpl_id
JOIN teams at ON f.away_team = at.fpl_id
WHERE f.finished = 0
ORDER BY f.gameweek, elo_difference DESC;

-- Player consistency analysis
SELECT 
    p.web_name,
    t.short_name as team,
    COUNT(pgs.gameweek) as games_played,
    AVG(pgs.total_points) as avg_points,
    STDDEV(pgs.total_points) as consistency_score,
    MIN(pgs.total_points) as worst_performance,
    MAX(pgs.total_points) as best_performance,
    SUM(CASE WHEN pgs.total_points >= 6 THEN 1 ELSE 0 END) as good_performances,
    SUM(CASE WHEN pgs.total_points <= 2 THEN 1 ELSE 0 END) as poor_performances,
    (SUM(CASE WHEN pgs.total_points >= 6 THEN 1 ELSE 0 END) * 100.0 / COUNT(pgs.gameweek)) as good_performance_percentage
FROM players p
JOIN teams t ON p.team_code = t.fpl_code
JOIN player_gameweek_stats pgs ON p.fpl_id = pgs.player_id
GROUP BY p.fpl_id, p.web_name, t.short_name
HAVING COUNT(pgs.gameweek) >= 3
ORDER BY consistency_score ASC, avg_points DESC;

SELECT 'All comprehensive SQL queries executed successfully!' as Status;
