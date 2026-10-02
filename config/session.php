<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/db.php';

/** Redirect to login if nobody is logged in */
function require_login() {
    if (empty($_SESSION['staff_id'])) {
        header('Location: ' . base_url('login.php'));
        exit;
    }
}

/** Redirect non-admins away from admin-only pages */
function require_admin() {
    require_login();
    if ($_SESSION['staff_type'] !== 'Admin') {
        header('Location: ' . base_url('dashboard.php?err=forbidden'));
        exit;
    }
}

/** Build an absolute-from-root URL so links work from any sub-folder */
function base_url($path = '') {
    $root = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
    // If we are inside /admin or /staff or /ajax, climb back to project root
    foreach (['/admin', '/staff', '/ajax', '/receipts'] as $sub) {
        if (substr($root, -strlen($sub)) === $sub) {
            $root = substr($root, 0, -strlen($sub));
            break;
        }
    }
    return $root . '/' . ltrim($path, '/');
}

function h($str) { return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8'); }

/* ---------------------------------------------------------------
 * App settings (generic key/value store) — used for Emergency Mode
 * --------------------------------------------------------------- */

/** Read a setting value from the `settings` table (returns $default if missing). */
function get_setting($conn, $key, $default = null) {
    $stmt = mysqli_prepare($conn, "SELECT setting_value FROM settings WHERE setting_key=? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 's', $key);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    return $row ? $row['setting_value'] : $default;
}

/** Create/update a setting value in the `settings` table. */
function set_setting($conn, $key, $value) {
    $stmt = mysqli_prepare($conn, "INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
                                    ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    mysqli_stmt_bind_param($stmt, 'ss', $key, $value);
    return mysqli_stmt_execute($stmt);
}

/** Is Emergency Mode currently switched ON? */
function is_emergency_mode($conn) {
    return get_setting($conn, 'emergency_mode', '0') === '1';
}

/* ---------------------------------------------------------------
 * Entry / Exit Operator permissions
 *
 * - Admin and (legacy) Staff accounts can always do both Entry & Exit.
 * - "Entry Operator" can only create new vehicle entries.
 * - "Exit Operator" can only check vehicles out.
 * - The moment Emergency Mode is switched ON (by Admin, from the
 *   Dashboard), BOTH operator types can do BOTH entry and exit —
 *   this restriction is lifted for as long as Emergency Mode stays on.
 * --------------------------------------------------------------- */

/** Can the currently logged-in staff member perform a vehicle ENTRY right now? */
function can_do_entry($conn) {
    $type = $_SESSION['staff_type'] ?? '';
    if (in_array($type, ['Admin', 'Staff', 'Entry Operator'], true)) return true;
    if ($type === 'Exit Operator' && is_emergency_mode($conn)) return true;
    return false;
}

/** Can the currently logged-in staff member perform a vehicle EXIT right now? */
function can_do_exit($conn) {
    $type = $_SESSION['staff_type'] ?? '';
    if (in_array($type, ['Admin', 'Staff', 'Exit Operator'], true)) return true;
    if ($type === 'Entry Operator' && is_emergency_mode($conn)) return true;
    return false;
}
