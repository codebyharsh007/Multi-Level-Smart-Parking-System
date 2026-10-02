<?php
require_once __DIR__ . '/../config/session.php';
require_login();
header('Content-Type: application/json');

$occRow = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM parking_detail WHERE vehicle_exit_time IS NULL"));
$todayRow = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM parking_detail WHERE DATE(vehicle_enter_time)=CURDATE()"));
$capRow = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(capacity),0) c FROM floor_details WHERE floor_status='Active'"));

$occupied = (int)$occRow['c'];
$capacity = (int)$capRow['c'];

echo json_encode([
    'ok' => true,
    'occupied' => $occupied,
    'available' => max(0, $capacity - $occupied),
    'entries_today' => (int)$todayRow['c'],
]);