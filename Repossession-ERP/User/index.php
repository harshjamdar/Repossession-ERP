<?php
// Start the session at the very beginning
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BHARAT ENTERPRISES</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
    /* Increase font size of vehicle numbers */
    .list-unstyled a {
        font-size: 1.4rem; /* Adjust as needed */
    }
    </style>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // 1. Get the input element
        var input = document.getElementById('search_veh_part');
        
        // 2. Focus and trigger input event
        input.focus(); 
        input.dispatchEvent(new Event('input')); // Simulate an input event 

        // 3. Set inputmode (optional, but it's good practice)
        input.setAttribute('inputmode', 'numeric'); 

        // 4. Handle form submission (if needed)
        input.addEventListener('input', function() {
            if (input.value.length === 4) {
                document.getElementById('search_form').submit();
            }
        });
        var urlParams = new URLSearchParams(window.location.search);
        var clearSession = urlParams.get('search_results=true');
        if (clearSession === 'true') {
            focusInput();
            
            
        }
    });
    </script>
</head>
<body>

<div class="container">
    <h1 class="mb-4 text-center">BHARAT ENTERPRISES</h1>
    <h2 class="mb-4 text-center"><a href="tel:+917666621322" class="btn btn-primary">Call +917666621322</a></h2>

    <div class="row">
        <div class="col-12 mb-4">
            <h2>Search for User</h2>
            <form id="search_form" method="post" action="search.php">
                <div class="form-group col-12">
                    <label for="search_veh_part">Enter Partial Vehicle No (4 digits):</label>
                   <input type="tel" name="search_veh_part" id="search_veh_part" class="form-control" maxlength="4" inputmode="numeric">
                </div>
            </form>
        </div>
    </div>
    

    <div class="row">
        <?php
        if (isset($_GET['clear_session']) && $_GET['clear_session'] === 'true') {
    unset($_SESSION['search_results']);
    // JavaScript to focus on the input field after clearing session
    echo "<script>";
    echo "document.addEventListener('DOMContentLoaded', function() {";
    echo "    var input = document.getElementById('search_veh_part');";
    echo "    input.focus();";
    echo "});";
    echo "</script>";
}

        // Display search results
        if (isset($_GET['search_results'])) {
            if (isset($_SESSION['search_results'])) {
                $veh_numbers = $_SESSION['search_results'];
                if (!empty($veh_numbers)) {
                    $total_results = count($veh_numbers);
                    echo "<div class='col-12'>";
                    echo "<h2>Search Results:</h2>";
                    echo "<div class='row'>";
                    
                    for ($i = 0; $i < $total_results; $i++) {
                        echo "<div class='col-6 mb-2'>"; // Decreased column size to col-4
                        echo "<ul class='list-unstyled'>";
                        echo "<li><a href='index.php?veh_no={$veh_numbers[$i]}' class='btn btn-link'>{$veh_numbers[$i]}</a></li>";
                        echo "</ul>";
                        echo "</div>";
                    }
                    
                    echo "</div>"; // Close the row for search results
                    echo "</div>"; // Close the col-12
                } else {
                    echo "<div class='col-12 alert alert-warning'>No vehicles found.</div>";
                }
            }
        }

        // Display vehicle details
        if (isset($_GET['veh_no'])) {
            echo "<div class='col-12 mt-4'>";
            require_once 'vehicle_details.php';
            echo "</div>";
        }
        ?>
    </div>

    <div class="row mt-4">
        <div class="col-md-12 text-right">
            <a href="index.php?logout=true" class="btn btn-danger ml-2">Logout</a> <!-- Logout Button -->
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="script.js"></script>
</body>
</html>
