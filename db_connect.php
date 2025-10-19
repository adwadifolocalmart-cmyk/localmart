<?php
// db_connect.php

// --- Database Credentials ---
// Replace with your actual database details
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root'); // Default for XAMPP
define('DB_PASSWORD', '');     // Default for XAMPP
define('DB_NAME', 'adwadifo_db');

// --- Establish Connection ---
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    // Create a new MySQLi object
    $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

    // Check connection for errors
    if ($conn->connect_error) {
        // If connection fails, stop the script and display an error
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Set character set to utf8mb4 for full Unicode support
    $conn->set_charset("utf8mb4");

} catch (Exception $e) {
    // Catch any exceptions during connection and display a user-friendly error
    // In a production environment, you might log this error instead of showing it
    error_log("DB Connection error: " . $e->getMessage());
    die("Error: Could not connect to the database. " . $e->getMessage());
}

// The $conn variable can now be used by any script that includes this file.
?>
