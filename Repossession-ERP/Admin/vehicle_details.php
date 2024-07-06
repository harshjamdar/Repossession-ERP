<?php 
require_once 'db_config.php';

if (isset($_GET['veh_no'])) {
    $veh_no = mysqli_real_escape_string($conn, $_GET['veh_no']); // Escape input
    $sql = "SELECT * FROM vehicles WHERE veh_no = '$veh_no'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo "<div class='vehicle-details mt-4'>";
        echo "<h2>Vehicle Details</h2>";
        echo "Vehicle No: " . $row["veh_no"] . "<br>";
        echo "Company: " . $row["COMPANY"] . "<br>";
        echo "Customer Name: " . $row["CustomerName"] . "<br>";
        echo "Model: " . $row["Model"] . "<br>";
        echo "Chassis No: " . $row["ChassisNo"] . "<br>";
        echo "Make: " . $row["Make"] . "<br>";
        echo "Conf Level 1: " . $row["ConfLevel1"] . "<br>";
        echo "Mob Level 1: " . $row["MobLevel1"] . "<br>";
        echo "Conf Level 2: " . $row["ConfLevel2"] . "<br>";
        echo "Mob Level 2: " . $row["MobLevel2"] . "<br>";
        echo "Agreement No: " . $row["AgrNo"] . "<br>";
        echo "Branch: " . $row["BRANCH"] . "<br>";
        echo "Engine No: " . $row["ENGNO"] . "<br>";
        echo "BKT: " . $row["BKT"] . "<br>";
        echo "EMI: " . $row["EMI"] . "<br>";
        echo "POS: " . $row["POS"] . "<br>";
        echo "Legal: " . $row["LEGAL"] . "<br>";

        // Edit and Delete buttons
        echo "<a href='edit_vehicle.php?veh_no=" . $row["veh_no"] . "' class='btn btn-primary'>Edit</a> ";
        echo "<a href='delete_vehicle.php?veh_no=" . $row["veh_no"] . "' class='btn btn-danger' onclick='return confirm(\"Are you sure you want to delete this vehicle?\");'>Delete</a>";
        
        echo "</div>";
    } else {
        echo "<div class='mt-4 alert alert-warning'>Vehicle not found.</div>";
    }
}
?>
