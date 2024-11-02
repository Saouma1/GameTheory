-- Game Info
INSERT INTO game_info (game_name, game_date, player_num, pair_num, round_num)
VALUES ('RedBlack', '2023-11-01', 4, 2, 5);

INSERT INTO game_info (game_name, game_date, player_num, pair_num, round_num)
VALUES ('WheatSteel', '2023-11-02', 6, NULL, 3);

-- RedBlack Stats
INSERT INTO red_black_stats (game_id, black_total, black_average, red_total, red_average,
                             highest_name, highest_score, lowest_name, lowest_score,
                             highest_black_name, highest_black_score, lowest_black_name, lowest_black_score,
                             highest_red_name, highest_red_score, lowest_red_name, lowest_red_score)
VALUES (1, 200, 50.0, 150, 37.5, 'Alice', 100, 'Bob', 20,
        'Alice', 80, 'Bob', 15, 'Charlie', 70, 'Dana', 10);

-- WheatSteel Stats
INSERT INTO wheat_steel_stats (game_id, teams_four, period_num, both_goals, one_goal, no_goals,
                               wheat_produce_total, wheat_produce_average, wheat_consume_total,
                               wheat_consume_average, wheat_trade_total, wheat_trade_average,
                               steel_produce_total, steel_produce_average, steel_consume_total,
                               steel_consume_average, steel_trade_total, steel_trade_average)
VALUES (2, TRUE, 5, 'Alice,Charlie', 'Bob,Dana', 'Eve',
        500, 125.0, 450, 112.5, 300, 75.0,
        400, 100.0, 350, 87.5, 200, 50.0);
