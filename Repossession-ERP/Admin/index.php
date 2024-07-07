<?php
// Optional: implement session timeout logic
// Set the session timeout to 30 minutes (1800 seconds)
ini_set('session.gc_maxlifetime', 1800);

session_start(); // Start or resume session

// Regenerate the session ID to prevent session fixation attacks
if (!isset($_SESSION['CREATED'])) {
    $_SESSION['CREATED'] = time();
} elseif (time() - $_SESSION['CREATED'] > 1800) {
    // Session started more than 30 minutes ago
    session_regenerate_id(true);    // Change session ID for the current session and invalidate old session ID
    $_SESSION['CREATED'] = time();  // Update creation time
}

// Check for session timeout
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 1800)) {
    // Last request was more than 30 minutes ago
    session_unset();     // Unset $_SESSION variables
    session_destroy();   // Destroy the session data
    header("Location: login.php"); // Redirect to login page
    exit;
}

$_SESSION['LAST_ACTIVITY'] = time(); // Update last activity time
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
        /* Custom CSS for scrollable uploaded files */
        #uploadedFiles {
            max-height: 300px; /* Set max height for scrollable area */
            overflow-y: auto; /* Enable vertical scroll */
        }
        .file-actions {
            margin-top: 10px; /* Added margin for spacing */
        }
    </style>
</head>
<body>
<div class="container">
    <h1 class="mb-4 text-center">Your Title</h1>
    
    <div class="row">
        <div class="col-md-12 text-right">
            <a href="logout.php" class="btn btn-danger ml-2">Logout</a> <!-- Logout Button -->
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-4">
            <h2>Upload CSV/XLSX/XLS File</h2>
            <form method="post" enctype="multipart/form-data" action="upload.php">
                <div class="form-group">
                    <label for="csv_file">Select file to upload:</label>
                    <input type="file" name="csv_file" id="csv_file" class="form-control-file" accept=".csv,.xlsx,.xls">
                </div>
                <input type="submit" value="Upload File" name="submit" class="btn btn-primary">
            </form>
        </div>

        <div class="col-md-6 mb-4">
            <h2>Search for Vehicle</h2>
            <form method="post" action="search.php">
                <div class="form-group">
                    <label for="search_veh_full">Enter Full Vehicle No:</label>
                    <input type="text" name="search_veh_full" id="search_veh_full" class="form-control">
                </div>
                <input type="submit" value="Search" name="submit" class="btn btn-primary">
            </form>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <h2>User Management</h2>
            <div class="list-group">
                <a href="view_users.php" class="list-group-item list-group-item-action">View Users</a>
                <a href="add_user.php" class="list-group-item list-group-item-action">Add User</a> <!-- Link to Add User Page -->
            </div>
        </div>

        <div class="col-md-6">
            <h2>View Uploaded Files</h2>
            <button id="viewFilesButton" class="btn btn-secondary">View Files</button>
            <div id="uploadedFiles" class="mt-3" style="display:none;">
                <?php
                $directory = 'uploads/'; // Your directory containing the uploaded files
                $files = array_diff(scandir($directory), array('..', '.')); // Get all files except . and ..

                if (!empty($files)) {
                    echo "<ul class='list-unstyled'>";
                    $sr_no = 1;
                    foreach ($files as $file) {
                        echo "<li class='mb-4'> <!-- Increased bottom margin for more space between list items -->
                                <div class='row'>
                                    <div class='col-md-5'>
                                        $sr_no. $file 
                                        <a href='$directory$file' target='_blank' class='btn btn-link'>View</a>
                                        <button class='btn btn-danger btn-sm delete-file' data-file='$file'>Delete File</button>
                                    </div>
                                    <div class='col-md-5 offset-md-2 file-actions'> <!-- Added offset for more space between columns -->
                                        <button class='btn btn-danger btn-sm delete-db' data-file='$file'>Delete from Database</button>
                                    </div>
                                </div>
                              </li>";
                        $sr_no++;
                    }
                    echo "</ul>";
                } else {
                    echo '<div class="alert alert-warning">No files uploaded.</div>';
                }
                ?>
            </div>
        </div>
    </div>

    <?php 
    // Include search results if available
    if (isset($_GET['search_results'])) {
        if (isset($_SESSION['search_results'])) {
            $veh_numbers = $_SESSION['search_results']; // Retrieve the array from the session

            if (!empty($veh_numbers)) { // Check if the array is not empty
                echo "<div class='mt-4'>";
                echo "<h2>Search Results:</h2>";
                echo "<ul class='list-unstyled'>";
                foreach ($veh_numbers as $veh_no) { 
                    echo "<li><a href='index.php?veh_no=$veh_no' class='btn btn-link'>$veh_no</a></li>";
                }
                echo "</ul>";
                echo "</div>";
            } else {
                echo "<div class='mt-4 alert alert-warning'>No vehicles found.</div>"; 
            }
            unset($_SESSION['search_results']); 
        }
    }

    // Include vehicle details if available
    if (isset($_GET['veh_no'])) {
        require_once 'vehicle_details.php'; 
    }
    ?>

</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
document.getElementById('viewFilesButton').addEventListener('click', function() {
    var uploadedFilesDiv = document.getElementById('uploadedFiles');
    if (uploadedFilesDiv.style.display === 'none' || uploadedFilesDiv.style.display === '') {
        uploadedFilesDiv.style.display = 'block';
    } else {
        uploadedFilesDiv.style.display = 'none';
    }
});

document.querySelectorAll('.delete-db').forEach(button => {
    button.addEventListener('click', function() {
        const fileName = this.getAttribute('data-file');
        if (confirm(`Are you sure you want to delete database entries for ${fileName}?`)) {
            fetch('delete_db.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ file: fileName })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Database entries deleted successfully');
                    location.reload(); // Refresh the page to update the list
                } else {
                    alert('Error deleting database entries');
                }
            });
        }
    });
});

document.querySelectorAll('.delete-file').forEach(button => {
    button.addEventListener('click', function() {
        const fileName = this.getAttribute('data-file');
        if (confirm(`Are you sure you want to delete the file ${fileName}?`)) {
            fetch('delete_file.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ file: fileName })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('File deleted successfully');
                    location.reload(); // Refresh the page to update the list
                } else {
                    alert('Error deleting file');
                }
            });
        }
    });
});
</script>

</body>
</html>
