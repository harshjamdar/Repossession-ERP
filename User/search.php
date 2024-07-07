<?php 
require_once 'db_config.php';

// Check if the form was submitted via POST method
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if search_veh_part is set and is exactly 4 characters long
    if (isset($_POST["search_veh_part"]) && strlen($_POST["search_veh_part"]) == 4) {
        // Sanitize input (although we will use prepared statements for security)
        $search_veh_part = $_POST["search_veh_part"];

        // Prepare the SQL statement using a parameterized query
        $sql = "SELECT veh_no FROM vehicles WHERE veh_no LIKE ? ORDER BY veh_no";
        $stmt = $conn->prepare($sql);

        // Check if the preparation of the statement was successful
        if ($stmt) {
            // Bind the search parameter and execute the statement
            $search_param = "%{$search_veh_part}%";
            $stmt->bind_param("s", $search_param);
            $stmt->execute();

            // Get result set
            $result = $stmt->get_result();

            // Check if there are any results
            if ($result->num_rows > 0) {
                // Fetch the vehicle numbers into an array
                $veh_numbers = array();
                while ($row = $result->fetch_assoc()) {
                    $veh_numbers[] = $row['veh_no'];
                }

                // Start session and store the search results in session variable
                session_start();
                $_SESSION['search_results'] = $veh_numbers;

                // Redirect to index.php with search_results=true to display results
                header("Location: index.php?search_results=true");
                exit();
            } else {
                //echo "No vehicles found with that criteria.";
                echo "<div class='col-12 alert alert-warning'>No vehicles found with that criteria. <a href='index.php' class='btn btn-link'>Go Back</a></div>";
            }

            // Close statement
            $stmt->close();
        } else {
            // If prepare fails, show error (for debugging)
            echo "Prepare statement failed: " . htmlspecialchars($conn->error);
        }
    } else {
        // If search_veh_part is not set or not 4 characters long, show error message
        echo "Please enter a partial number consisting of 4 digits.";
    }
}
?>
