<?php 
require_once 'db_config.php'; 
session_start();

if (isset($_SESSION['search_results'])) {
    $result = $_SESSION['search_results'];

    if ($result->num_rows > 0) {
        echo "<div class='mt-4'>";
        echo "<h2>Search Results:</h2>";
        echo "<ul>";
        while ($row = $result->fetch_assoc()) {
            $veh_no = $row["veh_no"];
            echo "<li><a href='?veh_no=$veh_no'>$veh_no</a></li>";
        }
        echo "</ul>";
        echo "</div>";
    } else {
        echo "<div class='mt-4 alert alert-warning'>No vehicles found with that criteria.</div>";
    }

    // DO NOT UNSET THE SESSION VARIABLE HERE 
}
?>