<?php
require_once 'db_config.php';
require 'phpspreadsheet/vendor/autoload.php'; // Autoload PHPExcel

use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $file = $data['file'];

    $filePath = 'uploads/' . $file;

    if (file_exists($filePath)) {
        $fileType = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $veh_no = [];

        if ($fileType == 'csv') {
            // Read the CSV file
            if (($handle = fopen($filePath, 'r')) !== FALSE) {
                while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                    // Assuming vehicle number is in the first column
                    $veh_no[] = $data[0];
                }
                fclose($handle);
            }
        } elseif (in_array($fileType, ['xls', 'xlsx'])) {
            // Read the Excel file
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            foreach ($worksheet->getRowIterator() as $row) {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);
                $cells = [];
                foreach ($cellIterator as $cell) {
                    $cells[] = $cell->getValue();
                }
                // Assuming vehicle number is in the first column
                $veh_no[] = $cells[0];
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Unsupported file type']);
            exit;
        }

        // Connect to the database
        //$conn = new mysqli('localhost', 'username', 'password', 'database');
        /*if ($conn->connect_error) {
            die('Connection failed: ' . $conn->connect_error);
        }*/

        // Delete rows from the database in chunks
        if (!empty($veh_no)) {
            $chunkSize = 1000; // Set chunk size to avoid too many placeholders
            $chunks = array_chunk($veh_no, $chunkSize);
            foreach ($chunks as $chunk) {
                $placeholders = rtrim(str_repeat('?,', count($chunk)), ',');
                $stmt = $conn->prepare("DELETE FROM vehicles WHERE veh_no IN ($placeholders)");
                $types = str_repeat('s', count($chunk));
                $stmt->bind_param($types, ...$chunk);
                $stmt->execute();
                $stmt->close();
            }
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
