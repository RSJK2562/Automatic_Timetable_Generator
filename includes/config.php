<?php
// Database configuration
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'timetable_generator';

// Create database connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set timezone
date_default_timezone_set('UTC');

// Start or resume session
session_start();

// Error reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>