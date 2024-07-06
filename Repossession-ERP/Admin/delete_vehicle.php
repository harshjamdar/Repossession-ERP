<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Vehicle</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <?php
    require_once 'db_config.php';

    if (isset($_GET['veh_no'])) {
        $veh_no = mysqli_real_escape_string($conn, $_GET['veh_no']);

        // Delete query
        $sql = "DELETE FROM vehicles WHERE veh_no='$veh_no'";

        if ($conn->query($sql) === TRUE) {
            echo "<div class='alert alert-success'>Vehicle deleted successfully.</div>";
        } else {
            echo "<div class='alert alert-danger'>Error deleting record: " . $conn->error . "</div>";
        }
    }
    ?>
    <a href="index.php" class="btn btn-primary">Back to Home</a>
</div>
</body>
</html>
