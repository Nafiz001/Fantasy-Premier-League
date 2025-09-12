-- =============================================
-- STORED PROCEDURES AND FUNCTIONS FOR FPL PROJECT
-- Advanced Database Lab Project
-- Date: September 2025
-- =============================================

USE fpl;

-- Drop existing procedures if they exist
DROP PROCEDURE IF EXISTS GetPlayerStatsByGameweek;
DROP PROCEDURE IF EXISTS UpdatePlayerScore;
DROP PROCEDURE IF EXISTS GetTeamValueAnalysis;
DROP PROCEDURE IF EXISTS GetTopPerformers;
DROP PROCEDURE IF EXISTS GetFixtureDifficulty;
DROP PROCEDURE IF EXISTS GetCaptainRecommendations;
DROP PROCEDURE IF EXISTS GetTransferSuggestions;
DROP PROCEDURE IF EXISTS CalculateTeamExpectedScore;
DROP FUNCTION IF EXISTS CalculatePlayerValue;
DROP FUNCTION IF EXISTS GetPositionName;
DROP FUNCTION IF EXISTS CalculateFormScore;
DROP FUNCTION IF EXISTS GetFixtureDifficultyRating;

-- Change delimiter for procedure definitions
DELIMITER //

-- =============================================
-- STORED FUNCTIONS
-- =============================================

-- Function to calculate player value score
CREATE FUNCTION CalculatePlayerValue(
    total_points INT,
    cost INT,
    form DECIMAL(3,1),
    minutes INT
) RETURNS DECIMAL(10,2)
READS SQL DATA
DETERMINISTIC
BEGIN
    DECLARE value_score DECIMAL(10,2);
    DECLARE points_per_million DECIMAL(10,2);
    DECLARE form_multiplier DECIMAL(5,2);
    DECLARE minutes_factor DECIMAL(5,2);
    
    -- Calculate base points per million
    SET points_per_million = total_points / (cost / 10.0);
    
    -- Form multiplier (range 0.5 to 1.5)
    SET form_multiplier = LEAST(GREATEST(form / 10.0, 0.5), 1.5);
    
    -- Minutes factor (players with more minutes get better rating)
    SET minutes_factor = LEAST(minutes / 1000.0, 1.2);
    
    -- Calculate final value score
    SET value_score = points_per_million * form_multiplier * minutes_factor;
    
    RETURN ROUND(value_score, 2);
END//

-- Function to get position name
CREATE FUNCTION GetPositionName(element_type INT) RETURNS VARCHAR(20)
READS SQL DATA
DETERMINISTIC
BEGIN
    CASE element_type
        WHEN 1 THEN RETURN 'Goalkeeper';
        WHEN 2 THEN RETURN 'Defender';
        WHEN 3 THEN RETURN 'Midfielder';
        WHEN 4 THEN RETURN 'Forward';
        ELSE RETURN 'Unknown';
    END CASE;
END//

-- Function to calculate form score
CREATE FUNCTION CalculateFormScore(
    form DECIMAL(3,1),
    goals INT,
    assists INT,
    bonus INT,
    minutes INT
) RETURNS DECIMAL(10,2)
READS SQL DATA
DETERMINISTIC
BEGIN
    DECLARE form_score DECIMAL(10,2);
    DECLARE performance_bonus DECIMAL(5,2);
    
    -- Base form score
    SET form_score = form * 2;
    
    -- Performance bonus based on contributions
    SET performance_bonus = (goals * 4 + assists * 3 + bonus) / GREATEST(minutes / 90.0, 1);
    
    RETURN ROUND(form_score + performance_bonus, 2);
END//

-- Function to get fixture difficulty rating
CREATE FUNCTION GetFixtureDifficultyRating(
    team_elo INT,
    opponent_elo INT,
    is_home BOOLEAN
) RETURNS INT
READS SQL DATA
DETERMINISTIC
BEGIN
    DECLARE difficulty INT;
    DECLARE elo_difference INT;
    
    -- Calculate ELO difference (positive means opponent is stronger)
    IF is_home THEN
        SET elo_difference = opponent_elo - team_elo - 100; -- Home advantage
    ELSE
        SET elo_difference = opponent_elo - team_elo + 100; -- Away disadvantage
    END IF;
    
    -- Convert to difficulty rating (1-5 scale)
    IF elo_difference > 200 THEN
        SET difficulty = 5;
    ELSEIF elo_difference > 100 THEN
        SET difficulty = 4;
    ELSEIF elo_difference > -100 THEN
        SET difficulty = 3;
    ELSEIF elo_difference > -200 THEN
        SET difficulty = 2;
    ELSE
        SET difficulty = 1;
    END IF;
    
    RETURN difficulty;
END//

-- =============================================
-- STORED PROCEDURES
-- =============================================

-- Procedure to get player statistics by gameweek
CREATE PROCEDURE GetPlayerStatsByGameweek(
    IN gameweek_num INT,
    IN position_filter INT DEFAULT NULL,
    IN min_points INT DEFAULT 0
)
BEGIN
    SELECT 
        p.web_name,
        GetPositionName(p.element_type) as position,
        t.short_name as team,
        ps.total_points,
        ps.form,
        ps.now_cost / 10.0 as cost_millions,
        CalculatePlayerValue(ps.total_points, ps.now_cost, ps.form, ps.minutes) as value_score,
        ps.selected_by_percent as ownership,
        ps.goals_scored,
        ps.assists,
        ps.bonus,
        ps.minutes,
        RANK() OVER (PARTITION BY p.element_type ORDER BY ps.total_points DESC) as position_rank
    FROM player_stats ps
    INNER JOIN players p ON ps.player_id = p.fpl_id
    INNER JOIN teams t ON p.team_code = t.fpl_code
    WHERE ps.gameweek = gameweek_num
        AND (position_filter IS NULL OR p.element_type = position_filter)
        AND ps.total_points >= min_points
    ORDER BY ps.total_points DESC;
END//

-- Procedure to update player score (simulation)
CREATE PROCEDURE UpdatePlayerScore(
    IN player_fpl_id INT,
    IN gameweek_num INT,
    IN new_points INT,
    IN goals INT DEFAULT 0,
    IN assists INT DEFAULT 0,
    IN bonus INT DEFAULT 0,
    IN minutes INT DEFAULT 90
)
BEGIN
    DECLARE current_total INT DEFAULT 0;
    
    -- Get current total points
    SELECT total_points INTO current_total 
    FROM player_stats 
    WHERE player_id = player_fpl_id AND gameweek = gameweek_num;
    
    -- Update the player stats
    UPDATE player_stats 
    SET 
        total_points = current_total + new_points,
        goals_scored = goals_scored + goals,
        assists = assists + assists,
        bonus = bonus + bonus,
        minutes = minutes + minutes,
        form = ROUND((form * 4 + new_points) / 5, 1) -- Update form as rolling average
    WHERE player_id = player_fpl_id AND gameweek = gameweek_num;
    
    -- Return updated stats
    SELECT 
        p.web_name,
        ps.total_points,
        ps.form,
        'Score updated successfully' as message
    FROM player_stats ps
    INNER JOIN players p ON ps.player_id = p.fpl_id
    WHERE ps.player_id = player_fpl_id AND ps.gameweek = gameweek_num;
END//

-- Procedure for team value analysis
CREATE PROCEDURE GetTeamValueAnalysis(
    IN team_fpl_id INT,
    IN gameweek_num INT
)
BEGIN
    SELECT 
        t.name as team_name,
        COUNT(p.fpl_id) as squad_size,
        SUM(ps.total_points) as total_points,
        AVG(ps.total_points) as avg_points,
        SUM(ps.now_cost) / 10.0 as total_value_millions,
        AVG(ps.now_cost) / 10.0 as avg_value_millions,
        SUM(CASE WHEN p.element_type = 1 THEN ps.total_points ELSE 0 END) as gk_points,
        SUM(CASE WHEN p.element_type = 2 THEN ps.total_points ELSE 0 END) as def_points,
        SUM(CASE WHEN p.element_type = 3 THEN ps.total_points ELSE 0 END) as mid_points,
        SUM(CASE WHEN p.element_type = 4 THEN ps.total_points ELSE 0 END) as fwd_points,
        MAX(ps.total_points) as best_player_points,
        MIN(ps.total_points) as worst_player_points,
        STD(ps.total_points) as points_consistency
    FROM teams t
    INNER JOIN players p ON t.fpl_code = p.team_code
    INNER JOIN player_stats ps ON p.fpl_id = ps.player_id
    WHERE t.fpl_id = team_fpl_id AND ps.gameweek = gameweek_num
    GROUP BY t.fpl_id, t.name;
END//

-- Procedure to get top performers
CREATE PROCEDURE GetTopPerformers(
    IN gameweek_num INT,
    IN limit_count INT DEFAULT 10
)
BEGIN
    -- Overall top performers
    SELECT 
        'Overall' as category,
        p.web_name,
        GetPositionName(p.element_type) as position,
        t.short_name as team,
        ps.total_points,
        ps.form,
        CalculateFormScore(ps.form, ps.goals_scored, ps.assists, ps.bonus, ps.minutes) as form_score
    FROM player_stats ps
    INNER JOIN players p ON ps.player_id = p.fpl_id
    INNER JOIN teams t ON p.team_code = t.fpl_code
    WHERE ps.gameweek = gameweek_num
    ORDER BY ps.total_points DESC
    LIMIT limit_count;
END//

-- Procedure to get fixture difficulty
CREATE PROCEDURE GetFixtureDifficulty(
    IN team_fpl_id INT,
    IN next_gameweeks INT DEFAULT 5
)
BEGIN
    SELECT 
        f.gameweek,
        f.kickoff_time,
        CASE 
            WHEN f.home_team = team_fpl_id THEN 'H'
            ELSE 'A'
        END as venue,
        CASE 
            WHEN f.home_team = team_fpl_id THEN at.short_name
            ELSE ht.short_name
        END as opponent,
        CASE 
            WHEN f.home_team = team_fpl_id THEN 
                GetFixtureDifficultyRating(f.home_team_elo, f.away_team_elo, TRUE)
            ELSE 
                GetFixtureDifficultyRating(f.away_team_elo, f.home_team_elo, FALSE)
        END as difficulty_rating,
        CASE 
            WHEN f.home_team = team_fpl_id THEN f.away_team_elo
            ELSE f.home_team_elo
        END as opponent_elo
    FROM fixtures f
    INNER JOIN teams ht ON f.home_team = ht.fpl_id
    INNER JOIN teams at ON f.away_team = at.fpl_id
    WHERE (f.home_team = team_fpl_id OR f.away_team = team_fpl_id)
        AND f.finished = 0
        AND f.gameweek <= (SELECT MAX(gameweek) FROM fixtures) + next_gameweeks
    ORDER BY f.gameweek
    LIMIT next_gameweeks;
END//

-- Procedure for captain recommendations
CREATE PROCEDURE GetCaptainRecommendations(
    IN gameweek_num INT,
    IN limit_count INT DEFAULT 10
)
BEGIN
    SELECT 
        p.web_name,
        GetPositionName(p.element_type) as position,
        t.short_name as team,
        ps.total_points,
        ps.form,
        ps.selected_by_percent as ownership,
        ps.now_cost / 10.0 as cost,
        CalculateFormScore(ps.form, ps.goals_scored, ps.assists, ps.bonus, ps.minutes) as form_score,
        -- Captain score calculation
        (
            LEAST(ps.form, 10) * 2 +
            LEAST(ps.total_points / 10, 15) +
            CASE WHEN ps.selected_by_percent < 5 THEN 5
                 WHEN ps.selected_by_percent < 15 THEN 3
                 WHEN ps.selected_by_percent < 30 THEN 1
                 ELSE 0 END +
            CASE WHEN p.element_type = 4 THEN 2
                 WHEN p.element_type = 3 THEN 1
                 ELSE 0 END
        ) as captain_score,
        CASE 
            WHEN ps.selected_by_percent < 10 THEN 'Differential'
            WHEN ps.selected_by_percent < 30 THEN 'Popular'
            ELSE 'Template'
        END as captain_type
    FROM player_stats ps
    INNER JOIN players p ON ps.player_id = p.fpl_id
    INNER JOIN teams t ON p.team_code = t.fpl_code
    WHERE ps.gameweek = gameweek_num
        AND ps.minutes > 500
        AND ps.status = 'a'
    ORDER BY (
        LEAST(ps.form, 10) * 2 +
        LEAST(ps.total_points / 10, 15) +
        CASE WHEN ps.selected_by_percent < 5 THEN 5
             WHEN ps.selected_by_percent < 15 THEN 3
             WHEN ps.selected_by_percent < 30 THEN 1
             ELSE 0 END +
        CASE WHEN p.element_type = 4 THEN 2
             WHEN p.element_type = 3 THEN 1
             ELSE 0 END
    ) DESC
    LIMIT limit_count;
END//

-- Procedure for transfer suggestions
CREATE PROCEDURE GetTransferSuggestions(
    IN gameweek_num INT,
    IN max_cost DECIMAL(3,1) DEFAULT 15.0,
    IN position_type INT DEFAULT NULL
)
BEGIN
    -- Players to transfer IN
    SELECT 
        'TRANSFER IN' as suggestion_type,
        p.web_name,
        GetPositionName(p.element_type) as position,
        t.short_name as team,
        ps.now_cost / 10.0 as cost,
        ps.total_points,
        ps.form,
        CalculatePlayerValue(ps.total_points, ps.now_cost, ps.form, ps.minutes) as value_score,
        ps.selected_by_percent as ownership,
        CASE 
            WHEN ps.form >= 7 AND ps.cost_change_start <= -20 THEN 'Great form, price dropped'
            WHEN ps.transfers_out_event > ps.transfers_in_event * 2 AND ps.total_points >= 80 THEN 'Mass exodus opportunity'
            WHEN ps.selected_by_percent < 5 AND ps.total_points >= 60 THEN 'Hidden gem'
            ELSE 'Good value option'
        END as reason
    FROM player_stats ps
    INNER JOIN players p ON ps.player_id = p.fpl_id
    INNER JOIN teams t ON p.team_code = t.fpl_code
    WHERE ps.gameweek = gameweek_num
        AND ps.now_cost / 10.0 <= max_cost
        AND (position_type IS NULL OR p.element_type = position_type)
        AND ps.status = 'a'
        AND ps.minutes > 300
        AND (
            (ps.form >= 6 AND ps.cost_change_start <= -10) OR
            (ps.transfers_out_event > ps.transfers_in_event AND ps.total_points >= 50) OR
            (ps.selected_by_percent < 10 AND ps.total_points >= 40)
        )
    ORDER BY CalculatePlayerValue(ps.total_points, ps.now_cost, ps.form, ps.minutes) DESC
    LIMIT 5;
    
    -- Players to transfer OUT
    SELECT 
        'TRANSFER OUT' as suggestion_type,
        p.web_name,
        GetPositionName(p.element_type) as position,
        t.short_name as team,
        ps.now_cost / 10.0 as cost,
        ps.total_points,
        ps.form,
        ps.selected_by_percent as ownership,
        CASE 
            WHEN ps.form <= 3 AND ps.selected_by_percent > 15 THEN 'Poor form, high ownership'
            WHEN ps.status != 'a' THEN 'Injury/suspension concern'
            WHEN ps.cost_change_start >= 30 AND ps.form <= 5 THEN 'Overpriced, poor form'
            WHEN ps.minutes <= 200 THEN 'Lack of playing time'
            ELSE 'Consider replacement'
        END as reason
    FROM player_stats ps
    INNER JOIN players p ON ps.player_id = p.fpl_id
    INNER JOIN teams t ON p.team_code = t.fpl_code
    WHERE ps.gameweek = gameweek_num
        AND (position_type IS NULL OR p.element_type = position_type)
        AND (
            (ps.form <= 4 AND ps.selected_by_percent > 10) OR
            ps.status != 'a' OR
            (ps.cost_change_start >= 20 AND ps.form <= 5) OR
            ps.minutes <= 300
        )
    ORDER BY ps.form ASC, ps.total_points ASC
    LIMIT 5;
END//

-- Procedure to calculate team expected score
CREATE PROCEDURE CalculateTeamExpectedScore(
    IN team_fpl_id INT,
    IN gameweek_num INT
)
BEGIN
    DECLARE team_expected_score DECIMAL(10,2) DEFAULT 0;
    
    -- Calculate expected score based on player stats and fixture difficulty
    SELECT 
        SUM(
            ps.expected_goals + 
            ps.expected_assists + 
            (ps.clean_sheets_prob * CASE WHEN p.element_type IN (1,2) THEN 4 ELSE 0 END) +
            (ps.form / 2)
        ) INTO team_expected_score
    FROM player_stats ps
    INNER JOIN players p ON ps.player_id = p.fpl_id
    WHERE p.team_code = (SELECT fpl_code FROM teams WHERE fpl_id = team_fpl_id)
        AND ps.gameweek = gameweek_num
        AND ps.minutes > 0;
    
    SELECT 
        t.name as team_name,
        ROUND(COALESCE(team_expected_score, 0), 2) as expected_score,
        'Calculated based on player xG, xA, and form' as calculation_method
    FROM teams t
    WHERE t.fpl_id = team_fpl_id;
END//

-- Reset delimiter
DELIMITER ;

-- =============================================
-- SAMPLE PROCEDURE CALLS FOR TESTING
-- =============================================

-- Test the procedures
-- CALL GetPlayerStatsByGameweek(1, 4, 50);  -- Get forwards with 50+ points in GW1
-- CALL GetTeamValueAnalysis(1, 1);           -- Arsenal analysis for GW1
-- CALL GetTopPerformers(1, 5);               -- Top 5 performers in GW1
-- CALL GetFixtureDifficulty(1, 3);           -- Arsenal's next 3 fixtures
-- CALL GetCaptainRecommendations(1, 10);     -- Top 10 captain options for GW1
-- CALL GetTransferSuggestions(1, 12.0, 3);   -- Transfer suggestions for midfielders under £12m
-- CALL CalculateTeamExpectedScore(1, 1);     -- Arsenal's expected score for GW1

SELECT 'All stored procedures and functions created successfully!' as Status;
