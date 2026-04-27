<?php 
include '../db_config.php'; 

// --- 1. HANDLE DELETE FACULTY (Secure Prepared Statement) ---
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM faculty WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        header("Location: admin_faculty.php?status=deleted");
        exit();
    }
}

// --- 2. HANDLE ADD FACULTY ---
if (isset($_POST['add_faculty'])) {
    $name     = mysqli_real_escape_string($conn, $_POST['name']);
    $dept     = mysqli_real_escape_string($conn, $_POST['department']);
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $phone    = mysqli_real_escape_string($conn, $_POST['phone']);
    $courses  = mysqli_real_escape_string($conn, $_POST['courses_count']);
    $password_raw = $_POST['password'];

    if (strlen($password_raw) < 8) {
        header("Location: admin_faculty.php?status=pw_short");
        exit();
    }

    $password = mysqli_real_escape_string($conn, $password_raw);
    $insert = "INSERT INTO faculty (name, department, email, password, phone, courses_count, status) 
               VALUES ('$name', '$dept', '$email', '$password', '$phone', '$courses', 'Active')";
    
    if (mysqli_query($conn, $insert)) {
        header("Location: admin_faculty.php?status=success");
        exit();
    }
}

// --- 3. FETCH DATA ---
$query = "SELECT * FROM faculty ORDER BY name ASC";
$result = mysqli_query($conn, $query);

// Helper for Avatar
function getInitials($name) {
    if (empty($name)) return "??";
    $words = explode(" ", $name);
    $initials = "";
    foreach ($words as $w) {
        if (strtolower($w) == "prof." || strtolower($w) == "dr.") continue;
        if (!empty($w)) $initials .= strtoupper($w[0]);
    }
    return substr($initials, 0, 2); 
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>MoniQR Admin | Faculty</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet" />

    <style>
        :root {
            --bs-maroon: #7B1C2C;
            --bs-blue-accent: #3498db;
            --sidebar-width: 85px;
            --sidebar-expanded: 260px;
            --bg-container: #f4f5f7;
            --transition-speed: 0.3s;
        }

        body { font-family: 'DM Sans', sans-serif; background-color: #ffffff; color: #1a1a1a; overflow-x: hidden; }

        /* Sidebar Logic */
        #sidebar { width: var(--sidebar-width); transition: width var(--transition-speed) ease; z-index: 1030; background-color: var(--bs-maroon); overflow: hidden; white-space: nowrap; height: 100vh; position: fixed; left: 0; top: 0; display: flex; flex-direction: column; }
        #sidebar:hover { width: var(--sidebar-expanded); }
        .sidebar-logo-container { padding: 25px 5px; display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 150px; }
        .sidebar-logo-container img { width: 150px; height: auto; }
        .nav-link { color: rgba(255, 255, 255, 0.6); display: flex; align-items: center; padding: 16px 0; text-decoration: none; transition: 0.2s; }
        .nav-link:hover, .nav-link.active { color: #fff; background: rgba(255, 255, 255, 0.1); }
        .nav-link i { min-width: var(--sidebar-width); text-align: center; font-size: 1.4rem; }
        .nav-label { opacity: 0; transition: opacity 0.2s; font-weight: 500; }
        #sidebar:hover .nav-label { opacity: 1; }

        #main { margin-left: var(--sidebar-width); min-height: 100vh; transition: margin-left var(--transition-speed) ease; }
        #sidebar:hover+#main { margin-left: var(--sidebar-expanded); }

        .top-navbar { height: 80px; display: flex; align-items: center; justify-content: space-between; padding: 0 40px; background: #fff; border-bottom: 1px solid #f0f0f0; }
        .content-body { padding: 40px; }
        .btn-custom { padding: 10px 24px; border-radius: 12px; font-size: 14px; font-weight: 600; display: inline-flex; align-items: center; gap: 10px; transition: all 0.3s ease; border: none; height: 45px; }
        .btn-add { background-color: var(--bs-maroon); color: white !important; }

        .faculty-container { background-color: var(--bg-container); border: 2px solid #e0e7ff; border-radius: 20px; padding: 30px; }
        .faculty-card { background: white; border-radius: 16px; border: 1px solid #f0f0f0; position: relative; transition: all 0.3s ease; height: 100%; display: flex; flex-direction: column; }
        .faculty-card:hover { transform: translateY(-8px); box-shadow: 0 15px 30px rgba(0, 0, 0, 0.08); border-color: var(--bs-blue-accent); }
        .faculty-avatar { width: 55px; height: 55px; background-color: var(--bs-maroon); color: white; display: flex; align-items: center; justify-content: center; border-radius: 50%; font-weight: 700; font-size: 1.1rem; }
        .badge-active { position: absolute; top: 20px; right: 20px; background-color: #e8f5e9; color: #2e7d32; padding: 5px 14px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        
        .btn-view-profile { background: transparent; border: 1.5px solid var(--bs-blue-accent); color: var(--bs-blue-accent); font-size: 14px; font-weight: 600; border-radius: 10px; padding: 10px; transition: all 0.2s; text-decoration: none; display: block; text-align: center; }
        .btn-view-profile:hover { background-color: var(--bs-blue-accent); color: white; }
        .btn-delete { background: transparent; border: 1.5px solid #dc3545; color: #dc3545; border-radius: 10px; padding: 10px; transition: all 0.2s; display: flex; align-items: center; justify-content: center; }
        .btn-delete:hover { background-color: #dc3545; color: white; }
    </style>
</head>

<body>
    <nav id="sidebar" class="shadow">
        <div class="sidebar-logo-container"><img src="../img/logo.png" alt="Logo"></div>
        <div class="flex-grow-1 mt-4">
            <a href="dashboard.php" class="nav-link"><i class="bi bi-grid-fill"></i><span class="nav-label">Dashboard</span></a>
            <a href="students.php" class="nav-link"><i class="bi bi-mortarboard-fill"></i><span class="nav-label">Students</span></a>
            <a href="admin_faculty.php" class="nav-link active"><i class="bi bi-people-fill"></i><span class="nav-label">Faculty</span></a>
            <a href="reports.php" class="nav-link"><i class="bi bi-file-earmark-text-fill"></i><span class="nav-label">Reports</span></a>
        </div>
        <div class="mb-4"><a href="../logout.php" class="nav-link"><i class="bi bi-box-arrow-left"></i><span class="nav-label">Sign Out</span></a></div>
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
            <?php if(isset($_GET['status'])): ?>
                <?php if($_GET['status'] == 'success'): ?>
                    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i> <strong>Success!</strong> Faculty member added.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php elseif($_GET['status'] == 'deleted'): ?>
                    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                        <i class="bi bi-trash-fill me-2"></i> <strong>Deleted!</strong> Faculty member removed.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <div class="page-header d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold m-0">Faculty</h2>
                    <p class="text-muted m-0">Manage faculty members and assignments</p>
                </div>
                <button class="btn-custom btn-add shadow-sm" data-bs-toggle="modal" data-bs-target="#facultyModal">
                    <i class="bi bi-plus-lg"></i> Add Faculty
                </button>
            </div>

            <div class="faculty-container">
                <div class="row g-4">
                    <?php if ($result && mysqli_num_rows($result) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($result)): ?>
                            <div class="col-lg-4 col-md-6">
                                <div class="faculty-card p-4 shadow-sm">
                                    <span class="badge-active"><?php echo $row['status']; ?></span>
                                    <div class="d-flex align-items-center mb-4">
                                        <div class="faculty-avatar shadow-sm"><?php echo getInitials($row['name']); ?></div>
                                        <div class="ms-3">
                                            <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($row['name']); ?></h6>
                                            <small class="text-muted"><?php echo htmlspecialchars($row['department']); ?></small>
                                        </div>
                                    </div>
                                    <div class="mb-4 small">
                                        <div class="text-muted mb-1"><i class="bi bi-envelope me-2"></i><?php echo htmlspecialchars($row['email']); ?></div>
                                        <div class="text-muted"><i class="bi bi-telephone me-2"></i><?php echo htmlspecialchars($row['phone']); ?></div>
                                    </div>
                                    <div class="d-flex justify-content-between py-2 border-top border-bottom mb-4 small">
                                        <span>Courses Teaching</span>
                                        <span class="fw-bold"><?php echo $row['courses_count']; ?></span>
                                    </div>
                                    
                                    <div class="d-flex gap-2 mt-auto">
                                        <a href="view_faculty.php?id=<?php echo $row['id']; ?>" class="btn btn-view-profile flex-grow-1">View Profile</a>
                                        <button type="button" class="btn btn-delete" 
                                                data-bs-toggle="modal" data-bs-target="#deleteModal" 
                                                data-id="<?php echo $row['id']; ?>" 
                                                data-name="<?php echo htmlspecialchars($row['name']); ?>">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="col-12 text-center py-5"><p class="text-muted">No faculty records found.</p></div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <div class="modal fade" id="facultyModal" tabindex="-1">
        <div class="modal-dialog">
            <form class="modal-content border-0 shadow" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Add Faculty Member</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Full Name</label><input type="text" name="name" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Department</label><input type="text" name="department" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Email Address</label><input type="email" name="email" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Password</label><input type="password" name="password" class="form-control" required minlength="8"></div>
                    <div class="mb-3"><label class="form-label">Phone Number</label><input type="text" name="phone" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Number of Courses</label><input type="number" name="courses_count" class="form-control" value="1" required></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="add_faculty" class="btn btn-add">Save Faculty</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <div class="modal-body text-center p-4">
                    <div class="text-danger mb-3"><i class="bi bi-exclamation-octagon-fill" style="font-size: 3rem;"></i></div>
                    <h5 class="fw-bold">Are you sure?</h5>
                    <p class="text-muted small">Deleting <strong id="delete-faculty-name"></strong> cannot be undone.</p>
                    <div class="d-grid gap-2 mt-4">
                        <a href="#" id="confirm-delete-btn" class="btn btn-danger rounded-pill py-2">Delete Now</a>
                        <button type="button" class="btn btn-light rounded-pill py-2" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="deleteToast" class="toast align-items-center text-white bg-danger border-0" role="alert" style="border-radius: 12px;">
            <div class="d-flex">
                <div class="toast-body"><i class="bi bi-trash3-fill me-2"></i> Deleting faculty member...</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const deleteModal = document.getElementById('deleteModal');
            const deleteToast = new bootstrap.Toast(document.getElementById('deleteToast'));

            // Set up Modal Data
            deleteModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const id = button.getAttribute('data-id');
                const name = button.getAttribute('data-name');
                
                document.getElementById('delete-faculty-name').textContent = name;
                document.getElementById('confirm-delete-btn').href = 'admin_faculty.php?delete_id=' + id;
            });

            // Trigger Notification
            document.getElementById('confirm-delete-btn').addEventListener('click', function(e) {
                deleteToast.show();
                const target = this.href;
                e.preventDefault();
                setTimeout(() => { window.location.href = target; }, 700);
            });

            // Clock Logic
            function updateDateTime() {
                const now = new Date();
                document.getElementById('topbar-date').textContent = now.toLocaleDateString('en-US', { weekday: 'short', month: 'long', day: 'numeric', year: 'numeric' });
                document.getElementById('topbar-time').textContent = now.toLocaleTimeString('en-US');
            }
            setInterval(updateDateTime, 1000); updateDateTime();
        });
    </script>
</body>
</html>