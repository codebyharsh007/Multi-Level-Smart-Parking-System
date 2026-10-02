<?php
require_once __DIR__ . '/../config/session.php';
require_login();

$p_id = (int)($_GET['p_id'] ?? 0);
$stmt = mysqli_prepare($conn, "SELECT pd.*, f.floor_name,
        se.staff_name AS entry_staff
        FROM parking_detail pd
        JOIN floor_details f ON f.floor_id = pd.floor_id
        LEFT JOIN staff_details se ON se.staff_id = pd.staff_id_entry
        WHERE pd.p_id = ?");
mysqli_stmt_bind_param($stmt, 'i', $p_id);
mysqli_stmt_execute($stmt);
$r = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$r) { die('Receipt not found.'); }
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8"><title>Receipt #<?php echo h($r['p_id']); ?></title>
<link rel="stylesheet" href="<?php echo h(base_url('assets/css/style.css')); ?>">
<style>
  body{background:#e9ecef;padding:30px 0;}
  .actions{width:340px;margin:14px auto;text-align:center;}
  @media print{ .actions{display:none;} body{background:#fff;padding:0;} }
</style>
</head>
<body>
  <div class="receipt shadow">
    <h5>HRS PARKING</h5>
    <p class="center" style="font-size:11px;margin:0 0 8px;">Multi-Level Smart Parking System</p>
    <hr>
    <div class="row-line"><span>Receipt No</span><span>#<?php echo h(str_pad($r['p_id'],6,'0',STR_PAD_LEFT)); ?></span></div>
    <div class="row-line"><span>Floor</span><span><?php echo h($r['floor_name']); ?></span></div>
    <div class="row-line"><span>Slot No</span><span><?php echo h($r['slot_no']); ?></span></div>
    <hr>
    <div class="row-line"><span>Vehicle No</span><span><?php echo h($r['vehicle_no']); ?></span></div>
    <div class="row-line"><span>Vehicle Type</span><span><?php echo h($r['vehicle_type']); ?></span></div>
    <div class="row-line"><span>Mobile</span><span><?php echo h($r['mobile_no']); ?></span></div>
    <hr>
    <div class="row-line"><span>Entry Time</span><span><?php echo h(date('d-M-Y H:i', strtotime($r['vehicle_enter_time']))); ?></span></div>
    <div class="row-line"><span>Entry Staff</span><span><?php echo h($r['entry_staff']); ?></span></div>
    <hr>
    <p class="center" style="font-size:11px;">Thank you. Please keep this receipt for reference.</p>
  </div>
  <div class="actions">
    <button class="btn btn-amber" onclick="window.print()">🖨 Print Receipt</button>
    <a class="btn btn-outline-secondary" href="<?php echo h(base_url('dashboard.php')); ?>">Back to Dashboard</a>
  </div>
</body>
</html>
