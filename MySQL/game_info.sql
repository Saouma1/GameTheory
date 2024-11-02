CREATE TABLE game_info (
    game_id INT AUTO_INCREMENT PRIMARY KEY,
    game_name VARCHAR(50),
    game_date DATE,
    player_num INT,
    pair_num INT,
    round_num INT
);

INSERT INTO game_info (game_name, game_date, player_count, pair_count, round_count)
VALUES
    ('Red Card Black Card', '2023-10-13', 24, 12, 8),
    ('Wheat & Steel', '2023-10-16', 24, NULL, 5);
