<?php
require_once __DIR__.'/../config/session.php';
require_login();

header('Content-Type: application/json');

$sql = "
SELECT
f.floor_name,
IFNULL(SUM(pd.parking_charge),0) revenue
FROM floor_details f
LEFT JOIN parking_detail pd
ON f.floor_id = pd.floor_id
AND pd.vehicle_exit_time IS NOT NULL
GROUP BY f.floor_id
ORDER BY f.floor_id
";

$result = mysqli_query($conn,$sql);

$data = [];

while($row = mysqli_fetch_assoc($result))
{
    $data[] = $row;
}

echo json_encode($data);