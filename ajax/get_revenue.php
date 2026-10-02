<?php
require_once __DIR__ . '/../config/session.php';
require_login();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// require_once __DIR__ . '/../config/session.php';
// require_login();
header('Content-Type: application/json');

$type = $_GET['type'] ?? 'week';

$data = [];

switch ($type)
{
    case "today":

        $sql = "
        SELECT
            HOUR(vehicle_exit_time) AS label,
            SUM(parking_charge) AS revenue
        FROM parking_detail
        WHERE DATE(vehicle_exit_time)=CURDATE()
        GROUP BY HOUR(vehicle_exit_time)
        ORDER BY HOUR(vehicle_exit_time)
        ";

        break;

    case "date":

        $selDate = $_GET['date'] ?? date('Y-m-d');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $selDate)) { $selDate = date('Y-m-d'); }

        $stmt = mysqli_prepare($conn, "
            SELECT
                HOUR(vehicle_exit_time) AS label,
                SUM(parking_charge) AS revenue
            FROM parking_detail
            WHERE DATE(vehicle_exit_time) = ?
            GROUP BY HOUR(vehicle_exit_time)
            ORDER BY HOUR(vehicle_exit_time)
        ");
        mysqli_stmt_bind_param($stmt, 's', $selDate);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        while ($row = mysqli_fetch_assoc($result)) { $data[] = $row; }
        echo json_encode($data);
        exit;

    case "month":

        $sql = "
        SELECT
            DATE(vehicle_exit_time) AS label,
            SUM(parking_charge) AS revenue
        FROM parking_detail
        WHERE MONTH(vehicle_exit_time)=MONTH(CURDATE())
        AND YEAR(vehicle_exit_time)=YEAR(CURDATE())
        GROUP BY DATE(vehicle_exit_time)
        ORDER BY DATE(vehicle_exit_time)
        ";

        break;

    case "year":

        $sql = "
        SELECT
            MONTHNAME(vehicle_exit_time) AS label,
            SUM(parking_charge) AS revenue
        FROM parking_detail
        WHERE YEAR(vehicle_exit_time)=YEAR(CURDATE())
        GROUP BY MONTH(vehicle_exit_time)
        ORDER BY MONTH(vehicle_exit_time)
        ";

        break;

    default:

        $sql = "
        SELECT
            DATE(vehicle_exit_time) AS label,
            SUM(parking_charge) AS revenue
        FROM parking_detail
        WHERE vehicle_exit_time >= CURDATE() - INTERVAL 6 DAY
        GROUP BY DATE(vehicle_exit_time)
        ORDER BY DATE(vehicle_exit_time)
        ";
}

$result = mysqli_query($conn, $sql);

while ($row = mysqli_fetch_assoc($result))
{
    $data[] = $row;
}

echo json_encode($data);