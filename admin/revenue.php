<?php
require_once __DIR__ . '/../config/session.php';
require_admin();

date_default_timezone_set('Asia/Kolkata');

$todayRevenue = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT IFNULL(SUM(parking_charge),0) total
FROM parking_detail
WHERE DATE(vehicle_exit_time)=CURDATE()
"))['total'];

$weekRevenue = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT IFNULL(SUM(parking_charge),0) total
FROM parking_detail
WHERE vehicle_exit_time>=CURDATE()-INTERVAL 6 DAY
"))['total'];

$monthRevenue = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT IFNULL(SUM(parking_charge),0) total
FROM parking_detail
WHERE MONTH(vehicle_exit_time)=MONTH(CURDATE())
AND YEAR(vehicle_exit_time)=YEAR(CURDATE())
"))['total'];

$yearRevenue = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT IFNULL(SUM(parking_charge),0) total
FROM parking_detail
WHERE YEAR(vehicle_exit_time)=YEAR(CURDATE())
"))['total'];


$totalParking = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT COUNT(*) total
FROM parking_detail
WHERE vehicle_exit_time IS NOT NULL
"))['total'];

$avgRevenue = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT IFNULL(AVG(parking_charge),0) avg_rev
FROM parking_detail
WHERE vehicle_exit_time IS NOT NULL
"))['avg_rev'];

$highestRevenue = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT IFNULL(MAX(parking_charge),0) max_rev
FROM parking_detail
WHERE vehicle_exit_time IS NOT NULL
"))['max_rev'];

$lowestRevenue = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT IFNULL(MIN(parking_charge),0) min_rev
FROM parking_detail
WHERE vehicle_exit_time IS NOT NULL
"))['min_rev'];




$yesterdayRevenue = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT IFNULL(SUM(parking_charge),0) total
FROM parking_detail
WHERE DATE(vehicle_exit_time)=CURDATE()-INTERVAL 1 DAY
"))['total'];

if($yesterdayRevenue>0)
{
    $growth = (($todayRevenue-$yesterdayRevenue)/$yesterdayRevenue)*100;
}
else
{
    $growth = 0;
}



/* Lifetime Revenue */

$lifetimeRevenue = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT IFNULL(SUM(parking_charge),0) total
FROM parking_detail
WHERE vehicle_exit_time IS NOT NULL
"))['total'];


/* Top Revenue Floor */

$topFloor = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT
f.floor_name,
IFNULL(SUM(pd.parking_charge),0) revenue
FROM floor_details f
LEFT JOIN parking_detail pd
ON f.floor_id=pd.floor_id
AND pd.vehicle_exit_time IS NOT NULL
GROUP BY f.floor_id
ORDER BY revenue DESC
LIMIT 1
"));


/* Highest Revenue Day */

$bestDay = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT
DATE(vehicle_exit_time) day,
SUM(parking_charge) revenue
FROM parking_detail
WHERE vehicle_exit_time IS NOT NULL
GROUP BY DATE(vehicle_exit_time)
ORDER BY revenue DESC
LIMIT 1
"));


/* Most Parked Vehicle */

$topVehicle = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT
vehicle_type,
COUNT(*) total
FROM parking_detail
GROUP BY vehicle_type
ORDER BY total DESC
LIMIT 1
"));

// $pageTitle = "Revenue Analytics";
// include __DIR__ . '/../partials/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HRS | Revenue Analytics</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">

<link rel="stylesheet" href="<?php echo h(base_url('assets/css/style.css')); ?>">
</head>
<body>
    <div class="app-shell">

    <?php include __DIR__ . '/../partials/sidebar.php'; ?>

    <main class="main">

        <div class="topbar">
            <h5>Revenue Analytics</h5>

            <div class="text-secondary">
                <?php echo date('d M Y, h:i:s A'); ?>
            </div>
        </div>

        <div class="content">

            <!-- Revenue Cards -->
            <div class="row g-3 mb-4">

                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-label">Today's Revenue</div>
                        <div class="stat-value">
                            ₹<?php echo number_format($todayRevenue,2); ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-label">7 Days Revenue</div>
                        <div class="stat-value">
                            ₹<?php echo number_format($weekRevenue,2); ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-label">Monthly Revenue</div>
                        <div class="stat-value">
                            ₹<?php echo number_format($monthRevenue,2); ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-label">Yearly Revenue</div>
                        <div class="stat-value">
                            ₹<?php echo number_format($yearRevenue,2); ?>
                        </div>
                    </div>
                </div>

            </div>


            <!-- Revenue By Date (selected date) -->
            <div class="panel mt-4">

                <div class="panel-header">
                    <h3>Revenue By Date</h3>
                    <div class="d-flex gap-2 align-items-center">
                        <input type="date" id="dateRevPicker" class="form-control form-control-sm" style="max-width:170px" value="<?php echo date('Y-m-d'); ?>">
                        <button id="btnDateRev" class="btn btn-amber btn-sm">View</button>
                    </div>
                </div>

                <div class="panel-body">

                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="stat-card">
                                <div class="stat-label">Total Revenue</div>
                                <div class="stat-value" id="dateRevTotal">₹0.00</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-card">
                                <div class="stat-label">Vehicles Exited</div>
                                <div class="stat-value" id="dateRevVehicles">0</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-card">
                                <div class="stat-label">Average Charge</div>
                                <div class="stat-value" id="dateRevAvg">₹0.00</div>
                            </div>
                        </div>
                    </div>

                    <div style="height:320px;">
                        <canvas id="dateRevChart"></canvas>
                    </div>

                    <div class="row g-3 mt-1">
                        <div class="col-md-6">
                            <table class="table table-dark-msp mb-0">
                                <thead><tr><th>Floor</th><th>Revenue</th><th>Vehicles</th></tr></thead>
                                <tbody id="dateRevByFloor"></tbody>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-dark-msp mb-0">
                                <thead><tr><th>Vehicle Type</th><th>Revenue</th><th>Vehicles</th></tr></thead>
                                <tbody id="dateRevByType"></tbody>
                            </table>
                        </div>
                    </div>

                </div>

            </div>

            <div class="panel mt-4">

    <div class="panel-header">

        <h3>Revenue Insights</h3>

    </div>

    <div class="panel-body">

        <div class="row g-3">

            <div class="col-md-3">

                <div class="stat-card">

                    <div class="stat-label">
                        Top Revenue Floor
                    </div>

                    <div class="stat-value">
                        <?php echo $topFloor['floor_name']; ?>
                    </div>

                    <small class="text-warning">
                        ₹<?php echo number_format($topFloor['revenue'],2); ?>
                    </small>

                </div>

            </div>

            <div class="col-md-3">

                <div class="stat-card">

                    <div class="stat-label">
                        Highest Revenue Day
                    </div>

                    <div class="stat-value">

                        <?php
                        echo $bestDay['day']
                        ? date('d M',strtotime($bestDay['day']))
                        : '-';
                        ?>

                    </div>

                    <small class="text-warning">

                        ₹<?php echo number_format($bestDay['revenue'] ?? 0,2); ?>

                    </small>

                </div>

            </div>

            <div class="col-md-3">

                <div class="stat-card">

                    <div class="stat-label">
                        Most Parked Vehicle
                    </div>

                    <div class="stat-value">

                        <?php echo $topVehicle['vehicle_type']; ?>

                    </div>

                    <small class="text-warning">

                        <?php echo $topVehicle['total']; ?> Vehicles

                    </small>

                </div>

            </div>

            <div class="col-md-3">

                <div class="stat-card">

                    <div class="stat-label">
                        Lifetime Revenue
                    </div>

                    <div class="stat-value">

                        ₹<?php echo number_format($lifetimeRevenue,2); ?>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

            <!-- Revenue Trend -->
            <div class="panel mt-4">

                <div class="panel-header">
                    <h3>Revenue Trend</h3>
                </div>

                <div class="panel-body">

                    <div class="d-flex gap-2 mb-4">

                        <button id="btnToday" class="btn btn-amber">
                            Today
                        </button>

                        <button id="btnWeek" class="btn btn-outline-light">
                            7 Days
                        </button>

                        <button id="btnMonth" class="btn btn-outline-light">
                            1 Month
                        </button>

                        <button id="btnYear" class="btn btn-outline-light">
                            1 Year
                        </button>

                    </div>

                    <div style="height:420px;">
                        <canvas id="revenueChart"></canvas>
                    </div>

                </div>

            </div>

            <!-- Revenue Summary -->
            <div class="panel mt-4">

                <div class="panel-header">
                    <h3>Revenue Summary</h3>
                </div>

                <div class="panel-body">

                    <div class="row g-3">

                        <div class="col-md-3">
                            <div class="stat-card">
                                <div class="stat-label">
                                    Completed Parking
                                </div>
                                <div class="stat-value">
                                    <?php echo $totalParking; ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="stat-card">
                                <div class="stat-label">
                                    Average Revenue
                                </div>
                                <div class="stat-value">
                                    ₹<?php echo number_format($avgRevenue,2); ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="stat-card">
                                <div class="stat-label">
                                    Highest Charge
                                </div>
                                <div class="stat-value">
                                    ₹<?php echo number_format($highestRevenue,2); ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="stat-card">
                                <div class="stat-label">
                                    Lowest Charge
                                </div>
                                <div class="stat-value">
                                    ₹<?php echo number_format($lowestRevenue,2); ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="stat-card">
                                <div class="stat-label">
                                    Revenue Growth
                                </div>
                                <div class="stat-value">
                                    <?php echo number_format($growth,1); ?>%
                                </div>
                            </div>

                        </div>

                    </div>

                </div>

            </div>
            <div class="panel mt-4">

                <div class="panel-header">
                    <h3>Floor Wise Revenue</h3>
                </div>

                <div class="panel-body">

                <div style="height:420px;">

                    <canvas id="floorRevenueChart"></canvas>

                </div>

                </div>

            </div>

            <div class="panel mt-4">

                <div class="panel-header">
                    <h3>Revenue by Vehicle Type</h3>
                </div>

                <div class="panel-body">

                <div style="height:400px">

                    <canvas id="vehicleRevenueChart"></canvas>

                </div>

                </div>

            </div>




            <div class="panel mt-4">

                <div class="panel-header">
                    <h3>Top Revenue Days</h3>
                </div>

                <div class="panel-body">

                <table class="table table-dark-msp">

                    <thead>

                        <tr>
                            <th>Date</th>
                            <th>Revenue</th>
                            <th>Vehicles</th>
                        </tr>

                    </thead>

                    <tbody>

                        <?php
                            $top = mysqli_query($conn,"
                            SELECT
                                DATE(vehicle_exit_time) day,
                                SUM(parking_charge) revenue,
                                COUNT(*) total
                                FROM parking_detail
                                WHERE vehicle_exit_time IS NOT NULL
                                GROUP BY DATE(vehicle_exit_time)
                                ORDER BY revenue DESC
                                LIMIT 10
                            ");

                            while($row=mysqli_fetch_assoc($top))
                            {
                            ?>

                            <tr>

                                <td><?php echo date("d M Y",strtotime($row['day'])); ?></td>

                                <td>
                                    ₹<?php echo number_format($row['revenue'],2); ?>
                                </td>

                                <td>
                                    <?php echo $row['total']; ?>
                                </td>

                            </tr>

                        <?php } ?>

                    </tbody>

                </table>

                </div>

            </div>
            <div class="panel mt-4">

                <div class="panel-header">
                    <h3>Payment Analytics</h3>
                </div>

                <div class="panel-body">

                <div style="height:380px;">
                    <canvas id="paymentChart"></canvas>
                </div>

                </div>

            </div>


        </div>

    </main>

</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>

<script src="<?php echo h(base_url('assets/js/revenue.js')); ?>"></script>
</body>
</html>











