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

// Function to delete a user by user_id
function deleteUser($pdo, $user_id) {
    $sql = "DELETE FROM users WHERE user_id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$user_id]);
}

// Handle delete request if user_id is provided
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user_id'])) {
    $delete_user_id = $_POST['delete_user_id'];
    if (deleteUser($pdo, $delete_user_id)) {
        // User deleted successfully
        header('Location: view_users.php');
        exit;
    } else {
        // Error handling
        echo '<div class="alert alert-danger" role="alert">Failed to delete user. Please try again.</div>';
    }
}

// Retrieve list of users based on search query or all users if no search query
if (isset($_GET['search']) && !empty($_GET['search_term'])) {
    $search_term = '%' . $_GET['search_term'] . '%';
    $sql = "SELECT user_id, username, name FROM users WHERE username LIKE :search_term OR name LIKE :search_term";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['search_term' => $search_term]);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $sql = "SELECT user_id, username, name FROM users";
    $stmt = $pdo->query($sql);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Users</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h2>View Users</h2>

    <div class="row mb-3">
        <div class="col-md-6">
            <form method="GET" class="form-inline">
                <div class="form-group mr-2">
                    <input type="text" name="search_term" class="form-control" placeholder="Search by Username or Name">
                </div>
                <button type="submit" name="search" class="btn btn-primary">Search</button>
            </form>
        </div>
    </div>

    <div class="list-group">
        <?php
        $sr_no = 1; // Initialize serial number
        foreach ($users as $user):
        ?>
            <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                <span><?php echo $sr_no++; ?></span> <!-- Serial number -->
                <div>
                    <span><strong><?php echo htmlspecialchars($user['name']); ?></strong> (<?php echo htmlspecialchars($user['username']); ?>)</span>
                </div>
                <div>
                    <a href="edit_user.php?user_id=<?php echo htmlspecialchars($user['user_id']); ?>" class="btn btn-primary btn-sm mr-2">Edit</a>
                    <form method="POST" style="display: inline-block;">
                        <input type="hidden" name="delete_user_id" value="<?php echo htmlspecialchars($user['user_id']); ?>">
                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this user?')">Delete</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
