<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Vehicle</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <?php 
    require_once 'db_config.php';

    if (isset($_GET['veh_no'])) {
        $veh_no = mysqli_real_escape_string($conn, $_GET['veh_no']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Get form data
            $company = mysqli_real_escape_string($conn, $_POST['company']);
            $customerName = mysqli_real_escape_string($conn, $_POST['customerName']);
            $model = mysqli_real_escape_string($conn, $_POST['model']);
            $chassisNo = mysqli_real_escape_string($conn, $_POST['chassisNo']);
            $make = mysqli_real_escape_string($conn, $_POST['make']);
            $confLevel1 = mysqli_real_escape_string($conn, $_POST['confLevel1']);
            $mobLevel1 = mysqli_real_escape_string($conn, $_POST['mobLevel1']);
            $confLevel2 = mysqli_real_escape_string($conn, $_POST['confLevel2']);
            $mobLevel2 = mysqli_real_escape_string($conn, $_POST['mobLevel2']);
            $agrNo = mysqli_real_escape_string($conn, $_POST['agrNo']);
            $branch = mysqli_real_escape_string($conn, $_POST['branch']);
            $engNo = mysqli_real_escape_string($conn, $_POST['engNo']);
            $bkt = mysqli_real_escape_string($conn, $_POST['bkt']);
            $emi = mysqli_real_escape_string($conn, $_POST['emi']);
            $pos = mysqli_real_escape_string($conn, $_POST['pos']);
            $legal = mysqli_real_escape_string($conn, $_POST['legal']);

            // Update query
            $sql = "UPDATE vehicles SET 
                        COMPANY='$company',
                        CustomerName='$customerName',
                        Model='$model',
                        ChassisNo='$chassisNo',
                        Make='$make',
                        ConfLevel1='$confLevel1',
                        MobLevel1='$mobLevel1',
                        ConfLevel2='$confLevel2',
                        MobLevel2='$mobLevel2',
                        AgrNo='$agrNo',
                        BRANCH='$branch',
                        ENGNO='$engNo',
                        BKT='$bkt',
                        EMI='$emi',
                        POS='$pos',
                        LEGAL='$legal'
                    WHERE veh_no='$veh_no'";

            if ($conn->query($sql) === TRUE) {
                echo "<div class='alert alert-success'>Vehicle details updated successfully.</div>";
            } else {
                echo "<div class='alert alert-danger'>Error updating record: " . $conn->error . "</div>";
            }
        } else {
            $sql = "SELECT * FROM vehicles WHERE veh_no = '$veh_no'";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                ?>
                <h2>Edit Vehicle Details</h2>
                <form method="post" action="" class="mt-4">
                    <div class="form-group">
                        <label for="company">Company</label>
                        <input type="text" class="form-control" id="company" name="company" value="<?php echo $row['COMPANY']; ?>">
                    </div>
                    <div class="form-group">
                        <label for="customerName">Customer Name</label>
                        <input type="text" class="form-control" id="customerName" name="customerName" value="<?php echo $row['CustomerName']; ?>">
                    </div>
                    <div class="form-group">
                        <label for="model">Model</label>
                        <input type="text" class="form-control" id="model" name="model" value="<?php echo $row['Model']; ?>">
                    </div>
                    <div class="form-group">
                        <label for="chassisNo">Chassis No</label>
                        <input type="text" class="form-control" id="chassisNo" name="chassisNo" value="<?php echo $row['ChassisNo']; ?>">
                    </div>
                    <div class="form-group">
                        <label for="make">Make</label>
                        <input type="text" class="form-control" id="make" name="make" value="<?php echo $row['Make']; ?>">
                    </div>
                    <div class="form-group">
                        <label for="confLevel1">Conf Level 1</label>
                        <input type="text" class="form-control" id="confLevel1" name="confLevel1" value="<?php echo $row['ConfLevel1']; ?>">
                    </div>
                    <div class="form-group">
                        <label for="mobLevel1">Mob Level 1</label>
                        <input type="text" class="form-control" id="mobLevel1" name="mobLevel1" value="<?php echo $row['MobLevel1']; ?>">
                    </div>
                    <div class="form-group">
                        <label for="confLevel2">Conf Level 2</label>
                        <input type="text" class="form-control" id="confLevel2" name="confLevel2" value="<?php echo $row['ConfLevel2']; ?>">
                    </div>
                    <div class="form-group">
                        <label for="mobLevel2">Mob Level 2</label>
                        <input type="text" class="form-control" id="mobLevel2" name="mobLevel2" value="<?php echo $row['MobLevel2']; ?>">
                    </div>
                    <div class="form-group">
                        <label for="agrNo">Agreement No</label>
                        <input type="text" class="form-control" id="agrNo" name="agrNo" value="<?php echo $row['AgrNo']; ?>">
                    </div>
                    <div class="form-group">
                        <label for="branch">Branch</label>
                        <input type="text" class="form-control" id="branch" name="branch" value="<?php echo $row['BRANCH']; ?>">
                    </div>
                    <div class="form-group">
                        <label for="engNo">Engine No</label>
                        <input type="text" class="form-control" id="engNo" name="engNo" value="<?php echo $row['ENGNO']; ?>">
                    </div>
                    <div class="form-group">
                        <label for="bkt">BKT</label>
                        <input type="text" class="form-control" id="bkt" name="bkt" value="<?php echo $row['BKT']; ?>">
                    </div>
                    <div class="form-group">
                        <label for="emi">EMI</label>
                        <input type="text" class="form-control" id="emi" name="emi" value="<?php echo $row['EMI']; ?>">
                    </div>
                    <div class="form-group">
                        <label for="pos">POS</label>
                        <input type="text" class="form-control" id="pos" name="pos" value="<?php echo $row['POS']; ?>">
                    </div>
                    <div class="form-group">
                        <label for="legal">Legal</label>
                        <input type="text" class="form-control" id="legal" name="legal" value="<?php echo $row['LEGAL']; ?>">
                    </div>
                    <button type="submit" class="btn btn-primary">Update</button>
                </form>
                <?php
            } else {
                echo "<div class='alert alert-warning'>Vehicle not found.</div>";
            }
        }
    }
    ?>
</div>
</body>
</html>
