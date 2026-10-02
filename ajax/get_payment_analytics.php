<?php
require_once __DIR__.'/../config/session.php';
require_login();

header('Content-Type: application/json');

$sql = "
SELECT
payment_mode,
COUNT(*) total,
SUM(parking_charge) revenue
FROM parking_detail
WHERE vehicle_exit_time IS NOT NULL
GROUP BY payment_mode
ORDER BY payment_mode
";

$result = mysqli_query($conn,$sql);

$data = [];

while($row = mysqli_fetch_assoc($result))
{
    $data[] = $row;
}

echo json_encode($data);