<?php
// Ensure error reporting is enabled for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('memory_limit', '2048M'); // Increase memory limit further
ini_set('max_execution_time', '600'); // Increase execution time to 10 minutes
ini_set('post_max_size', '1G');
ini_set('upload_max_filesize', '100M'); // Increase upload file size limit

// Include necessary files and libraries
require_once 'db_config.php'; // Assuming this file contains your database connection settings
require_once 'phpspreadsheet/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader\Xls;

// Start session and implement session timeout logic
session_start();

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

// Initialize error count
$errorCount = 0;

// Create a log file to track errors and progress
$logFile = 'upload_log.txt';
file_put_contents($logFile, "Upload process started at " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);

// Process file upload if it's a POST request and a file is uploaded
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["csv_file"])) {
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["csv_file"]["name"]);
    $uploadOk = 1;
    $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if file already exists
    if (file_exists($target_file)) {
        echo "Sorry, file already exists.";
        $uploadOk = 0;
    }

    // Check file size (example limit set to 10MB)
    if ($_FILES["csv_file"]["size"] > 100000000) {
        echo "Sorry, your file is too large.";
        $uploadOk = 0;
    }

    // Allow only certain file formats (CSV, XLSX, XLS)
    if ($fileType != "csv" && $fileType != "xlsx" && $fileType != "xls") {
        echo "Sorry, only CSV, XLSX, and XLS files are allowed.";
        $uploadOk = 0;
    }

    // If file upload is valid, proceed with processing
    if ($uploadOk == 1) {
        if (move_uploaded_file($_FILES["csv_file"]["tmp_name"], $target_file)) {
            echo "The file " . basename($_FILES["csv_file"]["name"]) . " has been uploaded successfully.";
            file_put_contents($logFile, "File uploaded successfully at " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);

            // Load the spreadsheet file using PHPSpreadsheet
            try {
                if ($fileType == "csv") {
                    $reader = new Csv();
                } elseif ($fileType == "xlsx") {
                    $reader = new Xlsx();
                } else {
                    $reader = new Xls();
                }
                $spreadsheet = $reader->load($target_file);
            } catch (Exception $e) {
                echo "Error loading file: " . $e->getMessage();
                file_put_contents($logFile, "Error loading file: " . $e->getMessage() . "\n", FILE_APPEND);
                exit;
            }

            // Prepare insert and update statements
            $insert_sql = "INSERT INTO vehicles (veh_no, COMPANY, CustomerName, Model, ChassisNo, Make, ConfLevel1, MobLevel1, ConfLevel2, MobLevel2, AgrNo, BRANCH, ENGNO, BKT, EMI, POS, LEGAL) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);

            $update_sql = "UPDATE vehicles SET COMPANY=?, CustomerName=?, Model=?, ChassisNo=?, Make=?, ConfLevel1=?, MobLevel1=?, ConfLevel2=?, MobLevel2=?, AgrNo=?, BRANCH=?, ENGNO=?, BKT=?, EMI=?, POS=?, LEGAL=? WHERE veh_no=?";
            $update_stmt = $conn->prepare($update_sql);

            // Read and process the file in chunks
            $chunkSize = 100; // Number of rows to process at a time
            $worksheet = $spreadsheet->getActiveSheet();
            $highestRow = $worksheet->getHighestRow();
            $records_added = 0;
            $records_updated = 0;
            $total_records = 0;

            // Output JavaScript to update progress on the client side
            echo '<script>';
            echo 'console.log("Processing data...");';
            echo 'var totalRows = ' . $highestRow . ';';
            echo 'var processedRows = 0;';
            echo 'function updateProgress() {';
            echo '  var percentage = Math.round((processedRows / totalRows) * 100);';
            echo '  document.getElementById("progress").innerHTML = "Processing... " + percentage + "%";';
            echo '}';
            echo '</script>';
            echo '<div id="progress"></div>';

            for ($startRow = 2; $startRow <= $highestRow; $startRow += $chunkSize) {
                $endRow = min($startRow + $chunkSize - 1, $highestRow);
                $rows = $worksheet->rangeToArray("A$startRow:Q$endRow", NULL, TRUE, TRUE, TRUE);

                foreach ($rows as $row) {
                    $total_records++;
                    try {
                        // Capitalize the veh_no before processing
                        // Ensure veh_no is not null or empty before using strtoupper
                        if (!empty($row['A']) && is_string($row['A'])) {
                            $veh_no = strtoupper($row['A']);
                        } else {
                            $veh_no = null;
                        }
                        
                        // Skip processing if veh_no is null or matches specific strings
                        if ($veh_no === null || $veh_no === "Newvehiclenotregister" || $veh_no === "NEWVEHICLENOTREGISTER") {
                            $errorCount++;
                            continue;
                        }
                        
                        $company = isset($row['B']) ? $row['B'] : null;
                        $customer_name = isset($row['C']) ? $row['C'] : null;
                        $model = isset($row['D']) ? $row['D'] : null;
                        $chassis_no = isset($row['E']) ? $row['E'] : null;
                        $make = isset($row['F']) ? $row['F'] : null;
                        $conf_level1 = isset($row['G']) ? $row['G'] : null;
                        $mob_level1 = isset($row['H']) ? $row['H'] : null;
                        $conf_level2 = isset($row['I']) ? $row['I'] : null;
                        $mob_level2 = isset($row['J']) ? $row['J'] : null;
                        $agr_no = isset($row['K']) ? $row['K'] : null;
                        $branch = isset($row['L']) ? $row['L'] : null;
                        $engno = isset($row['M']) ? $row['M'] : null;
                        $bkt = isset($row['N']) ? $row['N'] : null;
                        $emi = isset($row['O']) ? $row['O'] : null;
                        $pos = isset($row['P']) ? $row['P'] : null;
                        $legal = isset($row['Q']) ? $row['Q'] : null;

                        // Check if vehicle exists in the database
                        $sql_check = "SELECT * FROM vehicles WHERE veh_no = ?";
                        $check_stmt = $conn->prepare($sql_check);
                        $check_stmt->bind_param("s", $veh_no);
                        $check_stmt->execute();
                        $result = $check_stmt->get_result();

                        if ($result->num_rows > 0) {
                            // Vehicle exists - update the data
                            $update_stmt->bind_param("sssssssssssssssss", $company, $customer_name, $model, $chassis_no, $make,
                                $conf_level1, $mob_level1, $conf_level2, $mob_level2,
                                $agr_no, $branch, $engno, $bkt, $emi, $pos, $legal, $veh_no);
                            if ($update_stmt->execute()) {
                                $records_updated++;
                                file_put_contents($logFile, "Record updated for vehicle no: $veh_no at " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);
                            } else {
                                echo "Error updating vehicle: " . $update_stmt->error;
                                file_put_contents($logFile, "Error updating vehicle: $veh_no - " . $update_stmt->error . "\n", FILE_APPEND);
                            }
                        } else {
                            // Vehicle doesn't exist - insert new record
                            $insert_stmt->bind_param("sssssssssssssssss",
                                $veh_no, $company, $customer_name, $model, $chassis_no, $make,
                                $conf_level1, $mob_level1, $conf_level2, $mob_level2,
                                $agr_no, $branch, $engno, $bkt, $emi, $pos, $legal);
                            if ($insert_stmt->execute()) {
                                $records_added++;
                                file_put_contents($logFile, "Record added for vehicle no: $veh_no at " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);
                            } else {
                                if ($conn->errno == 1062) {
                                    // Duplicate entry error
                                    echo "Duplicate entry for vehicle no: $veh_no. Skipping...";
                                    file_put_contents($logFile, "Duplicate entry for vehicle no: $veh_no - " . $insert_stmt->error . "\n", FILE_APPEND);
                                } else {
                                    echo "Error inserting vehicle: " . $insert_stmt->error;
                                    file_put_contents($logFile, "Error inserting vehicle: $veh_no - " . $insert_stmt->error . "\n", FILE_APPEND);
                                }
                            }
                        }

                        // Update progress
                        echo '<script>processedRows++; updateProgress();</script>';
                        ob_flush();
                        flush();

                    } catch (Exception $e) {
                        echo "Error processing vehicle: " . $e->getMessage();
                        file_put_contents($logFile, "Error processing vehicle: $veh_no - " . $e->getMessage() . "\n", FILE_APPEND);
                        $errorCount++;
                    }
                }
            }

            // Close prepared statements
            $insert_stmt->close();
            $update_stmt->close();

            // Display final results
            echo "<p>Total records processed: $total_records</p>";
            echo "<p>Total records added: $records_added</p>";
            echo "<p>Total records updated: $records_updated</p>";
            echo "<p>Total errors encountered: $errorCount</p>";
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }
} else {
    echo "No file uploaded or invalid request method.";
}

// Close database connection
$conn->close();
?>
