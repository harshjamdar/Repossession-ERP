<?php


include 'session_check.php';

session_start();

// Serve the requested file
$directory = 'uploads/';
if (isset($_GET['file'])) {
    $file = basename($_GET['file']);
    $filePath = $directory . $file;

    // Check if the file exists
    if (file_exists($filePath)) {
        // Send headers for downloading the file
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
        header('Content-Length: ' . filesize($filePath));

        // Read the file and output it to the browser
        readfile($filePath);
        exit;
    } else {
        echo "File does not exist.";
    }
} else {
    echo "No file specified.";
}
?>