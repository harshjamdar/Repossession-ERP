<?php
require 'db_config.php'; // Include db_config.php to establish $conn connection
include 'session_check.php';

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $name = $_POST['name']; // Retrieve the name from the form

    // Check if the username already exists
    $stmt_check = $conn->prepare('SELECT username FROM users WHERE username = ?');
    $stmt_check->bind_param('s', $username);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows > 0) {
        $error_message = 'Username already exists. Please choose a different username.';
    } else {
        // Prepare and execute an INSERT statement
        $stmt_insert = $conn->prepare('INSERT INTO users (username, password, name) VALUES (?, ?, ?)');
        $stmt_insert->bind_param('sss', $username, $password, $name);

        if ($stmt_insert->execute()) {
            echo '<div class="alert alert-success" role="alert">User created successfully</div>';
        } else {
            echo '<div class="alert alert-danger" role="alert">Error creating user: ' . $conn->error . '</div>';
        }

        $stmt_insert->close(); // Close statement
    }

    $stmt_check->close(); // Close statement
    $conn->close(); // Close connection
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create User</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<div class="container">
    <h2 class="mt-5 text-center">Create User</h2>
    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo $error_message; ?>
        </div>
    <?php endif; ?>
    <form method="post" action="add_user.php" class="mt-4">
        <div class="form-group">
            <label for="name">Name:</label>
            <input type="text" name="name" id="name" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="username">Username:(Used For Login)</label>
            <input type="text" name="username" id="username" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" name="password" id="password" class="form-control" required>
        </div>
        <input type="submit" value="Create User" class="btn btn-primary">
    </form>
</div>
</body>
</html>