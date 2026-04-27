<?php 
include '../db_config.php'; 

// --- HANDLE ADD STUDENT ---
if (isset($_POST['add_student'])) {
    $sid = mysqli_real_escape_string($conn, $_POST['student_id']);
    $fname = mysqli_real_escape_string($conn, $_POST['first_name']);
    $lname = mysqli_real_escape_string($conn, $_POST['last_name']);
    $course = mysqli_real_escape_string($conn, $_POST['course_section']);

    $insert = "INSERT INTO students (student_id, first_name, last_name, course_section) VALUES ('$sid', '$fname', '$lname', '$course')";
    if (mysqli_query($conn, $insert)) {
        // Redirect to self to prevent form resubmission on refresh
        header("Location: admin_students.php?msg=added");
        exit();
    } else {
        $error = "Error adding student: " . mysqli_error($conn);
    }
}

// --- HANDLE DELETE STUDENT ---
if (isset($_GET['delete'])) {
    $id = mysqli_real_escape_string($conn, $_GET['delete']);
    $deleteQuery = "DELETE FROM students WHERE id = '$id'";
    
    if (mysqli_query($conn, $deleteQuery)) {
        header("Location: admin_students.php?msg=deleted");
        exit();
    }
}

// Fetch all students
$query = "SELECT * FROM students ORDER BY last_name ASC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>MoniQR Admin | Students</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet" />

    <style>
        :root { --bs-maroon: #7B1C2C; --sidebar-width: 85px; --sidebar-expanded: 260px; --transition-speed: 0.3s; --text-muted: #6c757d; }
        body { font-family: 'DM Sans', sans-serif; background-color: #ffffff; overflow-x: hidden; }
        
        /* --- SIDEBAR --- */
        #sidebar { width: var(--sidebar-width); transition: width var(--transition-speed) ease; z-index: 1030; background-color: var(--bs-maroon); overflow: hidden; white-space: nowrap; position: fixed; height: 100vh; left: 0; top: 0; display: flex; flex-direction: column; }
        #sidebar:hover { width: var(--sidebar-expanded); }
        .sidebar-logo-container { padding: 25px 5px; display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 150px; }
        .sidebar-logo-container img { width: 150px; height: auto; transition: 0.3s; }
        
        .nav-link { color: rgba(255, 255, 255, 0.7); display: flex; align-items: center; padding: 15px 0; text-decoration: none; transition: all 0.2s; }
        .nav-link:hover, .nav-link.active { color: #fff; background: rgba(255, 255, 255, 0.1); }
        .nav-link i { min-width: var(--sidebar-width); text-align: center; font-size: 1.4rem; }
        .nav-label { opacity: 0; transition: opacity 0.2s; font-weight: 500; }
        #sidebar:hover .nav-label { opacity: 1; }

        /* --- MAIN CONTENT --- */
        #main { margin-left: var(--sidebar-width); min-height: 100vh; transition: margin-left var(--transition-speed) ease; }
        #sidebar:hover+#main { margin-left: var(--sidebar-expanded); }
        .top-navbar { height: 80px; display: flex; align-items: center; justify-content: space-between; padding: 0 40px; border-bottom: 1px solid #eee; background: #fff; }
        .content-body { padding: 40px; }
        
        /* --- HEADER & BUTTONS --- */
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .page-title h1 { font-size: 28px; font-weight: 700; margin: 0; line-height: 1.2; }
        .page-title p { color: var(--text-muted); font-size: 14px; margin: 4px 0 0 0; }
        
        .actions { display: flex; gap: 12px; align-items: center; }
        .btn-custom { padding: 10px 22px; border-radius: 10px; font-size: 14px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; height: 45px; transition: 0.2s ease; border: none; }
        .btn-export { background: white; border: 1.5px solid #333; color: #333; }
        .btn-add { background-color: var(--bs-maroon); color: white !important; }
        .btn-add:hover { background-color: #5a1420; transform: translateY(-2px); }

        .table-container { background: #fff; border-radius: 16px; border: 1px solid #eee; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.02); }
    </style>
</head>

<body>
    <nav id="sidebar" class="shadow">
        <div class="sidebar-logo-container">
            <img src="../img/logo.png" alt="Logo">
        </div>
        <div class="flex-grow-1 mt-4">
            <a href="dashboard.php" class="nav-link"><i class="bi bi-grid-fill"></i><span class="nav-label">Dashboard</span></a>
            <a href="students.php" class="nav-link active"><i class="bi bi-mortarboard-fill"></i><span class="nav-label">Students</span></a>
            <a href="faculty.php" class="nav-link"><i class="bi bi-people-fill"></i><span class="nav-label">Faculty</span></a>
            <a href="reports.php" class="nav-link"><i class="bi bi-file-earmark-text-fill"></i><span class="nav-label">Reports</span></a>
        </div>
        <div class="mb-4">
            <a href="logout.php" class="nav-link" onclick="return confirm('Are you sure you want to sign out?')">
                <i class="bi bi-box-arrow-left"></i><span class="nav-label">Sign Out</span>
            </a>
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
                <i class="bi bi-person-circle" style="font-size: 2rem;"></i>
            </div>
        </header>

        <main class="content-body">
            <?php if(isset($_GET['msg'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php 
                        if($_GET['msg'] == 'added') echo "Student added successfully!";
                        if($_GET['msg'] == 'deleted') echo "Student record removed.";
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="page-header">
                <div class="page-title">
                    <h1>Students</h1>
                    <p>Manage student records and information</p>
                </div>
                <div class="actions">
                    <button class="btn-custom btn-export" onclick="exportTableToCSV('students_list.csv')">
                        <i class="bi bi-box-arrow-up"></i> Export
                    </button>
                    <button class="btn-custom btn-add" data-bs-toggle="modal" data-bs-target="#addModal">
                        <i class="bi bi-plus-lg"></i> Add Student
                    </button>
                </div>
            </div>

            <div class="table-container">
                <table class="table" id="studentTable">
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Full Name</th>
                            <th>Course & Section</th>
                            <th>Date Registered</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(mysqli_num_rows($result) > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td class="fw-bold"><?php echo htmlspecialchars($row['student_id']); ?></td>
                                <td><?php echo htmlspecialchars($row['last_name'] . ", " . $row['first_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['course_section']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                                <td class="text-center">
                                    <a href="admin_students.php?delete=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this student?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center py-4 text-muted">No students found in the database.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog">
            <form class="modal-content" method="POST" action="admin_students.php">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Student</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Student ID</label>
                        <input type="text" name="student_id" class="form-control" required placeholder="e.g. 2024-0001">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">First Name</label>
                        <input type="text" name="first_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Last Name</label>
                        <input type="text" name="last_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Course & Section</label>
                        <input type="text" name="course_section" class="form-control" required placeholder="BSIT-3A">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="add_student" class="btn btn-add">Save Student</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script>
        // Update Time Display
        function updateDateTime() {
            const now = new Date();
            document.getElementById('topbar-date').textContent = now.toLocaleDateString('en-US', { weekday: 'short', month: 'long', day: 'numeric', year: 'numeric' });
            document.getElementById('topbar-time').textContent = now.toLocaleTimeString('en-US');
        }
        setInterval(updateDateTime, 1000); 
        updateDateTime();

        // CSV Export Function
        function exportTableToCSV(filename) {
            let csv = [];
            let rows = document.querySelectorAll("#studentTable tr");
            
            for (let i = 0; i < rows.length; i++) {
                let row = [], cols = rows[i].querySelectorAll("td, th");
                // Don't export the 'Action' column
                for (let j = 0; j < cols.length - 1; j++) {
                    row.push('"' + cols[j].innerText.trim() + '"');
                }
                csv.push(row.join(","));
            }
            
            let csvFile = new Blob([csv.join("\n")], { type: "text/csv" });
            let downloadLink = document.createElement("a");
            downloadLink.download = filename;
            downloadLink.href = window.URL.createObjectURL(csvFile);
            downloadLink.style.display = "none";
            document.body.appendChild(downloadLink);
            downloadLink.click();
            document.body.removeChild(downloadLink);
        }
    </script>
</body>
</html>