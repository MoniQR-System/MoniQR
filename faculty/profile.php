<?php
session_start();

// 1. Database Connection 
// Ensure this filename is correct (db_config.php or db_connection.php?)
require_once '../db_config.php'; 

// 2. Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // FIX: Redirect to login.php, NOT profile.php to avoid infinite loop
    header("Location: login.php"); 
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";
$message_type = ""; 

// 3. Handle POST Request (Updating the Database)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $mobile = $_POST['mobile'] ?? '';
    $email = $_POST['email'] ?? '';
    $res_address = $_POST['residential_address'] ?? '';
    $perm_address = $_POST['permanent_address'] ?? '';

    try {
        $update_sql = "UPDATE users SET 
                        mobile = :mobile, 
                        email = :email, 
                        residential_address = :res_address, 
                        permanent_address = :perm_address 
                      WHERE id = :id";
        
        // Note: Using $pdo. If your config uses $conn, change this to $conn
        $stmt = $pdo->prepare($update_sql);
        $stmt->execute([
            'mobile' => $mobile,
            'email' => $email,
            'res_address' => $res_address,
            'perm_address' => $perm_address,
            'id' => $user_id
        ]);
        
        $message = "Profile updated successfully!";
        $message_type = "success";
    } catch (PDOException $e) {
        $message = "Error updating profile: " . $e->getMessage();
        $message_type = "danger";
    }
}

// 4. Fetch Dynamic Data 
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        session_destroy();
        header("Location: login.php?error=usernotfound");
        exit();
    }
    
    $faculty_name = $user['full_name']; 

} catch (PDOException $e) {
    die("Database connection error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Faculty Profile - MoniQR</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet" />

    <style>
        :root {
            --bs-maroon: #7B1C2C;
            --bs-maroon-light: #f0e6e8;
            --sidebar-width: 85px;
            --sidebar-expanded: 260px;
            --transition-speed: 0.3s;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background-color: #f4f7f6;
            overflow-x: hidden;
        }

        #sidebar {
            width: var(--sidebar-width);
            transition: width var(--transition-speed) cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1030;
            background-color: var(--bs-maroon);
            overflow: hidden;
        }

        #sidebar:hover { width: var(--sidebar-expanded); }

        .sidebar-logo-container {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 150px;
            width: 100%;
            padding: 10px;
        }

        .sidebar-logo-img {
            width: 150px; 
            height: auto;
            max-height: 140px;
            object-fit: contain;
            transition: all var(--transition-speed) ease;
            filter: brightness(0) invert(1);
        }

        .nav-label { opacity: 0; transition: opacity 0.2s ease; white-space: nowrap; font-size: 1.1rem; }
        #sidebar:hover .nav-label { opacity: 1; }

        .nav-link { transition: all 0.2s ease; border-left: 4px solid transparent; text-decoration: none; color: white !important; }
        .nav-link:hover { background-color: rgba(255, 255, 255, 0.1); }
        .nav-link.active { background-color: rgba(255, 255, 255, 0.15); border-left-color: #fff; }

        #main {
            margin-left: var(--sidebar-width);
            transition: margin-left var(--transition-speed) cubic-bezier(0.4, 0, 0.2, 1);
            min-height: 100vh;
        }

        #sidebar:hover+#main { margin-left: var(--sidebar-expanded); }
        
        .profile-card { border: 1px solid #dee2e6; border-radius: 4px; background-color: #fff; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02); }
        .profile-header { padding: 1rem 1.5rem; border-bottom: 1px solid #dee2e6; background-color: #f8f9fa; border-top-left-radius: 4px; border-top-right-radius: 4px; }
        .profile-name-bar { padding: 1rem 1.5rem; border-bottom: 1px solid #dee2e6; }
        .profile-name-bar h5 { color: #dc3545; font-weight: 700; margin: 0; font-size: 1.1rem; }
        .profile-body { padding: 1.5rem; }
        .field-label { color: #555; font-size: 0.9rem; font-weight: 500; }
        .field-value { font-weight: 700; color: #222; font-size: 0.95rem; }
        .profile-footer { padding: 1rem 1.5rem; background-color: #f8f9fa; border-top: 1px solid #dee2e6; }
        .btn-save { background-color: #8A0000; color: white; font-weight: 500; padding: 0.5rem 1.5rem; border: none; border-radius: 4px; }
        .btn-save:hover { background-color: #610000; color: white; }
        .btn-maroon { background-color: var(--bs-maroon); color: white; }
    </style>
</head>

<body class="d-flex">
    <nav id="sidebar" class="vh-100 position-fixed start-0 top-0 d-flex flex-column text-white shadow">
        <div class="sidebar-logo-container">
            <img src="../img/logo.png" alt="Logo" class="sidebar-logo-img">
        </div>
        <div class="flex-grow-1 pt-2">
            <a href="dashboard.php" class="nav-link d-flex align-items-center py-3 px-0 opacity-75">
                <div style="min-width: var(--sidebar-width);" class="text-center"><i class="bi bi-grid-1x2 fs-3"></i></div>
                <span class="nav-label">Dashboard</span>
            </a>
            <a href="archived.php" class="nav-link d-flex align-items-center py-3 px-0 opacity-75">
                <div style="min-width: var(--sidebar-width);" class="text-center"><i class="bi bi-archive fs-3"></i></div>
                <span class="nav-label">Archived Subject</span>
            </a>
        </div>
        <div class="pb-4">
            <a class="nav-link d-flex align-items-center py-3 px-0 opacity-75" style="cursor:pointer;" data-bs-toggle="modal" data-bs-target="#logoutModal">
                <div style="min-width: var(--sidebar-width);" class="text-center"><i class="bi bi-box-arrow-left fs-3"></i></div>
                <span class="nav-label">Sign Out</span>
            </a>
        </div>
    </nav>

    <div id="main" class="flex-grow-1 d-flex flex-column">
        <header class="navbar navbar-expand bg-white border-bottom px-4 px-lg-5 shadow-sm" style="height: 80px;">
            <div class="container-fluid px-0">
                <span class="navbar-brand text-dark m-0 fs-3 fw-bold">MoniQR</span>
                <div class="ms-auto d-flex align-items-center gap-4">
                    <div class="text-muted d-none d-sm-flex align-items-center">
                        <span id="topbar-date"></span><span class="mx-2">|</span><span id="topbar-time"></span>
                    </div>
                    <div class="dropdown">
                        <div class="rounded-circle border d-flex align-items-center justify-content-center bg-light shadow-sm"
                            style="width: 45px; height: 45px; cursor: pointer;" data-bs-toggle="dropdown">
                            <i class="bi bi-person-fill fs-4" style="color: #7B1C2C;"></i>
                        </div>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                            <li class="px-3 py-2 border-bottom mb-2">
                                <small class="text-muted d-block">Welcome,</small>
                                <span class="fw-bold"><?php echo htmlspecialchars($faculty_name); ?></span>
                            </li>
                            <li><a class="dropdown-item d-flex align-items-center gap-2" href="profile.php"><i class="bi bi-person text-muted"></i> Profile</a></li>
                            <li><a class="dropdown-item d-flex align-items-center gap-2" href="changepass.php"><i class="bi bi-shield-lock text-muted"></i> Change Password</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </header>

        <main class="p-4 p-lg-5">
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="profile-card">
                <div class="profile-header">
                    <h4 class="mb-0 text-dark" style="font-weight: 500;">Personal Data</h4>
                </div>
                <div class="profile-name-bar">
                    <h5><?php echo htmlspecialchars($user['full_name']); ?> (<?php echo htmlspecialchars($user['username']); ?>)</h5>
                </div>
                
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                    <div class="profile-body">
                        <div class="row gx-5">
                            <div class="col-lg-6">
                                <div class="row mb-3 align-items-center">
                                    <div class="col-sm-4 field-label">User Name</div>
                                    <div class="col-sm-8 field-value"><?php echo htmlspecialchars($user['username']); ?></div>
                                </div>
                                <div class="row mb-3 align-items-center">
                                    <div class="col-sm-4 field-label">Name</div>
                                    <div class="col-sm-8 field-value"><?php echo htmlspecialchars($user['full_name']); ?></div>
                                </div>
                                <div class="row mb-3 align-items-center">
                                    <div class="col-sm-4 field-label">Gender</div>
                                    <div class="col-sm-8 field-value"><?php echo htmlspecialchars($user['gender'] ?? 'Not set'); ?></div>
                                </div>
                                <div class="row mb-4 align-items-center">
                                    <div class="col-sm-4 field-label">Date of Birth</div>
                                    <div class="col-sm-8 field-value"><?php echo htmlspecialchars($user['dob'] ?? 'Not set'); ?></div>
                                </div>
                                <div class="row mb-3 align-items-center">
                                    <div class="col-sm-4 field-label">Mobile No.</div>
                                    <div class="col-sm-8">
                                        <input type="text" name="mobile" class="form-control" value="<?php echo htmlspecialchars($user['mobile'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="row mb-3 align-items-center">
                                    <div class="col-sm-4 field-label">Email Address</div>
                                    <div class="col-sm-8">
                                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="row mb-3">
                                    <div class="col-sm-4 field-label pt-1">Residential Address</div>
                                    <div class="col-sm-8">
                                        <textarea name="residential_address" class="form-control" rows="2"><?php echo htmlspecialchars($user['residential_address'] ?? ''); ?></textarea>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-4 field-label pt-1">Permanent Address</div>
                                    <div class="col-sm-8">
                                        <textarea name="permanent_address" class="form-control" rows="2"><?php echo htmlspecialchars($user['permanent_address'] ?? ''); ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="profile-footer">
                        <button type="submit" class="btn btn-save">Save Changes</button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <!-- Logout Modal -->
    <div class="modal fade" id="logoutModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content shadow-lg">
                <div class="modal-body text-center p-4">
                    <i class="bi bi-box-arrow-right" style="font-size: 3rem; color: var(--bs-maroon);"></i>
                    <h5 class="mt-3 fw-bold">Sign Out</h5>
                    <p class="text-muted mb-4">Are you sure you want to log out?</p>
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                        <a href="logout.php" class="btn btn-maroon px-4">Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script>
        function updateDateTime() {
            const now = new Date();
            document.getElementById('topbar-date').textContent = now.toLocaleDateString('en-US', {
                weekday: 'short', month: 'long', day: 'numeric', year: 'numeric'
            });
            document.getElementById('topbar-time').textContent = now.toLocaleTimeString('en-US', {
                hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true
            });
        }
        setInterval(updateDateTime, 1000);
        updateDateTime(); 
    </script>
</body>
</html>