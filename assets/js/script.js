
document.addEventListener('DOMContentLoaded', function () {

  const tabs = document.querySelectorAll('.floor-tab');
  const grid = document.getElementById('slotGrid');
  const floorMeta = document.getElementById('floorMeta');
  let currentFloor = null;
  let currentFloorVehType = null;
  let pollTimer = null;
  let canEntry = true;
  let canExit = true;
  let emergencyMode = false;

  const entryModalEl = document.getElementById('entryModal');
  const checkoutModalEl = document.getElementById('checkoutModal');
  const entryModal = entryModalEl ? new bootstrap.Modal(entryModalEl) : null;
  const checkoutModal = checkoutModalEl ? new bootstrap.Modal(checkoutModalEl) : null;

  function clock() {
    const el = document.getElementById('clock');
    if (!el) return;
    el.textContent = new Date().toLocaleString('en-IN', { dateStyle: 'medium', timeStyle: 'medium' });
  }
  clock(); setInterval(clock, 1000);

  function updateEmergencyBanner() {
    const el = document.getElementById('emergencyBanner');
    if (!el) return;
    el.style.display = emergencyMode ? 'block' : 'none';
  }

  function loadSlots(floorId) {
    currentFloor = floorId;
    grid.innerHTML = '<div class="text-secondary">Loading slots…</div>';
    fetch('ajax/get_slots.php?floor_id=' + floorId)
      .then(r => r.json())
      .then(data => {
        if (!data.ok) { grid.innerHTML = '<div class="text-danger">' + data.msg + '</div>'; return; }
        currentFloorVehType = data.floor.floor_vehicle_type;
        canEntry = !!data.can_entry;
        canExit = !!data.can_exit;
        emergencyMode = !!data.emergency_mode;
        updateEmergencyBanner();
        floorMeta.textContent = data.floor.floor_name + ' — ' + data.summary.available + ' available / ' +
          data.summary.occupied + ' occupied of ' + data.summary.total + ' total slots';
        grid.innerHTML = '';
        data.slots.forEach(s => {
          const div = document.createElement('div');
          const locked = (s.status === 'available' && !canEntry) || (s.status === 'occupied' && !canExit);
          div.className = 'slot ' + s.status + (locked ? ' locked' : '');
          div.dataset.slot = s.slot_no;
          div.dataset.floor = floorId;
          const shortLabel = s.slot_no.split('-')[1] || s.slot_no;
          div.innerHTML = '<div class="icn">' + (s.status === 'occupied' ? '🚗' : '🅿️') + '</div>' +
                           '<div class="lbl">' + shortLabel + '</div>';
          if (s.status === 'occupied') {
            div.title = locked ? ('Occupied by ' + s.vehicle_no + ' — you are not authorized to check vehicles out')
                                : ('Occupied by ' + s.vehicle_no);
          } else {
            div.title = locked ? 'You are not authorized to record a new entry' : 'Available — click to park a vehicle';
          }
          div.addEventListener('click', () => {
            if (s.status === 'available') {
              if (!canEntry) { alert('You are not authorized to record a vehicle Entry right now. Ask Admin to enable Emergency Mode if this is urgent.'); return; }
              openEntry(floorId, s.slot_no);
            } else {
              if (!canExit) { alert('You are not authorized to check a vehicle out right now. Ask Admin to enable Emergency Mode if this is urgent.'); return; }
              openCheckout(floorId, s.slot_no);
            }
          });
          grid.appendChild(div);
        });
      })
      .catch(() => { grid.innerHTML = '<div class="text-danger">Could not load slots.</div>'; });
  }

  tabs.forEach(tab => {
    tab.addEventListener('click', () => {
      tabs.forEach(t => t.classList.remove('active'));
      tab.classList.add('active');
      loadSlots(tab.dataset.floor);
    });
  });
  if (tabs.length) loadSlots(tabs[0].dataset.floor);

  // auto refresh every 8s so multiple staff terminals stay in sync
  setInterval(() => { if (currentFloor) loadSlots(currentFloor); }, 8000);

  // ---------------- ENTRY ----------------
  function openEntry(floorId, slotNo) {
    document.getElementById('entryFloorId').value = floorId;
    document.getElementById('entrySlotNo').value = slotNo;
    document.getElementById('entrySlotLabel').textContent = slotNo;
    document.getElementById('entryForm').reset();
    document.getElementById('entryFloorId').value = floorId;
    document.getElementById('entrySlotNo').value = slotNo;
    document.getElementById('entryAlert').innerHTML = '';
    const vtSel = document.getElementById('entryVehType');
    if (currentFloorVehType) {
      vtSel.value = currentFloorVehType;
      [...vtSel.options].forEach(o => o.disabled = (o.value !== currentFloorVehType));
    }
    entryModal.show();
  }

  const entryForm = document.getElementById('entryForm');

if (entryForm) {

    entryForm.addEventListener('submit', function (e) {

        e.preventDefault();

        const fd = new FormData(entryForm);

        fetch('ajax/park_vehicle.php', {
            method: 'POST',
            body: fd
        })
        .then(response => response.json())
        .then(data => {

            console.log(data);

            if (data.ok) {

                window.open(
                    'receipts/entry_print.php?p_id=' + data.p_id,
                    '_blank'
                );

                entryModal.hide();
                loadSlots(currentFloor);

            } else {

                document.getElementById('entryAlert').innerHTML =
                    '<div class="alert alert-danger py-2">' + data.msg + '</div>';

            }

        })
        .catch(error => {
            console.error(error);
            alert(error);
        });

    });

}

  // ---------------- CHECKOUT ----------------
  function openCheckout(floorId, slotNo) {
    document.getElementById('coSlotLabel').textContent = slotNo;
    document.getElementById('coBody').innerHTML = 'Loading…';
    document.getElementById('coFooter').innerHTML = '';
    checkoutModal.show();

    fetch('ajax/get_vehicle.php?floor_id=' + floorId + '&slot_no=' + encodeURIComponent(slotNo))
      .then(r => r.json())
      .then(data => {
        if (!data.ok) {
          document.getElementById('coBody').innerHTML = '<div class="text-danger">' + data.msg + '</div>';
          return;
        }
        const v = data.vehicle;
        document.getElementById('coBody').innerHTML = `
          <table class="table table-dark-msp mb-3">
            <tr><th>Vehicle No</th><td>${v.vehicle_no}</td></tr>
            <tr><th>Type</th><td>${v.vehicle_type}</td></tr>
            <tr><th>Mobile</th><td>${v.mobile_no}</td></tr>
            <tr><th>DL No</th><td>${v.dl_no}</td></tr>
            <tr><th>Entry Time</th><td>${v.vehicle_enter_time}</td></tr>
            <tr><th>Entered By</th><td>${v.entry_staff}</td></tr>
            <tr><th>Parked For</th><td>${v.elapsed}</td></tr>
          </table>
          <div class="mb-2">
            <label class="form-label">Payment Mode</label>
            <select id="paymentMode" class="form-select">
              <option value="Cash">Cash</option>
              <option value="UPI">UPI</option>
              <option value="Card">Card</option>
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
          this.disabled = true;
          this.textContent = 'Processing…';
          fetch('ajax/exit_vehicle.php', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(res => {
              if (res.ok) {
                checkoutModal.hide();
                loadSlots(currentFloor);
                window.open('receipts/print.php?p_id=' + res.p_id, '_blank');
              } else {
                document.getElementById('checkoutAlert').innerHTML =
                  '<div class="alert alert-danger py-2">' + res.msg + '</div>';
                this.disabled = false;
                this.textContent = 'Checkout & Generate Receipt';
              }
            });
        });
      });
  }

  // sidebar toggle for mobile (if a hamburger button exists)
  const burger = document.getElementById('burger');
  if (burger) burger.addEventListener('click', () => document.getElementById('sidebar').classList.toggle('open'));
});
