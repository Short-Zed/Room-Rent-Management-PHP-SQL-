<?php
session_start();
include 'db_connect.php';
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
}

$error = "";
if(isset($_POST['update'])) {
    $id = $_POST['id'];
    
    // Check if required fields are empty
    if(empty($_POST['prev_unit']) || empty($_POST['today_unit']) || empty($_POST['unit_price']) || empty($_POST['room_rent'])) {
        $error = "All required fields (Previous Unit, Today Unit, Unit Price, Room Rent) must be filled!";
    } else {
        $prev_unit = $_POST['prev_unit'];
        $today_unit = $_POST['today_unit'];
        $unit_price = $_POST['unit_price'];
        $room_rent = $_POST['room_rent'];
        $additional = $_POST['additional'] ?? 0;
        $subtraction = $_POST['subtraction'] ?? 0;
        
        // Validate today_unit > prev_unit
        if($today_unit <= $prev_unit) {
            $error = "Today Unit must be greater than Previous Unit!";
        } else {
            $total_units = $today_unit - $prev_unit;
            $electricity_bill = $total_units * $unit_price;
            $total_rent = $electricity_bill + $room_rent + $additional - $subtraction;
            
            $stmt = $conn->prepare("UPDATE rent_records SET previous_unit=?, today_unit=?, unit_price=?, room_rent=?, additional=?, subtraction=?, total_rent=?, record_date=CURRENT_TIMESTAMP WHERE id=? AND user_id=?");
            $stmt->execute([$prev_unit, $today_unit, $unit_price, $room_rent, $additional, $subtraction, $total_rent, $id, $_SESSION['user_id']]);
            header("Location: dashboard.php");
            exit();
        }
    }
}

$id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM rent_records WHERE id=? AND user_id=?");
$stmt->execute([$id, $_SESSION['user_id']]);
$record = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Rent Record</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, sans-serif; }
        body { background-color: #f0f2f5; color: #333; line-height: 1.6; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .section { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px; }
        h2 { margin-bottom: 20px; text-align: center; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 16px; }
        .btn { background-color: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; width: 100%; }
        .btn:hover { background-color: #0056b3; }
        .back-btn { background-color: #28a745; width: auto; padding: 10px 20px; margin-bottom: 20px; color:#fff; border:none; outline:none; border-radius:5px; }
        .back-btn:hover { background-color: #218838; }
        .error { color: red; text-align: center; margin-bottom: 15px; background-color: #ffe6e6; padding: 10px; border-radius: 4px; }
        .optional-note { font-style: italic; color: #666; font-size: 0.9em; }

        @media (max-width: 480px) {
            .container { padding: 10px; }
            .section { padding: 15px; }
            .btn, .back-btn { padding: 8px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="section">
            <a href="dashboard.php"><button class="back-btn">Back to Dashboard</button></a>
            <h2>Edit Rent Record</h2>
            <?php if(!empty($error)) echo "<p class='error'>$error</p>"; ?>
            <form method="POST">
                <input type="hidden" name="id" value="<?php echo $record['id']; ?>">
                <div class="form-group">
                    <label>Previous Unit</label>
                    <input type="number" name="prev_unit" value="<?php echo $record['previous_unit']; ?>" required>
                </div>
                <div class="form-group">
                    <label>Today Unit</label>
                    <input type="number" name="today_unit" value="<?php echo $record['today_unit']; ?>" required>
                </div>
                <div class="form-group">
                    <label>Unit Price</label>
                    <select name="unit_price" required>
                        <option value="3" <?php if($record['unit_price'] == 3) echo 'selected'; ?>>₹3</option>
                        <option value="4" <?php if($record['unit_price'] == 4) echo 'selected'; ?>>₹4</option>
                        <option value="5" <?php if($record['unit_price'] == 5) echo 'selected'; ?>>₹5</option>
                        <option value="6" <?php if($record['unit_price'] == 6) echo 'selected'; ?>>₹6</option>
                        <option value="7" <?php if($record['unit_price'] == 7) echo 'selected'; ?>>₹7</option>
                        <option value="8" <?php if($record['unit_price'] == 8) echo 'selected'; ?>>₹8</option>
                        <option value="9" <?php if($record['unit_price'] == 9) echo 'selected'; ?>>₹9</option>
                        <option value="10" <?php if($record['unit_price'] == 10) echo 'selected'; ?>>₹10</option>
                        <option value="11" <?php if($record['unit_price'] == 11) echo 'selected'; ?>>₹11</option>
                        <option value="12" <?php if($record['unit_price'] == 12) echo 'selected'; ?>>₹12</option>
                        <option value="13" <?php if($record['unit_price'] == 13) echo 'selected'; ?>>₹13</option>
                        <option value="14" <?php if($record['unit_price'] == 14) echo 'selected'; ?>>₹14</option>
                        <option value="15" <?php if($record['unit_price'] == 15) echo 'selected'; ?>>₹15</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Room Rent</label>
                    <input type="number" name="room_rent" value="<?php echo $record['room_rent']; ?>" required>
                </div>
                <div class="form-group">
                    <label>Additional (Optional)</label>
                    <input type="number" name="additional" value="<?php echo $record['additional']; ?>">
                    <span class="optional-note">Defaults to 0 if not entered</span>
                </div>
                <div class="form-group">
                    <label>Subtraction (Optional)</label>
                    <input type="number" name="subtraction" value="<?php echo $record['subtraction']; ?>">
                    <span class="optional-note">Defaults to 0 if not entered</span>
                </div>
                <button type="submit" name="update" class="btn">Update Record</button>
            </form>
        </div>
    </div>
</body>
</html>