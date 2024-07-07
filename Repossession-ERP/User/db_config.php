<?php
$servername = "your-database-servername(localhost)";
$username = "your-database-username";
$password = "your-database-password";
$dbname = "your-database-dbname";

$conn = new mysqli($servername, $username, $password, $dbname);

// Error handling for database connection
if ($conn->connect_error) {
    echo '<div class="alert alert-danger">Database connection failed: ' . $conn->connect_error . '</div>';
}
?>
