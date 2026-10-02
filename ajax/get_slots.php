<?php
require_once __DIR__ . '/../config/session.php';
require_login();
header('Content-Type: application/json');

$floor_id = (int)($_GET['floor_id'] ?? 0);
if (!$floor_id) { echo json_encode(['ok'=>false,'msg'=>'floor_id required']); exit; }

$fstmt = mysqli_prepare($conn, "SELECT floor_id, floor_name, capacity, floor_vehicle_type, floor_status FROM floor_details WHERE floor_id=?");
mysqli_stmt_bind_param($fstmt, 'i', $floor_id);
mysqli_stmt_execute($fstmt);
$floor = mysqli_fetch_assoc(mysqli_stmt_get_result($fstmt));
if (!$floor) { echo json_encode(['ok'=>false,'msg'=>'Floor not found']); exit; }

// currently parked vehicles on this floor (no exit time yet)
$ostmt = mysqli_prepare($conn, "SELECT slot_no, vehicle_no, vehicle_type, vehicle_enter_time
                                 FROM parking_detail WHERE floor_id=? AND vehicle_exit_time IS NULL");
mysqli_stmt_bind_param($ostmt, 'i', $floor_id);
mysqli_stmt_execute($ostmt);
$ores = mysqli_stmt_get_result($ostmt);
$occupied = [];
while ($r = mysqli_fetch_assoc($ores)) {
    $occupied[$r['slot_no']] = $r;
}

$slots = [];
for ($i = 1; $i <= (int)$floor['capacity']; $i++) {
    $slot_no = $floor['floor_id'] . '-' . str_pad($i, 3, '0', STR_PAD_LEFT);
    if (isset($occupied[$slot_no])) {
        $slots[] = [
            'slot_no'  => $slot_no,
            'status'   => 'occupied',
            'vehicle_no' => $occupied[$slot_no]['vehicle_no'],
        ];
    } else {
        $slots[] = ['slot_no' => $slot_no, 'status' => 'available'];
    }
}

echo json_encode([
    'ok' => true,
    'floor' => $floor,
    'slots' => $slots,
    'summary' => [
        'total' => count($slots),
        'occupied' => count($occupied),
        'available' => count($slots) - count($occupied),
    ],
    'emergency_mode' => is_emergency_mode($conn),
    'can_entry' => can_do_entry($conn),
    'can_exit' => can_do_exit($conn),
]);
