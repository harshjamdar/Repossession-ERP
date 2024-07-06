<?php
$servername = "localhost";
$username = "bharaten_vehdetails";
$password = "mkdY_5uQTTnk";
$dbname = "bharaten_vehdetails";

$conn = new mysqli($servername, $username, $password, $dbname);

// Error handling for database connection
if ($conn->connect_error) {
    echo '<div class="alert alert-danger">Database connection failed: ' . $conn->connect_error . '</div>';
}
?>