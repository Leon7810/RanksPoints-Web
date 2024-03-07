<?php
// error_reporting(E_ALL ^ E_WARNING); 
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
    'enable_pagination' => true, // Toggle to enable or disable the search function
    'enable_table_colours' => true, // Toggle the gold, silver and bronze colours on the table
    'api_key' => "YOUR-API-KEY", // Fill in your Steam API Key in here
    'rows_per_page' => "10", // How many rows of players should be shown on the table, default is 10

    //GeoIP Module - Default FALSE
    'enable_geoip' => true, // Toggles features that come with the GeoIP Module - ONLY PUT ON TRUE IF YOU HAVE THE CS2-GeoIP-RanksPoints INSTALLED ON YOUR SERVER
    'enable_flags' => true, // Toggle the country flags - Only put on true 
];

// Database Configuration
$dbConfig = [
    'hostname' => "HOSTNAME",
    'username' => "USERNAME",
    'password' => "PASSWORD",
    'dbname'   => "DATABASE",
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
