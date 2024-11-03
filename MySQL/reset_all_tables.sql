/*
    Game Theory tables and initialization data
    NOTE: Run this once the application is ready to go live
*/

-- Drop tables
DROP TABLE IF EXISTS session_partner;
DROP TABLE IF EXISTS partner_pairing;
DROP TABLE IF EXISTS question_submission;
DROP TABLE IF EXISTS rb_player_score;
DROP TABLE IF EXISTS player_round_history;
DROP TABLE IF EXISTS student_game_session;
DROP TABLE IF EXISTS red_black_session;
DROP TABLE IF EXISTS question_for_student;
DROP TABLE IF EXISTS login_credential;
DROP TABLE IF EXISTS group_student;
DROP TABLE IF EXISTS round_score;
DROP TABLE IF EXISTS group_trade;
DROP TABLE IF EXISTS trade_partnership;
DROP TABLE IF EXISTS group_pairing;
DROP TABLE IF EXISTS prod_goal_profile;
DROP TABLE IF EXISTS session_instance;
DROP TABLE IF EXISTS student_profile;
DROP TABLE IF EXISTS game_session;
DROP TABLE IF EXISTS red_black_card_param;
DROP TABLE IF EXISTS wheat_steel_param;
DROP TABLE IF EXISTS game_catalog;
DROP TABLE IF EXISTS teacher_profile;

-- Game catalog table to store the game's title
CREATE TABLE game_catalog (
    game_id INT PRIMARY key AUTO_INCREMENT,
    game_title VARCHAR(50)
);

-- Red/Black Card Parameters
CREATE TABLE red_black_card_param (
    red_black_id INT PRIMARY key AUTO_INCREMENT,
    game_id INT,
    game_instruction_txt VARCHAR(4000),
    red_point_value SMALLINT,
    black_point_value SMALLINT,
    total_round_num TINYINT,
    hidden_round_num TINYINT,
    FOREIGN KEY (game_id) REFERENCES game_catalog(game_id) ON DELETE SET NULL
);

-- Questions for students
CREATE TABLE question_for_student (
    question_id INT PRIMARY KEY AUTO_INCREMENT,
    game_id INT,
    question_txt VARCHAR(4000),
    create_dt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    mod_dt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (game_id) REFERENCES game_catalog(game_id) ON DELETE SET NULL
);

-- Wheat/Steel Parameters
CREATE TABLE wheat_steel_param (
    wheat_steel_id INT PRIMARY KEY AUTO_INCREMENT,
    game_id INT,
    game_instruction_txt VARCHAR(4000),
    total_round_num TINYINT,
    no_trade_round_num TINYINT,
    FOREIGN KEY (game_id) REFERENCES game_catalog(game_id) ON DELETE SET NULL
);

-- Teacher Profile
CREATE TABLE teacher_profile (
    teacher_id INT PRIMARY KEY AUTO_INCREMENT,
    first_nm VARCHAR(30),
    last_nm VARCHAR(30),
    email VARCHAR(100),
    organization_nm VARCHAR(100)
);

-- Game Session
CREATE TABLE game_session (
    game_session_id INT PRIMARY KEY AUTO_INCREMENT,
    teacher_id INT,
    game_id INT,
    red_black_param INT,
    wheat_steel_param INT,
    is_active CHAR(1),
    create_dt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    expiration_dt TIMESTAMP NOT NULL,
    join_game_code VARCHAR(12),
    FOREIGN KEY (teacher_id) REFERENCES teacher_profile(teacher_id),
    FOREIGN KEY (game_id) REFERENCES game_catalog(game_id),
    FOREIGN KEY (red_black_param) REFERENCES red_black_card_param(red_black_id),
    FOREIGN KEY (wheat_steel_param) REFERENCES wheat_steel_param(wheat_steel_id)
);

-- Red/Black Session
CREATE TABLE red_black_session (
    rb_session_id INT PRIMARY KEY AUTO_INCREMENT,
    game_session_id INT,
    rounds_complete TINYINT,
    round_concluded CHAR(1),
    FOREIGN KEY (game_session_id) REFERENCES game_session(game_session_id) ON DELETE SET NULL
);

-- Login Credential
CREATE TABLE login_credential (
    credential_id INT PRIMARY KEY AUTO_INCREMENT,
    teacher_id INT,
    username VARCHAR(30),
    user_password VARCHAR(100),
    FOREIGN KEY (teacher_id) REFERENCES teacher_profile(teacher_id) ON DELETE SET NULL
);

-- Student Profile
CREATE TABLE student_profile (
    student_id INT PRIMARY KEY AUTO_INCREMENT,
    first_nm VARCHAR(30),
    last_nm VARCHAR(30),
    email VARCHAR(100) UNIQUE NOT NULL,
    create_dt TIMESTAMP DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
    mod_dt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Session Instance
CREATE TABLE session_instance (
    session_id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT,
    teacher_id INT,
    login_attempts TINYINT,
    active_session CHAR(1),
    create_dt TIMESTAMP DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
    expiration_dt TIMESTAMP NOT NULL,
    FOREIGN KEY (teacher_id) REFERENCES teacher_profile(teacher_id) ON DELETE SET NULL,
    FOREIGN KEY (student_id) REFERENCES student_profile(student_id) ON DELETE SET NULL
);

-- Student Game Session
CREATE TABLE student_game_session (
    link_id INT PRIMARY KEY AUTO_INCREMENT,
    game_session_id INT,
    session_id INT,
    user_id INT,
    active_participant CHAR(1),
    FOREIGN KEY (game_session_id) REFERENCES game_session(game_session_id) ON DELETE SET NULL,
    FOREIGN KEY (session_id) REFERENCES session_instance(session_id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES session_instance(student_id) ON DELETE SET NULL
);

-- Question Submission
CREATE TABLE question_submission (
    reflection_id INT PRIMARY KEY AUTO_INCREMENT,
    session_id INT,
    user_id INT,
    game_session_id INT,
    submit_dt TIMESTAMP  DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
    question_txt VARCHAR(4000),
    response_txt VARCHAR(4000),
    FOREIGN KEY (session_id) REFERENCES session_instance(session_id),
    FOREIGN KEY (user_id) REFERENCES session_instance(student_id),
    FOREIGN KEY (game_session_id) REFERENCES game_session(game_session_id)
);

-- Partner Pairing
CREATE TABLE partner_pairing (
    pairing_id INT PRIMARY KEY AUTO_INCREMENT,
    round_num TINYINT,
    create_dt TIMESTAMP DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
);

-- Session Partner
CREATE TABLE session_partner (
    session_partner_id INT PRIMARY KEY AUTO_INCREMENT,
    link_id INT,
    pairing_id INT,
    round_num TINYINT,
    FOREIGN KEY (link_id) REFERENCES student_game_session(link_id),
    FOREIGN KEY (pairing_id) REFERENCES partner_pairing(pairing_id)
);

-- Player Round History
CREATE TABLE player_round_history (
    round_history_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    session_id INT,
    link_id INT,
    round_num TINYINT,
    card_selected VARCHAR(5),
    round_score SMALLINT,
    FOREIGN KEY (user_id) REFERENCES session_instance(student_id),
    FOREIGN KEY (session_id) REFERENCES session_instance(session_id),
    FOREIGN KEY (link_id) REFERENCES student_game_session(link_id)
);

-- Red/Black Player Score
CREATE TABLE rb_player_score (
    score_id INT PRIMARY KEY AUTO_INCREMENT,
    session_id INT,
    user_id INT,
    score_total SMALLINT,
    FOREIGN KEY (user_id) REFERENCES session_instance(student_id),
    FOREIGN KEY (session_id) REFERENCES session_instance(session_id)
);

/*
    Wheat & Steel Tables
*/

-- Production Goal Profile
CREATE TABLE prod_goal_profile (
    profile_id INT PRIMARY KEY AUTO_INCREMENT,
    profile_nm VARCHAR(30),
    wheat_goal_num SMALLINT,
    steel_goal_num SMALLINT,
    round_resource_num SMALLINT,
    wheat_rec_resource_num SMALLINT,
    steel_rec_resource_num SMALLINT
);

-- Group Pairing
CREATE TABLE group_pairing (
    group_id INT PRIMARY KEY AUTO_INCREMENT,
    profile_id INT,
    group_name VARCHAR(30),
    trading_complete CHAR(1),
    total_steel_cons SMALLINT,
    total_wheat_cons SMALLINT,
    FOREIGN KEY (profile_id) REFERENCES prod_goal_profile(profile_id)
);

-- Group Student
CREATE TABLE group_student (
    gs_id INT PRIMARY KEY AUTO_INCREMENT,
    session_id INT,
    user_id INT,
    group_id INT,
    is_leader CHAR(1),
    FOREIGN KEY (user_id) REFERENCES session_instance(student_id),
    FOREIGN KEY (session_id) REFERENCES session_instance(session_id),
    FOREIGN KEY (group_id) REFERENCES group_pairing(group_id)
);

-- Round Score
CREATE TABLE round_score (
    round_score_id INT PRIMARY KEY AUTO_INCREMENT,
    group_id INT,
    round_num TINYINT,
    wheat_produced SMALLINT,
    steel_produced SMALLINT,
    wheat_traded SMALLINT,
    steel_traded SMALLINT,
    wheat_cons SMALLINT,
    steel_cons SMALLINT,
    FOREIGN KEY (group_id) REFERENCES group_pairing(group_id)
);

-- Trade Partnership
CREATE TABLE trade_partnership (
    partnership_id INT PRIMARY KEY AUTO_INCREMENT,
    round_num TINYINT,
    sent_total SMALLINT,
    sent_type VARCHAR(10)
);

-- Group Trade
CREATE TABLE group_trade (
    group_trade_id INT PRIMARY KEY AUTO_INCREMENT,
    group_id INT,
    partnership_id INT,
    trade_role CHAR(1),
    FOREIGN KEY (group_id) REFERENCES group_pairing(group_id),
    FOREIGN KEY (partnership_id) REFERENCES trade_partnership(partnership_id)
);

/*
    Import Required Data
*/

-- Game Catalog
INSERT INTO game_catalog (game_title)
    VALUES ('Class Simulation: Red Card Black Card'),
           ('Class Simulation: Wheat & Steel');

-- Game Parameters/Ruleset
INSERT INTO red_black_card_param (game_id, game_instruction_txt, red_point_value, black_point_value, total_round_num, hidden_round_num)
    VALUES (1, 'You have a red card and a black card. 
In the first four rounds you will first pick a card and then you will find out who you are paired up with. You will then reveal the card to the other person. 
In the next eight rounds, you will be paired up with a person, and then you will, without showing the other person, pick one of the two cards. The other person, without showing you the card, will pick one of the two cards. 
You will then show each other the cards. 
In all rounds, if you play a red card you receive $50 and the other person receives nothing. 
If you play a black card you receive nothing and the other person receives $150. 
Participating in this exercise and answering the questions at the end of this is worth ten points. The person who has the greatest total receives 10 additional points and $10, and the person who has the second greatest total receives 5 additional points and $5.', 50, 150, 12, 4);

-- Questions for students
INSERT INTO question_for_student (game_id, question_txt)
    VALUES (1, 'Describe your strategy at the beginning of this exercise'),
           (1, 'Describe how it changed near the end of the exercise');
