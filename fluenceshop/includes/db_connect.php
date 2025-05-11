<?php
/**
 * Database Connection
 * 
 * This file establishes a connection to the MySQL database.
 */

// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fluenceshop";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}