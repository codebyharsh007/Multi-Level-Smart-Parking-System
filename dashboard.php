<?php
require_once __DIR__ . '/config/session.php';
require_login();

$floors = mysqli_query($conn, "SELECT * FROM floor_details WHERE floor_status='Active' ORDER BY floor_id");
$floors = mysqli_fetch_all($floors, MYSQLI_ASSOC);

$totFloors = count($floors);
$totCap = 0; foreach ($floors as $f) $totCap += (int)$f['capacity'];
$occRow = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM parking_detail WHERE vehicle_exit_time IS NULL"));
$totOcc = (int)$occRow['c'];
$todayRow = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM parking_detail WHERE DATE(vehicle_enter_time)=CURDATE()"));
$todayIn = (int)$todayRow['c'];
$emergencyOn = is_emergency_mode($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>HRS | Dashboard</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="app-shell">
  <?php include __DIR__ . '/partials/sidebar.php'; ?>

  <main class="main">
    <div class="topbar">
      <h5>Live Floor Dashboard</h5>
      <div class="text-secondary small" id="clock"></div>
    </div>

    <div class="content">

      <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
          <div class="stat-card"><div class="stat-label">Floors</div><div class="stat-value"><?php echo $totFloors; ?></div></div>
        </div>
        <div class="col-6 col-md-3">
          <div class="stat-card amber"><div class="stat-label">Total Capacity</div><div class="stat-value"><?php echo $totCap; ?></div></div>
        </div>
        <div class="col-6 col-md-3">
          <div class="stat-card busy"><div class="stat-label">Occupied Now</div><div class="stat-value" id="statOcc"><?php echo $totOcc; ?></div></div>
        </div>
        <div class="col-6 col-md-3">
          <div class="stat-card ok"><div class="stat-label">Entries Today</div><div class="stat-value"><?php echo $todayIn; ?></div></div>
        </div>
      </div>

      <?php if ($_SESSION['staff_type'] === 'Admin'): ?>
      <div class="emergency-card mb-4 <?php echo $emergencyOn ? 'is-on' : ''; ?>" id="emergencyCard">
        <div>
          <div class="stat-label">Emergency Mode</div>
          <div class="em-status" id="emStatusText"><?php echo $emergencyOn ? 'ON' : 'OFF'; ?></div>
          <small class="text-secondary">When ON, every Entry &amp; Exit Operator can perform both Entry and Exit.</small>
        </div>
        <label class="em-switch">
          <input type="checkbox" id="emSwitch" <?php echo $emergencyOn ? 'checked' : ''; ?>>
          <span class="em-slider"></span>
        </label>
      </div>
      <?php endif; ?>

      <div class="panel mb-4">
        <div class="panel-header">
          <div class="floor-tabs" id="floorTabs">
            <?php foreach ($floors as $i => $f): ?>
              <div class="floor-tab <?php echo $i===0?'active':''; ?>" data-floor="<?php echo (int)$f['floor_id']; ?>">
                <?php echo h($f['floor_name']); ?>
                <span class="cap"><?php echo h($f['floor_vehicle_type']); ?> &middot; <?php echo (int)$f['capacity']; ?> slots</span>
              </div>
            <?php endforeach; ?>
            <?php if (!$floors): ?>
              <span class="text-secondary">No active floors configured. Ask Admin to add one under Floor Setup.</span>
            <?php endif; ?>
          </div>
          <div class="legend">
            <span><span class="sw" style="background:var(--ok)"></span>Available</span>
            <span><span class="sw" style="background:var(--busy)"></span>Occupied</span>
          </div>
        </div>
        <div class="panel-body">
          <div id="floorMeta" class="text-secondary small mb-3"></div>
          <div class="slot-grid" id="slotGrid">
            <div class="text-secondary">Select a floor to view slots…</div>
          </div>
        </div>
      </div>

    </div>
  </main>
</div>

<!-- ENTRY MODAL -->
<div class="modal fade" id="entryModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="entryForm">
        <div class="modal-header">
          <h5 class="modal-title">Vehicle Entry &mdash; Slot <span id="entrySlotLabel"></span></h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="floor_id" id="entryFloorId">
          <input type="hidden" name="slot_no" id="entrySlotNo">
          <div id="entryAlert"></div>
          <div class="mb-3">
            <label class="form-label">Vehicle Number</label>
            <input type="text" name="vehicle_no" class="form-control text-uppercase" placeholder="e.g. MP09AB1234" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Vehicle Type</label>
            <select name="vehicle_type" id="entryVehType" class="form-select" required>
              <option value="2 Wheeler">2 Wheeler</option>
              <option value="3 Wheeler">3 Wheeler</option>
              <option value="4 Wheeler">4 Wheeler</option>
            </select>
          </div>
          <div class="row">
            <div class="col-6 mb-3">
              <label class="form-label">Driving Licence No</label>
              <input type="text" name="dl_no" class="form-control" placeholder="Optional">
            </div>
            <div class="col-6 mb-3">
              <label class="form-label">Mobile No</label>
              <input type="text" name="mobile_no" maxlength="10" pattern="[0-9]{10}" class="form-control"placeholder="Optional">
            </div>
            <div class="mb-3">
             <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control " placeholder="Optional">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-amber">Allot Slot</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- CHECKOUT MODAL -->
<div class="modal fade" id="checkoutModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Vehicle Details &mdash; Slot <span id="coSlotLabel"></span></h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="coBody">Loading…</div>
      <div class="modal-footer" id="coFooter"></div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/script.js"></script>
<?php if ($_SESSION['staff_type'] === 'Admin'): ?>
<script>
document.getElementById('emSwitch').addEventListener('change', function () {
  const on = this.checked;
  const card = document.getElementById('emergencyCard');
  const statusText = document.getElementById('emStatusText');
  this.disabled = true;
  const fd = new FormData();
  fd.append('state', on ? '1' : '0');
  fetch('ajax/toggle_emergency.php', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(res => {
      this.disabled = false;
      if (!res.ok) { alert(res.msg || 'Could not update Emergency Mode.'); this.checked = !on; return; }
      card.classList.toggle('is-on', res.emergency_mode);
      statusText.textContent = res.emergency_mode ? 'ON' : 'OFF';
      const banner = document.getElementById('emergencyBanner');
      if (banner) banner.style.display = res.emergency_mode ? 'block' : 'none';
    })
    .catch(() => { this.disabled = false; this.checked = !on; alert('Network error while updating Emergency Mode.'); });
});
</script>
<?php endif; ?>
</body>
</html>
