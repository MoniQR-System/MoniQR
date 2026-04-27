<?php 
session_start();
include '../db_config.php'; 

date_default_timezone_set('Asia/Manila');

// 1. Security & Get Faculty ID
if (!isset($_GET['id'])) {
    header("Location: faculty.php");
    exit();
}

$faculty_id = $_GET['id'];

// 2. Handle Folder Actions
if (isset($_POST['add_folder'])) {
    $folder_name = $_POST['folder_name'];
    $subject_code = $_POST['subject_code']; 
    $description = $_POST['description'];
    
    $stmt = mysqli_prepare($conn, "INSERT INTO faculty_folders (faculty_id, folder_name, subject_code, description) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "isss", $faculty_id, $folder_name, $subject_code, $description);
    
    if (mysqli_stmt_execute($stmt)) {
        header("Location: view_faculty.php?id=$faculty_id&status=folder_added");
        exit();
    }
}

// Handle Archive/Restore Logic
if (isset($_GET['archive_id'])) {
    $fid = $_GET['archive_id'];
    mysqli_query($conn, "UPDATE faculty_folders SET is_archived = 1 WHERE id = $fid AND faculty_id = $faculty_id");
    header("Location: view_faculty.php?id=$faculty_id&status=folder_archived");
    exit();
}

if (isset($_GET['restore_id'])) {
    $fid = $_GET['restore_id'];
    mysqli_query($conn, "UPDATE faculty_folders SET is_archived = 0 WHERE id = $fid AND faculty_id = $faculty_id");
    header("Location: view_faculty.php?id=$faculty_id&status=folder_restored");
    exit();
}

// 3. Handle Add Student Logic
if (isset($_POST['add_student'])) {
    $folder_id = $_POST['folder_id'];
    $student_id_num = trim($_POST['student_id']);
    
    $check_exists = mysqli_prepare($conn, "SELECT student_id FROM students WHERE student_id = ?");
    mysqli_stmt_bind_param($check_exists, "s", $student_id_num);
    mysqli_stmt_execute($check_exists);
    $res_exists = mysqli_stmt_get_result($check_exists);
    
    if ($row = mysqli_fetch_assoc($res_exists)) {
        $check_dup = mysqli_prepare($conn, "SELECT id FROM folder_students WHERE folder_id = ? AND student_id = ?");
        mysqli_stmt_bind_param($check_dup, "is", $folder_id, $student_id_num);
        mysqli_stmt_execute($check_dup);
        
        if (mysqli_num_rows(mysqli_stmt_get_result($check_dup)) > 0) {
            header("Location: view_faculty.php?id=$faculty_id&status=error_duplicate");
            exit();
        } else {
            $query = mysqli_prepare($conn, "INSERT INTO folder_students (folder_id, student_id) VALUES (?, ?)");
            mysqli_stmt_bind_param($query, "is", $folder_id, $student_id_num);
            mysqli_stmt_execute($query);
            header("Location: view_faculty.php?id=$faculty_id&status=student_added");
            exit();
        }
    } else {
        header("Location: view_faculty.php?id=$faculty_id&status=error_not_found");
        exit();
    }
}

// 4. Handle Delete Logic
if (isset($_POST['delete_folder'])) {
    $delete_id = $_POST['folder_id'];
    mysqli_query($conn, "DELETE FROM folder_students WHERE folder_id = $delete_id");
    $stmt = mysqli_prepare($conn, "DELETE FROM faculty_folders WHERE id = ? AND faculty_id = ?");
    mysqli_stmt_bind_param($stmt, "ii", $delete_id, $faculty_id);
    if (mysqli_stmt_execute($stmt)) {
        header("Location: view_faculty.php?id=$faculty_id&status=folder_deleted");
        exit();
    }
}

// 5. Fetch Faculty Data
$stmt = mysqli_prepare($conn, "SELECT * FROM faculty WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $faculty_id);
mysqli_stmt_execute($stmt);
$faculty = mysqli_stmt_get_result($stmt)->fetch_assoc();
if (!$faculty) { die("Faculty member not found."); }

// 6. Fetch Folders
$active_folders = mysqli_query($conn, "SELECT * FROM faculty_folders WHERE faculty_id = $faculty_id AND is_archived = 0 ORDER BY id DESC");
$archived_folders = mysqli_query($conn, "SELECT * FROM faculty_folders WHERE faculty_id = $faculty_id AND is_archived = 1 ORDER BY id DESC");

function getInitials($name) {
    $words = explode(" ", $name);
    $initials = "";
    foreach ($words as $w) {
        if (in_array(strtolower($w), ["prof.", "dr.", "mr.", "ms."])) continue;
        if (!empty($w)) $initials .= strtoupper($w[0]);
    }
    return substr($initials, 0, 2); 
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Profile | <?php echo htmlspecialchars($faculty['name']); ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-maroon: #7B1C2C;
            --bg-light: #F8F9FA;
            --bs-maroon-light: #f8f0f1;
        }

        body { background-color: var(--bg-light); font-family: 'DM Sans', sans-serif; }

        .profile-header { 
            background: var(--primary-maroon); 
            color: white; padding: 70px 0 110px; 
            border-radius: 0 0 40px 40px; 
        }

        .avatar-circle { 
            width: 100px; height: 100px; background: white; color: var(--primary-maroon); 
            font-size: 2.5rem; display: flex; align-items: center; justify-content: center; 
            border-radius: 50%; font-weight: bold; margin: 0 auto 15px; 
            border: 4px solid rgba(255,255,255,0.2);
        }

        .glass-card { 
            background: white; border-radius: 25px; border: none; 
            box-shadow: 0 8px 30px rgba(0,0,0,0.04); margin-top: -60px;
            position: relative;
        }

.btn-back-side {position: absolute; left: -50px; top: -60px; width: 45px; height: 45px; background: white;color: var(--primary-maroon); border-radius: 50%;display: flex; align-items: center; justify-content: center;text-decoration: none; box-shadow: 0 4px 15px rgba(0,0,0,0.1);transition: 0.3s; z-index: 10;}
.btn-back-side:hover { transform: translateX(-5px); background: var(--primary-maroon); color: white; }

/* Mobile Adjustment */
@media (max-width: 991px) {.btn-back-side { left: 20px; top: -75px;}}
        .folder-card { 
            border-radius: 22px; background: white; border: 1px solid #f2f2f2; 
            padding: 24px; transition: 0.3s; position: relative;
        }
        .folder-card:hover { transform: translateY(-5px); box-shadow: 0 12px 25px rgba(0,0,0,0.06); }

        .folder-icon {
            width: 55px; height: 55px; background: var(--bs-maroon-light); 
            color: var(--primary-maroon); border-radius: 16px; 
            display: flex; align-items: center; justify-content: center;
            font-size: 1.6rem; margin-bottom: 15px;
        }

        .btn-maroon {
            background-color: var(--primary-maroon); color: white; 
            border-radius: 20px; padding: 8px 20px; font-weight: 500; border: none;
        }
        .btn-maroon:hover { opacity: 0.9; color: white; }

        .btn-outline-maroon {
            border: 1.5px solid var(--primary-maroon); color: var(--primary-maroon); 
            border-radius: 20px; padding: 8px 15px; font-weight: 600; font-size: 0.85rem;
        }
        .btn-outline-maroon:hover { background: var(--primary-maroon); color: white; }

        .circle-action-btn {
            width: 38px; height: 38px; display: flex; 
            align-items: center; justify-content: center;
            background: white; border: 1px solid #eee; 
            border-radius: 50%; transition: 0.2s;
        }
        .circle-action-btn:hover { background: #f8f9fa; transform: scale(1.1); }

        .alert-floating { position: fixed; top: 20px; right: 20px; z-index: 2000; border-radius: 15px; }

        @media (max-width: 991px) {
            .btn-back-side { left: 20px; top: -70px; }
        }
    </style>
</head>
<body>

<?php if (isset($_GET['status'])): ?>
    <div class="alert <?php echo strpos($_GET['status'], 'error') !== false ? 'alert-danger' : 'alert-success'; ?> alert-dismissible fade show alert-floating shadow border-0" role="alert" id="statusAlert">
        <i class="bi <?php echo strpos($_GET['status'], 'error') !== false ? 'bi-exclamation-triangle-fill' : 'bi-check-circle-fill'; ?> me-2"></i>
        <?php 
            if($_GET['status'] == 'student_added') echo "Student linked successfully!";
            if($_GET['status'] == 'folder_added') echo "New folder created!";
            if($_GET['status'] == 'folder_deleted') echo "Folder removed successfully!";
            if($_GET['status'] == 'folder_archived') echo "Class archived!";
            if($_GET['status'] == 'folder_restored') echo "Class restored!";
            if($_GET['status'] == 'error_not_found') echo "Student ID not found.";
            if($_GET['status'] == 'error_duplicate') echo "Student already in this class!"; 
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="profile-header text-center">
    <div class="container">
        <div class="avatar-circle shadow-sm"><?php echo getInitials($faculty['name']); ?></div>
        <h2 class="fw-bold mb-1"><?php echo htmlspecialchars($faculty['name']); ?></h2>
        <p class="opacity-75 mb-0"><?php echo htmlspecialchars($faculty['department']); ?> Department</p>
    </div>
</div>

<div class="container pb-5">
    <div class="row position-relative">
        
        <div class="col-12">
             <a href="faculty.php" class="btn-back-side shadow" title="Back to Faculty List">
                <i class="bi bi-arrow-left"></i>
            </a>
        </div>

        <div class="col-lg-4 mb-4">
            <div class="card glass-card p-4">
                <h6 class="fw-bold mb-4 mt-2">Faculty Information</h6>
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-light rounded-circle p-2 me-3"><i class="bi bi-envelope text-maroon"></i></div>
                    <div class="text-truncate">
                        <small class="text-muted d-block small">Email</small>
                        <span class="fw-bold small"><?php echo htmlspecialchars($faculty['email']); ?></span>
                    </div>
                </div>
                <div class="d-flex align-items-center mb-4">
                    <div class="bg-light rounded-circle p-2 me-3"><i class="bi bi-telephone text-maroon"></i></div>
                    <div>
                        <small class="text-muted d-block small">Contact</small>
                        <span class="fw-bold small"><?php echo htmlspecialchars($faculty['phone']); ?></span>
                    </div>
                </div>
                <div class="bg-light rounded-4 py-3 text-center">
                    <h3 class="fw-bold text-maroon mb-0"><?php echo mysqli_num_rows($active_folders); ?></h3>
                    <small class="text-muted text-uppercase fw-bold" style="font-size: 0.65rem;">Active Classes</small>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card glass-card p-4">
                <div class="d-flex justify-content-between align-items-center mb-4 px-2">
                    <h5 class="fw-bold m-0">Class Folders</h5>
                    <button class="btn btn-maroon btn-sm px-3" data-bs-toggle="modal" data-bs-target="#addFolderModal">
                        <i class="bi bi-plus-lg me-2"></i>Create Folder
                    </button>
                </div>

                <div class="row g-3">
                    <?php if (mysqli_num_rows($active_folders) > 0): ?>
                        <?php while($folder = mysqli_fetch_assoc($active_folders)): ?>
                            <div class="col-md-6">
                                <div class="folder-card h-100 d-flex flex-column">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div class="folder-icon"><i class="bi bi-folder-fill"></i></div>
                                        
                                        <div class="d-flex flex-column gap-2">
                                            <a href="view_faculty.php?id=<?php echo $faculty_id; ?>&archive_id=<?php echo $folder['id']; ?>" 
                                               class="circle-action-btn shadow-sm" title="Archive">
                                                <i class="bi bi-archive text-warning"></i>
                                            </a>
                                            <button class="circle-action-btn shadow-sm" 
                                                    data-bs-toggle="modal" data-bs-target="#addStudentModal" 
                                                    data-folder-id="<?php echo $folder['id']; ?>" 
                                                    data-folder-name="<?php echo htmlspecialchars($folder['folder_name']); ?>" title="Add Student">
                                                <i class="bi bi-person-plus text-primary"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <h6 class="fw-bold mb-1 fs-5"><?php echo htmlspecialchars($folder['folder_name']); ?></h6>
                                    <span class="badge bg-secondary mb-3 align-self-start" style="font-size: 0.7rem;">
                                        <?php echo htmlspecialchars($folder['subject_code']); ?>
                                    </span>
                                    
                                    <p class="text-muted small mb-4 flex-grow-1 text-truncate">
                                        <?php echo htmlspecialchars($folder['description'] ?: 'No description provided.'); ?>
                                    </p>

                                    <div class="d-flex gap-2">
                                        <a href="view_folder_students.php?folder_id=<?php echo $folder['id']; ?>&name=<?php echo urlencode($folder['folder_name']); ?>" 
                                           class="btn btn-outline-maroon flex-grow-1">Students</a>
                                        <a href="view_attendance.php?folder_id=<?php echo $folder['id']; ?>" 
                                           class="btn btn-outline-secondary px-3"><i class="bi bi-clock-history"></i></a>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="text-center py-5 w-100">
                            <i class="bi bi-folder2-open display-4 text-muted opacity-25"></i>
                            <p class="text-muted mt-2">No class folders assigned yet.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if (mysqli_num_rows($archived_folders) > 0): ?>
                    <div class="mt-5">
                        <div class="d-flex align-items-center mb-3">
                            <h6 class="fw-bold m-0 text-muted small text-uppercase">Archived</h6>
                            <div class="flex-grow-1 ms-3" style="height: 1px; background: #eee;"></div>
                        </div>
                        <div class="row g-2">
                            <?php while($archived = mysqli_fetch_assoc($archived_folders)): ?>
                                <div class="col-md-6">
                                    <div class="p-3 bg-white border rounded-4 d-flex justify-content-between align-items-center">
                                        <div class="text-truncate">
                                            <span class="fw-bold small d-block"><?php echo htmlspecialchars($archived['folder_name']); ?></span>
                                            <small class="text-muted" style="font-size: 0.7rem;"><?php echo htmlspecialchars($archived['subject_code']); ?></small>
                                        </div>
                                        <div class="d-flex gap-1">
                                            <a href="view_faculty.php?id=<?php echo $faculty_id; ?>&restore_id=<?php echo $archived['id']; ?>" class="btn btn-sm btn-light text-success"><i class="bi bi-arrow-counterclockwise"></i></a>
                                            <button class="btn btn-sm btn-light text-danger" data-bs-toggle="modal" data-bs-target="#deleteFolderModal" data-folder-id="<?php echo $archived['id']; ?>" data-folder-name="<?php echo htmlspecialchars($archived['folder_name']); ?>"><i class="bi bi-trash"></i></button>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addFolderModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content border-0 rounded-4 shadow-lg" method="POST">
            <div class="modal-header border-0 pb-0 px-4 pt-4"><h5 class="fw-bold">New Class Folder</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body p-4">
                <div class="mb-3"><label class="form-label small fw-bold">Class Name</label><input type="text" name="folder_name" class="form-control rounded-3" placeholder="e.g. BSIT 3-1" required></div>
                <div class="mb-3"><label class="form-label small fw-bold">Subject Code</label><input type="text" name="subject_code" class="form-control rounded-3" placeholder="e.g. IT-101" required></div>
                <div><label class="form-label small fw-bold">Description</label><textarea name="description" class="form-control rounded-3" rows="3"></textarea></div>
            </div>
            <div class="modal-footer border-0 px-4 pb-4"><button type="submit" name="add_folder" class="btn btn-maroon rounded-pill w-100 py-2">Create Folder</button></div>
        </form>
    </div>
</div>

<div class="modal fade" id="addStudentModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content border-0 rounded-4 shadow-lg" method="POST">
            <input type="hidden" name="folder_id" id="modalFolderId">
            <div class="modal-body text-center p-5">
                <div class="bg-primary bg-opacity-10 text-primary rounded-circle mx-auto mb-4 d-flex align-items-center justify-content-center" style="width: 70px; height: 70px;"><i class="bi bi-person-plus-fill fs-2"></i></div>
                <h5 class="fw-bold mb-1">Add Student</h5>
                <p class="text-muted small mb-4">Adding to <span id="targetFolderName" class="fw-bold"></span></p>
                <div class="text-start"><label class="form-label fw-bold small text-uppercase">Student ID Number</label><input type="text" name="student_id" class="form-control form-control-lg rounded-3" placeholder="2024-XXXX" required autofocus></div>
            </div>
            <div class="modal-footer border-0 pt-0 justify-content-center pb-5"><button type="submit" name="add_student" class="btn btn-maroon rounded-pill px-5">Add Student</button></div>
        </form>
    </div>
</div>

<div class="modal fade" id="deleteFolderModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <form class="modal-content border-0 rounded-4 shadow" method="POST">
            <input type="hidden" name="folder_id" id="deleteFolderId">
            <div class="modal-body text-center p-4">
                <div class="text-danger mb-3"><i class="bi bi-exclamation-circle-fill fs-1"></i></div>
                <h5 class="fw-bold">Delete Folder?</h5>
                <p class="text-muted small">This unlinks all students from <b id="deleteFolderName"></b>.</p>
            </div>
            <div class="modal-footer border-0 pt-0 justify-content-center pb-4"><button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Cancel</button><button type="submit" name="delete_folder" class="btn btn-danger rounded-pill px-4">Delete</button></div>
        </form>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
<script>
    const studentModal = document.getElementById('addStudentModal');
    if (studentModal) {
        studentModal.addEventListener('show.bs.modal', event => {
            const btn = event.relatedTarget;
            document.getElementById('modalFolderId').value = btn.getAttribute('data-folder-id');
            document.getElementById('targetFolderName').textContent = btn.getAttribute('data-folder-name');
        });
    }
    const deleteModal = document.getElementById('deleteFolderModal');
    if (deleteModal) {
        deleteModal.addEventListener('show.bs.modal', event => {
            const btn = event.relatedTarget;
            document.getElementById('deleteFolderId').value = btn.getAttribute('data-folder-id');
            document.getElementById('deleteFolderName').textContent = btn.getAttribute('data-folder-name');
        });
    }
    const statusAlert = document.getElementById('statusAlert');
    if (statusAlert) { setTimeout(() => { new bootstrap.Alert(statusAlert).close(); }, 4000); }
</script>
</body>
</html>