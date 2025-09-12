-- =============================================
-- COMPLETE DATABASE SETUP AND EXECUTION SCRIPT
-- Fantasy Premier League Database Lab Project
-- Execute this file to set up the complete database
-- Date: September 2025
-- =============================================

-- Step 1: Database and Tables Creation
SOURCE c:/xampp/htdocs/fantasy-premier-league/database/sql/01_create_database_and_tables.sql;

-- Step 2: Comprehensive Queries Examples
-- NOTE: This file contains examples, not executable statements
-- SOURCE c:/xampp/htdocs/fantasy-premier-league/database/sql/02_comprehensive_queries.sql;

-- Step 3: Views Creation
SOURCE c:/xampp/htdocs/fantasy-premier-league/database/sql/03_create_views.sql;

-- Step 4: Stored Procedures and Functions
SOURCE c:/xampp/htdocs/fantasy-premier-league/database/sql/04_stored_procedures.sql;

-- Step 5: Triggers and Advanced Operations
SOURCE c:/xampp/htdocs/fantasy-premier-league/database/sql/05_triggers_advanced.sql;

-- =============================================
-- VERIFICATION AND TESTING SECTION
-- =============================================

USE fpl;

-- Verify database structure
SELECT 'DATABASE STRUCTURE VERIFICATION' as Section;
SHOW TABLES;

-- Check sample data
SELECT 'SAMPLE DATA VERIFICATION' as Section;
SELECT COUNT(*) as team_count FROM teams;
SELECT COUNT(*) as player_count FROM players;
SELECT COUNT(*) as gameweek_count FROM gameweeks;
SELECT COUNT(*) as fixture_count FROM fixtures;

-- Test views
SELECT 'TESTING VIEWS' as Section;
SELECT COUNT(*) as player_value_analysis_rows FROM player_value_analysis LIMIT 1;
SELECT COUNT(*) as team_performance_rows FROM team_performance_summary LIMIT 1;
SELECT COUNT(*) as position_rankings_rows FROM position_rankings LIMIT 1;

-- Test stored procedures
SELECT 'TESTING STORED PROCEDURES' as Section;
CALL GetTopPerformers(1, 5);

-- Test functions
SELECT 'TESTING FUNCTIONS' as Section;
SELECT GetPositionName(1) as goalkeeper_position;
SELECT GetPositionName(2) as defender_position;
SELECT GetPositionName(3) as midfielder_position;
SELECT GetPositionName(4) as forward_position;

-- =============================================
-- DEMONSTRATION QUERIES FOR DATABASE LAB
-- =============================================

SELECT 'DEMONSTRATION QUERIES FOR DATABASE LAB' as Section;

-- 1. BASIC CRUD OPERATIONS
SELECT '1. BASIC CRUD OPERATIONS' as Query_Type;

-- SELECT with multiple conditions
SELECT p.web_name, p.position, t.short_name, ps.total_points, ps.form
FROM players p
INNER JOIN teams t ON p.team_code = t.fpl_code
INNER JOIN player_stats ps ON p.fpl_id = ps.player_id
WHERE ps.total_points > 50 AND ps.form > 5.0
ORDER BY ps.total_points DESC
LIMIT 10;

-- 2. JOINS DEMONSTRATION
SELECT '2. JOINS DEMONSTRATION' as Query_Type;

-- Complex join with multiple tables
SELECT 
    p.web_name,
    t.name as team_name,
    ps.total_points,
    ps.now_cost / 10.0 as cost_millions,
    f.kickoff_time as next_fixture,
    CASE 
        WHEN f.home_team = t.fpl_id THEN 'Home'
        ELSE 'Away'
    END as venue
FROM players p
INNER JOIN teams t ON p.team_code = t.fpl_code
LEFT JOIN player_stats ps ON p.fpl_id = ps.player_id AND ps.gameweek = 1
LEFT JOIN fixtures f ON (f.home_team = t.fpl_id OR f.away_team = t.fpl_id) 
    AND f.finished = 0 AND f.gameweek = 2
WHERE ps.total_points IS NOT NULL
ORDER BY ps.total_points DESC
LIMIT 5;

-- 3. SUBQUERIES DEMONSTRATION
SELECT '3. SUBQUERIES DEMONSTRATION' as Query_Type;

-- Correlated subquery
SELECT 
    p.web_name,
    ps.total_points,
    (SELECT AVG(ps2.total_points) 
     FROM player_stats ps2 
     INNER JOIN players p2 ON ps2.player_id = p2.fpl_id 
     WHERE p2.element_type = p.element_type AND ps2.gameweek = ps.gameweek) as position_avg
FROM players p
INNER JOIN player_stats ps ON p.fpl_id = ps.player_id
WHERE ps.gameweek = 1
    AND ps.total_points > (
        SELECT AVG(ps2.total_points) 
        FROM player_stats ps2 
        INNER JOIN players p2 ON ps2.player_id = p2.fpl_id 
        WHERE p2.element_type = p.element_type AND ps2.gameweek = 1
    )
ORDER BY ps.total_points DESC
LIMIT 10;

-- 4. GROUP BY AND HAVING
SELECT '4. GROUP BY AND HAVING DEMONSTRATION' as Query_Type;

-- Team performance summary with having clause
SELECT 
    t.name,
    COUNT(p.fpl_id) as squad_size,
    AVG(ps.total_points) as avg_points,
    SUM(ps.total_points) as total_points,
    MAX(ps.total_points) as best_player_points
FROM teams t
INNER JOIN players p ON t.fpl_code = p.team_code
INNER JOIN player_stats ps ON p.fpl_id = ps.player_id
WHERE ps.gameweek = 1
GROUP BY t.fpl_id, t.name
HAVING AVG(ps.total_points) > 30
ORDER BY avg_points DESC;

-- 5. AGGREGATE FUNCTIONS
SELECT '5. AGGREGATE FUNCTIONS DEMONSTRATION' as Query_Type;

-- Comprehensive aggregation
SELECT 
    CASE p.element_type
        WHEN 1 THEN 'Goalkeeper'
        WHEN 2 THEN 'Defender'
        WHEN 3 THEN 'Midfielder'
        WHEN 4 THEN 'Forward'
    END as position,
    COUNT(*) as player_count,
    MIN(ps.total_points) as min_points,
    MAX(ps.total_points) as max_points,
    AVG(ps.total_points) as avg_points,
    STD(ps.total_points) as std_dev_points,
    SUM(ps.now_cost) / 10.0 as total_value_millions
FROM players p
INNER JOIN player_stats ps ON p.fpl_id = ps.player_id
WHERE ps.gameweek = 1
GROUP BY p.element_type
ORDER BY p.element_type;

-- 6. WINDOW FUNCTIONS
SELECT '6. WINDOW FUNCTIONS DEMONSTRATION' as Query_Type;

-- Advanced window functions
SELECT 
    p.web_name,
    t.short_name as team,
    ps.total_points,
    RANK() OVER (ORDER BY ps.total_points DESC) as overall_rank,
    DENSE_RANK() OVER (PARTITION BY p.element_type ORDER BY ps.total_points DESC) as position_rank,
    LAG(ps.total_points) OVER (PARTITION BY p.fpl_id ORDER BY ps.gameweek) as prev_gw_points,
    ps.total_points - LAG(ps.total_points) OVER (PARTITION BY p.fpl_id ORDER BY ps.gameweek) as points_change,
    AVG(ps.total_points) OVER (PARTITION BY p.element_type) as position_avg,
    ps.total_points - AVG(ps.total_points) OVER (PARTITION BY p.element_type) as above_position_avg
FROM players p
INNER JOIN teams t ON p.team_code = t.fpl_code
INNER JOIN player_stats ps ON p.fpl_id = ps.player_id
WHERE ps.gameweek = 1 AND ps.minutes > 0
ORDER BY ps.total_points DESC
LIMIT 15;

-- =============================================
-- PERFORMANCE ANALYSIS QUERIES
-- =============================================

SELECT 'PERFORMANCE ANALYSIS QUERIES' as Section;

-- Top value players by position
SELECT 'Top Value Players by Position' as Analysis_Type;
SELECT 
    CASE p.element_type
        WHEN 1 THEN 'GK'
        WHEN 2 THEN 'DEF'
        WHEN 3 THEN 'MID'
        WHEN 4 THEN 'FWD'
    END as pos,
    p.web_name,
    t.short_name as team,
    ps.total_points,
    ps.now_cost / 10.0 as cost,
    ROUND(ps.total_points / (ps.now_cost / 10.0), 2) as value_score,
    ROW_NUMBER() OVER (PARTITION BY p.element_type ORDER BY ps.total_points / (ps.now_cost / 10.0) DESC) as value_rank
FROM players p
INNER JOIN teams t ON p.team_code = t.fpl_code
INNER JOIN player_stats ps ON p.fpl_id = ps.player_id
WHERE ps.gameweek = 1 AND ps.minutes > 0
QUALIFY value_rank <= 3
ORDER BY p.element_type, value_rank;

-- Team strength comparison
SELECT 'Team Strength Comparison' as Analysis_Type;
SELECT 
    t.name,
    t.elo,
    t.strength,
    COUNT(p.fpl_id) as squad_size,
    SUM(ps.total_points) as total_squad_points,
    AVG(ps.total_points) as avg_player_points,
    SUM(ps.now_cost) / 10.0 as squad_value_millions,
    RANK() OVER (ORDER BY t.elo DESC) as elo_rank,
    RANK() OVER (ORDER BY AVG(ps.total_points) DESC) as performance_rank
FROM teams t
INNER JOIN players p ON t.fpl_code = p.team_code
INNER JOIN player_stats ps ON p.fpl_id = ps.player_id
WHERE ps.gameweek = 1
GROUP BY t.fpl_id, t.name, t.elo, t.strength
ORDER BY t.elo DESC;

-- =============================================
-- CLEANUP AND FINAL STATUS
-- =============================================

SELECT 'FINAL SETUP STATUS' as Section;

-- Show all created objects
SELECT 'Tables:' as Object_Type, COUNT(*) as Count 
FROM information_schema.tables 
WHERE table_schema = 'fpl';

SELECT 'Views:' as Object_Type, COUNT(*) as Count 
FROM information_schema.views 
WHERE table_schema = 'fpl';

SELECT 'Procedures:' as Object_Type, COUNT(*) as Count 
FROM information_schema.routines 
WHERE routine_schema = 'fpl' AND routine_type = 'PROCEDURE';

SELECT 'Functions:' as Object_Type, COUNT(*) as Count 
FROM information_schema.routines 
WHERE routine_schema = 'fpl' AND routine_type = 'FUNCTION';

SELECT 'Triggers:' as Object_Type, COUNT(*) as Count 
FROM information_schema.triggers 
WHERE trigger_schema = 'fpl';

SELECT 
    '=== FANTASY PREMIER LEAGUE DATABASE SETUP COMPLETE ===' as Status,
    'Ready for Web Development and Database Lab Projects!' as Message,
    NOW() as Completed_At;
