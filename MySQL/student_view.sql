CREATE TABLE student_view (
    view_id INT AUTO_INCREMENT PRIMARY KEY,
    red_black_num INT,
    red_black_high INT,
    red_black_low INT,
    red_black_gpa DECIMAL(5,2),
    wheat_steel_num INT,
    wheat_high INT,
    wheat_goal_num VARCHAR(20),
    steel_high INT,
    steel_goal_num VARCHAR(20)
);

INSERT INTO student_view 
    (red_black_num, red_black_high, red_black_low, red_black_gpa, wheat_steel_num, wheat_high, wheat_goal_num, steel_high, steel_goal_num)
VALUES 
    (3, 900, 400, 633.33, 2, 100, '1/2 (50%)', 750, '2/2 (100%)');
