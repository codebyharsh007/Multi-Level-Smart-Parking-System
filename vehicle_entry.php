<?php
require_once __DIR__ . '/config/session.php';
require_login();

$rows = mysqli_query($conn, "SELECT pd.p_id, pd.vehicle_no, pd.vehicle_type, pd.vehicle_enter_time,
        pd.mobile_no, pd.floor_id, pd.slot_no, f.floor_name, s.staff_name AS entry_staff
        FROM parking_detail pd
        JOIN floor_details f ON f.floor_id = pd.floor_id
        LEFT JOIN staff_details s ON s.staff_id = pd.staff_id_entry
        WHERE pd.vehicle_exit_time IS NULL
        ORDER BY pd.vehicle_enter_time DESC");
$canExitNow = can_do_exit($conn);
$canEntryNow = can_do_entry($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>HRS | Vehicle In / Out</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="app-shell">
  <?php include __DIR__ . '/partials/sidebar.php'; ?>
  <main class="main">
    <div class="topbar"><h5>Vehicle In / Out — Active Sessions</h5>
      <?php if ($canEntryNow): ?>
      <a href="dashboard.php" class="btn btn-sm btn-amber">+ New Entry (Floor Grid)</a>
      <?php else: ?>
      <button class="btn btn-sm btn-outline-secondary" disabled title="Not authorized to record entries">+ New Entry (Floor Grid)</button>
      <?php endif; ?>
    </div>
    <div class="content">
      <div class="panel">
        <div class="panel-header">
          <input type="text" id="q" class="form-control w-auto" placeholder="Search vehicle no / mobile…" style="min-width:260px">
          <span class="text-secondary small"><?php echo mysqli_num_rows($rows); ?> vehicles currently parked</span>
        </div>
        <div class="panel-body p-0">
          <table class="table table-dark-msp mb-0" id="tbl">
            <thead>
              <tr><th>Vehicle No</th><th>Type</th><th>Floor</th><th>Slot</th><th>Mobile</th><th>Entered</th><th>By</th><th></th></tr>
            </thead>
            <tbody>
            <?php if (mysqli_num_rows($rows) === 0): ?>
              <tr><td colspan="8" class="text-center text-secondary py-4">No vehicles currently parked.</td></tr>
            <?php endif; ?>
            <?php while ($r = mysqli_fetch_assoc($rows)): ?>
              <tr>
                <td class="mono fw-semibold"><?php echo h($r['vehicle_no']); ?></td>
                <td><?php echo h($r['vehicle_type']); ?></td>
                <td><?php echo h($r['floor_name']); ?></td>
                <td class="mono"><?php echo h($r['slot_no']); ?></td>
                <td><?php echo h($r['mobile_no']); ?></td>
                <td><?php echo h(date('d-M H:i', strtotime($r['vehicle_enter_time']))); ?></td>
                <td><?php echo h($r['entry_staff']); ?></td>
                <td>
                  <?php if ($canExitNow): ?>
                  <button class="btn btn-sm btn-outline-warning"
                    onclick="quickCheckout(<?php echo (int)$r['floor_id']; ?>, '<?php echo h($r['slot_no']); ?>')">
                    Checkout
                  </button>
                  <?php else: ?>
                  <button class="btn btn-sm btn-outline-secondary" disabled title="Not authorized to check out">Checkout</button>
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

<!-- CHECKOUT MODAL (shared markup, reused from dashboard.js logic) -->
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
<script>
  const checkoutModal = new bootstrap.Modal(document.getElementById('checkoutModal'));
  let currentFloor = null;

  function quickCheckout(floorId, slotNo) {
    currentFloor = floorId;
    document.getElementById('coSlotLabel').textContent = slotNo;
    document.getElementById('coBody').innerHTML = 'Loading…';
    document.getElementById('coFooter').innerHTML = '';
    checkoutModal.show();

    fetch('ajax/get_vehicle.php?floor_id=' + floorId + '&slot_no=' + encodeURIComponent(slotNo))
      .then(r => r.json())
      .then(data => {
        if (!data.ok) { document.getElementById('coBody').innerHTML = '<div class="text-danger">' + data.msg + '</div>'; return; }
        const v = data.vehicle;
        document.getElementById('coBody').innerHTML = `
          <table class="table table-dark-msp mb-3">
            <tr><th>Vehicle No</th><td>${v.vehicle_no}</td></tr>
            <tr><th>Type</th><td>${v.vehicle_type}</td></tr>
            <tr><th>Mobile</th><td>${v.mobile_no}</td></tr>
            <tr><th>Entry Time</th><td>${v.vehicle_enter_time}</td></tr>
            <tr><th>Parked For</th><td>${v.elapsed}</td></tr>
          </table>
          <div class="mb-2">
            <label class="form-label">Payment Mode</label>
            <select id="paymentMode" class="form-select">
              <option value="Cash">Cash</option><option value="UPI">UPI</option><option value="Card">Card</option>
            </select>
          </div>
          <div id="checkoutAlert"></div>`;
        document.getElementById('coFooter').innerHTML =
          '<button class="btn btn-outline-light" data-bs-dismiss="modal">Close</button>' +
          '<button class="btn btn-amber" id="btnCheckout">Checkout &amp; Generate Receipt</button>';
        document.getElementById('btnCheckout').addEventListener('click', function () {
          const fd = new FormData();
          fd.append('p_id', v.p_id);
          fd.append('payment_mode', document.getElementById('paymentMode').value);
          this.disabled = true; this.textContent = 'Processing…';
          fetch('ajax/exit_vehicle.php', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(res => {
              if (res.ok) { window.open('receipts/print.php?p_id=' + res.p_id, '_blank'); location.reload(); }
              else {
                document.getElementById('checkoutAlert').innerHTML = '<div class="alert alert-danger py-2">' + res.msg + '</div>';
                this.disabled = false; this.textContent = 'Checkout & Generate Receipt';
              }
            });
        });
      });
  }

  document.getElementById('q').addEventListener('input', function () {
    const term = this.value.toLowerCase();
    document.querySelectorAll('#tbl tbody tr').forEach(tr => {
      tr.style.display = tr.textContent.toLowerCase().includes(term) ? '' : 'none';
    });
  });
</script>
</body>
</html>
