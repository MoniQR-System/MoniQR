<?php
session_start();
include '../db_config.php';

// 1. Set Timezone
date_default_timezone_set('Asia/Manila'); 

// 2. Security Check
if (!isset($_SESSION['student_id'])) {
    header("Location: login_student.php");
    exit();
}

$student_id = $_SESSION['student_id'];
$student_name = $_SESSION['student_name'] ?? 'Student';

// 3. Fetch Overall Statistics
$stats_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status='Present' THEN 1 ELSE 0 END) as present,
    SUM(CASE WHEN status='Late' THEN 1 ELSE 0 END) as late,
    SUM(CASE WHEN status='Absent' THEN 1 ELSE 0 END) as absent
    FROM attendance WHERE student_id = '$student_id'";
$stats_result = mysqli_query($conn, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);

$attendance_rate = ($stats['total'] > 0) ? round(($stats['present'] / $stats['total']) * 100, 1) : 0;

// 4. Prepare Chart Data (LOOP REMOVED)
// We fetch the last 5 months in one go and use array_column to separate them
$chart_sql = "SELECT 
    DATE_FORMAT(scan_time, '%M') as month_name, 
    COUNT(*) as count 
    FROM attendance 
    WHERE student_id = '$student_id' 
    AND status = 'Present' 
    AND scan_time >= DATE_SUB(CURRENT_DATE, INTERVAL 4 MONTH)
    GROUP BY YEAR(scan_time), MONTH(scan_time)
    ORDER BY scan_time ASC";

$chart_query_result = mysqli_fetch_all(mysqli_query($conn, $chart_sql), MYSQLI_ASSOC);

// Instead of a loop, we extract columns directly from the result set
$months = array_column($chart_query_result, 'month_name');
$chart_data = array_column($chart_query_result, 'count');

// 5. Fetch Logs (Using the functional approach to avoid loop in HTML)
$logs_query = "SELECT * FROM attendance WHERE student_id = '$student_id' ORDER BY scan_time DESC LIMIT 6";
$logs_result = mysqli_query($conn, $logs_query);
$logs_array = mysqli_fetch_all($logs_result, MYSQLI_ASSOC);

function render_log($l) {
    $status_class = ($l['status'] == 'Present') ? 'bg-success' : (($l['status'] == 'Late') ? 'bg-warning' : 'bg-danger');
    $time = date('M d, Y h:i A', strtotime($l['scan_time']));
    return "
    <div class='list-group-item px-0 border-0 d-flex justify-content-between align-items-center mb-2'>
        <div>
            <div class='fw-bold small'>Attendance Log</div>
            <div class='text-muted' style='font-size: 11px;'>$time</div>
        </div>
        <span class='badge rounded-pill $status_class'>{$l['status']}</span>
    </div>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>MoniQR | Attendance Report</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet" />

    <style>
        :root { --bs-maroon: #7B1C2C; --sidebar-width: 85px; --sidebar-expanded: 260px; --transition-speed: 0.3s; }
        body { font-family: 'DM Sans', sans-serif; background-color: #f4f7f6; overflow-x: hidden; }
        #sidebar { width: var(--sidebar-width); transition: width var(--transition-speed) ease; z-index: 1030; background-color: var(--bs-maroon); overflow-x: hidden; white-space: nowrap; position: fixed; height: 100vh; }
        #sidebar:hover { width: var(--sidebar-expanded); }
        .sidebar-logo-container { height: 150px; display: flex; align-items: center; justify-content: center; }
        .sidebar-logo-img { width: 150px; height: auto; transition: width var(--transition-speed) ease; }
        #sidebar:hover .sidebar-logo-img { width: 160px; }
        .nav-label { opacity: 0; transition: opacity 0.2s ease; margin-left: 10px; font-size: 1.1rem; }
        #sidebar:hover .nav-label { opacity: 1; }
        .nav-link { color: rgba(255,255,255,0.7) !important; display: flex; align-items: center; padding: 15px 0; border-left: 4px solid transparent; transition: all 0.2s ease; }
        .nav-link i { min-width: var(--sidebar-width); text-align: center; font-size: 1.6rem; }
        .nav-link:hover, .nav-link.active { color: #fff !important; background: rgba(255,255,255,0.1); border-left-color: white; }
        #main { margin-left: var(--sidebar-width); transition: margin-left var(--transition-speed) ease; width: calc(100% - var(--sidebar-width)); min-height: 100vh; }
        #sidebar:hover + #main { margin-left: var(--sidebar-expanded); width: calc(100% - var(--sidebar-expanded)); }
        .stat-card { background: white; border-radius: 20px; padding: 25px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); text-align: center; height: 100%; border: 1px solid #eee; }
        .stat-val { font-size: 2.2rem; font-weight: 700; color: var(--bs-maroon); }
        .chart-container { background: white; border-radius: 25px; padding: 30px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); border: 1px solid #eee; min-height: 400px;}
        @media (max-width: 768px) { #sidebar { width: 0; } #sidebar:hover { width: var(--sidebar-expanded); } #main { margin-left: 0; width: 100%; } }
    </style>
</head>

<body class="d-flex">
<nav id="sidebar" class="shadow d-flex flex-column text-white">
    <div class="sidebar-logo-container">
        <img src="../img/logo.png" alt="Logo" class="sidebar-logo-img">
    </div>
    <div class="flex-grow-1">
      <a href="home.php" class="nav-link active"><i class="bi bi-house-door"></i><span class="nav-label">Home</span></a>      
      <a href="folders.php" class="nav-link"><i class="bi bi-journal-bookmark-fill fs-3"></i><span class="nav-label">Class Folder</span></a>      
      <a href="archived.php" class="nav-link"><i class="bi bi-archive"></i><span class="nav-label">Archived</span></a>
    </div>
    <div class="pb-4">
        <a href="#" class="nav-link" data-bs-toggle="modal" data-bs-target="#logoutModal">
            <i class="bi bi-box-arrow-left"></i><span class="nav-label">Sign Out</span>
        </a>
    </div>
</nav>

    <div id="main" class="d-flex flex-column">
        <header class="navbar navbar-expand bg-white border-bottom px-4 shadow-sm" style="height: 80px;">
            <div class="container-fluid px-0">
                <span class="navbar-brand text-dark m-0 fs-3 fw-bold">MoniQR</span>
                <div class="ms-auto d-flex align-items-center gap-4">
                    <div class="text-muted d-none d-md-flex align-items-center fw-medium">
                        <span id="topbar-date"></span><span class="mx-2 text-secondary">|</span><span id="topbar-time"></span>
                    </div>
                    <div class="dropdown">
                        <div class="rounded-circle border d-flex align-items-center justify-content-center bg-light shadow-sm" style="width: 45px; height: 45px; cursor: pointer;" data-bs-toggle="dropdown">
                            <i class="bi bi-person-fill fs-4" style="color: var(--bs-maroon);"></i>
                        </div>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2 p-2">
                            <li class="px-3 py-2 border-bottom mb-2 text-center">
                                <span class="fw-bold d-block"><?php echo htmlspecialchars($student_name); ?></span>
                                <small class="text-muted">Student</small>
                            </li>
                            <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person me-2"></i> Profile</a></li>
                            <li><a class="dropdown-item text-danger" href="#logoutModal" data-bs-toggle="modal" data-bs-target="#logoutModal"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </header>

        <main class="p-4 p-lg-5">
            <h2 class="fw-bold mb-4">My Attendance Report</h2>

            <div class="row g-4 mb-5">
                <div class="col-md-3"><div class="stat-card"><div class="text-muted small fw-bold mb-1">ATTENDANCE RATE</div><div class="stat-val"><?php echo $attendance_rate; ?>%</div></div></div>
                <div class="col-md-3"><div class="stat-card"><div class="text-muted small fw-bold mb-1 text-uppercase">Total Present</div><div class="stat-val text-success"><?php echo $stats['present'] ?? 0; ?></div></div></div>
                <div class="col-md-3"><div class="stat-card"><div class="text-muted small fw-bold mb-1 text-uppercase">Total Late</div><div class="stat-val text-warning"><?php echo $stats['late'] ?? 0; ?></div></div></div>
                <div class="col-md-3"><div class="stat-card"><div class="text-muted small fw-bold mb-1 text-uppercase">Total Absent</div><div class="stat-val text-danger"><?php echo $stats['absent'] ?? 0; ?></div></div></div>
            </div>

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="chart-container">
                        <h5 class="fw-bold mb-4">Monthly Performance</h5>
                        <canvas id="studentChart"></canvas>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="chart-container">
                        <h5 class="fw-bold mb-4">Latest Logs</h5>
                        <div class="list-group list-group-flush">
                            <?php 
                                // Render without a visible loop block in the HTML
                                echo !empty($logs_array) ? implode('', array_map('render_log', $logs_array)) : '<p class="text-muted text-center py-4">No attendance logs found.</p>';
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <div class="modal fade" id="logoutModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered modal-sm"><div class="modal-content border-0 shadow" style="border-radius: 20px;"><div class="modal-body text-center p-4"><i class="bi bi-box-arrow-right text-danger" style="font-size: 3rem;"></i><h5 class="mt-3 fw-bold">Sign Out</h5><p class="text-muted mb-4">Confirm logout?</p><div class="d-grid gap-2"><a href="../logout.php" class="btn btn-danger rounded-pill" style="background-color: var(--bs-maroon); border: none;">Logout</a><button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Cancel</button></div></div></div></div></div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        function updateDateTime() {
            const now = new Date();
            const dateStr = now.toLocaleDateString('en-US', { weekday: 'short', month: 'long', day: 'numeric', year: 'numeric' });
            const timeStr = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
            document.getElementById('topbar-date').textContent = dateStr;
            document.getElementById('topbar-time').textContent = timeStr;
        }
        updateDateTime(); setInterval(updateDateTime, 1000);

        const ctx = document.getElementById('studentChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($months); ?>,
                datasets: [{
                    label: 'Present Days',
                    data: <?php echo json_encode($chart_data); ?>,
                    borderColor: '#7B1C2C', backgroundColor: 'rgba(123, 28, 44, 0.1)',
                    borderWidth: 3, fill: true, tension: 0.4
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });
    </script>
</body>
</html>