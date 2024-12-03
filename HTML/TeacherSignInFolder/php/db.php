<?php

include('../../../env.php');

// Load environment variables
$dbHost = getenv('DB_HOST') ?: die('DB_HOST is not set.');
$dbName = getenv('DB_NAME') ?: die('DB_NAME is not set.');
$dbUser = getenv('DB_USER') ?: die('DB_USER is not set.');
$dbPass = getenv('DB_PASS') ?: die('DB_PASS is not set.');

// Establish database connection
$mysqli = new mysqli("cssgametheory.com", "txlxmqol_dbUser", "databaseManager", "txlxmqol_GameTheory_2.0");

// Check for connection errors
if ($mysqli->connect_errno) {
    error_log("Database connection error: " . $mysqli->connect_error);
    die("A connection error occurred. Please try again later.");
}

return $mysqli;
