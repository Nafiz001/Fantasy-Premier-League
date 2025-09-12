-- =============================================
-- FPL Database Creation Script
-- Database Lab Project - Fantasy Premier League
-- Date: September 2025
-- =============================================

-- Create database if not exists
CREATE DATABASE IF NOT EXISTS fpl CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE fpl;

-- Drop tables if they exist (for clean setup)
DROP TABLE IF EXISTS player_gameweek_stats;
DROP TABLE IF EXISTS player_match_stats;
DROP TABLE IF EXISTS player_stats;
DROP TABLE IF EXISTS fixtures;
DROP TABLE IF EXISTS matches;
DROP TABLE IF EXISTS players;
DROP TABLE IF EXISTS gameweeks;
DROP TABLE IF EXISTS teams;

-- =============================================
-- TEAMS TABLE
-- =============================================
CREATE TABLE teams (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    fpl_code INT NOT NULL UNIQUE,
    fpl_id INT NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    short_name VARCHAR(10) NOT NULL,
    strength INT NOT NULL,
    strength_overall_home INT NOT NULL,
    strength_overall_away INT NOT NULL,
    strength_attack_home INT NOT NULL,
    strength_attack_away INT NOT NULL,
    strength_defence_home INT NOT NULL,
    strength_defence_away INT NOT NULL,
    pulse_id INT NULL,
    elo DECIMAL(8,2) NULL,
    form DECIMAL(3,1) DEFAULT 0,
    position INT DEFAULT 0,
    played INT DEFAULT 0,
    won INT DEFAULT 0,
    drawn INT DEFAULT 0,
    lost INT DEFAULT 0,
    points INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes for performance
    INDEX idx_teams_fpl_id (fpl_id),
    INDEX idx_teams_fpl_code (fpl_code),
    INDEX idx_teams_strength (strength),
    INDEX idx_teams_elo (elo)
);

-- =============================================
-- GAMEWEEKS TABLE
-- =============================================
CREATE TABLE gameweeks (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    gameweek_id INT NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    deadline_time TIMESTAMP NOT NULL,
    deadline_time_epoch BIGINT NOT NULL,
    deadline_time_game_offset INT NOT NULL DEFAULT 0,
    average_entry_score DECIMAL(5,2) NULL,
    highest_score INT NULL,
    finished BOOLEAN DEFAULT FALSE,
    is_previous BOOLEAN DEFAULT FALSE,
    is_current BOOLEAN DEFAULT FALSE,
    is_next BOOLEAN DEFAULT FALSE,
    chip_plays JSON NULL,
    most_selected INT NULL,
    most_transferred_in INT NULL,
    most_captained INT NULL,
    most_vice_captained INT NULL,
    top_element INT NULL,
    transfers_made BIGINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_gameweeks_current (is_current),
    INDEX idx_gameweeks_finished (finished),
    INDEX idx_gameweeks_deadline (deadline_time)
);

-- =============================================
-- PLAYERS TABLE
-- =============================================
CREATE TABLE players (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    fpl_code INT NOT NULL UNIQUE,
    fpl_id INT NOT NULL UNIQUE,
    first_name VARCHAR(255) NOT NULL,
    second_name VARCHAR(255) NOT NULL,
    web_name VARCHAR(255) NOT NULL,
    team_code INT NOT NULL,
    position VARCHAR(50) NOT NULL, -- GKP, DEF, MID, FWD
    element_type INT NOT NULL, -- 1=GK, 2=DEF, 3=MID, 4=FWD
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign key constraints
    FOREIGN KEY (team_code) REFERENCES teams(fpl_code) ON DELETE CASCADE,
    
    -- Indexes
    INDEX idx_players_fpl_id (fpl_id),
    INDEX idx_players_element_type (element_type),
    INDEX idx_players_team_code (team_code),
    INDEX idx_players_name (web_name)
);

-- =============================================
-- MATCHES TABLE (Historical match data)
-- =============================================
CREATE TABLE matches (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    match_id INT NOT NULL UNIQUE,
    gameweek INT NOT NULL,
    home_team INT NOT NULL,
    away_team INT NOT NULL,
    home_score INT DEFAULT 0,
    away_score INT DEFAULT 0,
    finished BOOLEAN DEFAULT FALSE,
    kickoff_time TIMESTAMP NULL,
    
    -- Match statistics
    home_possession INT NULL,
    away_possession INT NULL,
    home_total_shots INT NULL,
    away_total_shots INT NULL,
    home_shots_on_target INT NULL,
    away_shots_on_target INT NULL,
    home_big_chances INT NULL,
    away_big_chances INT NULL,
    home_expected_goals_xg DECIMAL(5,2) NULL,
    away_expected_goals_xg DECIMAL(5,2) NULL,
    home_tackles_won INT NULL,
    away_tackles_won INT NULL,
    home_interceptions INT NULL,
    away_interceptions INT NULL,
    home_blocks INT NULL,
    away_blocks INT NULL,
    home_clearances INT NULL,
    away_clearances INT NULL,
    home_accurate_passes INT NULL,
    away_accurate_passes INT NULL,
    home_accurate_passes_pct DECIMAL(5,2) NULL,
    away_accurate_passes_pct DECIMAL(5,2) NULL,
    
    -- ELO ratings at time of match
    home_team_elo DECIMAL(8,2) NULL,
    away_team_elo DECIMAL(8,2) NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign key constraints
    FOREIGN KEY (home_team) REFERENCES teams(fpl_id) ON DELETE CASCADE,
    FOREIGN KEY (away_team) REFERENCES teams(fpl_id) ON DELETE CASCADE,
    FOREIGN KEY (gameweek) REFERENCES gameweeks(gameweek_id) ON DELETE CASCADE,
    
    -- Indexes
    INDEX idx_matches_gameweek (gameweek),
    INDEX idx_matches_home_team (home_team),
    INDEX idx_matches_away_team (away_team),
    INDEX idx_matches_finished (finished),
    INDEX idx_matches_kickoff (kickoff_time)
);

-- =============================================
-- FIXTURES TABLE (Upcoming matches)
-- =============================================
CREATE TABLE fixtures (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    fixture_id INT NOT NULL UNIQUE,
    gameweek INT NOT NULL,
    home_team INT NOT NULL,
    away_team INT NOT NULL,
    finished BOOLEAN DEFAULT FALSE,
    kickoff_time TIMESTAMP NULL,
    home_team_elo DECIMAL(8,2) NULL,
    away_team_elo DECIMAL(8,2) NULL,
    home_difficulty INT DEFAULT 3,
    away_difficulty INT DEFAULT 3,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign key constraints
    FOREIGN KEY (home_team) REFERENCES teams(fpl_id) ON DELETE CASCADE,
    FOREIGN KEY (away_team) REFERENCES teams(fpl_id) ON DELETE CASCADE,
    FOREIGN KEY (gameweek) REFERENCES gameweeks(gameweek_id) ON DELETE CASCADE,
    
    -- Indexes
    INDEX idx_fixtures_gameweek (gameweek),
    INDEX idx_fixtures_home_team (home_team),
    INDEX idx_fixtures_away_team (away_team),
    INDEX idx_fixtures_finished (finished)
);

-- =============================================
-- PLAYER STATS TABLE (Season cumulative)
-- =============================================
CREATE TABLE player_stats (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    player_id INT NOT NULL,
    gameweek INT NOT NULL,
    first_name VARCHAR(255) NOT NULL,
    second_name VARCHAR(255) NOT NULL,
    web_name VARCHAR(255) NOT NULL,
    status VARCHAR(10) DEFAULT 'a', -- a=available, i=injured, etc.
    news TEXT NULL,
    news_added TIMESTAMP NULL,
    chance_of_playing_next_round INT NULL,
    chance_of_playing_this_round INT NULL,
    
    -- Pricing and selection
    now_cost INT NOT NULL,
    now_cost_rank INT NULL,
    now_cost_rank_type INT NULL,
    cost_change_event INT DEFAULT 0,
    cost_change_event_fall INT DEFAULT 0,
    cost_change_start INT DEFAULT 0,
    cost_change_start_fall INT DEFAULT 0,
    selected_by_percent DECIMAL(5,2) DEFAULT 0,
    selected_rank INT NULL,
    selected_rank_type INT NULL,
    
    -- Performance statistics
    total_points INT DEFAULT 0,
    event_points INT DEFAULT 0,
    points_per_game DECIMAL(5,2) DEFAULT 0,
    points_per_game_rank INT NULL,
    points_per_game_rank_type INT NULL,
    minutes INT DEFAULT 0,
    goals_scored INT DEFAULT 0,
    assists INT DEFAULT 0,
    clean_sheets INT DEFAULT 0,
    goals_conceded INT DEFAULT 0,
    own_goals INT DEFAULT 0,
    penalties_saved INT DEFAULT 0,
    penalties_missed INT DEFAULT 0,
    yellow_cards INT DEFAULT 0,
    red_cards INT DEFAULT 0,
    saves INT DEFAULT 0,
    starts INT DEFAULT 0,
    bonus INT DEFAULT 0,
    bps INT DEFAULT 0,
    
    -- Form and value metrics
    form DECIMAL(3,1) DEFAULT 0,
    form_rank INT NULL,
    form_rank_type INT NULL,
    value_form DECIMAL(5,2) DEFAULT 0,
    value_season DECIMAL(5,2) DEFAULT 0,
    dreamteam_count INT DEFAULT 0,
    
    -- Transfer statistics
    transfers_in BIGINT DEFAULT 0,
    transfers_in_event BIGINT DEFAULT 0,
    transfers_out BIGINT DEFAULT 0,
    transfers_out_event BIGINT DEFAULT 0,
    
    -- Expected statistics
    ep_next DECIMAL(5,2) NULL,
    ep_this DECIMAL(5,2) NULL,
    expected_goals DECIMAL(5,2) DEFAULT 0,
    expected_assists DECIMAL(5,2) DEFAULT 0,
    expected_goal_involvements DECIMAL(5,2) DEFAULT 0,
    expected_goals_conceded DECIMAL(5,2) DEFAULT 0,
    expected_goals_per_90 DECIMAL(5,2) DEFAULT 0,
    expected_assists_per_90 DECIMAL(5,2) DEFAULT 0,
    expected_goal_involvements_per_90 DECIMAL(5,2) DEFAULT 0,
    expected_goals_conceded_per_90 DECIMAL(5,2) DEFAULT 0,
    goals_conceded_per_90 DECIMAL(5,2) DEFAULT 0,
    starts_per_90 DECIMAL(5,2) DEFAULT 0,
    clean_sheets_per_90 DECIMAL(5,2) DEFAULT 0,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign key constraints
    FOREIGN KEY (player_id) REFERENCES players(fpl_id) ON DELETE CASCADE,
    FOREIGN KEY (gameweek) REFERENCES gameweeks(gameweek_id) ON DELETE CASCADE,
    
    -- Composite unique constraint
    UNIQUE KEY unique_player_gameweek (player_id, gameweek),
    
    -- Indexes for performance
    INDEX idx_player_stats_player_id (player_id),
    INDEX idx_player_stats_gameweek (gameweek),
    INDEX idx_player_stats_total_points (total_points),
    INDEX idx_player_stats_form (form),
    INDEX idx_player_stats_cost (now_cost),
    INDEX idx_player_stats_selected (selected_by_percent)
);

-- =============================================
-- PLAYER MATCH STATS TABLE (Individual match performance)
-- =============================================
CREATE TABLE player_match_stats (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    player_id INT NOT NULL,
    match_id INT NOT NULL,
    total_points INT DEFAULT 0,
    goals INT DEFAULT 0,
    assists INT DEFAULT 0,
    bonus_points INT DEFAULT 0,
    minutes_played INT DEFAULT 0,
    yellow_cards INT DEFAULT 0,
    red_cards INT DEFAULT 0,
    saves INT DEFAULT 0,
    penalties_saved INT DEFAULT 0,
    penalties_missed INT DEFAULT 0,
    own_goals INT DEFAULT 0,
    clean_sheet BOOLEAN DEFAULT FALSE,
    goals_conceded INT DEFAULT 0,
    
    -- Advanced stats
    expected_goals DECIMAL(5,2) DEFAULT 0,
    expected_assists DECIMAL(5,2) DEFAULT 0,
    shots INT DEFAULT 0,
    shots_on_target INT DEFAULT 0,
    key_passes INT DEFAULT 0,
    big_chances_created INT DEFAULT 0,
    big_chances_missed INT DEFAULT 0,
    tackles INT DEFAULT 0,
    interceptions INT DEFAULT 0,
    clearances INT DEFAULT 0,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign key constraints
    FOREIGN KEY (player_id) REFERENCES players(fpl_id) ON DELETE CASCADE,
    FOREIGN KEY (match_id) REFERENCES matches(match_id) ON DELETE CASCADE,
    
    -- Composite unique constraint
    UNIQUE KEY unique_player_match (player_id, match_id),
    
    -- Indexes
    INDEX idx_player_match_stats_player_id (player_id),
    INDEX idx_player_match_stats_match_id (match_id),
    INDEX idx_player_match_stats_total_points (total_points)
);

-- =============================================
-- PLAYER GAMEWEEK STATS TABLE (Weekly performance)
-- =============================================
CREATE TABLE player_gameweek_stats (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    player_id INT NOT NULL,
    gameweek INT NOT NULL,
    total_points INT DEFAULT 0,
    minutes_played INT DEFAULT 0,
    goals INT DEFAULT 0,
    assists INT DEFAULT 0,
    clean_sheets INT DEFAULT 0,
    goals_conceded INT DEFAULT 0,
    own_goals INT DEFAULT 0,
    penalties_saved INT DEFAULT 0,
    penalties_missed INT DEFAULT 0,
    yellow_cards INT DEFAULT 0,
    red_cards INT DEFAULT 0,
    saves INT DEFAULT 0,
    bonus_points INT DEFAULT 0,
    bps INT DEFAULT 0,
    influence DECIMAL(5,1) DEFAULT 0,
    creativity DECIMAL(5,1) DEFAULT 0,
    threat DECIMAL(5,1) DEFAULT 0,
    ict_index DECIMAL(5,1) DEFAULT 0,
    archived_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign key constraints
    FOREIGN KEY (player_id) REFERENCES players(fpl_id) ON DELETE CASCADE,
    FOREIGN KEY (gameweek) REFERENCES gameweeks(gameweek_id) ON DELETE CASCADE,
    
    -- Composite unique constraint
    UNIQUE KEY unique_player_gameweek_stats (player_id, gameweek),
    
    -- Indexes
    INDEX idx_player_gameweek_stats_player_id (player_id),
    INDEX idx_player_gameweek_stats_gameweek (gameweek),
    INDEX idx_player_gameweek_stats_total_points (total_points),
    INDEX idx_player_gameweek_stats_archived (archived_at)
);

-- =============================================
-- ADDITIONAL TABLES FOR ADVANCED FEATURES
-- =============================================

-- Player transfers tracking
CREATE TABLE player_transfers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    player_id INT NOT NULL,
    from_team_code INT NULL,
    to_team_code INT NOT NULL,
    transfer_date DATE NOT NULL,
    transfer_fee BIGINT NULL,
    season VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (player_id) REFERENCES players(fpl_id) ON DELETE CASCADE,
    FOREIGN KEY (from_team_code) REFERENCES teams(fpl_code) ON DELETE SET NULL,
    FOREIGN KEY (to_team_code) REFERENCES teams(fpl_code) ON DELETE CASCADE,
    
    INDEX idx_player_transfers_player_id (player_id),
    INDEX idx_player_transfers_date (transfer_date)
);

-- FPL Manager teams (for tracking user teams)
CREATE TABLE fpl_managers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    manager_name VARCHAR(255) NOT NULL,
    team_name VARCHAR(255) NOT NULL,
    total_points INT DEFAULT 0,
    overall_rank INT NULL,
    gameweek_rank INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_fpl_managers_total_points (total_points),
    INDEX idx_fpl_managers_rank (overall_rank)
);

-- Manager team selections (weekly picks)
CREATE TABLE manager_selections (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    manager_id BIGINT UNSIGNED NOT NULL,
    gameweek INT NOT NULL,
    player_id INT NOT NULL,
    is_captain BOOLEAN DEFAULT FALSE,
    is_vice_captain BOOLEAN DEFAULT FALSE,
    is_starting_11 BOOLEAN DEFAULT TRUE,
    position_order INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (manager_id) REFERENCES fpl_managers(id) ON DELETE CASCADE,
    FOREIGN KEY (player_id) REFERENCES players(fpl_id) ON DELETE CASCADE,
    FOREIGN KEY (gameweek) REFERENCES gameweeks(gameweek_id) ON DELETE CASCADE,
    
    UNIQUE KEY unique_manager_player_gameweek (manager_id, player_id, gameweek),
    
    INDEX idx_manager_selections_manager_id (manager_id),
    INDEX idx_manager_selections_gameweek (gameweek),
    INDEX idx_manager_selections_player_id (player_id)
);

-- =============================================
-- SAMPLE DATA INSERTION
-- =============================================

-- Insert sample teams
INSERT INTO teams (fpl_code, fpl_id, name, short_name, strength, strength_overall_home, strength_overall_away, strength_attack_home, strength_attack_away, strength_defence_home, strength_defence_away, pulse_id, elo) VALUES
(1, 1, 'Arsenal', 'ARS', 85, 85, 80, 85, 80, 85, 80, 1, 1650.5),
(2, 2, 'Manchester City', 'MCI', 90, 90, 88, 90, 88, 90, 88, 2, 1750.8),
(3, 3, 'Liverpool', 'LIV', 88, 88, 85, 88, 85, 88, 85, 3, 1720.3),
(4, 4, 'Chelsea', 'CHE', 80, 80, 78, 80, 78, 80, 78, 4, 1580.2),
(5, 5, 'Newcastle United', 'NEW', 82, 82, 78, 82, 78, 82, 78, 5, 1620.7),
(6, 6, 'Manchester United', 'MUN', 78, 78, 75, 78, 75, 78, 75, 6, 1590.4),
(7, 7, 'Tottenham Hotspur', 'TOT', 81, 81, 78, 81, 78, 81, 78, 7, 1610.9),
(8, 8, 'Brighton & Hove Albion', 'BRI', 72, 72, 68, 72, 68, 72, 68, 8, 1520.6);

-- Insert sample gameweeks
INSERT INTO gameweeks (gameweek_id, name, deadline_time, deadline_time_epoch, deadline_time_game_offset, finished, is_previous, is_current, is_next) VALUES
(1, 'Gameweek 1', '2025-08-16 11:30:00', 1692185400, 0, TRUE, TRUE, FALSE, FALSE),
(2, 'Gameweek 2', '2025-08-23 11:30:00', 1692790200, 0, TRUE, TRUE, FALSE, FALSE),
(3, 'Gameweek 3', '2025-08-30 11:30:00', 1693395000, 0, FALSE, FALSE, TRUE, FALSE),
(4, 'Gameweek 4', '2025-09-06 11:30:00', 1693999800, 0, FALSE, FALSE, FALSE, TRUE),
(5, 'Gameweek 5', '2025-09-13 11:30:00', 1694604600, 0, FALSE, FALSE, FALSE, TRUE);

COMMIT;

SELECT 'Database and tables created successfully!' as Status;
