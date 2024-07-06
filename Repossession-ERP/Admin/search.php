<?php 
require_once 'db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["search_veh_part"]) && strlen($_POST["search_veh_part"]) == 4) {
        $search_veh_part = $conn->real_escape_string($_POST["search_veh_part"]);
        $sql = "SELECT veh_no FROM vehicles WHERE veh_no LIKE '%$search_veh_part' ORDER BY veh_no";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            // Fetch the vehicle numbers into an array
            $veh_numbers = array();
            while ($row = $result->fetch_assoc()) {
                $veh_numbers[] = $row['veh_no'];
            }

            session_start();
            $_SESSION['search_results'] = $veh_numbers; // Store the array in the session
            header("Location: index.php?search_results=true");
            exit();
        } else {
            echo "No vehicles found with that criteria.";
        }
    } else if (isset($_POST["search_veh_full"])) {
        $search_veh_full = $_POST["search_veh_full"];
        $sql = "SELECT * FROM vehicles WHERE veh_no = '$search_veh_full'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            // Redirect to index.php with vehicle details
            $row = $result->fetch_assoc();
            header("Location: index.php?veh_no=".urlencode($row['veh_no']));
            exit();
        } else {
            echo "Vehicle not found.";
        }
    } else {
        echo "Please enter a partial or full vehicle number.";
    }
}
?>