CREATE TABLE player_stats (
    game_id INT,
    highest_name VARCHAR(30),
    highest_score INT,
    lowest_name VARCHAR(30),
    lowest_score INT,
    FOREIGN KEY (game_id) REFERENCES game_info(game_id)
);

INSERT INTO player_stats (game_id, highest_name, highest_score, lowest_name, lowest_score)
VALUES 
    (1, 'Jack', 1400, 'Sam', 200);
