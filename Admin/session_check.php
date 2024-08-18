<?php
session_start();

function checkSession() {
    // Check if the user is logged in
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }
}

// Call this function on all protected pages
checkSession();
?>
