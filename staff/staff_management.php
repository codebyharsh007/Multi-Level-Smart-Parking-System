<?php
require_once __DIR__ . '/../config/session.php';
require_admin();
$msg=''; $err='';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_staff'])) {
    $id = (int)($_POST['staff_id'] ?? 0);
    $name = trim($_POST['staff_name']);
    $mobile = trim($_POST['staff_mobile_no']);
    $gender = $_POST['staff_gender'];
    $dob = $_POST['staff_dob'];
    $ptype = $_POST['proof_type'];
    $pnum = trim($_POST['proof_number']);
    $status = $_POST['staff_status'];
    $stype = $_POST['staff_type'];
    $pass = $_POST['staff_password'] ?? '';

    if ($name==='' || !preg_match('/^[0-9]{10}$/', $mobile) || $pnum==='') {
        $err = 'Please fill all required fields correctly (mobile must be 10 digits).';
    } elseif (!$id && $pass === '') {
        $err = 'Password is required for a new staff account.';
    } else {
        if ($id) {
            if ($pass !== '') {
                $hash = password_hash($pass, PASSWORD_BCRYPT);
                $stmt = mysqli_prepare($conn, "UPDATE staff_details SET staff_name=?, staff_mobile_no=?, staff_gender=?, staff_dob=?, proof_type=?, proof_number=?, staff_status=?, staff_type=?, staff_password=? WHERE staff_id=?");
                mysqli_stmt_bind_param($stmt, 'sssssssssi', $name,$mobile,$gender,$dob,$ptype,$pnum,$status,$stype,$hash,$id);
            } else {
                $stmt = mysqli_prepare($conn, "UPDATE staff_details SET staff_name=?, staff_mobile_no=?, staff_gender=?, staff_dob=?, proof_type=?, proof_number=?, staff_status=?, staff_type=? WHERE staff_id=?");
                mysqli_stmt_bind_param($stmt, 'ssssssssi', $name,$mobile,$gender,$dob,$ptype,$pnum,$status,$stype,$id);
            }
            mysqli_stmt_execute($stmt);
            $msg = 'Staff record updated.';
        } else {
            $hash = password_hash($pass, PASSWORD_BCRYPT);
            $stmt = mysqli_prepare($conn, "INSERT INTO staff_details (staff_name, staff_mobile_no, staff_gender, staff_dob, proof_type, proof_number, staff_status, staff_type, staff_password) VALUES (?,?,?,?,?,?,?,?,?)");
            mysqli_stmt_bind_param($stmt, 'sssssssss', $name,$mobile,$gender,$dob,$ptype,$pnum,$status,$stype,$hash);
            if (!mysqli_stmt_execute($stmt)) {
                $err = 'Could not save (mobile number or proof number may already be in use).';
            } else {
                $msg = 'Staff added.';
            }
        }
    }
}

$staff = mysqli_query($conn, "SELECT * FROM staff_details ORDER BY staff_id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>HRS | Staff Management</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="app-shell">
  <?php include __DIR__ . '/../partials/sidebar.php'; ?>
  <main class="main">
    <div class="topbar"><h5>Staff Management</h5></div>
    <div class="content">
      <?php if ($msg): ?><div class="alert alert-success"><?php echo h($msg); ?></div><?php endif; ?>
      <?php if ($err): ?><div class="alert alert-danger"><?php echo h($err); ?></div><?php endif; ?>

      <div class="row g-4">
        <div class="col-lg-5">
          <div class="panel panel-body">
            <h6 id="sTitle" class="mb-3">Add Staff</h6>
            <form method="post" id="staffForm">
              <input type="hidden" name="staff_id" id="s_id">
              <div class="mb-2"><label class="form-label">Full Name</label><input class="form-control" name="staff_name" id="s_name" required></div>
              <div class="row">
                <div class="col-6 mb-2"><label class="form-label">Mobile No</label><input class="form-control" name="staff_mobile_no" id="s_mobile" maxlength="10" required></div>
                <div class="col-6 mb-2"><label class="form-label">Gender</label>
                  <select class="form-select" name="staff_gender" id="s_gender"><option>Male</option><option>Female</option><option>Other</option></select>
                </div>
              </div>
              <div class="row">
                <div class="col-6 mb-2"><label class="form-label">Date of Birth</label><input type="date" class="form-control" name="staff_dob" id="s_dob" required></div>
                <div class="col-6 mb-2"><label class="form-label">Role</label>
                  <select class="form-select" name="staff_type" id="s_type">
                    <option>Staff</option>
                    <option>Admin</option>
                    <option>Entry Operator</option>
                    <option>Exit Operator</option>
                  </select>
                  <small class="text-secondary">Entry/Exit Operators only do their own task — unless Emergency Mode is ON.</small>
                </div>
              </div>
              <div class="row">
                <div class="col-6 mb-2"><label class="form-label">Proof Type</label>
                  <select class="form-select" name="proof_type" id="s_ptype"><option>Aadhaar</option><option>PAN Card</option></select>
                </div>
                <div class="col-6 mb-2"><label class="form-label">Proof Number</label><input class="form-control" name="proof_number" id="s_pnum" required></div>
              </div>
              <div class="mb-2"><label class="form-label">Status</label>
                <select class="form-select" name="staff_status" id="s_status"><option>Active</option><option>Inactive</option></select>
              </div>
              <div class="mb-3"><label class="form-label">Login Password <span id="pwHint" class="text-secondary">(leave blank to keep unchanged)</span></label>
                <input type="text" class="form-control" name="staff_password" id="s_pass" placeholder="Set a login password">
              </div>
              <button class="btn btn-amber w-100" name="save_staff">Save</button>
              <button type="button" class="btn btn-outline-light w-100 mt-2" onclick="resetStaff()">Clear / New</button>
            </form>
          </div>
        </div>

        <div class="col-lg-7">
          <div class="panel">
            <div class="panel-header"><strong>All Staff</strong></div>
            <div class="panel-body p-0">
              <table class="table table-dark-msp mb-0">
                <thead><tr><th>Name</th><th>Mobile</th><th>Role</th><th>Status</th><th></th></tr></thead>
                <tbody>
                <?php while ($s = mysqli_fetch_assoc($staff)): ?>
                  <tr>
                    <td><?php echo h($s['staff_name']); ?></td>
                    <td><?php echo h($s['staff_mobile_no']); ?></td>
                    <td><?php echo h($s['staff_type']); ?></td>
                    <td><span class="badge <?php echo $s['staff_status']==='Active'?'badge-active':'badge-inactive'; ?>"><?php echo h($s['staff_status']); ?></span></td>
                    <td class="text-end"><button class="btn btn-sm btn-outline-warning" onclick='editStaff(<?php echo json_encode($s); ?>)'>Edit</button></td>
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
function editStaff(s){
  document.getElementById('sTitle').textContent = 'Edit Staff #' + s.staff_id;
  document.getElementById('s_id').value = s.staff_id;
  document.getElementById('s_name').value = s.staff_name;
  document.getElementById('s_mobile').value = s.staff_mobile_no;
  document.getElementById('s_gender').value = s.staff_gender;
  document.getElementById('s_dob').value = s.staff_dob;
  document.getElementById('s_type').value = s.staff_type;
  document.getElementById('s_ptype').value = s.proof_type;
  document.getElementById('s_pnum').value = s.proof_number;
  document.getElementById('s_status').value = s.staff_status;
  document.getElementById('s_pass').value = '';
  window.scrollTo(0,0);
}
function resetStaff(){
  document.getElementById('staffForm').reset();
  document.getElementById('s_id').value='';
  document.getElementById('sTitle').textContent='Add Staff';
}
</script>
</body>
</html>
