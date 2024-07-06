<?php
require_once 'db_config.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $file = $data['file'];

    $filePath = 'uploads/' . $file;

    if (file_exists($filePath)) {
        // Read the CSV file and get the vehicle numbers
        $vehicleNumbers = [];
        if (($handle = fopen($filePath, 'r')) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                // Assuming vehicle number is in the first column
                $vehicleNumbers[] = $data[0];
            }
            fclose($handle);
        }

        // Connect to the database
        //$conn = new mysqli('localhost', 'username', 'password', 'database');
        /*if ($conn->connect_error) {
            die('Connection failed: ' . $conn->connect_error);
        }*/

        // Delete rows from the database
        if (!empty($vehicleNumbers)) {
            $placeholders = rtrim(str_repeat('?,', count($vehicleNumbers)), ',');
            $stmt = $conn->prepare("DELETE FROM vehicles WHERE veh_no IN ($placeholders)");
            $types = str_repeat('s', count($vehicleNumbers));
            $stmt->bind_param($types, ...$vehicleNumbers);
            $stmt->execute();
            $stmt->close();
        }

        $conn->close();

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'File not found']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
