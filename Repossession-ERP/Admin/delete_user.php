<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require 'db_config.php'; // Adjust this based on your database connection setup

// Handle deletion based on user_id from query string
if (!empty($_GET['id'])) {
    $user_id = $_GET['id'];

    $sql = "DELETE FROM users WHERE user_id = ?";
    $stmt = $pdo->prepare($sql);
    if ($stmt->execute([$user_id])) {
        // User deleted successfully
        header('Location: user_management.php');
        exit;
    } else {
        // Error handling, e.g., display an error message
    }
}
?>
