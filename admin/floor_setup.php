<?php
require_once __DIR__ . '/../config/session.php';
require_admin();

$msg = ''; $err = '';

// CREATE / UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_floor'])) {
    $floor_id  = (int)($_POST['floor_id'] ?? 0);
    $name      = trim($_POST['floor_name']);
    $capacity  = (int)$_POST['capacity'];
    $vtype     = $_POST['floor_vehicle_type'];
    $status    = $_POST['floor_status'];

    if ($name === '' || $capacity < 1 || !in_array($vtype, ['2 Wheeler','3 Wheeler','4 Wheeler'], true)) {
        $err = 'Please fill all fields correctly (capacity must be at least 1).';
    } elseif ($floor_id) {
        $stmt = mysqli_prepare($conn, "UPDATE floor_details SET floor_name=?, capacity=?, floor_vehicle_type=?, floor_status=? WHERE floor_id=?");
        mysqli_stmt_bind_param($stmt, 'sissi', $name, $capacity, $vtype, $status, $floor_id);
        mysqli_stmt_execute($stmt);
        $msg = 'Floor updated successfully.';
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO floor_details (floor_name, capacity, floor_vehicle_type, floor_status) VALUES (?,?,?,?)");
        mysqli_stmt_bind_param($stmt, 'siss', $name, $capacity, $vtype, $status);
        mysqli_stmt_execute($stmt);
        $msg = 'Floor added successfully.';
    }
}

// BLOCK / UNBLOCK (per whiteboard: Blocked -> Y / Unblocked -> N i.e. toggling floor_status)
if (isset($_GET['toggle'])) {
    $fid = (int)$_GET['toggle'];
    $stmt = mysqli_prepare($conn, "UPDATE floor_details SET floor_status = IF(floor_status='Active','Inactive','Active') WHERE floor_id=?");
    mysqli_stmt_bind_param($stmt, 'i', $fid);
    mysqli_stmt_execute($stmt);
    header('Location: floor_setup.php?msg=toggled'); exit;
}

$floors = mysqli_query($conn, "SELECT * FROM floor_details ORDER BY floor_id");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>HRS | Floor Setup</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="app-shell">
  <?php include __DIR__ . '/../partials/sidebar.php'; ?>
  <main class="main">
    <div class="topbar"><h5>Floor Setup / Configuration</h5></div>
    <div class="content">

      <?php if ($msg): ?><div class="alert alert-success"><?php echo h($msg); ?></div><?php endif; ?>
      <?php if ($err): ?><div class="alert alert-danger"><?php echo h($err); ?></div><?php endif; ?>

      <div class="row g-4">
        <div class="col-lg-4">
          <div class="panel">
            <div class="panel-header"><strong id="formTitle">Add Floor</strong></div>
            <div class="panel-body">
              <form method="post" id="floorForm">
                <input type="hidden" name="floor_id" id="f_id">
                <div class="mb-3">
                  <label class="form-label">Floor Name</label>
                  <input type="text" name="floor_name" id="f_name" class="form-control" required>
                </div>
                <div class="mb-3">
                  <label class="form-label">Vehicle Type</label>
                  <select name="floor_vehicle_type" id="f_vtype" class="form-select" required>
                    <option value="2 Wheeler">2 Wheeler</option>
                    <option value="3 Wheeler">3 Wheeler</option>
                    <option value="4 Wheeler">4 Wheeler</option>
                  </select>
                </div>
                <div class="mb-3">
                  <label class="form-label">Floor Capacity (slots)</label>
                  <input type="number" name="capacity" id="f_cap" min="1" class="form-control" required>
                </div>
                <div class="mb-3">
                  <label class="form-label">Status</label>
                  <select name="floor_status" id="f_status" class="form-select">
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                  </select>
                </div>
                <button type="submit" name="save_floor" class="btn btn-amber w-100">Add</button>
                <button type="button" class="btn btn-outline-light w-100 mt-2" onclick="resetForm()">Clear / New Floor</button>
              </form>
            </div>
          </div>
        </div>

        <div class="col-lg-8">
          <div class="panel">
            <div class="panel-header"><strong>All Floors</strong></div>
            <div class="panel-body p-0">
              <table class="table table-dark-msp mb-0">
                <thead><tr><th>ID</th><th>Floor Name</th><th>Vehicle Type</th><th>Capacity</th><th>Status</th><th></th></tr></thead>
                <tbody>
                <?php while ($f = mysqli_fetch_assoc($floors)): ?>
                  <tr>
                    <td class="mono">#<?php echo (int)$f['floor_id']; ?></td>
                    <td><?php echo h($f['floor_name']); ?></td>
                    <td><?php echo h($f['floor_vehicle_type']); ?></td>
                    <td><?php echo (int)$f['capacity']; ?></td>
                    <td><span class="badge <?php echo $f['floor_status']==='Active'?'badge-active':'badge-inactive'; ?>"><?php echo h($f['floor_status']); ?></span></td>
                    <td class="text-end">
                      <button class="btn btn-sm btn-outline-warning"
                        onclick='editFloor(<?php echo json_encode($f); ?>)'>Edit</button>
                      <a class="btn btn-sm btn-outline-secondary" href="?toggle=<?php echo (int)$f['floor_id']; ?>"
                         onclick="return confirm('Toggle status of this floor?')">
                        <?php echo $f['floor_status']==='Active' ? 'Block' : 'Unblock'; ?>
                      </a>
                    </td>
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
function editFloor(f) {
  document.getElementById('formTitle').textContent = 'Edit Floor #' + f.floor_id;
  document.getElementById('f_id').value = f.floor_id;
  document.getElementById('f_name').value = f.floor_name;
  document.getElementById('f_vtype').value = f.floor_vehicle_type;
  document.getElementById('f_cap').value = f.capacity;
  document.getElementById('f_status').value = f.floor_status;
  document.querySelector('button[name=save_floor]').textContent = 'Update';
  window.scrollTo(0,0);
}
function resetForm() {
  document.getElementById('floorForm').reset();
  document.getElementById('f_id').value = '';
  document.getElementById('formTitle').textContent = 'Add Floor';
  document.querySelector('button[name=save_floor]').textContent = 'Add';
}
</script>
</body>
</html>
