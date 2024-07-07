<?php
session_start();

// Redirect to login page if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require 'db_config.php'; // Adjust this based on your database connection setup

// Initialize $pdo if not already initialized
if (!isset($pdo)) {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    // Set PDO to throw exceptions on error
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}

$user = null;
$user_id = null;
$update_success = false;
$error_message = '';

// Retrieve user details based on user_id from query string
if (!empty($_GET['user_id'])) {
    $user_id = $_GET['user_id'];

    $sql = "SELECT username, name FROM users WHERE user_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        // Redirect to user management page or handle error
        header('Location: user_management.php');
        exit;
    }
}

// Handle form submission to update user details
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_username'])) {
        $username = $_POST['username'];
        $name = $_POST['name'];

        // Validate inputs
        if (empty($username) || empty($name)) {
            $error_message = 'Username and Name cannot be empty.';
        } else {
            $sql = "UPDATE users SET username = ?, name = ? WHERE user_id = ?";
            $stmt = $pdo->prepare($sql);
            if ($stmt->execute([$username, $name, $user_id])) {
                // User updated successfully
                $update_success = true;
                $user['username'] = $username; // Update the username for display
                $user['name'] = $name; // Update the name for display
            } else {
                // Error handling, e.g., display an error message
                $error_message = 'Failed to update username and name. Please try again.';
            }
        }
    } elseif (isset($_POST['update_password'])) {
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        // Validate passwords
        if (empty($password) || empty($confirm_password)) {
            $error_message = 'Password and confirmation password cannot be empty.';
        } elseif ($password !== $confirm_password) {
            $error_message = 'Passwords do not match.';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql = "UPDATE users SET password = ? WHERE user_id = ?";
            $stmt = $pdo->prepare($sql);
            if ($stmt->execute([$hashed_password, $user_id])) {
                // Password updated successfully
                $update_success = true;
            } else {
                // Error handling, e.g., display an error message
                $error_message = 'Failed to update password. Please try again.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        .password-toggle {
            cursor: pointer;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h2>Edit User</h2>

    <?php if ($update_success): ?>
        <div class="alert alert-success" role="alert">
            <?php echo isset($_POST['update_username']) ? 'Username and Name updated successfully.' : 'Password updated successfully.'; ?>
        </div>
    <?php elseif (!empty($error_message)): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>

    <!-- Form to update username and name -->
    <form method="POST">
        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" required>
        </div>
        <div class="form-group">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" required>
        </div>
        <button type="submit" name="update_username" class="btn btn-primary">Update Username and Name</button>
    </form>

    <!-- Form to update password -->
    <form method="POST" class="mt-4">
        <div class="form-group">
            <label for="password">New Password:</label>
            <div class="input-group">
                <input type="password" id="password" name="password" class="form-control" required>
                <div class="input-group-append">
                    <span class="input-group-text password-toggle" onclick="togglePassword('password')">
                        <i class="fas fa-eye"></i>
                    </span>
                </div>
            </div>
        </div>
        <div class="form-group">
            <label for="confirm_password">Confirm New Password:</label>
            <div class="input-group">
                <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                <div class="input-group-append">
                    <span class="input-group-text password-toggle" onclick="togglePassword('confirm_password')">
                        <i class="fas fa-eye"></i>
                    </span>
                </div>
            </div>
        </div>
        <button type="submit" name="update_password" class="btn btn-primary">Update Password</button>
    </form>
</div>

<script>
    function togglePassword(id) {
        var input = document.getElementById(id);
        var icon = input.nextElementSibling.querySelector('i');
        if (input.type === "password") {
            input.type = "text";
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = "password";
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
</script>
</body>
</html>
