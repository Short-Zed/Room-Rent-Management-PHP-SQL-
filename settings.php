<?php
session_start();
include 'db_connect.php';
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
}

$error = $success = "";
if(isset($_POST['update'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // Fetch current user data
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    try {
        if(!empty($username) && $username != $user['username']) {
            $stmt = $conn->prepare("UPDATE users SET username = ? WHERE id = ?");
            $stmt->execute([$username, $_SESSION['user_id']]);
            $success = "Username updated successfully!";
        }
        
        if(!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashed_password, $_SESSION['user_id']]);
            $success .= " Password updated successfully!";
        }
    } catch(PDOException $e) {
        $error = "Username already exists!";
    }
}

// Handle account deletion
if(isset($_POST['delete_account'])) {
    // Delete all rent records for the user
    $stmt = $conn->prepare("DELETE FROM rent_records WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    
    // Delete the user account
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    
    // Destroy session and redirect to login
    session_destroy();
    header("Location: login.php");
    exit();
}

// Fetch current username
$stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$current_user = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Settings</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, sans-serif; }
        body { background-color: #f0f2f5; color: #333; line-height: 1.6; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .section { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px; }
        h2 { margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 16px; }
        .btn { background-color: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; width: auto; }
        .btn:hover { background-color: #0056b3; }
        .back-btn { background-color: #28a745; width: auto; padding: 10px 20px; float: left; color: #fff; border: none; outline: none; border-radius: 5px; }
        .back-btn:hover { background-color: #218838; cursor: pointer; }
        .delete-btn { background-color: #dc3545; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; width: auto; margin-top: 10px; }
        .delete-btn:hover { background-color: #c82333; }
        .error { color: red; text-align: center; margin-bottom: 15px; }
        .success { color: green; text-align: center; margin-bottom: 15px; }
        .clearfix::after { content: ""; clear: both; display: table; }

        @media (max-width: 768px) {
            .container { padding: 10px; }
            .section { padding: 15px; }
            .form-group { margin-bottom: 10px; }
            .back-btn, .delete-btn { float: none; width: 100%; margin-bottom: 10px; }
        }
        @media (max-width: 480px) {
            .btn, .delete-btn { padding: 8px 15px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="section clearfix">
            <a href="dashboard.php"><button class="back-btn">Back to Dashboard</button></a>
        </div>

        <div class="section">
            <h2>User Settings</h2>
            <?php 
            if(!empty($error)) echo "<p class='error'>$error</p>";
            if(!empty($success)) echo "<p class='success'>$success</p>";
            ?>
            <form method="POST">
                <div class="form-group">
                    <label>Current Username: <?php echo $current_user['username']; ?></label>
                </div>
                <div class="form-group">
                    <label>New Username (leave blank to keep current)</label>
                    <input type="text" name="username">
                </div>
                <div class="form-group">
                    <label>New Password (leave blank to keep current)</label>
                    <input type="password" name="password">
                </div>
                <button type="submit" name="update" class="btn">Update Settings</button>
            </form>
            <form method="POST" onsubmit="return confirmDelete();">
                <button type="submit" name="delete_account" class="delete-btn">Delete Account</button>
            </form>
        </div>
    </div>

    <script>
    function confirmDelete() {
        return confirm("Are you sure you want to delete your account? This will also delete all your rent records and cannot be undone.");
    }
    </script>
</body>
</html>