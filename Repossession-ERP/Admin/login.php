<?php
session_start();

// Include the database configuration file
require 'db_config.php';

$max_attempts = 5;
$lockout_time = 30; // seconds
$error_message = ''; // Initialize error message variable

// Check if the session variables for attempts and lockout are set
if (!isset($_SESSION['attempts'])) {
    $_SESSION['attempts'] = 0;
}

if (!isset($_SESSION['last_attempt_time'])) {
    $_SESSION['last_attempt_time'] = time();
}

// Function to check if the user is locked out
function isLockedOut() {
    global $max_attempts, $lockout_time;
    
    if ($_SESSION['attempts'] >= $max_attempts) {
        $elapsed_time = time() - $_SESSION['last_attempt_time'];
        if ($elapsed_time < $lockout_time) {
            return true;
        } else {
            // Reset attempts after lockout time has passed
            $_SESSION['attempts'] = 0;
            return false;
        }
    }
    return false;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isLockedOut()) {
        $error_message = 'Too many login attempts. Please try again in 30 seconds.';
    } else {
        $username = $_POST['username'];
        $password = $_POST['password'];

        // Prepare and execute a query to fetch the user
        $stmt = $conn->prepare('SELECT user_id, username, password FROM admins WHERE username = ?');
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            // User authenticated successfully
            $_SESSION['user_id'] = $user['user_id']; // Store user id in session
            $_SESSION['username'] = $user['username']; // Optionally store username
            $_SESSION['attempts'] = 0; // Reset attempts on successful login
            header('Location: index.php');
            exit;
        } else {
            // Authentication failed
            $_SESSION['attempts']++;
            $_SESSION['last_attempt_time'] = time();
            $error_message = 'Invalid username or password';
        }

        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        .password-toggle {
            cursor: pointer;
        }
    </style>
</head>
<body>
<div class="container">
    <h2 class="mt-5 text-center">Login</h2>
    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo $error_message; ?>
        </div>
    <?php endif; ?>
    <form method="post" action="login.php" class="mt-4">
        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" name="username" id="username" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="password">Password:</label>
            <div class="input-group">
                <input type="password" name="password" id="password" class="form-control" required>
                <div class="input-group-append">
                    <span class="input-group-text password-toggle" onclick="togglePassword('password')">
                        <i class="fas fa-eye"></i>
                    </span>
                </div>
            </div>
        </div>
        <input type="submit" value="Login" class="btn btn-primary">
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
