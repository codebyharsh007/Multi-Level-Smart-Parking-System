<?php
require_once __DIR__ . '/../config/session.php';
require_login();
require_once __DIR__ . '/../mail_config.php';


date_default_timezone_set('Asia/Kolkata');


header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['ok'=>false,'msg'=>'Invalid request']); exit; }

if (!can_do_exit($conn)) {
    echo json_encode(['ok'=>false,'msg'=>'You are not authorized to check a vehicle out right now. Ask Admin to enable Emergency Mode if this is urgent.']);
    exit;
}

$p_id         = (int)($_POST['p_id'] ?? 0);
$payment_mode = trim($_POST['payment_mode'] ?? 'Cash');
$staff_id     = (int)$_SESSION['staff_id'];

$stmt = mysqli_prepare($conn, "SELECT * FROM parking_detail WHERE p_id=? AND vehicle_exit_time IS NULL");
mysqli_stmt_bind_param($stmt, 'i', $p_id);
mysqli_stmt_execute($stmt);
$row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
if (!$row) { echo json_encode(['ok'=>false,'msg'=>'Record not found or already checked out.']); exit; }

$entered = new DateTime($row['vehicle_enter_time']);
$now = new DateTime();
$diff = $entered->diff($now);
$totalMinutes = ($diff->days * 24 * 60) + ($diff->h * 60) + $diff->i;
if ($totalMinutes < 1) $totalMinutes = 1;
$hours = (int)ceil($totalMinutes / 60);

// pull tariff for this vehicle type
$pstmt = mysqli_prepare($conn, "SELECT price, duration FROM parking_price WHERE vehicle_type=? AND price_status='Active'");
mysqli_stmt_bind_param($pstmt, 's', $row['vehicle_type']);
mysqli_stmt_execute($pstmt);
$pres = mysqli_stmt_get_result($pstmt);
$firstHour = 0; $everyExtra = 0;
while ($p = mysqli_fetch_assoc($pres)) {
    if (stripos($p['duration'], 'first') !== false) $firstHour = (float)$p['price'];
    elseif (stripos($p['duration'], 'extra') !== false || stripos($p['duration'], 'additional') !== false) $everyExtra = (float)$p['price'];
}
if ($firstHour == 0 && $everyExtra == 0) { $firstHour = 20; $everyExtra = 10; } // fallback default

$charge = $hours <= 1 ? $firstHour : $firstHour + (($hours - 1) * $everyExtra);
$durationTime = gmdate('H:i:s', $totalMinutes * 60);

if (!in_array($payment_mode, ['Cash','UPI','Card'], true)) $payment_mode = 'Cash';

$ustmt = mysqli_prepare($conn, "UPDATE parking_detail
    SET vehicle_exit_time = NOW(), staff_id_exit = ?, parking_duration = ?, parking_charge = ?, payment_mode = ?
    WHERE p_id = ?");
mysqli_stmt_bind_param($ustmt, 'isdsi', $staff_id, $durationTime, $charge, $payment_mode, $p_id);

if (mysqli_stmt_execute($ustmt)) {
    if (!empty($row['email'])) {

    $subject = "Vehicle Exit Receipt - HRS Parking";

    $body = "
    <h2>HRS Parking System</h2>
    <p>Your vehicle has been checked out successfully.</p>

    <table border='1' cellpadding='8'>
        <tr>
            <td><b>Vehicle No</b></td>
            <td>{$row['vehicle_no']}</td>
        </tr>

        <tr>
            <td><b>Vehicle Type</b></td>
            <td>{$row['vehicle_type']}</td>
        </tr>

        <tr>
            <td><b>Entry Time</b></td>
            <td>{$row['vehicle_enter_time']}</td>
        </tr>

        <tr>
            <td><b>Exit Time</b></td>
            <td>" . date('d-m-Y h:i A') . "</td>
        </tr>

        <tr>
            <td><b>Parking Duration</b></td>
            <td>{$durationTime}</td>
        </tr>

        <tr>
            <td><b>Parking Charge</b></td>
            <td>₹ {$charge}</td>
        </tr>

        <tr>
            <td><b>Payment Mode</b></td>
            <td>{$payment_mode}</td>
        </tr>
    </table>

    <br><br>
    <b>Thank you for using HRS Parking System.</b>
    ";

    sendMail($row['email'], $subject, $body);
}
    echo json_encode([
        'ok' => true,
        'msg' => 'Vehicle checked out.',
        'p_id' => $p_id,
        'charge' => number_format($charge, 2),
        'duration' => $durationTime,
        'payment_mode' => $payment_mode,
    ]);
} else {
    echo json_encode(['ok'=>false, 'msg'=>'Database error: ' . mysqli_error($conn)]);
}
