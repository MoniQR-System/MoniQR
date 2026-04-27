<?php
session_start();
include '../db_config.php';
date_default_timezone_set('Asia/Manila');

if (!isset($_SESSION['student_id'])) {
    header("Location: ../login_student.php");
    exit();
}

$student_id = $_SESSION['student_id'];
$student_name = $_SESSION['student_name'] ?? 'Student';

// UPDATED QUERY: Added is_archived = 0 to filter only active classes
$folder_query = "SELECT 
                    ff.id, 
                    ff.folder_name, 
                    ff.subject_code, 
                    f.name as faculty_name 
                 FROM faculty_folders ff
                 INNER JOIN folder_students fs ON ff.id = fs.folder_id
                 INNER JOIN faculty f ON ff.faculty_id = f.id
                 WHERE fs.student_id = ? AND ff.is_archived = 0
                 ORDER BY ff.folder_name ASC";

$stmt = mysqli_prepare($conn, $folder_query);
mysqli_stmt_bind_param($stmt, "s", $student_id); 
mysqli_stmt_execute($stmt);
$folder_result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MoniQR | Student Class Folder</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet"/>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet"/>

    <style>
        :root { 
            --bs-maroon: #7B1C2C; 
            --sidebar-width: 85px; 
            --sidebar-expanded: 260px; 
            --transition-speed: 0.3s; 
        }

        body { font-family: 'DM Sans', sans-serif; background-color: #f4f7f6; overflow-x: hidden; margin: 0; }
        
        #sidebar { width: var(--sidebar-width); transition: width var(--transition-speed) ease; z-index: 1030; background-color: var(--bs-maroon); overflow-x: hidden; white-space: nowrap; position: fixed; height: 100vh; }
        #sidebar:hover { width: var(--sidebar-expanded); }
        .sidebar-logo-container { height: 150px; display: flex; align-items: center; justify-content: center; }
        .sidebar-logo-img { width: 150px; height: auto; transition: width var(--transition-speed) ease; }
        .nav-link { color: rgba(255,255,255,0.7) !important; display: flex; align-items: center; padding: 15px 0; border-left: 4px solid transparent; transition: all 0.2s ease; text-decoration: none; }
        .nav-link i { min-width: var(--sidebar-width); text-align: center; font-size: 1.6rem; }
        .nav-link:hover, .nav-link.active { color: #fff !important; background: rgba(255,255,255,0.1); border-left-color: white; }
        .nav-label { opacity: 0; transition: opacity 0.2s ease; margin-left: 10px; font-size: 1.1rem; }
        #sidebar:hover .nav-label { opacity: 1; }

        #main { margin-left: var(--sidebar-width); transition: margin-left var(--transition-speed) ease; width: calc(100% - var(--sidebar-width)); min-height: 100vh; }
        #sidebar:hover + #main { margin-left: var(--sidebar-expanded); width: calc(100% - var(--sidebar-expanded)); }

        .navbar { background: white; height: 80px; padding: 0 30px; border-bottom: 1px solid #eee; position: sticky; top: 0; z-index: 1000; }

        .folder-card { 
            width: 260px; height: 280px; background: white; border-radius: 30px; 
            border: 1px solid #f0f0f0; display: flex; flex-direction: column; 
            align-items: center; justify-content: center; transition: 0.3s; 
            cursor: pointer; position: relative; padding: 20px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.03);
        }
        .folder-card:hover { transform: translateY(-8px); border: 2px solid var(--bs-maroon); background: #fdfafb; box-shadow: 0 12px 25px rgba(0,0,0,0.1); }
        .folder-icon { font-size: 4.5rem; color: var(--bs-maroon); margin-bottom: 10px; }
        .subject-badge { font-size: 0.7rem; font-weight: 700; background: #f8f9fa; color: var(--bs-maroon); padding: 4px 12px; border-radius: 50px; border: 1px solid #eee; margin-bottom: 8px; }
        .stat-box { padding: 15px; border-radius: 20px; background: #f8f9fa; text-align: center; border: 1px solid #eee; }

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
      <a href="folders.php" class="nav-link active"><i class="bi bi-journal-bookmark-fill fs-3"></i><span class="nav-label">Class Folder</span></a>      
      <a href="archived.php" class="nav-link"><i class="bi bi-archive"></i><span class="nav-label">Archived</span></a>
    </div>
    <div class="pb-4">
        <a href="../logout.php" class="nav-link"><i class="bi bi-box-arrow-left"></i><span class="nav-label">Sign Out</span></a>
    </div>
</nav>

<div id="main" class="d-flex flex-column">
    <header class="navbar navbar-expand bg-white px-4 shadow-sm">
        <div class="container-fluid px-0">
            <span class="navbar-brand text-dark m-0 fs-3 fw-bold">MoniQR</span>
            <div class="ms-auto d-flex align-items-center gap-4">
                <div class="text-muted d-none d-md-flex align-items-center fw-medium small">
                    <span id="topbar-date"></span> <span class="mx-2">|</span> <span id="topbar-time"></span>
                </div>
                <div class="dropdown">
                    <div class="rounded-circle border d-flex align-items-center justify-content-center bg-light" style="width: 45px; height: 45px; cursor: pointer;" data-bs-toggle="dropdown">
                        <i class="bi bi-person-fill fs-4" style="color: var(--bs-maroon);"></i>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 p-2 mt-2">
                        <li class="px-3 py-2 border-bottom mb-2 text-center">
                            <span class="fw-bold d-block"><?php echo htmlspecialchars($student_name); ?></span>
                            <small class="text-muted">Student</small>
                        </li>
                        <li><a class="dropdown-item rounded-2" href="profile.php"><i class="bi bi-person me-2"></i> Profile</a></li>
                        <li><a class="dropdown-item rounded-2 text-danger" href="../logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </header>

    <main class="p-4 p-lg-5">
        <div class="d-flex justify-content-between align-items-end mb-5">
            <div>
                <h2 class="fw-bold m-0">My Class Folders</h2>
                <p class="text-muted">Select a folder to view your attendance records</p>
            </div>
        </div>

        <div class="d-flex flex-wrap gap-4 justify-content-center justify-content-md-start" id="folderContainer">
            <?php if(mysqli_num_rows($folder_result) > 0): ?>
                <?php while($folder = mysqli_fetch_assoc($folder_result)): ?>
                    <div class="folder-card open-folder-modal" 
                         data-id="<?php echo $folder['id']; ?>" 
                         data-name="<?php echo htmlspecialchars($folder['folder_name']); ?>" 
                         data-faculty="<?php echo htmlspecialchars($folder['faculty_name']); ?>">
                        
                        <span class="subject-badge"><?php echo htmlspecialchars($folder['subject_code']); ?></span>
                        <i class="bi bi-folder-fill folder-icon"></i>
                        
                        <h6 class="fw-bold text-center mb-1"><?php echo htmlspecialchars($folder['folder_name']); ?></h6>
                        <hr class="w-75 my-2 opacity-10">
                        <small class="text-secondary fw-medium"><?php echo htmlspecialchars($folder['faculty_name']); ?></small>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="text-center w-100 py-5">
                    <i class="bi bi-folder-x display-1 text-muted opacity-25"></i>
                    <p class="mt-3 text-muted">You are not enrolled in any active classes yet.</p>
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
                    <div class="col-3"><div class="stat-box"><h6>Total</h6><h4 id="totalCount" class="fw-bold">0</h4></div></div>
                    <div class="col-3"><div class="stat-box text-success"><h6>Present</h6><h4 id="presentCount" class="fw-bold">0</h4></div></div>
                    <div class="col-3"><div class="stat-box text-warning"><h6>Late</h6><h4 id="lateCount" class="fw-bold">0</h4></div></div>
                    <div class="col-3"><div class="stat-box text-danger"><h6>Absent</h6><h4 id="absentCount" class="fw-bold">0</h4></div></div>
                </div>
                <div class="bg-light p-3 rounded-4">
                    <h6 class="fw-bold mb-3 px-2">History Logs</h6>
                    <div id="attendanceHistory" style="max-height: 350px; overflow-y: auto;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    function updateDateTime() {
        const now = new Date();
        document.getElementById('topbar-date').textContent = now.toLocaleDateString('en-US', { weekday: 'short', month: 'long', day: 'numeric', year: 'numeric' });
        document.getElementById('topbar-time').textContent = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
    }
    updateDateTime(); setInterval(updateDateTime, 1000);

    const folderModal = new bootstrap.Modal(document.getElementById('folderModal'));

    document.getElementById('folderContainer').addEventListener('click', function(e) {
        const card = e.target.closest('.open-folder-modal');
        if (!card) return;

        const folderId = card.getAttribute('data-id');
        document.getElementById('modalFolderName').textContent = card.getAttribute('data-name');
        document.getElementById('modalFacultyName').textContent = card.getAttribute('data-faculty');
        document.getElementById('attendanceHistory').innerHTML = '<div class="text-center py-5"><div class="spinner-border text-maroon" role="status"></div></div>';
        
        folderModal.show();

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
                    html = "<div class='text-center py-5 text-muted'><i class='bi bi-info-circle mb-2 fs-2 d-block'></i>No attendance logs found.</div>"; 
                }
                document.getElementById('attendanceHistory').innerHTML = html;
            })
            .catch(err => {
                document.getElementById('attendanceHistory').innerHTML = "<p class='text-danger text-center py-5'>Failed to load data. Please try again.</p>";
            });
    });
});
</script>
</body>
</html>