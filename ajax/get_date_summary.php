<?php
require_once __DIR__ . '/../config/session.php';
require_admin();
header('Content-Type: application/json');

$selDate = $_GET['date'] ?? date('Y-m-d');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $selDate)) {
    $selDate = date('Y-m-d');
}

// Overall total / count / avg for the selected date (based on exit time)
$stmt = mysqli_prepare($conn, "
    SELECT
        IFNULL(SUM(parking_charge),0) AS total,
        COUNT(*) AS vehicles,
        IFNULL(AVG(parking_charge),0) AS avg_charge
    FROM parking_detail
    WHERE DATE(vehicle_exit_time) = ?
");
mysqli_stmt_bind_param($stmt, 's', $selDate);
mysqli_stmt_execute($stmt);
$summary = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

// Vehicles that entered on this date (may not have exited yet)
$stmt2 = mysqli_prepare($conn, "SELECT COUNT(*) AS c FROM parking_detail WHERE DATE(vehicle_enter_time) = ?");
mysqli_stmt_bind_param($stmt2, 's', $selDate);
mysqli_stmt_execute($stmt2);
$entries = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt2))['c'];

// Revenue by floor for the selected date
$stmt3 = mysqli_prepare($conn, "
    SELECT f.floor_name, IFNULL(SUM(pd.parking_charge),0) AS revenue, COUNT(pd.p_id) AS vehicles
    FROM floor_details f
    LEFT JOIN parking_detail pd ON f.floor_id = pd.floor_id AND DATE(pd.vehicle_exit_time) = ?
    GROUP BY f.floor_id
    ORDER BY revenue DESC
");
mysqli_stmt_bind_param($stmt3, 's', $selDate);
mysqli_stmt_execute($stmt3);
$byFloor = mysqli_fetch_all(mysqli_stmt_get_result($stmt3), MYSQLI_ASSOC);

// Revenue by vehicle type for the selected date
$stmt4 = mysqli_prepare($conn, "
    SELECT vehicle_type, IFNULL(SUM(parking_charge),0) AS revenue, COUNT(*) AS vehicles
    FROM parking_detail
    WHERE DATE(vehicle_exit_time) = ?
    GROUP BY vehicle_type
    ORDER BY revenue DESC
");
mysqli_stmt_bind_param($stmt4, 's', $selDate);
mysqli_stmt_execute($stmt4);
$byType = mysqli_fetch_all(mysqli_stmt_get_result($stmt4), MYSQLI_ASSOC);

echo json_encode([
    'ok' => true,
    'date' => $selDate,
    'total_revenue' => (float)$summary['total'],
    'vehicles_exited' => (int)$summary['vehicles'],
    'vehicles_entered' => (int)$entries,
    'avg_charge' => (float)$summary['avg_charge'],
    'by_floor' => $byFloor,
    'by_type' => $byType,
]);
