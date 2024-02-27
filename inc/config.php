<?php

/**
 * Ranking System Configuration and Database Connection Script
 */

// Site Configuration
$siteConfig = [
    'title' => "Ranking System", // Title for the browser tab
    'main_title_1' => "Ranking", // First part of the site title
    'main_title_2' => "System", // Second part of the site title
    'enable_footer' => true, // Toggle to enable or disable the footer
    'enable_search' => true, // Toggle to enable or disable the search function
];

// Database Configuration
$dbConfig = [
    'hostname' => "HOSTNAME",
    'username' => "USERNAME",
    'password' => "PASSWORD",
    'dbname' => "DATABASE",
];

/**
 * Establishes a connection to the database using the provided configuration.
 * 
 * @param array $config Database configuration settings.
 * @return mysqli Returns a mysqli object representing the connection to the database.
 */
function connectToDatabase($config) {
    $connection = new mysqli($config['hostname'], $config['username'], $config['password'], $config['dbname']);

    // Check the connection
    if ($connection->connect_error) {
        die("Connection failed: " . $connection->connect_error);
    }

    return $connection;
}

// Attempt to connect to the database
$conn = connectToDatabase($dbConfig);
?>
