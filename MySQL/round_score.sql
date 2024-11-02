CREATE TABLE round_score (
    round_id INT AUTO_INCREMENT PRIMARY KEY,
    game_id INT,
    round_num INT,
    wheat_produced INT,
    steel_produced INT,
    wheat_traded VARCHAR(10),
    steel_traded VARCHAR(10),
    wheat_consumed INT,
    steel_consumed INT,
    FOREIGN KEY (game_id) REFERENCES game_info(game_id)
);

INSERT INTO round_score 
    (game_id, round_num, wheat_produced, steel_produced, wheat_traded, steel_traded, wheat_consumed, steel_consumed)
VALUES 
    (2, 1, 0, 150, '-', '-', 0, 150),
    (2, 2, 0, 150, '0', '0', 0, 150),
    (2, 3, 0, 150, '0', '0', 0, 150),
    (2, 4, 100, 0, '+100', '-150', 100, 0),
    (2, 5, 100, 0, '+100', '-150', 100, 0);
