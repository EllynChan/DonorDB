<?php
$servername = "localhost"; // XAMPP runs MySQL on localhost
$username = "root"; // Default user in XAMPP
$password = ""; // No password by default
$database = "donor_db"; // Use the database name you created

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>