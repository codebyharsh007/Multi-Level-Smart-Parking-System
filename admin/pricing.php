<?php
require_once __DIR__ . '/../config/session.php';
require_admin();
$msg='';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_price'])) {
    $id = (int)($_POST['pprice_id'] ?? 0);
    $price = (float)$_POST['price'];
    $duration = trim($_POST['duration']);
    $vtype = $_POST['vehicle_type'];
    $status = $_POST['price_status'];

    if ($id) {
        $stmt = mysqli_prepare($conn, "UPDATE parking_price SET price=?, duration=?, vehicle_type=?, price_status=? WHERE pprice_id=?");
        mysqli_stmt_bind_param($stmt, 'dsssi', $price, $duration, $vtype, $status, $id);
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO parking_price (price, duration, vehicle_type, price_status) VALUES (?,?,?,?)");
        mysqli_stmt_bind_param($stmt, 'dsss', $price, $duration, $vtype, $status);
    }
    mysqli_stmt_execute($stmt);
    $msg = 'Tariff saved.';
}

$rows = mysqli_query($conn, "SELECT * FROM parking_price ORDER BY vehicle_type, pprice_id");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>HRS | Parking Pricing</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="app-shell">
  <?php include __DIR__ . '/../partials/sidebar.php'; ?>
  <main class="main">
    <div class="topbar"><h5>Parking Pricing</h5></div>
    <div class="content">
      <?php if ($msg): ?><div class="alert alert-success"><?php echo h($msg); ?></div><?php endif; ?>
      <div class="row g-4">
        <div class="col-lg-4">
          <div class="panel panel-body">
            <form method="post" id="priceForm">
              <input type="hidden" name="pprice_id" id="p_id">
              <div class="mb-3"><label class="form-label">Vehicle Type</label>
                <select name="vehicle_type" id="p_vtype" class="form-select" required>
                  <option>2 Wheeler</option><option>3 Wheeler</option><option>4 Wheeler</option>
                </select>
              </div>
              <div class="mb-3"><label class="form-label">Duration Label</label>
                <input type="text" name="duration" id="p_duration" class="form-control" placeholder="First 1 Hour / Every Additional Hour" required>
              </div>
              <div class="mb-3"><label class="form-label">Price (₹)</label>
                <input type="number" step="0.01" name="price" id="p_price" class="form-control" required>
              </div>
              <div class="mb-3"><label class="form-label">Status</label>
                <select name="price_status" id="p_status" class="form-select"><option>Active</option><option>Inactive</option></select>
              </div>
              <button class="btn btn-amber w-100" name="save_price">Save Tariff</button>
              <button type="button" class="btn btn-outline-light w-100 mt-2" onclick="document.getElementById('priceForm').reset();document.getElementById('p_id').value=''">Clear</button>
            </form>
          </div>
        </div>
        <div class="col-lg-8">
          <div class="panel">
            <div class="panel-header"><strong>Tariff Card</strong></div>
            <div class="panel-body p-0">
              <table class="table table-dark-msp mb-0">
                <thead><tr><th>Vehicle Type</th><th>Duration</th><th>Price</th><th>Status</th><th></th></tr></thead>
                <tbody>
                <?php while ($p = mysqli_fetch_assoc($rows)): ?>
                  <tr>
                    <td><?php echo h($p['vehicle_type']); ?></td>
                    <td><?php echo h($p['duration']); ?></td>
                    <td>&#8377; <?php echo number_format($p['price'],2); ?></td>
                    <td><span class="badge <?php echo $p['price_status']==='Active'?'badge-active':'badge-inactive'; ?>"><?php echo h($p['price_status']); ?></span></td>
                    <td class="text-end"><button class="btn btn-sm btn-outline-warning" onclick='editPrice(<?php echo json_encode($p); ?>)'>Edit</button></td>
                  </tr>
                <?php endwhile; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function editPrice(p){
  document.getElementById('p_id').value=p.pprice_id;
  document.getElementById('p_vtype').value=p.vehicle_type;
  document.getElementById('p_duration').value=p.duration;
  document.getElementById('p_price').value=p.price;
  document.getElementById('p_status').value=p.price_status;
  window.scrollTo(0,0);
}
</script>
</body>
</html>
