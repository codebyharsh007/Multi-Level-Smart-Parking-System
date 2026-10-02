<?php
require_once __DIR__ . '/config/session.php';
require_login();

$vehicle = trim($_GET['vehicle_no'] ?? '');
$floor_id = (int)($_GET['floor_id'] ?? 0);
$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';
$status = $_GET['status'] ?? '';

$where = ['1=1']; $params = []; $types = '';
if ($vehicle !== '') { $where[] = 'pd.vehicle_no LIKE ?'; $params[] = '%'.$vehicle.'%'; $types.='s'; }
if ($floor_id) { $where[] = 'pd.floor_id = ?'; $params[] = $floor_id; $types.='i'; }
if ($from !== '') { $where[] = 'DATE(pd.vehicle_enter_time) >= ?'; $params[] = $from; $types.='s'; }
if ($to !== '') { $where[] = 'DATE(pd.vehicle_enter_time) <= ?'; $params[] = $to; $types.='s'; }
if ($status === 'open') { $where[] = 'pd.vehicle_exit_time IS NULL'; }
if ($status === 'closed') { $where[] = 'pd.vehicle_exit_time IS NOT NULL'; }

$sql = "SELECT pd.*, f.floor_name FROM parking_detail pd JOIN floor_details f ON f.floor_id=pd.floor_id
        WHERE " . implode(' AND ', $where) . " ORDER BY pd.vehicle_enter_time DESC LIMIT 300";
$stmt = mysqli_prepare($conn, $sql);
if ($params) mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$rows = mysqli_stmt_get_result($stmt);

$floors = mysqli_query($conn, "SELECT floor_id, floor_name FROM floor_details ORDER BY floor_name");
$floors = mysqli_fetch_all($floors, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>HRS | Search Records</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="app-shell">
  <?php include __DIR__ . '/partials/sidebar.php'; ?>
  <main class="main">
    <div class="topbar"><h5>Search Parking Records</h5></div>
    <div class="content">
      <div class="panel panel-body mb-4">
        <form class="row g-3" method="get">
          <div class="col-md-3">
            <label class="form-label">Vehicle No</label>
            <input class="form-control" name="vehicle_no" value="<?php echo h($vehicle); ?>">
          </div>
          <div class="col-md-3">
            <label class="form-label">Floor</label>
            <select class="form-select" name="floor_id">
              <option value="0">All Floors</option>
              <?php foreach ($floors as $f): ?>
                <option value="<?php echo (int)$f['floor_id']; ?>" <?php echo $floor_id==$f['floor_id']?'selected':''; ?>><?php echo h($f['floor_name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-2"><label class="form-label">From</label><input type="date" class="form-control" name="from" value="<?php echo h($from); ?>"></div>
          <div class="col-md-2"><label class="form-label">To</label><input type="date" class="form-control" name="to" value="<?php echo h($to); ?>"></div>
          <div class="col-md-2">
            <label class="form-label">Status</label>
            <select class="form-select" name="status">
              <option value="">All</option>
              <option value="open" <?php echo $status==='open'?'selected':''; ?>>Currently Parked</option>
              <option value="closed" <?php echo $status==='closed'?'selected':''; ?>>Checked Out</option>
            </select>
          </div>
          <div class="col-12"><button class="btn btn-amber">Search</button> <a href="search.php" class="btn btn-outline-light">Reset</a></div>
        </form>
      </div>

      <div class="panel">
        <div class="panel-body p-0">
          <table class="table table-dark-msp mb-0">
            <thead><tr><th>Vehicle No</th><th>Type</th><th>Floor</th><th>Slot</th><th>Entry</th><th>Exit</th><th>Duration</th><th>Charge</th><th>Status</th></tr></thead>
            <tbody>
            <?php if (mysqli_num_rows($rows) === 0): ?>
              <tr><td colspan="9" class="text-center text-secondary py-4">No matching records.</td></tr>
            <?php endif; ?>
            <?php while ($r = mysqli_fetch_assoc($rows)): ?>
              <tr>
                <td class="mono fw-semibold"><?php echo h($r['vehicle_no']); ?></td>
                <td><?php echo h($r['vehicle_type']); ?></td>
                <td><?php echo h($r['floor_name']); ?></td>
                <td class="mono"><?php echo h($r['slot_no']); ?></td>
                <td><?php echo h(date('d-M-y H:i', strtotime($r['vehicle_enter_time']))); ?></td>
                <td><?php echo $r['vehicle_exit_time'] ? h(date('d-M-y H:i', strtotime($r['vehicle_exit_time']))) : '--'; ?></td>
                <td><?php echo h($r['parking_duration'] ?? '--'); ?></td>
                <td><?php echo $r['parking_charge'] ? '₹'.number_format($r['parking_charge'],2) : '--'; ?></td>
                <td>
                  <?php if ($r['vehicle_exit_time']): ?>
                    <span class="badge badge-inactive">Checked Out</span>
                  <?php else: ?>
                    <span class="badge badge-active">Parked</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>
</div>
</body>
</html>
