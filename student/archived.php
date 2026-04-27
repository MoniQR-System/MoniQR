<?php
session_start();
include '../db_config.php';
date_default_timezone_set('Asia/Manila');

// Security: Check if student is logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: ../login_student.php");
    exit();
}

$student_id = $_SESSION['student_id'];
$student_name = $_SESSION['student_name'] ?? 'Student';

$query = "SELECT 
            ff.id, 
            ff.folder_name, 
            ff.subject_code, 
            f.name as faculty_name 
          FROM faculty_folders ff
          INNER JOIN folder_students fs ON ff.id = fs.folder_id
          INNER JOIN faculty f ON ff.faculty_id = f.id
          WHERE fs.student_id = ? AND ff.is_archived = 1
          ORDER BY ff.folder_name ASC";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "s", $student_id); 
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Check if query execution failed to prevent the "Undefined variable $result" error
if (!$result) {
    die("Database query failed: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Archived Classes | MoniQR</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet" />

    <style>
        :root { 
            --bs-maroon: #7B1C2C; 
            --sidebar-width: 85px; 
            --sidebar-expanded: 260px; 
            --transition-speed: 0.3s; 
        }

        body { font-family: 'DM Sans', sans-serif; background-color: #f4f7f6; overflow-x: hidden; }
        
        #sidebar { 
            width: var(--sidebar-width); 
            transition: width var(--transition-speed) ease; 
            z-index: 1030; 
            background-color: var(--bs-maroon); 
            overflow-x: hidden;
            white-space: nowrap;
            position: fixed;
            height: 100vh;
        }
        #sidebar:hover { width: var(--sidebar-expanded); }
        .sidebar-logo-container { height: 150px; display: flex; align-items: center; justify-content: center; }
        .sidebar-logo-img { width: 150px; transition: width var(--transition-speed) ease; }
        .nav-label { opacity: 0; transition: opacity 0.2s ease; margin-left: 10px; }
        #sidebar:hover .nav-label { opacity: 1; }
        .nav-link { 
            color: rgba(255,255,255,0.7) !important;
            display: flex; align-items: center; padding: 15px 0;
            border-left: 4px solid transparent; text-decoration: none;
        }
        .nav-link i { min-width: var(--sidebar-width); text-align: center; font-size: 1.6rem; }
        .nav-link:hover, .nav-link.active { color: #fff !important; background: rgba(255,255,255,0.1); border-left-color: white; }

        #main { margin-left: var(--sidebar-width); transition: margin-left var(--transition-speed) ease; width: calc(100% - var(--sidebar-width)); min-height: 100vh; }
        #sidebar:hover + #main { margin-left: var(--sidebar-expanded); width: calc(100% - var(--sidebar-expanded)); }

        .archive-card { 
            background: #ffffff; 
            border: 1px solid #e0e0e0; 
            border-radius: 25px; 
            padding: 2rem 1rem; 
            text-align: center; 
            transition: all 0.3s ease; 
            cursor: pointer; 
            height: 100%; 
            position: relative;
            overflow: hidden;
        }
        .archive-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.05); border-color: var(--bs-maroon); }
        .folder-icon { color: #dee2e6; font-size: 4rem; margin-bottom: 10px; transition: color 0.3s; }
        .archive-card:hover .folder-icon { color: var(--bs-maroon); opacity: 0.5; }
        .subject-code { font-weight: 700; font-size: 1.1rem; color: #495057; }
        .badge-archived { position: absolute; top: 15px; right: 15px; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 1px; }

        .stat-card { background: #f8f9fa; border-radius: 15px; padding: 12px; text-align: center; }
        .stat-value { font-size: 1.5rem; font-weight: 800; }
        .history-container { background: #ffffff; border: 1px solid #eee; border-radius: 20px; padding: 15px; margin-top: 15px; max-height: 350px; overflow-y: auto; }

        @media (max-width: 768px) {
            #sidebar { width: 0; }
            #main { margin-left: 0; width: 100%; }
        }
    </style>
</head>

<body class="d-flex">
    <nav id="sidebar" class="shadow d-flex flex-column text-white">
        <div class="sidebar-logo-container">
            <img src="../img/logo.png" alt="Logo" class="sidebar-logo-img">
        </div>
        <div class="flex-grow-1">
            <a href="home.php" class="nav-link"><i class="bi bi-house-door"></i><span class="nav-label">Home</span></a>      
            <a href="folders.php" class="nav-link"><i class="bi bi-journal-bookmark-fill"></i><span class="nav-label">Class Folder</span></a>      
            <a href="archived.php" class="nav-link active"><i class="bi bi-archive"></i><span class="nav-label">Archived</span></a>
        </div>
        <div class="pb-4">
            <a href="../logout.php" class="nav-link"><i class="bi bi-box-arrow-left"></i><span class="nav-label">Sign Out</span></a>
        </div>
    </nav>

    <div id="main" class="d-flex flex-column">
        <header class="navbar navbar-expand bg-white border-bottom px-4 shadow-sm" style="height: 80px;">
            <div class="container-fluid px-0">
                <span class="navbar-brand text-dark m-0 fs-3 fw-bold">MoniQR</span>
                <div class="ms-auto d-flex align-items-center gap-4">
                    <div class="text-muted d-none d-md-flex align-items-center fw-medium small">
                        <span id="topbar-date"></span><span class="mx-2 text-secondary">|</span><span id="topbar-time"></span>
                    </div>
                    <div class="dropdown">
                        <div class="rounded-circle border d-flex align-items-center justify-content-center bg-light" style="width: 45px; height: 45px; cursor: pointer;" data-bs-toggle="dropdown">
                            <i class="bi bi-person-fill fs-4" style="color: var(--bs-maroon);"></i>
                        </div>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2 p-2">
                            <li class="px-3 py-2 border-bottom mb-2 text-center">
                                <span class="fw-bold d-block"><?php echo htmlspecialchars($student_name); ?></span>
                            </li>
                            <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person me-2"></i> Profile</a></li>
                            <li><a class="dropdown-item text-danger" href="#logoutModal" data-bs-toggle="modal" data-bs-target="#logoutModal"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
                            </ul>
                    </div>
                </div>
            </div>
        </header>

        <main class="p-4 p-lg-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold m-0">Archived Classes</h2>
                <p class="text-muted m-0 small">Records of completed or archived subjects</p>
            </div>

            <div class="row g-4" id="folderContainer">
                <?php if(mysqli_num_rows($result) > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($result)): 
                        $folder_id = $row['id'];
                    ?>
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="archive-card open-folder-modal" 
                             data-id="<?php echo $folder_id; ?>" 
                             data-name="<?php echo htmlspecialchars($row['folder_name']); ?>" 
                             data-faculty="<?php echo htmlspecialchars($row['faculty_name']); ?>">
                            <span class="badge bg-secondary opacity-50 badge-archived">Archived</span>
                            <i class="bi bi-folder2-open folder-icon"></i>
                            <div class="subject-code text-uppercase"><?php echo htmlspecialchars($row['subject_code']); ?></div>
                            <div class="text-muted small mb-3"><?php echo htmlspecialchars($row['folder_name']); ?></div>
                            <div class="small fw-bold"><i class="bi bi-person-circle me-1"></i><?php echo htmlspecialchars($row['faculty_name']); ?></div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12 text-center py-5">
                        <i class="bi bi-archive fs-1 text-muted opacity-25"></i>
                        <p class="text-muted mt-3">You have no archived subjects yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <div class="modal fade" id="folderModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 30px;">
                <div class="modal-header border-0 p-4 pb-0">
                    <div>
                        <h4 class="fw-bold mb-0" id="modalFolderName"></h4>
                        <p class="text-muted mb-0" id="modalFacultyName"></p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3 mb-4">
                        <div class="col-3"><div class="stat-box stat-card"><h6>Total</h6><h4 id="totalCount" class="fw-bold">0</h4></div></div>
                        <div class="col-3"><div class="stat-box stat-card text-success"><h6>Present</h6><h4 id="presentCount" class="fw-bold">0</h4></div></div>
                        <div class="col-3"><div class="stat-box stat-card text-warning"><h6>Late</h6><h4 id="lateCount" class="fw-bold">0</h4></div></div>
                        <div class="col-3"><div class="stat-box stat-card text-danger"><h6>Absent</h6><h4 id="absentCount" class="fw-bold">0</h4></div></div>
                    </div>
                    <div class="bg-light p-3 rounded-4">
                        <h6 class="fw-bold mb-3 px-2">Final Attendance History</h6>
                        <div id="attendanceHistory" class="history-container"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="logoutModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow" style="border-radius: 20px;">
                <div class="modal-body text-center p-4">
                    <i class="bi bi-box-arrow-right text-danger" style="font-size: 3rem;"></i>
                    <h5 class="mt-3 fw-bold">Sign Out</h5>
                    <p class="text-muted mb-4">Confirm logout?</p>
                    <div class="d-grid gap-2">
                        <a href="../logout.php" class="btn btn-danger rounded-pill" style="background-color: var(--bs-maroon); border: none;">Logout</a>
                        <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script>
        function updateDateTime() {
            const now = new Date();
            document.getElementById('topbar-date').textContent = now.toLocaleDateString('en-US', { weekday: 'short', month: 'long', day: 'numeric', year: 'numeric' });
            document.getElementById('topbar-time').textContent = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        }
        setInterval(updateDateTime, 1000); updateDateTime();

        const folderModal = new bootstrap.Modal(document.getElementById('folderModal'));

        document.getElementById('folderContainer').addEventListener('click', function(e) {
            const card = e.target.closest('.open-folder-modal');
            if (!card) return;

            const folderId = card.getAttribute('data-id');
            document.getElementById('modalFolderName').textContent = card.getAttribute('data-name');
            document.getElementById('modalFacultyName').textContent = card.getAttribute('data-faculty');
            document.getElementById('attendanceHistory').innerHTML = '<div class="text-center py-5"><div class="spinner-border text-danger" role="status"></div></div>';
            
            folderModal.show();

            // Reusing your working fetch call from folders.php
            fetch(`get_student_attendance.php?folder_id=${folderId}`)
                .then(res => res.json())
                .then(data => {
                    document.getElementById('totalCount').textContent = data.total;
                    document.getElementById('presentCount').textContent = data.present;
                    document.getElementById('lateCount').textContent = data.late;
                    document.getElementById('absentCount').textContent = data.absent;

                    let html = "";
                    if(data.history && data.history.length > 0) {
                        data.history.forEach(item => {
                            let color = item.status === "Present" ? "success" : (item.status === "Late" ? "warning" : "danger");
                            html += `
                            <div class="d-flex justify-content-between align-items-center p-3 border-bottom bg-white mb-2 rounded-3 mx-2 shadow-sm">
                                <span>
                                    <i class="bi bi-calendar3 me-2 text-muted"></i>${item.date} 
                                    <small class="text-muted d-block ms-4">${item.time}</small>
                                </span>
                                <span class="badge bg-${color}-subtle text-${color} border border-${color} px-3 py-2 rounded-pill">${item.status}</span>
                            </div>`;
                        });
                    } else { 
                        html = "<div class='text-center py-5 text-muted'>No logs found.</div>"; 
                    }
                    document.getElementById('attendanceHistory').innerHTML = html;
                });
        });
    </script>
</body>
</html>