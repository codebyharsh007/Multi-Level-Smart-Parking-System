<?php
require_once __DIR__ . '/../config/session.php';
require_login();
header('Content-Type: application/json');

$floor_id = (int)($_GET['floor_id'] ?? 0);
$slot_no  = trim($_GET['slot_no'] ?? '');

$stmt = mysqli_prepare($conn, "SELECT pd.*, f.floor_name,
        (SELECT staff_name FROM staff_details WHERE staff_id = pd.staff_id_entry) AS entry_staff
        FROM parking_detail pd
        JOIN floor_details f ON f.floor_id = pd.floor_id
        WHERE pd.floor_id=? AND pd.slot_no=? AND pd.vehicle_exit_time IS NULL
        ORDER BY pd.p_id DESC LIMIT 1");
mysqli_stmt_bind_param($stmt, 'is', $floor_id, $slot_no);
mysqli_stmt_execute($stmt);
$row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$row) { echo json_encode(['ok'=>false,'msg'=>'This slot is not occupied.']); exit; }

$entered = new DateTime($row['vehicle_enter_time']);
$now = new DateTime();
$diff = $entered->diff($now);
$row['elapsed'] = ($diff->days*24+$diff->h) . 'h ' . $diff->i . 'm';

echo json_encode(['ok'=>true, 'vehicle'=>$row]);
