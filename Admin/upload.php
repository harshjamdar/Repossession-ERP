<?php
// Ensure error reporting is enabled for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('memory_limit', '1024M');
ini_set('max_execution_time', '300'); // Set to 5 minutes
ini_set('post_max_size', '1G');
//ini_set('upload_max_filesize', '10M');

// Include necessary files and libraries
require_once 'db_config.php'; // Assuming this file contains your database connection settings
require_once 'phpspreadsheet/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

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
    $csvFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if file already exists
    if (file_exists($target_file)) {
        echo "Sorry, file already exists.";
        $uploadOk = 0;
    }

    // Check file size (example limit set to 5MB)
    if ($_FILES["csv_file"]["size"] > 500000000) {
        echo "Sorry, your file is too large.";
        $uploadOk = 0;
    }

    // Allow only certain file formats (CSV, XLSX, XLS)
    if ($csvFileType != "csv" && $csvFileType != "xlsx" && $csvFileType != "xls") {
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
                $spreadsheet = IOFactory::load($target_file);
            } catch (Exception $e) {
                echo "Error loading file: " . $e->getMessage();
                file_put_contents($logFile, "Error loading file: " . $e->getMessage() . "\n", FILE_APPEND);
                exit;
            }
            $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

            // Get the header row and remove it from the data
            $header = array_shift($sheetData);

            $records_added = 0;
            $records_updated = 0;

            // Prepare insert and update statements
            $insert_sql = "INSERT INTO vehicles (veh_no, COMPANY, CustomerName, Model, ChassisNo, Make, ConfLevel1, MobLevel1, ConfLevel2, MobLevel2, AgrNo, BRANCH, ENGNO, BKT, EMI, POS, LEGAL) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);

            $update_sql = "UPDATE vehicles SET COMPANY=?, CustomerName=?, Model=?, ChassisNo=?, Make=?, ConfLevel1=?, MobLevel1=?, ConfLevel2=?, MobLevel2=?, AgrNo=?, BRANCH=?, ENGNO=?, BKT=?, EMI=?, POS=?, LEGAL=? WHERE veh_no=?";
            $update_stmt = $conn->prepare($update_sql);

            foreach ($sheetData as $row) {
                $veh_no = $row['A'];
                if ($veh_no === "Newvehiclenotregister") {
                    continue;
                }
                if (empty($veh_no)) {
                    $errorCount++;
                    continue; // Skip processing this row
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
                        echo "Error inserting vehicle: " . $insert_stmt->error;
                        file_put_contents($logFile, "Error inserting vehicle: $veh_no - " . $insert_stmt->error . "\n", FILE_APPEND);
                    }
                }
            }
            echo "Records Added: $records_added<br>";
            echo "Records Updated: $records_updated<br>";
            echo "Rows with empty vehicle number: $errorCount<br>";
        } else {
            echo "Sorry, there was an error uploading your file.";
            file_put_contents($logFile, "Error uploading file at " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);
        }
    }
} else {
    echo "Invalid request.";
    file_put_contents($logFile, "Invalid request at " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);
}

// Close database connection
$conn->close();
?>
