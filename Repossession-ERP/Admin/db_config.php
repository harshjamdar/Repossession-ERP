<?php
$servername = "localhost";
$username = "bharaten_vehdetails";
$password = "mkdY_5uQTTnk";
$dbname = "bharaten_vehdetails";

$conn = new mysqli($servername, $username, $password, $dbname);

// Error handling for database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>