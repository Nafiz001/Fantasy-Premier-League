-- =============================================
-- TRIGGERS AND ADVANCED SQL OPERATIONS
-- Advanced Database Lab Project
-- Date: September 2025
-- =============================================

USE fpl;

-- =============================================
-- AUDIT TABLES FOR TRIGGERS
-- =============================================

-- Player stats audit table
CREATE TABLE IF NOT EXISTS player_stats_audit (
    audit_id INT AUTO_INCREMENT PRIMARY KEY,
    player_id INT,
    gameweek INT,
    old_total_points INT,
    new_total_points INT,
    old_form DECIMAL(3,1),
    new_form DECIMAL(3,1),
    old_cost INT,
    new_cost INT,
    change_type ENUM('INSERT', 'UPDATE', 'DELETE'),
    changed_by VARCHAR(100) DEFAULT USER(),
    change_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Transfer tracking table
CREATE TABLE IF NOT EXISTS player_transfer_log (
    transfer_log_id INT AUTO_INCREMENT PRIMARY KEY,
    player_id INT,
    gameweek INT,
    transfers_in_previous INT,
    transfers_out_previous INT,
    transfers_in_new INT,
    transfers_out_new INT,
    net_transfers_change INT,
    ownership_change DECIMAL(5,2),
    price_change_triggered BOOLEAN DEFAULT FALSE,
    logged_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =============================================
-- TRIGGERS
-- =============================================

-- Drop existing triggers if they exist
DROP TRIGGER IF EXISTS player_stats_after_insert;
DROP TRIGGER IF EXISTS player_stats_after_update;
DROP TRIGGER IF EXISTS player_stats_before_delete;
DROP TRIGGER IF EXISTS player_transfer_tracking;
DROP TRIGGER IF EXISTS validate_player_cost;

-- Trigger to log player stats changes
DELIMITER //

CREATE TRIGGER player_stats_after_insert
    AFTER INSERT ON player_stats
    FOR EACH ROW
BEGIN
    INSERT INTO player_stats_audit (
        player_id, gameweek, new_total_points, new_form, new_cost, change_type
    ) VALUES (
        NEW.player_id, NEW.gameweek, NEW.total_points, NEW.form, NEW.now_cost, 'INSERT'
    );
END//

CREATE TRIGGER player_stats_after_update
    AFTER UPDATE ON player_stats
    FOR EACH ROW
BEGIN
    INSERT INTO player_stats_audit (
        player_id, gameweek, 
        old_total_points, new_total_points,
        old_form, new_form,
        old_cost, new_cost,
        change_type
    ) VALUES (
        NEW.player_id, NEW.gameweek,
        OLD.total_points, NEW.total_points,
        OLD.form, NEW.form,
        OLD.now_cost, NEW.now_cost,
        'UPDATE'
    );
END//

CREATE TRIGGER player_stats_before_delete
    BEFORE DELETE ON player_stats
    FOR EACH ROW
BEGIN
    INSERT INTO player_stats_audit (
        player_id, gameweek, old_total_points, old_form, old_cost, change_type
    ) VALUES (
        OLD.player_id, OLD.gameweek, OLD.total_points, OLD.form, OLD.now_cost, 'DELETE'
    );
END//

-- Trigger to track transfer movements and predict price changes
CREATE TRIGGER player_transfer_tracking
    AFTER UPDATE ON player_stats
    FOR EACH ROW
BEGIN
    DECLARE net_change INT;
    DECLARE ownership_diff DECIMAL(5,2);
    DECLARE price_change_likely BOOLEAN DEFAULT FALSE;
    
    -- Calculate net transfer change
    SET net_change = (NEW.transfers_in_event - NEW.transfers_out_event) - 
                    (OLD.transfers_in_event - OLD.transfers_out_event);
    
    -- Calculate ownership change
    SET ownership_diff = NEW.selected_by_percent - OLD.selected_by_percent;
    
    -- Determine if price change is likely (simplified logic)
    IF net_change > 200000 OR (net_change > 100000 AND ownership_diff > 2) THEN
        SET price_change_likely = TRUE;
    END IF;
    
    -- Log the transfer activity
    INSERT INTO player_transfer_log (
        player_id, gameweek,
        transfers_in_previous, transfers_out_previous,
        transfers_in_new, transfers_out_new,
        net_transfers_change, ownership_change, price_change_triggered
    ) VALUES (
        NEW.player_id, NEW.gameweek,
        OLD.transfers_in_event, OLD.transfers_out_event,
        NEW.transfers_in_event, NEW.transfers_out_event,
        net_change, ownership_diff, price_change_likely
    );
END//

-- Trigger to validate player cost changes
CREATE TRIGGER validate_player_cost
    BEFORE UPDATE ON player_stats
    FOR EACH ROW
BEGIN
    -- Ensure cost doesn't change by more than £0.3 in one update
    IF ABS(NEW.now_cost - OLD.now_cost) > 3 THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Player cost cannot change by more than £0.3 in one update';
    END IF;
    
    -- Ensure cost stays within reasonable bounds (£3.5 to £15.0)
    IF NEW.now_cost < 35 OR NEW.now_cost > 150 THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Player cost must be between £3.5 and £15.0';
    END IF;
END//

DELIMITER ;

-- =============================================
-- ADVANCED SQL OPERATIONS WITH CTEs AND WINDOW FUNCTIONS
-- =============================================

-- Complex CTE for player performance analytics
WITH player_performance_cte AS (
    SELECT 
        ps.player_id,
        p.web_name,
        p.element_type,
        t.short_name as team,
        ps.gameweek,
        ps.total_points,
        ps.form,
        ps.now_cost,
        ps.selected_by_percent,
        ps.minutes,
        ps.goals_scored,
        ps.assists,
        ps.bonus,
        
        -- Window functions for advanced analytics
        LAG(ps.total_points) OVER (PARTITION BY ps.player_id ORDER BY ps.gameweek) as prev_points,
        LEAD(ps.total_points) OVER (PARTITION BY ps.player_id ORDER BY ps.gameweek) as next_points,
        
        -- Rolling averages
        AVG(ps.total_points) OVER (
            PARTITION BY ps.player_id 
            ORDER BY ps.gameweek 
            ROWS BETWEEN 4 PRECEDING AND CURRENT ROW
        ) as rolling_5_avg_points,
        
        AVG(ps.form) OVER (
            PARTITION BY ps.player_id 
            ORDER BY ps.gameweek 
            ROWS BETWEEN 2 PRECEDING AND CURRENT ROW
        ) as rolling_3_avg_form,
        
        -- Cumulative stats
        SUM(ps.goals_scored) OVER (
            PARTITION BY ps.player_id 
            ORDER BY ps.gameweek
        ) as cumulative_goals,
        
        SUM(ps.assists) OVER (
            PARTITION BY ps.player_id 
            ORDER BY ps.gameweek
        ) as cumulative_assists,
        
        -- Rankings
        RANK() OVER (
            PARTITION BY ps.gameweek, p.element_type 
            ORDER BY ps.total_points DESC
        ) as gameweek_position_rank,
        
        DENSE_RANK() OVER (
            PARTITION BY ps.gameweek 
            ORDER BY ps.total_points DESC
        ) as gameweek_overall_rank,
        
        -- Percentiles
        PERCENT_RANK() OVER (
            PARTITION BY ps.gameweek, p.element_type 
            ORDER BY ps.total_points
        ) as points_percentile_in_position,
        
        NTILE(10) OVER (
            PARTITION BY ps.gameweek 
            ORDER BY ps.total_points DESC
        ) as points_decile
    FROM player_stats ps
    INNER JOIN players p ON ps.player_id = p.fpl_id
    INNER JOIN teams t ON p.team_code = t.fpl_code
    WHERE ps.minutes > 0
),
value_analysis_cte AS (
    SELECT 
        player_id,
        web_name,
        element_type,
        team,
        gameweek,
        total_points,
        now_cost / 10.0 as cost_millions,
        total_points / (now_cost / 10.0) as points_per_million,
        
        -- Value rankings
        RANK() OVER (
            PARTITION BY gameweek, element_type 
            ORDER BY total_points / (now_cost / 10.0) DESC
        ) as value_rank_in_position,
        
        -- Identify bargains and overpriced players
        CASE 
            WHEN total_points / (now_cost / 10.0) > 
                 AVG(total_points / (now_cost / 10.0)) OVER (PARTITION BY gameweek, element_type) * 1.5 
            THEN 'Bargain'
            WHEN total_points / (now_cost / 10.0) < 
                 AVG(total_points / (now_cost / 10.0)) OVER (PARTITION BY gameweek, element_type) * 0.7 
            THEN 'Overpriced'
            ELSE 'Fair Value'
        END as value_category
    FROM player_performance_cte
)
-- Create a view from this complex CTE
CREATE OR REPLACE VIEW player_analytics_dashboard AS
SELECT 
    ppc.web_name,
    CASE ppc.element_type
        WHEN 1 THEN 'GK'
        WHEN 2 THEN 'DEF'
        WHEN 3 THEN 'MID'
        WHEN 4 THEN 'FWD'
    END as position,
    ppc.team,
    ppc.gameweek,
    ppc.total_points,
    ppc.rolling_5_avg_points,
    ppc.form,
    ppc.rolling_3_avg_form,
    vac.cost_millions,
    vac.points_per_million,
    vac.value_rank_in_position,
    vac.value_category,
    ppc.gameweek_position_rank,
    ppc.points_percentile_in_position,
    ppc.points_decile,
    ppc.cumulative_goals,
    ppc.cumulative_assists,
    
    -- Trend analysis
    CASE 
        WHEN ppc.next_points > ppc.total_points THEN 'Improving'
        WHEN ppc.next_points < ppc.total_points THEN 'Declining'
        ELSE 'Stable'
    END as trend,
    
    -- Performance consistency
    CASE 
        WHEN ppc.points_percentile_in_position >= 0.8 THEN 'Excellent'
        WHEN ppc.points_percentile_in_position >= 0.6 THEN 'Good'
        WHEN ppc.points_percentile_in_position >= 0.4 THEN 'Average'
        WHEN ppc.points_percentile_in_position >= 0.2 THEN 'Below Average'
        ELSE 'Poor'
    END as performance_tier
FROM player_performance_cte ppc
INNER JOIN value_analysis_cte vac ON ppc.player_id = vac.player_id AND ppc.gameweek = vac.gameweek;

-- =============================================
-- RECURSIVE CTE FOR FIXTURE DIFFICULTY CASCADE
-- =============================================

-- Recursive CTE to analyze fixture difficulty over multiple gameweeks
WITH RECURSIVE fixture_difficulty_cascade AS (
    -- Base case: current gameweek
    SELECT 
        f.fixture_id,
        f.gameweek,
        f.home_team,
        f.away_team,
        f.home_team_elo,
        f.away_team_elo,
        ABS(f.home_team_elo - f.away_team_elo) as elo_difference,
        1 as depth_level
    FROM fixtures f
    WHERE f.gameweek = (SELECT MIN(gameweek) FROM fixtures WHERE finished = 0)
    
    UNION ALL
    
    -- Recursive case: next gameweeks
    SELECT 
        f.fixture_id,
        f.gameweek,
        f.home_team,
        f.away_team,
        f.home_team_elo,
        f.away_team_elo,
        ABS(f.home_team_elo - f.away_team_elo) as elo_difference,
        fdc.depth_level + 1
    FROM fixtures f
    INNER JOIN fixture_difficulty_cascade fdc ON f.gameweek = fdc.gameweek + 1
    WHERE fdc.depth_level < 5  -- Limit to 5 gameweeks
)
-- Create view for fixture difficulty analysis
CREATE OR REPLACE VIEW fixture_cascade_analysis AS
SELECT 
    fdc.gameweek,
    fdc.depth_level,
    ht.short_name as home_team,
    at.short_name as away_team,
    fdc.elo_difference,
    CASE 
        WHEN fdc.elo_difference < 50 THEN 'Very Close Match'
        WHEN fdc.elo_difference < 100 THEN 'Close Match'
        WHEN fdc.elo_difference < 200 THEN 'Moderate Difference'
        WHEN fdc.elo_difference < 300 THEN 'Clear Favorite'
        ELSE 'Huge Mismatch'
    END as match_type,
    CASE 
        WHEN fdc.home_team_elo > fdc.away_team_elo THEN ht.short_name
        ELSE at.short_name
    END as favored_team
FROM fixture_difficulty_cascade fdc
INNER JOIN teams ht ON fdc.home_team = ht.fpl_id
INNER JOIN teams at ON fdc.away_team = at.fpl_id;

-- =============================================
-- PIVOT-LIKE ANALYSIS USING CONDITIONAL AGGREGATION
-- =============================================

-- Team performance matrix across positions
CREATE OR REPLACE VIEW team_position_matrix AS
SELECT 
    t.short_name as team,
    COUNT(DISTINCT p.fpl_id) as total_players,
    
    -- Points by position
    SUM(CASE WHEN p.element_type = 1 THEN ps.total_points ELSE 0 END) as gk_points,
    SUM(CASE WHEN p.element_type = 2 THEN ps.total_points ELSE 0 END) as def_points,
    SUM(CASE WHEN p.element_type = 3 THEN ps.total_points ELSE 0 END) as mid_points,
    SUM(CASE WHEN p.element_type = 4 THEN ps.total_points ELSE 0 END) as fwd_points,
    
    -- Average points by position
    AVG(CASE WHEN p.element_type = 1 THEN ps.total_points END) as avg_gk_points,
    AVG(CASE WHEN p.element_type = 2 THEN ps.total_points END) as avg_def_points,
    AVG(CASE WHEN p.element_type = 3 THEN ps.total_points END) as avg_mid_points,
    AVG(CASE WHEN p.element_type = 4 THEN ps.total_points END) as avg_fwd_points,
    
    -- Player counts by position
    COUNT(CASE WHEN p.element_type = 1 THEN 1 END) as gk_count,
    COUNT(CASE WHEN p.element_type = 2 THEN 1 END) as def_count,
    COUNT(CASE WHEN p.element_type = 3 THEN 1 END) as mid_count,
    COUNT(CASE WHEN p.element_type = 4 THEN 1 END) as fwd_count,
    
    -- Total value by position
    SUM(CASE WHEN p.element_type = 1 THEN ps.now_cost ELSE 0 END) / 10.0 as gk_total_value,
    SUM(CASE WHEN p.element_type = 2 THEN ps.now_cost ELSE 0 END) / 10.0 as def_total_value,
    SUM(CASE WHEN p.element_type = 3 THEN ps.now_cost ELSE 0 END) / 10.0 as mid_total_value,
    SUM(CASE WHEN p.element_type = 4 THEN ps.now_cost ELSE 0 END) / 10.0 as fwd_total_value,
    
    ps.gameweek
FROM teams t
INNER JOIN players p ON t.fpl_code = p.team_code
INNER JOIN player_stats ps ON p.fpl_id = ps.player_id
GROUP BY t.fpl_id, t.short_name, ps.gameweek;

-- =============================================
-- ADVANCED RANKING AND ANALYTICAL FUNCTIONS
-- =============================================

-- Player momentum analysis
CREATE OR REPLACE VIEW player_momentum_analysis AS
SELECT 
    p.web_name,
    p.position,
    t.short_name as team,
    ps.gameweek,
    ps.total_points,
    ps.form,
    
    -- Momentum indicators
    LAG(ps.total_points, 1) OVER (PARTITION BY ps.player_id ORDER BY ps.gameweek) as prev_gw_points,
    LAG(ps.total_points, 2) OVER (PARTITION BY ps.player_id ORDER BY ps.gameweek) as prev_2gw_points,
    LAG(ps.total_points, 3) OVER (PARTITION BY ps.player_id ORDER BY ps.gameweek) as prev_3gw_points,
    
    -- Rolling momentum score
    (
        ps.total_points * 3 +
        COALESCE(LAG(ps.total_points, 1) OVER (PARTITION BY ps.player_id ORDER BY ps.gameweek), 0) * 2 +
        COALESCE(LAG(ps.total_points, 2) OVER (PARTITION BY ps.player_id ORDER BY ps.gameweek), 0) * 1
    ) / 6.0 as weighted_momentum_score,
    
    -- Trend analysis
    CASE 
        WHEN ps.total_points > COALESCE(LAG(ps.total_points, 1) OVER (PARTITION BY ps.player_id ORDER BY ps.gameweek), 0)
             AND COALESCE(LAG(ps.total_points, 1) OVER (PARTITION BY ps.player_id ORDER BY ps.gameweek), 0) > 
                 COALESCE(LAG(ps.total_points, 2) OVER (PARTITION BY ps.player_id ORDER BY ps.gameweek), 0)
        THEN 'Hot Streak'
        WHEN ps.total_points < COALESCE(LAG(ps.total_points, 1) OVER (PARTITION BY ps.player_id ORDER BY ps.gameweek), 0)
             AND COALESCE(LAG(ps.total_points, 1) OVER (PARTITION BY ps.player_id ORDER BY ps.gameweek), 0) < 
                 COALESCE(LAG(ps.total_points, 2) OVER (PARTITION BY ps.player_id ORDER BY ps.gameweek), 0)
        THEN 'Cold Streak'
        WHEN ps.total_points > COALESCE(LAG(ps.total_points, 1) OVER (PARTITION BY ps.player_id ORDER BY ps.gameweek), 0)
        THEN 'Improving'
        WHEN ps.total_points < COALESCE(LAG(ps.total_points, 1) OVER (PARTITION BY ps.player_id ORDER BY ps.gameweek), 0)
        THEN 'Declining'
        ELSE 'Stable'
    END as momentum_trend,
    
    -- Consistency measure (coefficient of variation)
    STDDEV(ps.total_points) OVER (
        PARTITION BY ps.player_id 
        ORDER BY ps.gameweek 
        ROWS BETWEEN 4 PRECEDING AND CURRENT ROW
    ) / NULLIF(AVG(ps.total_points) OVER (
        PARTITION BY ps.player_id 
        ORDER BY ps.gameweek 
        ROWS BETWEEN 4 PRECEDING AND CURRENT ROW
    ), 0) as consistency_coefficient
FROM player_stats ps
INNER JOIN players p ON ps.player_id = p.fpl_id
INNER JOIN teams t ON p.team_code = t.fpl_code
WHERE ps.minutes > 0;

-- =============================================
-- TESTING TRIGGERS (Commented out for safety)
-- =============================================

/*
-- Test trigger functionality (UNCOMMENT TO TEST)
UPDATE player_stats 
SET total_points = total_points + 5, 
    form = form + 0.5 
WHERE player_id = 1 AND gameweek = 1;

-- Check audit trail
SELECT * FROM player_stats_audit ORDER BY change_timestamp DESC LIMIT 5;

-- Check transfer log
SELECT * FROM player_transfer_log ORDER BY logged_at DESC LIMIT 5;
*/

SELECT 'All triggers and advanced SQL operations created successfully!' as Status;

-- Show all created objects
SELECT 'VIEWS CREATED:' as Object_Type;
SHOW TABLES LIKE '%view%';

SELECT 'PROCEDURES AVAILABLE:' as Object_Type;
SHOW PROCEDURE STATUS WHERE Db = 'fpl';

SELECT 'FUNCTIONS AVAILABLE:' as Object_Type;
SHOW FUNCTION STATUS WHERE Db = 'fpl';

SELECT 'TRIGGERS CREATED:' as Object_Type;
SHOW TRIGGERS;
