CREATE TABLE game_stats (
    game_id INT,
    black_total INT,
    black_average DECIMAL(5,2),
    red_total INT,
    red_average DECIMAL(5,2),
    FOREIGN KEY (game_id) REFERENCES game_info(game_id)
);

INSERT INTO game_stats (game_id, black_total, black_average, red_total, red_average)
VALUES 
    (1, 88, 11, 104, 13);
