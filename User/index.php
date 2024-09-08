<?php
session_start();
// Check if user is not logged in, redirect to login page
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Logout functionality
if (isset($_GET['logout']) && $_GET['logout'] === 'true') {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
}

// Function to display search results if available
function displaySearchResults() {
    if (isset($_GET['search_results']) && isset($_SESSION['search_results'])) {
        $veh_numbers = $_SESSION['search_results'];
        if (!empty($veh_numbers)) {
            sort($veh_numbers); // Sort results alphabetically
            $total_results = count($veh_numbers);
            $half = ceil($total_results / 2); // Determine the midpoint for splitting into two columns

            echo '<div class="container">';
            echo '<div class="row search-results">';

            // Display first column
            echo '<div class="col-6">';
            for ($i = 0; $i < $half; $i++) {
                echo '<div class="search-result-item">';
                echo '<a href="index.php?veh_no=' . $veh_numbers[$i] . '" class="vehicle-number">' . $veh_numbers[$i] . '</a>';
                echo '</div>';
            }
            echo '</div>';

            // Display second column
            echo '<div class="col-6">';
            for ($i = $half; $i < $total_results; $i++) {
                echo '<div class="search-result-item">';
                echo '<a href="index.php?veh_no=' . $veh_numbers[$i] . '" class="vehicle-number">' . $veh_numbers[$i] . '</a>';
                echo '</div>';
            }
            echo '</div>';

            echo '</div>';
            echo '</div>';
        } else {
            echo '<div class="alert alert-warning">No vehicles found. <a href="index.php" class="btn btn-link">Go Back</a></div>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Title</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa; /* Light gray background */
        }

        .container {
            padding-top: 0px;
            padding-bottom: 0px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .brand-title {
            font-size: 14px;
            font-weight: bold;
            color: #333;/* text-align: center; */ /* No need for text-align center here */
            display: inline-block; /* Ensures inline display */
        }

        .btn {
            vertical-align: middle; /* Aligns the button vertically with text */
        }

        .search-form {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 5px;
        }

        .form-control {
            border: 1px solid #ced4da;
            border-radius: 5px;
            padding: 5px;
            font-size: 12px;
        }

        .form-control:focus {
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .search-results {
            margin-top: 1px;
            margin-bottom: 0px;
            
        }

        .search-result-item {
            background-color: #e9ecef;
            padding: 5px;
            margin-bottom: 10px;
            border-radius: 5px;
            margin-top: 0px;
            text-align: center;
        }

        .search-result-item:hover {
            background-color: #d1d5db;
        }

        .vehicle-number {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            text-align: center;
        }

        .logout-btn {
            background-color: #dc3545; /* Red button */
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px 25px;
            cursor: pointer;
            display: block;
            margin-left: auto;
            margin-right: auto;
            margin-top: 20px;
            margin-bottom: 20px;
            width: 100px; /* Ensures consistent width */
        }

        .logout-btn:hover {
            background-color: #c82333; /* Darker red on hover */
        }

        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 14px;
            color: #6c757d;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Function to focus on input and open keypad
            function confirmLogout(event) {
            if (!confirm('Are you sure you want to log out?')) {
                event.preventDefault(); // Prevent the default action (logout) if user cancels
                }
            }

            // Add event listener to the logout link
            var logoutLink = document.querySelector('.logout-btn');
            if (logoutLink) {
                logoutLink.addEventListener('click', confirmLogout);
            }

            function focusInput() {
                var input = document.getElementById('search_veh_part');
                input.focus();
                input.dispatchEvent(new Event('input')); // Simulate input event if needed
            }

            // Initial focus and input event
            var input = document.getElementById('search_veh_part');
            input.focus();
            input.dispatchEvent(new Event('input')); // Simulate an input event 

            // Handle form submission
            input.addEventListener('input', function() {
                if (input.value.length === 4) {
                    document.getElementById('search_form').submit();
                }
            });

            // Check URL parameters to determine if session was cleared
            var urlParams = new URLSearchParams(window.location.search);
            var clearSession = urlParams.get('clear_session');
            if (clearSession === 'true') {
                focusInput();
            }

            // Function to open recursive keypad after displaying search results
            var searchResults = urlParams.get('search_results');
            if (searchResults === 'true') {
                focusInput(); // Focus on input field
                // You can trigger the recursive keypad here if needed
                // Example: window.androidInterface.openRecursiveKeypad();
            }

            // Function to reopen keypad after clicking on a vehicle number link
            var vehicleLink = urlParams.get('veh_no');
            if (vehicleLink) {
                focusInput(); // Focus on input field
            }
        });
    </script>
</head>
<body>
    <div class="container">
        <!-- Header and search form -->
        <div class="text-center">
            <h1 class="brand-title mb-0">Your Title</h1>
            <a href="tel:Your Phone Number" class="btn btn-primary btn-sm ml-2">Call Your Phone Number</a>
        </div>

 	<div class="search-form">
    	    <form id="search_form" method="post" action="search.php" class="form-inline">
        	<div class="form-group mb-2">
            	<input type="text" name="state_code" id="state_code" class="form-control" placeholder="State Code (e.g., MH)" value="<?php echo 			isset($_SESSION['state_code']) ? $_SESSION['state_code'] : ''; ?>" style="width: 100px; margin-right: 10px;">
        	</div>
        	<div class="form-group mb-2">
            	<input type="tel" name="search_veh_part" id="search_veh_part" class="form-control" maxlength="4" inputmode="numeric" placeholder="4 digits" 		style="width: 120px;">
        	</div>
    	    </form>
	</div>

        <!-- Display search results -->
        <?php displaySearchResults(); ?>

        <!-- Display vehicle details if veh_no is set -->
        <?php
        if (isset($_GET['veh_no'])) {
            echo '<div class="mt-4">';
            require_once 'vehicle_details.php';
            echo '</div>';
        }
        ?>

        <a href="index.php?logout=true" class="logout-btn">Logout</a>
        <div class="footer">Copyright © 2024 Your Title</div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="script.js"></script>
</body>
</html>
