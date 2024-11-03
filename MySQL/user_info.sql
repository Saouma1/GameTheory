CREATE TABLE user_info (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) DEFAULT 'n/a',
    last_name VARCHAR(50) DEFAULT 'n/a',
    email VARCHAR(100) DEFAULT 'n/a'
);

INSERT INTO user_info (first_name, last_name, email)
VALUES
    ('John', 'Doe', 'john.doe@example.com'); -- Replace these values with actual data or user input values

SELECT COUNT(*) AS numRecords FROM user_info;

SELECT * FROM user_info ORDER BY user_id DESC LIMIT 1;
