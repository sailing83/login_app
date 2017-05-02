<?php

/**
 * Check the database connection details are ok.
 *
 * *** Temporary script that should be deleted before putting live! ***
 *
 */

/**
 * Database connection data
 */
$host = "localhost";
$db_name = "framework";
$user = "dbuser";
$password = "Password";

/**
 * Create a connection
 */
$conn = new mysqli($host, $user, $password, $db_name);

/**
 * Check the connection
 */
if ($conn->connect_error) {
    echo "Connection failed: " . $conn->connect_error;
} else {
    echo "Connected successfully, connection data are ok.";
}
