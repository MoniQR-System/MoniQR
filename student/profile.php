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

$sid = $_SESSION['student_id'];
$msg = "";

// Profile Update Logic
if (isset($_POST['update_profile'])) {
    $mobile = mysqli_real_escape_string($conn, $_POST['mobile']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $res_addr = mysqli_real_escape_string($conn, $_POST['res_address']);
    $perm_addr = mysqli_real_escape_string($conn, $_POST['perm_address']);

    $update_query = "UPDATE students SET 
                    mobile = '$mobile', 
                    email = '$email', 
                    residential_address = '$res_addr', 
                    permanent_address = '$perm_addr' 
                    WHERE student_id = '$sid'";

    if (mysqli_query($conn, $update_query)) {
        $msg = "<div class='alert alert-success py-2 mb-3 shadow-sm'><i class='bi bi-check-circle-fill me-2'></i>Profile updated successfully!</div>";
    } else {
        $msg = "<div class='alert alert-danger py-2 mb-3 shadow-sm'>Error updating profile: " . mysqli_error($conn) . "</div>";
    }
}

// Fetch Current Data
$fetch_query = "SELECT * FROM students WHERE student_id = '$sid'";
$row = mysqli_fetch_assoc(mysqli_query($conn, $fetch_query));
$student_name = $row['first_name'] . " " . $row['last_name'];
$full_name_upper = strtoupper($row['last_name'] . ", " . $row['first_name'] . " " . ($row['middle_name'] ?? ""));
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>MoniQR | Student Profile</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet" />

  <style>
    /* STYLES MATCHING REPORT.PHP EXACTLY */
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
    
    .sidebar-logo-img { 
        width: 150px; 
        height: auto;
        transition: width var(--transition-speed) ease; 
    }
    #sidebar:hover .sidebar-logo-img { width: 160px; }

    .nav-label { opacity: 0; transition: opacity 0.2s ease; margin-left: 10px; font-size: 1.1rem; }
    #sidebar:hover .nav-label { opacity: 1; }

    .nav-link { 
        color: rgba(255,255,255,0.7) !important;
        display: flex; align-items: center; padding: 15px 0;
        border-left: 4px solid transparent; transition: all 0.2s ease;
        text-decoration: none;
    }
    .nav-link i { min-width: var(--sidebar-width); text-align: center; font-size: 1.6rem; }
    .nav-link:hover, .nav-link.active { color: #fff !important; background: rgba(255,255,255,0.1); border-left-color: white; }

    #main { 
        margin-left: var(--sidebar-width); 
        transition: margin-left var(--transition-speed) ease; 
        width: calc(100% - var(--sidebar-width));
        min-height: 100vh;
    }
    #sidebar:hover + #main { 
        margin-left: var(--sidebar-expanded); 
        width: calc(100% - var(--sidebar-expanded));
    }

    .navbar { height: 80px; }

    /* Profile Specific Cards */
    .profile-card { background: #fff; border-radius: 20px; border: 1px solid #eee; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
    .profile-header { padding: 1.5rem; background: #f8f9fa; border-bottom: 1px solid #eee; }
    .profile-name-bar { padding: 1.2rem 1.5rem; border-bottom: 1px solid #eee; }
    .profile-name-bar h5 { color: var(--bs-maroon); font-weight: 700; margin: 0; }
    .profile-body { padding: 2.5rem; }
    .field-label { color: #666; font-size: 0.85rem; font-weight: 600; text-uppercase: tracking-wide; }
    .field-value { font-weight: 700; color: #222; font-size: 1rem; }
    
    .btn-save { background-color: var(--bs-maroon); color: white; padding: 0.8rem 3rem; border-radius: 50px; border: none; font-weight: 600; transition: 0.3s; }
    .btn-save:hover { background-color: #5a1420; transform: translateY(-2px); box-shadow: 0 4px 15px rgba(0,0,0,0.15); }

    @media (max-width: 768px) {
        #sidebar { width: 0; }
        #sidebar:hover { width: var(--sidebar-expanded); }
        #main { margin-left: 0; width: 100%; }
    }
  </style>
</head>

<body class="d-flex">
  <!-- SIDEBAR (Matching Report.php) -->
  <nav id="sidebar" class="shadow d-flex flex-column text-white">
    <div class="sidebar-logo-container">
        <img src="../img/logo.png" alt="Logo" class="sidebar-logo-img">
    </div>
    
    <div class="flex-grow-1">
      <a href="dashboard.php" class="nav-link py-3 px-0">
        <div style="min-width: var(--sidebar-width);" class="text-center"><i class="bi bi-grid-1x2 fs-3"></i></div>
        <span class="nav-label">Dashboard</span>
      </a>
      <a href="report.php" class="nav-link py-3 px-0">
        <div style="min-width: var(--sidebar-width);" class="text-center"><i class="bi bi-clipboard-data fs-3"></i></div>
        <span class="nav-label">Reports</span>
      </a>
      <a href="archived.php" class="nav-link py-3 px-0">
        <div style="min-width: var(--sidebar-width);" class="text-center"><i class="bi bi-archive fs-3"></i></div>
        <span class="nav-label">Archived Subject</span>
      </a>
    </div>

    <div class="pb-4">
        <a href="#" class="nav-link" data-bs-toggle="modal" data-bs-target="#logoutModal">
            <i class="bi bi-box-arrow-left"></i><span class="nav-label">Sign Out</span>
        </a>
    </div>
  </nav>

  <div id="main" class="d-flex flex-column">
    <!-- NAVBAR (Matching Report.php) -->
    <header class="navbar navbar-expand bg-white border-bottom px-4 shadow-sm">
        <div class="container-fluid px-0">
            <span class="navbar-brand text-dark m-0 fs-3 fw-bold">MoniQR</span>
            <div class="ms-auto d-flex align-items-center gap-4">
                <div class="text-muted d-none d-md-flex align-items-center fw-medium">
                    <span id="topbar-date"></span>
                    <span class="mx-2 text-secondary">|</span>
                    <span id="topbar-time"></span>
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
                        <li><a class="dropdown-item" href="student_profile.php"><i class="bi bi-person me-2"></i> Profile</a></li>
                        <li><a class="dropdown-item" href="#logoutModal" data-bs-toggle="modal" data-bs-target="#logoutModal"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </header>

    <main class="p-4 p-lg-5">
      <?php echo $msg; ?>
      
      <div class="profile-card">
        <div class="profile-header"><h4 class="fw-bold mb-0">Personal Data</h4></div>
        <div class="profile-name-bar"><h5><?php echo $full_name_upper; ?></h5></div>
        
        <form class="profile-body" method="POST">
          <div class="row gx-5">
            <div class="col-lg-6">
              <div class="row mb-4">
                <div class="col-4 field-label">Student ID</div>
                <div class="col-8 field-value text-muted"><?php echo $sid; ?></div>
              </div>
              <div class="row mb-4">
                <div class="col-4 field-label">Gender</div>
                <div class="col-8 field-value"><?php echo $row['gender'] ?? 'Not Set'; ?></div>
              </div>
              <div class="row mb-4">
                <div class="col-4 field-label">Birthday</div>
                <div class="col-8 field-value"><?php echo $row['dob'] ? date('F d, Y', strtotime($row['dob'])) : 'Not Set'; ?></div>
              </div>
              
              <hr class="my-4 opacity-50">

              <div class="mb-3">
                <label class="field-label mb-2 d-block">Mobile Number</label>
                <input type="text" name="mobile" class="form-control rounded-3" value="<?php echo htmlspecialchars($row['mobile'] ?? ''); ?>">
              </div>
              <div class="mb-3">
                <label class="field-label mb-2 d-block">Email Address</label>
                <input type="email" name="email" class="form-control rounded-3" value="<?php echo htmlspecialchars($row['email'] ?? ''); ?>">
              </div>
            </div>

            <div class="col-lg-6">
              <div class="mb-4">
                <label class="field-label mb-2 d-block">Residential Address</label>
                <textarea name="res_address" class="form-control rounded-3" rows="4"><?php echo htmlspecialchars($row['residential_address'] ?? ''); ?></textarea>
              </div>
              <div class="mb-4">
                <label class="field-label mb-2 d-block">Permanent Address</label>
                <textarea name="perm_address" class="form-control rounded-3" rows="4" placeholder="Leave blank if same as residential"><?php echo htmlspecialchars($row['permanent_address'] ?? ''); ?></textarea>
              </div>
            </div>
          </div>
          <div class="mt-5 text-center">
            <button type="submit" name="update_profile" class="btn btn-save shadow">Update Profile Details</button>
          </div>
        </form>
      </div>
    </main>
  </div>

   <!-- LOGOUT MODAL (Matching Report.php) -->
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
      document.getElementById('topbar-time').textContent = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
    }
    setInterval(updateDateTime, 1000); 
    updateDateTime(); 
  </script>
</body>
</html>