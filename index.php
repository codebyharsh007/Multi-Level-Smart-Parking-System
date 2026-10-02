<?php
require_once __DIR__ . '/config/session.php';
// require_login();

$floors = mysqli_query($conn, "SELECT * FROM floor_details WHERE floor_status='Active' ORDER BY floor_id");
$floors = mysqli_fetch_all($floors, MYSQLI_ASSOC);

///Total floor count...
$totFloors = count($floors);

////Finding floor capacity....
$totCap = 0; foreach ($floors as $f) $totCap += (int)$f['capacity'];

///occupied........
$occRow = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM parking_detail WHERE vehicle_exit_time IS NULL"));
$totOcc = (int)$occRow['c'];

/////Entry........
$todayRow = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM parking_detail WHERE DATE(vehicle_enter_time)=CURDATE()"));
$todayIn = (int)$todayRow['c'];
?>




<?php include 'partials/header.php'; ?>

<div id="home" class="content">

<div class="container">

<div class="panel">

<div class="panel-body text-center py-5">



<div><h2>HRS</h2></div>


<h1 class="display-4 text-warning">

MULTI LEVEL SMART PARKING

</h1>

<p class="lead text-secondary mt-3">

Industrial Smart Parking Solution with
Real-Time Slot Monitoring,
Vehicle Entry,
Vehicle Exit,
Automatic Receipt Generation,
Floor Management and Reports.

</p>

<a href="login.php" class="btn btn-amber btn-lg mt-3">

Staff Login

</a>

</div>

</div>

<div class="content">

      <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
          <div class="stat-card"><div class="stat-label">Floors</div><div class="stat-value"><?php echo $totFloors; ?></div></div>
        </div>
        <div class="col-6 col-md-3">
          <div class="stat-card amber"><div class="stat-label">Total Capacity</div><div class="stat-value"><?php echo $totCap; ?></div></div>
        </div>
        <div class="col-6 col-md-3">
          <div class="stat-card busy"><div class="stat-label">Occupied Now</div><div class="stat-value" id="statOcc"><?php echo $totOcc; ?></div></div>
        </div>
        <div class="col-6 col-md-3">
          <div class="stat-card ok"><div class="stat-label">Entries Today</div><div class="stat-value"><?php echo $todayIn; ?></div></div>
        </div>
      </div>


<section id="features" class="mt-5">

<div class="panel">

<div class="panel-header">

<h3>System Features</h3>
</div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-4">
                <h5 class="text-warning">Vehicle Entry</h5>
                <p>Fast Vehicle Entry with Auto Slot</p>
                <ul>
                    <li>Vehicle Registration</li>
                    <li>Automatic Entry Time</li>
                    <li>Floor Selection</li>
                    <li>Available Slot Allocation</li>
                    <li>Vehicle Type Validation</li>
                    <li>Duplicate Vehicle Check</li>
                    <li>Staff Tracking</li>
                    <li>Entry Receipt Generation</li>
                    <li>Email Receipt</li>
                    <li>Real-Time Slot Status Update</li>
                    <li>Input Validation</li>
                    <li>Database Record Management</li>
                </ul>
                
            </div>

            <div class="col-md-4">
                <h5 class="text-warning">Vehicle Exit</h5>
                <p>Automatic Parking Fee Calculation & Receipt.</p>
                <ul>
                    <li>Vehicle Search</li>
                    <li>Vehicle Details Display</li>
                    <li>Automatic Exit Time</li>
                    <li>Parking Duration Calculation</li>
                    <li>Automatic Parking Fee Calculation</li>
                    <li>Payment Management</li>
                    <li>Exit Staff Tracking</li>
                    <li>Exit Receipt Generation</li>
                    <li>Email Receipt</li>
                    <li>Automatic Slot Release</li>
                    <li>Database Update</li>
                    <li>Parking History</li>
                    <li>Real-Time Dashboard Update</li>
                   
                </ul>
            </div>

            <div class="col-md-4">
                <h5 class="text-warning">Live Parking Status</h5>
                <p>Available & Occupied Slots in Real Time.</p>
                <ul>
                    <li>Real-Time Parking Dashboard</li>
                    <li>Available Slot Count</li>
                    <li>Occupied Slot Count</li>
                    <li>Floor-Wise Parking Status</li>
                    <li>Vehicle Type-Based Slot Status</li>
                    <li>Color-Coded Slot Indicators</li>
                    <li>Automatic Status Refresh</li>
                    <li>Instant Slot Updates</li>
                    <li>Today's Vehicle Entry Count</li>
                    <li>Parking Capacity Overview</li>
                    <li>ast Slot Monitoring</li>
                    <li>Improved Parking Management</li>
                    <li>Real-Time Dashboard Update</li>
                   
                </ul>
            </div>
        </div>
    </div>

</div>

</section>


<section id="about" class="mt-5">

<div class="panel">

<div class="panel-header">

<h3>

About Project

</h3>

</div>

<div class="panel-body">

<p>The <b>Multi Level Parking System (MLPS)</b> is a web-based parking management application developed to automate and simplify vehicle parking operations in a multi-floor parking facility. The system helps administrators and staff efficiently manage vehicle entry, parking slot allocation, vehicle exit, and parking fee calculation.
</p>

<p>
The application provides separate login access for administrators and staff. The administrator can manage floors, parking slots, staff members, and parking charges, while staff members can record vehicle entry and exit details. The system automatically assigns available parking slots based on the selected floor and vehicle type, reducing manual work and improving parking efficiency.
</p>
<p>
The project maintains complete parking records, including vehicle number, vehicle type, driver's details, entry time, exit time, parking duration, payment information, and receipt generation. It also provides a real-time view of available and occupied parking slots, making parking management faster, more accurate, and more organized.
</p>

<p>
This project is developed using <b>HTML, CSS, JavaScript, PHP, MySQL, and AJAX </b>, providing a responsive and user-friendly interface with secure database management.
</p>
<br>
<br>
<h4>Technologies Used</h4>
<br>
<p><b>Frontend:</b> HTML5, CSS3, JavaScript, Bootstrap</p>
<p><b>Backend:</b> PHP</p>
<p><b>Database:</b> MySQL</p>
<p><b>Asynchronous Requests:</b>  AJAX</p>
<p><b>Server:</b> XAMPP (Apache + MySQL)</p>
</div>
</div>     
</section>



<section id="members" class="mt-5">

    <div class="panel">

        <div class="panel-header">
            <h3>Project Created </h3>
        </div>

        <div class="panel-body">

            <div class="row justify-content-center">

                <div class="col-md-5">

                    <div class="member-card text-center">

                        <img src="assets/images/Harsh.jpg"
                             alt="Harsh Singh"
                             class="member-photo">

                        <h3 class="member-name mt-3">
                            Harsh Singh
                        </h3>

                        <span class="member-role">
                            Full Stack PHP Developer
                        </span>

                        <p class="member-desc mt-3">
                            Developed the complete Multi Level Smart Parking
                            System using HTML, CSS, JavaScript, Bootstrap,
                            PHP, MySQL and AJAX. Responsible for designing
                            the database, developing the frontend interface,
                            implementing backend logic, vehicle entry & exit,
                            receipt generation, authentication system,
                            dashboard, reports and real-time parking
                            management.
                        </p>

                        <div class="member-skills">
                            <span>PHP</span>
                            <span>MySQL</span>
                            <span>JavaScript</span>
                            <span>AJAX</span>
                            <span>Bootstrap</span>
                            <span>HTML</span>
                            <span>CSS</span>
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</section>



<section id="contact" class="mt-5 mb-5">

<div class="panel">

<div class="panel-header">

<h3>

Contact

</h3>

</div>

<div class="panel-body">

<p>

<i class="bi bi-envelope-fill text-warning"></i>

harshsingh248203@gmail.com

</p>

<p>

<i class="bi bi-telephone-fill text-warning"></i>

+91-9569436471

</p>

</div>

</div>

</section>

</div>

</div>

<?php include 'partials/footer.php'; ?>