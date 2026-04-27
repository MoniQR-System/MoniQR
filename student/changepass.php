<?php
session_start();
include '../db_config.php';

// 1. Security Check
if (!isset($_SESSION['student_id'])) {
    header("Location: login_student.php");
    exit();
}

$sid = $_SESSION['student_id'];
$error = "";
$success = "";

// 2. Fetch Student Info for the Header
$info_query = mysqli_query($conn, "SELECT * FROM students WHERE student_id = '$sid'");
$student = mysqli_fetch_assoc($info_query);
$display_name = strtoupper($student['last_name'] . ", " . $student['first_name'] . " " . ($student['middle_name'] ?? ""));

// 3. Handle Password Change Logic
if (isset($_POST['change_pass_btn'])) {
    $old_pass = $_POST['old_password'];
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];

    // Validation
    if (empty($old_pass) || empty($new_pass) || empty($confirm_pass)) {
        $error = "All fields are required.";
    } elseif ($old_pass !== $student['password']) {
        $error = "The old password you entered is incorrect.";
    } elseif ($new_pass !== $confirm_pass) {
        $error = "New password and confirmation do not match.";
    } elseif (strlen($new_pass) < 5) {
        $error = "New password must be at least 5 characters long.";
    } else {
        // Update Database
        $update = mysqli_query($conn, "UPDATE students SET password = '$new_pass' WHERE student_id = '$sid'");
        if ($update) {
            $success = "Your password has been updated successfully!";
            // Update the local $student variable so it doesn't trigger "wrong old pass" if they try again immediately
            $student['password'] = $new_pass;
        } else {
            $error = "Database error. Please try again later.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>MoniQR | Change Password</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet" />

  <style>
    :root { --bs-maroon: #7B1C2C; --bs-maroon-light: #f0e6e8; --sidebar-width: 85px; --sidebar-expanded: 260px; --transition-speed: 0.3s; }
    body { font-family: 'DM Sans', sans-serif; background-color: #f4f7f6; overflow-x: hidden; }
    
    #sidebar { width: var(--sidebar-width); transition: width var(--transition-speed) ease; z-index: 1030; background-color: var(--bs-maroon); overflow: hidden; position: fixed; height: 100vh; top:0; left:0; display:flex; flex-direction:column;}
    #sidebar:hover { width: var(--sidebar-expanded); }
    .sidebar-logo-img { width: 0; opacity: 0; transition: 0.3s; margin: 30px auto; display: block; }
    #sidebar:hover .sidebar-logo-img { width: 150px; opacity: 1; }
    .nav-link { border-left: 4px solid transparent; color: white; opacity: 0.7; transition: 0.2s; text-decoration: none; display: flex; align-items:center; }
    .nav-link:hover, .nav-link.active { opacity: 1; background: rgba(255,255,255,0.1); border-left-color: #fff; }
    .nav-label { opacity: 0; transition: opacity 0.2s; margin-left: 10px; }
    #sidebar:hover .nav-label { opacity: 1; }

    #main { margin-left: var(--sidebar-width); transition: margin-left var(--transition-speed) ease; min-height: 100vh; }
    #sidebar:hover+#main { margin-left: var(--sidebar-expanded); }

    .profile-card { border: 1px solid #dee2e6; border-radius: 8px; background-color: #fff; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02); }
    .profile-name-bar { padding: 1.25rem 1.5rem; border-bottom: 1px solid #dee2e6; }
    .profile-name-bar h5 { color: #dc3545; font-weight: 700; margin: 0; font-size: 1.1rem; }
    .profile-body { padding: 2rem 1.5rem; }
    .form-control:focus { border-color: var(--bs-maroon); box-shadow: 0 0 0 0.25rem rgba(123, 28, 44, 0.25); }
    .input-group-text { background-color: #e9ecef; border-color: #ced4da; color: #495057; padding: 0 1.25rem; }
    .profile-footer { padding: 1rem 1.5rem; background-color: #f8f9fa; border-top: 1px solid #dee2e6; border-bottom-left-radius: 4px; border-bottom-right-radius: 4px; }
    .btn-save { background-color: #8A0000; color: white; font-weight: 500; padding: 0.5rem 1.5rem; border: none; border-radius: 4px; transition: 0.2s; }
    .btn-save:hover { background-color: #610000; color: white; }
  </style>
</head>

<body class="d-flex">
  <!-- SIDEBAR -->
  <nav id="sidebar" class="shadow text-white">
    <div style="min-height: 150px;"><img src="../img/logo.png" class="sidebar-logo-img"></div>
    <div class="flex-grow-1">
      <a href="student_dashboard.php" class="nav-link py-3 px-0 opacity-75">
        <div style="min-width: var(--sidebar-width);" class="text-center"><i class="bi bi-grid-1x2 fs-3"></i></div>
        <span class="nav-label">Dashboard</span>
      </a>
      <a href="student_report.php" class="nav-link py-3 px-0 opacity-75">
        <div style="min-width: var(--sidebar-width);" class="text-center"><i class="bi bi-clipboard-data fs-3"></i></div>
        <span class="nav-label">Reports</span>
      </a>
      <a href="student_archived.php" class="nav-link py-3 px-0 opacity-75">
        <div style="min-width: var(--sidebar-width);" class="text-center"><i class="bi bi-archive fs-3"></i></div>
        <span class="nav-label">Archived Subject</span>
      </a>
    </div>
    <div class="pb-4">
      <a class="nav-link py-3 px-0 opacity-75" data-bs-toggle="modal" data-bs-target="#logoutModal" style="cursor: pointer;">
        <div style="min-width: var(--sidebar-width);" class="text-center"><i class="bi bi-box-arrow-left fs-3"></i></div>
        <span class="nav-label">Sign Out</span>
      </a>
    </div>
  </nav>

  <div id="main" class="flex-grow-1">
    <header class="navbar navbar-expand bg-white border-bottom px-4 px-lg-5 shadow-sm" style="height: 80px;">
      <div class="container-fluid px-0">
        <span class="navbar-brand text-dark m-0 fs-3 fw-bold">MoniQR</span>
        <div class="ms-auto d-flex align-items-center gap-4">
          <div class="text-muted d-none d-sm-flex align-items-center"><span id="topbar-date"></span><span class="mx-2">|</span><span id="topbar-time"></span></div>
          <div class="dropdown">
            <div class="rounded-circle border d-flex align-items-center justify-content-center bg-light shadow-sm" style="width: 45px; height: 45px; cursor: pointer;" data-bs-toggle="dropdown">
              <i class="bi bi-person-fill fs-4" style="color: #7B1C2C;"></i>
            </div>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
              <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person me-2"></i> Profile</a></li>
              <li><a class="dropdown-item" href="changepass.php"><i class="bi bi-shield-lock me-2"></i> Password</a></li>
            </ul>
          </div>
        </div>
      </div>
    </header>

    <main class="p-4 p-lg-5">
      <h3 class="mb-3 text-dark" style="font-weight: 400; font-size: 1.6rem;">Change Password</h3>

      <!-- ALERT MESSAGES -->
      <?php if($error): ?>
        <div class="alert alert-danger py-2 shadow-sm" style="max-width: 500px;"><i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $error; ?></div>
      <?php endif; ?>
      <?php if($success): ?>
        <div class="alert alert-success py-2 shadow-sm" style="max-width: 500px;"><i class="bi bi-check-circle-fill me-2"></i><?php echo $success; ?></div>
      <?php endif; ?>

      <div class="profile-card" style="max-width: 700px;">
        <div class="profile-name-bar">
          <h5><?php echo $display_name; ?> (<?php echo $sid; ?>)</h5>
        </div>
        <form class="profile-body" method="POST" action="changepass.php">
          <div class="row">
            <div class="col-12 col-md-10 col-lg-7">
              <div class="input-group mb-3">
                <input type="password" name="old_password" class="form-control" placeholder="Old Password" required>
                <span class="input-group-text"><i class="bi bi-key-fill"></i></span>
              </div>
              <div class="input-group mb-3">
                <input type="password" name="new_password" class="form-control" placeholder="New Password" required>
                <span class="input-group-text"><i class="bi bi-key-fill"></i></span>
              </div>
              <div class="input-group mb-2">
                <input type="password" name="confirm_password" class="form-control" placeholder="Confirm Password" required>
                <span class="input-group-text"><i class="bi bi-key-fill"></i></span>
              </div>
            </div>
          </div>
          <div class="mt-4 pt-3 border-top">
            <button type="submit" name="change_pass_btn" class="btn btn-save">Update Password</button>
          </div>
        </form>
      </div>
    </main>
  </div>

  <!-- LOGOUT MODAL -->
  <div class="modal fade" id="logoutModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
      <div class="modal-content shadow-lg p-3 text-center border-0">
          <i class="bi bi-box-arrow-right mb-2" style="font-size: 3rem; color: var(--bs-maroon);"></i>
          <h5 class="fw-bold">Sign Out</h5>
          <p class="text-muted mb-4">Are you sure you want to log out?</p>
          <div class="d-flex justify-content-center gap-2">
            <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
            <a href="../logout.php" class="btn btn-maroon px-4 text-white">Logout</a>
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
    setInterval(updateDateTime, 1000); updateDateTime(); 
  </script>
</body>
</html>