<?php
require_once __DIR__ . '/../config/session.php';
require_login();
require_once __DIR__ . '/../mail_config.php';

date_default_timezone_set('Asia/Kolkata'); 

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['ok'=>false,'msg'=>'Invalid request']); exit; }

if (!can_do_entry($conn)) {
    echo json_encode(['ok'=>false,'msg'=>'You are not authorized to record a vehicle Entry right now. Ask Admin to enable Emergency Mode if this is urgent.']);
    exit;
}

$floor_id     = (int)($_POST['floor_id'] ?? 0);
$slot_no      = trim($_POST['slot_no'] ?? '');
$vehicle_no   = strtoupper(trim($_POST['vehicle_no'] ?? ''));
$vehicle_type = trim($_POST['vehicle_type'] ?? '');
$dl_no        = trim($_POST['dl_no'] ?? '');
$mobile_no    = trim($_POST['mobile_no'] ?? '');
$email        = trim($_POST['email'] ?? '');
$staff_id     = (int)$_SESSION['staff_id'];

$errors = [];
if (!$floor_id) $errors[] = 'Floor is required.';
if ($slot_no === '') $errors[] = 'Slot is required.';
if ($vehicle_no === '') $errors[] = 'Vehicle number is required.';
if (!in_array($vehicle_type, ['2 Wheeler','3 Wheeler','4 Wheeler'], true)) $errors[] = 'Valid vehicle type is required.';
// if ($dl_no === '') $errors[] = 'Driving licence number is required.';
// if (!preg_match('/^[0-9]{10}$/', $mobile_no)) $errors[] = 'Mobile number must be 10 digits.';
if ($mobile_no != '' && !preg_match('/^[0-9]{10}$/', $mobile_no)) {
    $errors[] = 'Mobile number must be 10 digits.';
}
// Email Optional
if ($email != '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Invalid email address.';
}

if ($errors) { echo json_encode(['ok'=>false,'msg'=>implode(' ', $errors)]); exit; }

// vehicle type must match this floor's designated type
$ftype = mysqli_prepare($conn, "SELECT floor_vehicle_type FROM floor_details WHERE floor_id=?");
mysqli_stmt_bind_param($ftype, 'i', $floor_id);
mysqli_stmt_execute($ftype);
$frow = mysqli_fetch_assoc(mysqli_stmt_get_result($ftype));
if (!$frow) { echo json_encode(['ok'=>false,'msg'=>'Floor not found.']); exit; }
if ($frow['floor_vehicle_type'] !== $vehicle_type) {
    echo json_encode(['ok'=>false,'msg'=>'This floor is designated for '.$frow['floor_vehicle_type'].' only.']);
    exit;
}

// this vehicle (same number plate AND same vehicle type) must not already have an open entry anywhere (any floor/slot)
// Different vehicle types on the same plate are allowed to have separate open entries.
$dupe = mysqli_prepare($conn, "SELECT p_id, floor_id, slot_no FROM parking_detail WHERE vehicle_no=? AND vehicle_type=? AND vehicle_exit_time IS NULL");
mysqli_stmt_bind_param($dupe, 'ss', $vehicle_no, $vehicle_type);
mysqli_stmt_execute($dupe);
if ($drow = mysqli_fetch_assoc(mysqli_stmt_get_result($dupe))) {
    echo json_encode(['ok'=>false,'msg'=>"This vehicle ($vehicle_no, $vehicle_type) is already parked on floor {$drow['floor_id']}, slot {$drow['slot_no']}. It must exit before a new entry can be recorded."]);
    exit;
}

// re-check slot is still free (race condition guard)
$chk = mysqli_prepare($conn, "SELECT p_id FROM parking_detail WHERE floor_id=? AND slot_no=? AND vehicle_exit_time IS NULL");
mysqli_stmt_bind_param($chk, 'is', $floor_id, $slot_no);
mysqli_stmt_execute($chk);
if (mysqli_fetch_assoc(mysqli_stmt_get_result($chk))) {
    echo json_encode(['ok'=>false,'msg'=>'This slot was just taken by another entry. Please pick another slot.']);
    exit;
}

$stmt = mysqli_prepare($conn, "INSERT INTO parking_detail
    (vehicle_no, vehicle_type, vehicle_enter_time, dl_no, mobile_no,email, floor_id, slot_no, staff_id_entry)
    VALUES (?,?,NOW(),?,?,?,?,?,?)");
mysqli_stmt_bind_param($stmt, 'sssssisi', $vehicle_no, $vehicle_type, $dl_no, $mobile_no,$email, $floor_id, $slot_no, $staff_id);

if (mysqli_stmt_execute($stmt)) {

    // Email bhejo
    if (!empty($email)) {

        $subject = "Vehicle Entry Confirmation";

        $body = "
        <h2>HRS Parking System</h2>
        <p>Your vehicle has been parked successfully.</p>

        <table border='1' cellpadding='8'>
            <tr><td><b>Vehicle No</b></td><td>$vehicle_no</td></tr>
            <tr><td><b>Vehicle Type</b></td><td>$vehicle_type</td></tr>
            <tr><td><b>Floor ID</b></td><td>$floor_id</td></tr>
            <tr><td><b>Slot</b></td><td>$slot_no</td></tr>
            <tr><td><b>Entry Time</b></td><td>".date('d-m-Y h:i A')."</td></tr>
        </table>

        <br>Thank you for using HRS Parking.
        ";

       if (!sendMail($email, $subject, $body)) {
    error_log("Email sending failed.");
}
    }

    echo json_encode([
        'ok' => true,
        'msg' => 'Vehicle parked successfully.',
        'p_id' => mysqli_insert_id($conn)
    ]);

} else {

    if (mysqli_errno($conn) === 1062 && (strpos(mysqli_error($conn), 'uq_active_vehicle') !== false)) {
        echo json_encode([
            'ok' => false,
            'msg' => "This vehicle ($vehicle_no, $vehicle_type) already has an open entry. It must exit before a new entry can be recorded."
        ]);
        exit;
    }

    echo json_encode([
        'ok' => false,
        'msg' => 'Database error: ' . mysqli_error($conn)
    ]);

}

?>