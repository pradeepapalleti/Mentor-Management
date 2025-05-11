<?php
session_start();
include 'db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'mentee') {
    header("Location: login.php");
    exit;
}
$mentee_id = $_SESSION['user_id'];
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $activity = $_POST['activity'];
    $progress = $_POST['progress'];
    $date = date('Y-m-d');
    $conn->query("INSERT INTO activities (mentee_id, activity, progress, date) VALUES ($mentee_id, '$activity', '$progress', '$date')");
}
header("Location: mentee_dashboard.php");
exit;
?>