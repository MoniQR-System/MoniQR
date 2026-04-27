<?php 
include '../db_config.php'; 

// 1. Fetch Total Students
$total_query = mysqli_query($conn, "SELECT COUNT(*) as count FROM students");
$total_students = ($total_query) ? mysqli_fetch_assoc($total_query)['count'] : 0;

// 2. Optimized: Fetch all attendance statuses in ONE query for today
$attendance_stats_query = "
    SELECT 
        SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) AS present,
        SUM(CASE WHEN status = 'Absent' THEN 1 ELSE 0 END) AS absent,
        SUM(CASE WHEN status = 'Late' THEN 1 ELSE 0 END) AS late,
        SUM(CASE WHEN status = 'Excused' THEN 1 ELSE 0 END) AS excused
    FROM attendance 
    WHERE DATE(scan_time) = CURDATE()";

$stats_result = mysqli_query($conn, $attendance_stats_query);
$stats = mysqli_fetch_assoc($stats_result);

$present_today = $stats['present'] ?? 0;
$absent_today  = $stats['absent'] ?? 0;
$late_today    = $stats['late'] ?? 0;
$excused_today = $stats['excused'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>MoniQR Admin | Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet" />

    <style>
        :root {
            --bs-maroon: #7B1C2C;
            --sidebar-width: 85px;
            --sidebar-expanded: 260px;
            --card-bg: #f8f9fa;
            --transition-speed: 0.3s;
        }

        body { font-family: 'DM Sans', sans-serif; background-color: #ffffff; overflow-x: hidden; }

        /* --- SIDEBAR --- */
        #sidebar {
            width: var(--sidebar-width);
            transition: width var(--transition-speed) ease;
            z-index: 1030;
            background-color: var(--bs-maroon);
            overflow: hidden;
            white-space: nowrap;
        }
        #sidebar:hover { width: var(--sidebar-expanded); }

        .sidebar-logo-container { padding: 25px 5px; display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 140px; }
        .sidebar-logo-container img { width: 150px; height: auto; transition: all var(--transition-speed) ease; }
        #sidebar:hover .sidebar-logo-container img { width: 150px; }

        .nav-link { color: rgba(255, 255, 255, 0.7); display: flex; align-items: center; padding: 15px 0; text-decoration: none; transition: all 0.2s; }
        .nav-link:hover, .nav-link.active { color: #fff; background: rgba(255, 255, 255, 0.1); }
        .nav-link i { min-width: var(--sidebar-width); text-align: center; font-size: 1.4rem; }
        .nav-label { opacity: 0; transition: opacity 0.2s; }
        #sidebar:hover .nav-label { opacity: 1; }

        /* --- MAIN CONTENT --- */
        #main { margin-left: var(--sidebar-width); min-height: 100vh; transition: margin-left var(--transition-speed) ease; }
        #sidebar:hover+#main { margin-left: var(--sidebar-expanded); }

        /* --- HEADER --- */
        .top-navbar { height: 80px; display: flex; align-items: center; justify-content: space-between; padding: 0 40px; border-bottom: 1px solid #eee; background: #fff; }
        .profile-icon { font-size: 2rem; color: #333; cursor: pointer; }

        /* --- STAT CARDS --- */
        .stat-card { background: var(--card-bg); border-radius: 12px; padding: 20px; height: 100%; position: relative; transition: transform 0.2s; border: 1px solid rgba(0, 0, 0, 0.05); }
        .stat-card:hover { transform: translateY(-5px); }
        .stat-title { font-size: 0.85rem; font-weight: 600; color: #666; text-transform: uppercase; letter-spacing: 0.5px; }
        .stat-icon { position: absolute; top: 20px; right: 20px; font-size: 1.2rem; color: var(--bs-maroon); }
        .stat-value { font-size: 2rem; font-weight: 700; margin-top: 10px; }

        .val-present { color: #2D8A4E; }
        .val-absent { color: #BB2D3B; }
        .val-late { color: #D4AF37; }
        .val-excused { color: #4361EE; }

        .content-card { background: var(--card-bg); border-radius: 15px; padding: 25px; min-height: 300px; border: 1px solid rgba(0, 0, 0, 0.05); }
        .badge-recent { background-color: var(--bs-maroon); color: white; padding: 8px 20px; border-radius: 8px 20px 20px 8px; display: inline-flex; align-items: center; gap: 10px; font-size: 0.9rem; margin-bottom: 20px; }
    </style>
</head>

<body>
    <nav id="sidebar" class="vh-100 position-fixed start-0 top-0 d-flex flex-column shadow">
        <div class="sidebar-logo-container">
            <img src="../img/logo.png" alt="Logo">
        </div>
        <div class="flex-grow-1 mt-4">
            <a href="dashboard.php" class="nav-link active"><i class="bi bi-grid-fill"></i><span class="nav-label">Dashboard</span></a>
            <a href="students.php" class="nav-link"><i class="bi bi-mortarboard-fill"></i><span class="nav-label">Students</span></a>
            <a href="faculty.php" class="nav-link"><i class="bi bi-people-fill"></i><span class="nav-label">Faculty</span></a>
            <a href="reports.php" class="nav-link"><i class="bi bi-file-earmark-text-fill"></i><span class="nav-label">Reports</span></a>
        </div>
        <div class="mb-4">
            <a href="logout.php" class="nav-link"><i class="bi bi-box-arrow-left"></i><span class="nav-label">Sign Out</span></a>
        </div>
    </nav>

    <div id="main">
        <header class="top-navbar">
            <h4 class="m-0 fw-bold">MoniQR</h4>
            <div class="d-flex align-items-center gap-4">
                <div class="text-end d-none d-md-block">
                    <div class="fw-bold" id="topbar-date">---</div>
                    <small class="text-muted" id="topbar-time">00:00:00</small>
                </div>
                <i class="bi bi-person-circle profile-icon"></i>
            </div>
        </header>

        <main class="p-4 p-lg-5">
            <div class="mb-4">
                <h2 class="fw-bold mb-1">Welcome Back, Admin</h2>
                <p class="text-muted">Live data from MoniQR database.</p>
            </div>

            <div class="row g-3 mb-5">
                <div class="col-md-2 col-sm-6">
                    <div class="stat-card">
                        <div class="stat-title">Total Students</div>
                        <i class="bi bi-people-fill stat-icon"></i>
                        <div class="stat-value"><?= number_format($total_students); ?></div>
                    </div>
                </div>
                <div class="col-md-2 col-sm-6">
                    <div class="stat-card">
                        <div class="stat-title">Present Today</div>
                        <i class="bi bi-person-check-fill stat-icon"></i>
                        <div class="stat-value val-present"><?= $present_today; ?></div>
                    </div>
                </div>
                <div class="col-md-2 col-sm-6">
                    <div class="stat-card">
                        <div class="stat-title">Absent Today</div>
                        <i class="bi bi-person-x-fill stat-icon"></i>
                        <div class="stat-value val-absent"><?= $absent_today; ?></div>
                    </div>
                </div>
                <div class="col-md-2 col-sm-6">
                    <div class="stat-card">
                        <div class="stat-title">Late Arrivals</div>
                        <i class="bi bi-person-walking stat-icon"></i>
                        <div class="stat-value val-late"><?= $late_today; ?></div>
                    </div>
                </div>
                <div class="col-md-2 col-sm-6">
                    <div class="stat-card">
                        <div class="stat-title">Excused</div>
                        <i class="bi bi-person-vcard-fill stat-icon"></i>
                        <div class="stat-value val-excused"><?= $excused_today; ?></div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-md-12">
                    <div class="content-card shadow-sm">
                        <div class="badge-recent"><i class="bi bi-qr-code"></i> Recent Activity</div>
                        <table class="table mt-3 align-middle">
                            <thead>
                                <tr>
                                    <th>Student ID</th>
                                    <th>Status</th>
                                    <th>Time Scanned</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $recent_query = mysqli_query($conn, "SELECT * FROM attendance ORDER BY scan_time DESC LIMIT 5");
                                while($row = mysqli_fetch_assoc($recent_query)) {
                                    // Logic for dynamic badge colors
                                    $badge_class = 'bg-secondary';
                                    if($row['status'] == 'Present') $badge_class = 'bg-success';
                                    elseif($row['status'] == 'Absent') $badge_class = 'bg-danger';
                                    elseif($row['status'] == 'Late') $badge_class = 'bg-warning text-dark';
                                    elseif($row['status'] == 'Excused') $badge_class = 'bg-primary';

                                    echo "<tr>
                                            <td class='fw-medium'>Student #" . htmlspecialchars($row['student_id']) . "</td>
                                            <td><span class='badge {$badge_class}'>" . htmlspecialchars($row['status']) . "</span></td>
                                            <td class='text-muted'>" . date('h:i A', strtotime($row['scan_time'])) . "</td>
                                          </tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        function updateDateTime() {
            const now = new Date();
            document.getElementById('topbar-date').textContent = now.toLocaleDateString('en-US', { weekday: 'short', month: 'long', day: 'numeric', year: 'numeric' });
            document.getElementById('topbar-time').textContent = now.toLocaleTimeString('en-US');
        }
        setInterval(updateDateTime, 1000);
        updateDateTime(); 
    </script>
</body>
</html>