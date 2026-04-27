<?php
session_start();
include '../db_config.php'; 

// 1. Check Login
if (!isset($_SESSION['faculty_id'])) {
    header("Location: ../login_faculty.php");
    exit();
}

$faculty_id = $_SESSION['faculty_id'];
$faculty_name = $_SESSION['faculty_name'] ?? 'Faculty';

// 2. Fetch ARCHIVED Folders (is_archived = 1)
$query = "SELECT * FROM faculty_folders WHERE faculty_id = '$faculty_id' AND is_archived = 1 ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Archived Folders | MoniQR</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <style>
    :root { 
        --bs-maroon: #7B1C2C; 
        --sidebar-width: 85px; 
        --sidebar-expanded: 260px; 
        --transition-speed: 0.3s; 
    }

    body { font-family: 'DM Sans', sans-serif; background-color: #f4f7f6; overflow-x: hidden; }

    /* Sidebar */
    #sidebar { width: var(--sidebar-width); transition: width var(--transition-speed) ease; z-index: 1030; background-color: var(--bs-maroon); overflow-x: hidden; white-space: nowrap; }
    #sidebar:hover { width: var(--sidebar-expanded); }
    .sidebar-logo-container { height: 150px; display: flex; align-items: center; justify-content: center; overflow: hidden; }
    .sidebar-logo-img { width: 150px; transition: all var(--transition-speed) ease; }
    .nav-label { opacity: 0; transition: opacity 0.2s ease; margin-left: 10px; }
    #sidebar:hover .nav-label { opacity: 1; }
    .nav-link { border-left: 4px solid transparent; display: flex; align-items: center; padding: 15px 0; color: rgba(255,255,255,0.7) !important; text-decoration: none; }
    .nav-link i { min-width: var(--sidebar-width); text-align: center; font-size: 1.5rem; }
    .nav-link:hover, .nav-link.active { background-color: rgba(255, 255, 255, 0.1); color: #fff !important; }
    .nav-link.active { border-left-color: #fff; }

    /* Main Content */
    #main { margin-left: var(--sidebar-width); transition: margin-left var(--transition-speed) ease; width: calc(100% - var(--sidebar-width)); }
    #sidebar:hover + #main { margin-left: var(--sidebar-expanded); width: calc(100% - var(--sidebar-expanded)); }

    /* Archive Card */
    .archive-card { 
        position: relative;
        background: #fff; 
        border: 1px solid #eee; 
        border-radius: 25px; 
        transition: all 0.3s ease; 
        padding: 2.5rem 1rem; 
        text-align: center; 
        height: 100%; 
    }
    .archive-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05); }
    .folder-icon { color: #dee2e6; font-size: 4rem; margin-bottom: 1rem; }
    .subject-code { font-weight: 700; font-size: 1.2rem; color: #6c757d; }

    .restore-btn {
        position: absolute;
        top: 15px;
        right: 15px;
        background: #f8f9fa;
        border: none;
        border-radius: 50%;
        width: 38px;
        height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #198754;
        transition: all 0.2s;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        z-index: 10;
    }
    .restore-btn:hover { background: #198754; color: white; transform: scale(1.1); }

    @media (max-width: 768px) {
        #sidebar { width: 0; }
        #main { margin-left: 0; width: 100%; }
    }
  </style>
</head>

<body class="d-flex">
  <nav id="sidebar" class="vh-100 position-fixed start-0 top-0 d-flex flex-column text-white shadow">
    <div class="sidebar-logo-container">
        <img src="../img/logo.png" alt="Logo" class="sidebar-logo-img">
    </div>
    <div class="flex-grow-1 pt-2">
      <a href="dashboard.php" class="nav-link"><i class="bi bi-grid-1x2"></i><span class="nav-label">Dashboard</span></a>
      <a href="archived.php" class="nav-link active"><i class="bi bi-archive"></i><span class="nav-label">Archived Subjects</span></a>
    </div>
    <div class="pb-4">
      <a class="nav-link" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#logoutModal">
        <i class="bi bi-box-arrow-left"></i><span class="nav-label">Sign Out</span>
      </a>
    </div>
  </nav>

  <div id="main" class="flex-grow-1 d-flex flex-column">
    <header class="navbar navbar-expand bg-white border-bottom px-4 shadow-sm" style="height: 80px;">
      <div class="container-fluid px-0">
        <span class="navbar-brand text-dark m-0 fs-3 fw-bold">MoniQR</span>
        <div class="ms-auto d-flex align-items-center gap-4">
          <div class="text-muted d-none d-sm-flex align-items-center">
            <span id="topbar-date"></span><span class="mx-2">|</span><span id="topbar-time"></span>
          </div>
          <div class="dropdown">
            <div class="rounded-circle border d-flex align-items-center justify-content-center bg-light shadow-sm" style="width: 45px; height: 45px; cursor: pointer;" data-bs-toggle="dropdown">
              <i class="bi bi-person-fill fs-4" style="color: var(--bs-maroon);"></i>
            </div>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2 p-2">
              <li class="px-3 py-2 border-bottom mb-2">
                <small class="text-muted d-block">Welcome,</small>
                <span class="fw-bold"><?php echo htmlspecialchars($faculty_name); ?></span>
              </li>
              <li><a class="dropdown-item rounded-2" href="profile.php"><i class="bi bi-person me-2"></i> Profile</a></li>
              <li><a class="dropdown-item rounded-2" href="changepass.php"><i class="bi bi-shield-lock me-2"></i> Password</a></li>
            </ul>
          </div>
        </div>
      </div>
    </header>

    <main class="p-4 p-lg-5">
      <div class="d-flex justify-content-between align-items-center mb-4">
          <h2 class="fw-bold m-0">Archived Folders</h2>
          <span class="badge bg-secondary rounded-pill px-3 py-2"><?php echo mysqli_num_rows($result); ?> Folders</span>
      </div>

      <div class="row g-4">
        <?php if(mysqli_num_rows($result) > 0): ?>
            <?php while($row = mysqli_fetch_assoc($result)): ?>
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="archive-card">
                        <form id="restoreForm<?php echo $row['id']; ?>" action="restore_folder.php" method="POST">
                            <input type="hidden" name="folder_id" value="<?php echo $row['id']; ?>">
                            <button type="button" class="restore-btn" title="Restore to Dashboard" 
                                    onclick="confirmRestore(<?php echo $row['id']; ?>, '<?php echo addslashes($row['folder_name']); ?>')">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </button>
                        </form>

                        <i class="bi bi-folder-fill folder-icon"></i>
                        <div class="subject-code"><?php echo htmlspecialchars($row['folder_name']); ?></div>
                        <div class="text-muted small mt-2">
                            Archived: <?php echo date('M d, Y', strtotime($row['created_at'])); ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <i class="bi bi-archive fs-1 text-muted"></i>
                <p class="text-muted mt-2">No archived folders found.</p>
                <a href="dashboard.php" class="btn btn-sm btn-outline-secondary rounded-pill">Return to Dashboard</a>
            </div>
        <?php endif; ?>
      </div>
    </main>
  </div>

  <div class="modal fade" id="logoutModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
      <div class="modal-content border-0 shadow" style="border-radius: 20px;">
        <div class="modal-body text-center p-4">
          <i class="bi bi-exclamation-circle text-warning" style="font-size: 3rem;"></i>
          <h5 class="mt-3 fw-bold">Sign Out</h5>
          <p class="text-muted mb-4">Log out of your account?</p>
          <div class="d-grid gap-2">
            <a href="../logout.php" class="btn btn-danger rounded-pill">Logout</a>
            <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Cancel</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
  <script>
    // --- SweetAlert Restore Confirmation ---
    function confirmRestore(id, folderName) {
        Swal.fire({
            title: 'Restore Folder?',
            text: `Move "${folderName}" back to your Active Dashboard?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754', // Green for restore
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, restore it!',
            borderRadius: '15px'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('restoreForm' + id).submit();
            }
        });
    }

    // --- Date/Time Clock ---
    function updateDateTime() {
      const now = new Date();
      document.getElementById('topbar-date').textContent = now.toLocaleDateString('en-US', { weekday: 'short', month: 'long', day: 'numeric', year: 'numeric' });
      document.getElementById('topbar-time').textContent = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
    }
    setInterval(updateDateTime, 1000);
    updateDateTime(); 
  </script>
</body>
</html>