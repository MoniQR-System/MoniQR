<?php
session_start();
include '../db_config.php';

// 1. Validate folder input
if (!isset($_GET['folder_id'])) {
    header("Location: admin_faculty.php");
    exit();
}

$folder_id = (int)$_GET['folder_id'];
$folder_name = isset($_GET['name']) ? $_GET['name'] : 'Folder Contents';

// 2. Handle Student Removal
if (isset($_POST['remove_student'])) {
    $fs_id = (int)$_POST['fs_id'];
    $delete_stmt = mysqli_prepare($conn, "DELETE FROM folder_students WHERE id = ?");
    mysqli_stmt_bind_param($delete_stmt, "i", $fs_id);
    mysqli_stmt_execute($delete_stmt);
    header("Location: view_folder_students.php?folder_id=$folder_id&name=".urlencode($folder_name)."&status=removed");
    exit();
}
// Update Section 3 in view_folder_students.php
$query = "SELECT fs.id as link_id, 
                 s.student_id as id_number, 
                 s.first_name, 
                 s.last_name, 
                 s.course_section, 
                 s.email, 
                 s.mobile
          FROM folder_students fs
          JOIN students s ON (fs.student_id = s.student_id OR fs.student_id = CAST(s.id AS CHAR))
          WHERE fs.folder_id = ? 
          ORDER BY fs.id DESC";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $folder_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);

// Check if query actually returned a result to prevent the Fatal Error
if (!$res) {
    die("Query Failed: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enrolled Students | <?php echo htmlspecialchars($folder_name); ?></title>
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">

    <style>
        :root { 
            --bs-maroon: #7B1C2C; 
            --bs-maroon-light: #f8eeee;
        }
        body { background-color: #f4f7f6; font-family: 'DM Sans', sans-serif; }
        
        .card { 
            border: none; 
            border-radius: 25px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.05); 
            overflow: hidden;
        }
        
        .avatar-sm { 
            width: 42px; height: 42px; 
            background: var(--bs-maroon-light); 
            color: var(--bs-maroon); 
            display: flex; align-items: center; justify-content: center; 
            border-radius: 12px; font-weight: bold; font-size: 1.1rem;
        }

        .table thead th {
            background-color: #fafbfc;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 1px;
            font-weight: 700;
            color: #6c757d;
            padding: 1.25rem 1rem;
            border-top: none;
        }

        .table tbody td { padding: 1rem; }
        
        .btn-back {
            background: white;
            border: 1px solid #eee;
            color: #666;
            transition: 0.3s;
        }
        .btn-back:hover { background: #f8f9fa; color: var(--bs-maroon); }

        .status-badge {
            padding: 5px 12px;
            border-radius: 8px;
            font-size: 0.8rem;
            background: #e9ecef;
            color: #495057;
            border: 1px solid #dee2e6;
        }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-maroon mb-1">
                <i class="bi bi-people-fill me-2"></i>Class List
            </h3>
            <p class="text-muted mb-0">
                Folder: <span class="fw-bold text-dark"><?php echo htmlspecialchars($folder_name); ?></span>
            </p>
        </div>
        <a href="javascript:history.back()" class="btn btn-back rounded-pill px-4 shadow-sm">
            <i class="bi bi-arrow-left me-2"></i>Back to Folder
        </a>
    </div>

    <?php if (isset($_GET['status']) && $_GET['status'] == 'removed'): ?>
        <div class="alert alert-warning alert-dismissible fade show border-0 rounded-4 shadow-sm mb-4" role="alert">
            <i class="bi bi-info-circle-fill me-2"></i> Student has been removed from this class folder.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">Student Profile</th>
                            <th>ID Number</th>
                            <th>Section</th>
                            <th>Contact Info</th>
                            <th class="text-center">Manage</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(mysqli_num_rows($res) > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($res)): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm me-3">
                                            <?php echo strtoupper(substr($row['first_name'], 0, 1)); ?>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark mb-0">
                                                <?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?>
                                            </div>
                                            <small class="text-muted"><?php echo htmlspecialchars($row['email']); ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="status-badge fw-bold">
                                        <?php echo htmlspecialchars($row['id_number']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-medium"><?php echo htmlspecialchars($row['course_section']); ?></div>
                                </td>
                                <td>
                                    <div class="small"><i class="bi bi-phone me-1 text-muted"></i> <?php echo htmlspecialchars($row['mobile']); ?></div>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-danger border-0 rounded-circle" 
                                            onclick="confirmRemoval(<?php echo $row['link_id']; ?>, '<?php echo addslashes($row['first_name']); ?>')">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="opacity-25 mb-3">
                                        <i class="bi bi-person-x" style="font-size: 4rem;"></i>
                                    </div>
                                    <h5 class="text-muted">No Students Found</h5>
                                    <p class="text-muted small">Linked students will appear here for attendance monitoring.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="removeModal" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <form class="modal-content border-0 rounded-4 shadow" method="POST">
            <input type="hidden" name="fs_id" id="removeFsId">
            <div class="modal-body text-center p-4">
                <i class="bi bi-exclamation-circle text-warning display-4 mb-3"></i>
                <h6 class="fw-bold">Remove Student?</h6>
                <p class="small text-muted">Are you sure you want to remove <span id="studentNameText" class="fw-bold"></span> from this folder?</p>
                <div class="d-grid gap-2">
                    <button type="submit" name="remove_student" class="btn btn-danger rounded-pill">Yes, Remove</button>
                    <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
<script>
function confirmRemoval(linkId, name) {
    const removeModal = new bootstrap.Modal(document.getElementById('removeModal'));
    document.getElementById('removeFsId').value = linkId;
    document.getElementById('studentNameText').textContent = name;
    removeModal.show();
}
</script>

</body>
</html>