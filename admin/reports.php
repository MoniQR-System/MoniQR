<?php 
include '../db_config.php'; 

// --- HANDLE DELETE REPORT LOG ---
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    mysqli_query($conn, "DELETE FROM reports WHERE id = $id");
    header("Location: reports.php?msg=log_deleted");
    exit();
}

// Fetch reports from database (ORDER BY latest first)
$query = "SELECT * FROM reports ORDER BY generated_at DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>MoniQR Admin | Reports</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet" />

    <style>
        :root {
            --bs-maroon: #7B1C2C;
            --sidebar-width: 85px;
            --sidebar-expanded: 260px;
            --transition-speed: 0.3s;
            --bg-light-grey: #f8f9fa;
        }
        body { font-family: 'DM Sans', sans-serif; background-color: #ffffff; color: #212529; overflow-x: hidden; }
        #sidebar { width: var(--sidebar-width); transition: width var(--transition-speed) ease; z-index: 1030; background-color: var(--bs-maroon); overflow: hidden; white-space: nowrap; height: 100vh; position: fixed; left: 0; top: 0; display: flex; flex-direction: column; }
        #sidebar:hover { width: var(--sidebar-expanded); }
        .sidebar-logo-container { padding: 25px 0; display: flex; align-items: center; justify-content: center; min-height: 150px; }
        .sidebar-logo-container img { width: 150px; height: auto; }
        .nav-link { color: rgba(255, 255, 255, 0.6); display: flex; align-items: center; padding: 16px 0; text-decoration: none; transition: 0.2s; }
        .nav-link:hover, .nav-link.active { color: #fff; background: rgba(255, 255, 255, 0.1); }
        .nav-link i { min-width: var(--sidebar-width); text-align: center; font-size: 1.4rem; }
        .nav-label { opacity: 0; transition: opacity 0.2s; font-weight: 500; }
        #sidebar:hover .nav-label { opacity: 1; }
        #main { margin-left: var(--sidebar-width); min-height: 100vh; transition: margin-left var(--transition-speed) ease; }
        #sidebar:hover+#main { margin-left: var(--sidebar-expanded); }
        .top-navbar { height: 80px; display: flex; align-items: center; justify-content: space-between; padding: 0 40px; border-bottom: 1px solid #f0f0f0; background: #fff; }
        .content-body { padding: 40px; }
        .reports-main-box { background-color: #f1f3f5; border-radius: 20px; border: 1px solid #e9ecef; min-height: 500px; padding: 30px; }
        .report-item { background-color: #e9ecef; border-radius: 16px; transition: all 0.3s ease; text-decoration: none; color: inherit; display: flex; align-items: center; padding: 1.2rem; margin-bottom: 1rem; position: relative; }
        .report-item:hover { background-color: #dee2e6; transform: translateX(5px); }
        .report-icon-box { width: 45px; height: 45px; background-color: var(--bs-maroon); color: white; display: flex; align-items: center; justify-content: center; border-radius: 12px; font-size: 1.4rem; flex-shrink: 0; margin-right: 1.2rem; }
        .report-text { flex-grow: 1; }
        .report-text p { font-size: 15px; font-weight: 500; margin: 0; color: #2d3436; }
        .report-text small { font-size: 13px; color: #636e72; }
        .btn-delete-log { color: #adb5bd; transition: 0.2s; background: none; border: none; padding: 10px; }
        .btn-delete-log:hover { color: #dc3545; }
    </style>
</head>

<body>
    <nav id="sidebar" class="shadow">
        <div class="sidebar-logo-container"><img src="../img/logo.png" alt="Logo"></div>
        <div class="flex-grow-1 mt-4">
            <a href="dashboard.php" class="nav-link"><i class="bi bi-grid-fill"></i><span class="nav-label">Dashboard</span></a>
            <a href="students.php" class="nav-link"><i class="bi bi-mortarboard-fill"></i><span class="nav-label">Students</span></a>
            <a href="faculty.php" class="nav-link"><i class="bi bi-people-fill"></i><span class="nav-label">Faculty</span></a>
            <a href="reports.php" class="nav-link active"><i class="bi bi-file-earmark-text-fill"></i><span class="nav-label">Reports</span></a>
        </div>
        <div class="mb-4"><a href="logout.php" class="nav-link"><i class="bi bi-box-arrow-left"></i><span class="nav-label">Sign Out</span></a></div>
    </nav>

    <div id="main">
        <header class="top-navbar">
            <h4 class="m-0 fw-bold">MoniQR</h4>
            <div class="d-flex align-items-center gap-4">
                <div class="text-end d-none d-md-block">
                    <div class="fw-bold" id="topbar-date">---</div>
                    <small class="text-muted" id="topbar-time">00:00:00</small>
                </div>
                <i class="bi bi-person-circle" style="font-size: 2rem;"></i>
            </div>
        </header>

        <main class="content-body">
            <div class="page-header mb-5">
                <h1 class="fw-bold">Reports Activity</h1>
                <p class="text-muted">History of all generated attendance reports.</p>
            </div>

            <div class="reports-main-box">
                <div class="report-list">
                    <?php if(mysqli_num_rows($result) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($result)): ?>
                        <div class="report-item shadow-sm">
                            <div class="report-icon-box">
                                <i class="bi bi-file-earmark-check-fill"></i>
                            </div>
                            <div class="report-text">
                                <p>
                                    <strong><?php echo htmlspecialchars($row['faculty_name']); ?></strong> 
                                    generated the <u><?php echo htmlspecialchars($row['report_type']); ?></u> 
                                    for <strong><?php echo htmlspecialchars($row['course_code']); ?></strong> 
                                    (<?php echo htmlspecialchars($row['month_year']); ?>).
                                </p>
                                <small><i class="bi bi-clock me-1"></i> <?php echo date('F d, Y | h:i A', strtotime($row['generated_at'])); ?></small>
                            </div>
                            <a href="reports.php?delete_id=<?php echo $row['id']; ?>" 
                               class="btn-delete-log" 
                               onclick="return confirm('Remove this report log?')">
                                <i class="bi bi-x-circle-fill"></i>
                            </a>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="bi bi-cloud-slash text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-3">No report activity recorded yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script>
        function updateDateTime() {
            const now = new Date();
            document.getElementById('topbar-date').textContent = now.toLocaleDateString('en-US', { weekday: 'short', month: 'long', day: 'numeric', year: 'numeric' });
            document.getElementById('topbar-time').textContent = now.toLocaleTimeString('en-US');
        }
        setInterval(updateDateTime, 1000); updateDateTime();
    </script>
</body>
</html>