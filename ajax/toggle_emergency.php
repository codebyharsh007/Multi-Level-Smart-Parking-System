<?php
require_once __DIR__ . '/../config/session.php';
require_admin(); // Only Admin is allowed to flip Emergency Mode

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok' => false, 'msg' => 'Invalid request']);
    exit;
}

$state = trim($_POST['state'] ?? '');
if (!in_array($state, ['0', '1'], true)) {
    echo json_encode(['ok' => false, 'msg' => 'Invalid state.']);
    exit;
}

if (set_setting($conn, 'emergency_mode', $state)) {
    echo json_encode([
        'ok' => true,
        'emergency_mode' => $state === '1',
        'msg' => $state === '1'
            ? 'Emergency Mode is now ON — all Entry/Exit operators can perform both entry and exit.'
            : 'Emergency Mode is now OFF — Entry/Exit operators are restricted to their own task again.',
    ]);
} else {
    echo json_encode(['ok' => false, 'msg' => 'Database error: ' . mysqli_error($conn)]);
}
