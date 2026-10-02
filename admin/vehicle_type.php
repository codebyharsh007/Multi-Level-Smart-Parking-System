<?php
require_once __DIR__ . '/../config/session.php';
require_admin();

$types = ['2 Wheeler','3 Wheeler','4 Wheeler'];
$stats = [];
foreach ($types as $t) {
    $stmt = mysqli_prepare($conn, "SELECT
        (SELECT COUNT(*) FROM floor_details WHERE floor_vehicle_type=? AND floor_status='Active') AS floors,
        (SELECT COALESCE(SUM(capacity),0) FROM floor_details WHERE floor_vehicle_type=? AND floor_status='Active') AS capacity,
        (SELECT COUNT(*) FROM parking_detail WHERE vehicle_type=? AND vehicle_exit_time IS NULL) AS occupied");
    mysqli_stmt_bind_param($stmt, 'sss', $t, $t, $t);
    mysqli_stmt_execute($stmt);
    $stats[$t] = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>HRS | Vehicle Types</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="app-shell">
  <?php include __DIR__ . '/../partials/sidebar.php'; ?>
  <main class="main">
    <div class="topbar"><h5>Vehicle Types</h5></div>
    <div class="content">
      <div class="alert alert-secondary">Vehicle types are fixed system-wide (<code>2 Wheeler</code>, <code>3 Wheeler</code>, <code>4 Wheeler</code>) and are mapped to floors under <a href="floor_setup.php">Floor Setup</a>. This page shows how each type is currently being used.</div>
      <div class="row g-3">
        <?php foreach ($types as $t): $s = $stats[$t]; ?>
          <div class="col-md-4">
            <div class="panel panel-body">
              <h5 class="mb-3"><?php echo h($t); ?></h5>
              <div class="d-flex justify-content-between mb-1"><span class="text-secondary">Active Floors</span><strong><?php echo (int)$s['floors']; ?></strong></div>
              <div class="d-flex justify-content-between mb-1"><span class="text-secondary">Total Slots</span><strong><?php echo (int)$s['capacity']; ?></strong></div>
              <div class="d-flex justify-content-between"><span class="text-secondary">Currently Parked</span><strong class="text-warning"><?php echo (int)$s['occupied']; ?></strong></div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </main>
</div>
</body>
</html>
