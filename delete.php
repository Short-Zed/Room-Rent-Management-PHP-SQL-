<?php
session_start();
include 'db_connect.php';
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
}

$id = $_GET['id'];
$stmt = $conn->prepare("DELETE FROM rent_records WHERE id=? AND user_id=?");
$stmt->execute([$id, $_SESSION['user_id']]);
header("Location: dashboard.php");
?>