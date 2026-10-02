<?php
require_once __DIR__ . '/config/session.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['staff_name'] ?? '');
    $pass = $_POST['staff_password'] ?? '';

    if ($name === '' || $pass === '') {
        $error = 'Please enter both name and password.';
    } else {
        $stmt = mysqli_prepare($conn, "SELECT staff_id, staff_name, staff_password, staff_type, staff_status
                                        FROM staff_details WHERE staff_name = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, 's', $name);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($res);

        if (!$user) {
            $error = 'Invalid name or password.';
        } elseif ($user['staff_status'] !== 'Active') {
            $error = 'This account has been deactivated. Contact Admin.';
        } elseif (!password_verify($pass, $user['staff_password'])) {
            $error = 'Invalid name or password.';
        } else {
            $_SESSION['staff_id']   = $user['staff_id'];
            $_SESSION['staff_name'] = $user['staff_name'];
            $_SESSION['staff_type'] = $user['staff_type'];
            header('Location: dashboard.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>HRS | Sign In</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-body">
  <div class="auth-wrap">
    <div class="auth-card shadow-lg">
      <div class="auth-brand">
        <div class="brand-mark">HRS</div>
        <div>
          <h1>HRS</h1>
          <span>Multi-Level Smart Parking</span>
        </div>
      </div>

      <?php if ($error): ?>
        <div class="alert alert-danger py-2"><?php echo h($error); ?></div>
      <?php endif; ?>


      <?php if (!file_exists(__DIR__.'/install.lock')): ?>
        <div class="alert alert-warning py-2">
          No accounts found yet. <a href="install.php">Run first-time setup</a> to create the default Admin / Staff logins.
        </div>
      <?php endif; ?>

      <form method="post" autocomplete="off">
        <div class="mb-3">
          <label class="form-label">Staff Name</label>
          <input type="text" name="staff_name" class="form-control form-control-lg" placeholder="e.g. System Admin" required autofocus>
        </div>
        <div class="mb-4">
          <label class="form-label">Password</label>
          <input type="password" name="staff_password" class="form-control form-control-lg" placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;" required>
        </div>
        <button class="btn btn-warning btn-lg w-100 fw-semibold">Sign In</button>
      </form>


      <p class="auth-foot">Floor Managers log vehicle entry / exit &middot; Admins configure floors, pricing &amp; staff</p>
    </div>

    
    <div style="text-align:center; margin-top:20px;">
    <a href="index.php"
       style="display:inline-block;
              background:#F2A93B;
              color:black;
              padding:10px 20px;
              text-decoration:none;
              border-radius:6px;">
        Go Back
    </a>
    </div>



  </div>
</body>
</html>
