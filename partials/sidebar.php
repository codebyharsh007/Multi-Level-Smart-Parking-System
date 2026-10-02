<?php
$cur = basename($_SERVER['SCRIPT_NAME']);
function nav_link($href, $label, $icon, $cur) {
    $active = ($cur === basename($href)) ? 'active' : '';
    echo '<a class="nav-link '.$active.'" href="'.h($href).'"><span class="dot"></span>'.$icon.' '.h($label).'</a>';
}
$__emergencyOn = isset($conn) ? is_emergency_mode($conn) : false;
?>
<aside class="sidebar" id="sidebar">
  <div class="brand">
    <div class="brand-mark" style="width:38px;height:38px;font-size:18px;">HRS</div>
    <div>
      <h4>HRS</h4>
      <small>Smart Parking</small>
    </div>
  </div>

  <div id="emergencyBanner" class="emergency-banner" style="display:<?php echo $__emergencyOn ? 'block' : 'none'; ?>">
    🚨 <strong>Emergency Mode ON</strong><br>
    <small>All Entry/Exit operators can do both entry &amp; exit</small>
  </div>

  <div class="nav-section">Operations</div>
  <?php nav_link(base_url('dashboard.php'), 'Dashboard', '📊', $cur); ?>
  <?php nav_link(base_url('vehicle_entry.php'), 'Vehicle In / Out', '🚗', $cur); ?>
  <?php nav_link(base_url('search.php'), 'Search Records', '🔎', $cur); ?>
  <?php nav_link(base_url('reports.php'), 'Reports', '📄', $cur); ?>

  <?php if ($_SESSION['staff_type'] === 'Admin'): ?>
    <div class="nav-section">Admin Setup</div>
    <?php nav_link(base_url('admin/floor_setup.php'), 'Floor Setup', '🏢', $cur); ?>
    <?php nav_link(base_url('admin/vehicle_type.php'), 'Vehicle Types', '🏷️', $cur); ?>
    <?php nav_link(base_url('admin/pricing.php'), 'Parking Pricing', '💲', $cur); ?>
    <?php nav_link(base_url('staff/staff_management.php'), 'Staff Management', '👥', $cur); ?>
    <?php nav_link(base_url('admin/revenue.php'), 'Revenue Analytics', '📈', $cur); ?>
  <?php endif; ?>

  <div class="sidebar-foot">
    <div class="who"><?php echo h($_SESSION['staff_name']); ?></div>
    <div class="role"><?php echo h($_SESSION['staff_type']); ?></div>
    <a href="<?php echo h(base_url('logout.php')); ?>" class="btn btn-sm btn-outline-light w-100 mt-2">Sign Out</a>
  </div>
</aside>
