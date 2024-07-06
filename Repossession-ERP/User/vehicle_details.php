<?php 
require_once 'db_config.php';

if (isset($_GET['veh_no'])) {
    $veh_no = mysqli_real_escape_string($conn, $_GET['veh_no']); // Escape input
    $sql = "SELECT veh_no, CustomerName, Model FROM vehicles WHERE veh_no = '$veh_no'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo "<div class='vehicle-details mt-4'>";
        echo "<h2>Vehicle Details</h2>";
        echo "Vehicle No: " . $row["veh_no"] . "<br>";
        echo "Customer Name: " . $row["CustomerName"] . "<br>";
        echo "Model: " . $row["Model"] . "<br>";
        echo "<a href='index.php?search_results=true' class='btn btn-secondary'>Back to Search Results</a>"; 
        echo "<br>"; 
        echo "<hr>"; 
        echo "<a href='index.php?clear_session=true' class='btn btn-secondary'>Back to Home Search (Clear Session)</a>";
        echo "</div>";
    } else {
        echo "<div class='mt-4 alert alert-warning'>Vehicle not found.</div>";
    }
}
?>