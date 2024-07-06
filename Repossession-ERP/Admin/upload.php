<?php
require_once 'db_config.php';

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

    // Check file size
    if ($_FILES["csv_file"]["size"] > 5000000000) {
        echo "Sorry, your file is too large.";
        $uploadOk = 0;
    }

    // Allow certain file formats
    if ($csvFileType != "csv") {
        echo "Sorry, only CSV files are allowed.";
        $uploadOk = 0;
    }

    if ($uploadOk == 1) {
        if (move_uploaded_file($_FILES["csv_file"]["tmp_name"], $target_file)) {
            echo "The file " . basename($_FILES["csv_file"]["name"]) . " has been uploaded.";

            // Import CSV data into database
            $file = fopen($target_file, "r");
            $header = fgetcsv($file); // Get the header row

            // Prepare an array to store data for insertion
            $data_array = array();

            while (($row = fgetcsv($file)) !== false) {
                // Prepare the data based on the header
                $data = array();
                foreach ($header as $key => $value) {
                    $data[$value] = $row[$key];
                }

                // Add the data to the array
                $data_array[] = $data;
            }

            fclose($file);
            $records_added = 0;
            $records_updated = 0;

            // Loop through the data array
            foreach ($data_array as $row) {
                $veh_no = $row["veh_no"];

                // Skip the entry if it's "Newvehiclenotregister"
                if ($veh_no === "Newvehiclenotregister") {
                    continue;
                }

                // Prepare an array of values for the INSERT/UPDATE query
                $values = array();
                foreach ($row as $key => $value) {
                    // Escape values to prevent SQL injection
                    $values[$key] = $conn->real_escape_string($value);
                }

                // Check if vehicle exists in the database
                $sql_check = "SELECT * FROM vehicles WHERE veh_no = '$veh_no'";
                $result = $conn->query($sql_check);

                if ($result->num_rows > 0) {
                    // Vehicle exists - update the data
                    $sql = "UPDATE vehicles SET ";
                    $update_fields = array();
                    foreach ($values as $key => $value) {
                        $update_fields[] = "$key = '$value'";
                    }
                    $sql .= implode(", ", $update_fields);
                    $sql .= " WHERE veh_no = '$veh_no'";
                    if ($conn->query($sql) === TRUE) {
                        $records_updated++;
                    } else {
                        echo "Error updating vehicle: " . $sql . "<br>" . $conn->error;
                    }
                } else {
                    // Vehicle doesn't exist - insert new record
                    $columns = implode(",", array_keys($values));
                    $escaped_values = array_map('addslashes', array_values($values)); // Escape for quotes
                    $values_string = "'" . implode("','", $escaped_values) . "'"; // Add quotes

                    try {
                        $sql = "INSERT INTO vehicles ($columns) VALUES ($values_string)";
                        if ($conn->query($sql) === TRUE) {
                            $records_added++;
                        } else {
                            echo "Error inserting new vehicle: " . $sql . "<br>" . $conn->error;
                        }
                    } catch (mysqli_sql_exception $e) {
                        // Skip the duplicate entry
                        if ($e->getCode() == 1062) { // 1062 is the code for duplicate entry
                            continue;
                        } else {
                            echo "Error inserting new vehicle: " . $sql . "<br>" . $e->getMessage();
                        }
                    }
                }
            }
            echo "Uploaded Successfully. New Records Added: $records_added , Records Updated: $records_updated ";
            echo "<hr>";
            echo "<a href='index.php' class='btn btn-secondary'>Back</a>";
            //header("Location: index.php");
            exit();
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }
}
?>
