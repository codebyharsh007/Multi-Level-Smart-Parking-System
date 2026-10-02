<?php
require_once __DIR__ . '/config/session.php';
require_login();

$from = $_GET['from'] ?? date('Y-m-d', strtotime('-6 days'));
$to   = $_GET['to'] ?? date('Y-m-d');

function q($conn, $sql, $types = '', $params = []) {
    $stmt = mysqli_prepare($conn, $sql);
    if ($params) mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt);
}

$totals = q($conn, "SELECT COUNT(*) trips, COALESCE(SUM(parking_charge),0) revenue
    FROM parking_detail WHERE vehicle_exit_time IS NOT NULL AND DATE(vehicle_enter_time) BETWEEN ? AND ?",
    'ss', [$from, $to]);
$totals = mysqli_fetch_assoc($totals);

$byType = q($conn, "SELECT vehicle_type, COUNT(*) trips, COALESCE(SUM(parking_charge),0) revenue
    FROM parking_detail WHERE vehicle_exit_time IS NOT NULL AND DATE(vehicle_enter_time) BETWEEN ? AND ?
    GROUP BY vehicle_type", 'ss', [$from, $to]);

$byDay = q($conn, "SELECT DATE(vehicle_enter_time) d, COUNT(*) trips, COALESCE(SUM(parking_charge),0) revenue
    FROM parking_detail WHERE vehicle_exit_time IS NOT NULL AND DATE(vehicle_enter_time) BETWEEN ? AND ?
    GROUP BY DATE(vehicle_enter_time) ORDER BY d", 'ss', [$from, $to]);

$byStaff = q($conn, "SELECT s.staff_name, COUNT(*) vehicles_handled
    FROM parking_detail pd JOIN staff_details s ON s.staff_id = pd.staff_id_entry
    WHERE DATE(pd.vehicle_enter_time) BETWEEN ? AND ?
    GROUP BY pd.staff_id_entry ORDER BY vehicles_handled DESC", 'ss', [$from, $to]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title> HRS | Reports</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="app-shell">
  <?php include __DIR__ . '/partials/sidebar.php'; ?>
  <main class="main">
    <div class="topbar"><h5>Reporting &amp; Analytics</h5>
      <button class="btn btn-sm btn-outline-light" onclick="window.print()">🖨 Print Report</button>
    </div>
    <div class="content">

      <form class="panel panel-body mb-4 row g-3" method="get">
        <div class="col-md-3"><label class="form-label">From</label><input type="date" name="from" class="form-control" value="<?php echo h($from); ?>"></div>
        <div class="col-md-3"><label class="form-label">To</label><input type="date" name="to" class="form-control" value="<?php echo h($to); ?>"></div>
        <div class="col-md-2 d-flex align-items-end"><button class="btn btn-amber w-100">Apply</button></div>
      </form>

      <div class="row g-3 mb-4">
        <div class="col-6 col-md-3"><div class="stat-card"><div class="stat-label">Completed Trips</div><div class="stat-value"><?php echo (int)$totals['trips']; ?></div></div></div>
        <div class="col-6 col-md-3"><div class="stat-card ok"><div class="stat-label">Total Revenue</div><div class="stat-value">₹<?php echo number_format($totals['revenue'],0); ?></div></div></div>
      </div>

      <div class="row g-4">
        <div class="col-lg-6">
          <div class="panel">
            <div class="panel-header"><strong>Revenue by Vehicle Type</strong></div>
            <div class="panel-body p-0">
              <table class="table table-dark-msp mb-0">
                <thead><tr><th>Vehicle Type</th><th>Trips</th><th>Revenue</th></tr></thead>
                <tbody>
                <?php $any=false; while ($r = mysqli_fetch_assoc($byType)): $any=true; ?>
                  <tr><td><?php echo h($r['vehicle_type']); ?></td><td><?php echo (int)$r['trips']; ?></td><td>₹<?php echo number_format($r['revenue'],2); ?></td></tr>
                <?php endwhile; if(!$any): ?><tr><td colspan="3" class="text-secondary text-center py-3">No data in this range.</td></tr><?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <div class="col-lg-6">
          <div class="panel">
            <div class="panel-header"><strong>Staff Performance (Entries Logged)</strong></div>
            <div class="panel-body p-0">
              <table class="table table-dark-msp mb-0">
                <thead><tr><th>Staff</th><th>Vehicles Handled</th></tr></thead>
                <tbody>
                <?php $any=false; while ($r = mysqli_fetch_assoc($byStaff)): $any=true; ?>
                  <tr><td><?php echo h($r['staff_name']); ?></td><td><?php echo (int)$r['vehicles_handled']; ?></td></tr>
                <?php endwhile; if(!$any): ?><tr><td colspan="2" class="text-secondary text-center py-3">No data in this range.</td></tr><?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <div class="col-12">
          <div class="panel">
            <div class="panel-header"><strong>Daily Break-up</strong></div>
            <div class="panel-body p-0">
              <table class="table table-dark-msp mb-0">
                <thead><tr><th>Date</th><th>Trips</th><th>Revenue</th></tr></thead>
                <tbody>
                <?php $any=false; while ($r = mysqli_fetch_assoc($byDay)): $any=true; ?>
                  <tr><td><?php echo h(date('d-M-Y', strtotime($r['d']))); ?></td><td><?php echo (int)$r['trips']; ?></td><td>₹<?php echo number_format($r['revenue'],2); ?></td></tr>
                <?php endwhile; if(!$any): ?><tr><td colspan="3" class="text-secondary text-center py-3">No data in this range.</td></tr><?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

    </div>
  </main>
</div>
</body>
</html>
