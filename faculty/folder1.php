<?php
session_start();
include '../db_config.php'; 
date_default_timezone_set('Asia/Manila');

// 1. Security check: Ensure faculty is logged in
if (!isset($_SESSION['faculty_id'])) {
    header("Location: ../login_faculty.php");
    exit();
}

if (isset($_GET['id'])) {
    // Sanitize the ID using mysqli_real_escape_string or typecasting
    $folder_id = (int)$_GET['id']; 

    // 2. Fetch Folder Details (Works for both active and archived)
    $folder_query = "SELECT folder_name, is_archived FROM faculty_folders WHERE id = '$folder_id'";
    $folder_result = mysqli_query($conn, $folder_query);
    $folder_data = mysqli_fetch_assoc($folder_result);

    if (!$folder_data) {
        header("Location: dashboard.php"); // Redirect if folder doesn't exist
        exit();
    }

    // 3. Fetch ONLY the students linked to THIS specific folder via the junction table
$student_query = "SELECT s.id, s.student_id, s.first_name, s.last_name 
                  FROM students s 
                  INNER JOIN folder_students fs ON s.student_id = fs.student_id 
                  WHERE fs.folder_id = '$folder_id' 
                  ORDER BY s.last_name ASC";

$student_result = mysqli_query($conn, $student_query);

} else {
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enrolled Students | <?php echo htmlspecialchars($folder_data['folder_name']); ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet" />

    <style>
        :root {
            --primary-maroon: #7B1C2C;
            --bg-light: #F8F9FA;
            --success-green: #D1F7D1;
            --success-text: #28a745;
            --info-blue: #D1E9F7;
            --info-text: #007bff;
            --archive-gray: #E9ECEF;
            --archive-text: #6C757D;
        }

        body { background-color: var(--bg-light); font-family: 'DM Sans', sans-serif; }
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        .header-title { display: flex; align-items: center; gap: 15px; }

        .icon-circle {
            background-color: var(--primary-maroon);
            color: white; width: 45px; height: 45px;
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            text-decoration: none; transition: transform 0.2s;
        }
        .icon-circle:hover { transform: scale(1.1); color: white; }

        .btn-maroon {
            background-color: var(--primary-maroon);
            color: white; border-radius: 20px; padding: 8px 20px; 
            font-weight: 500; border: none; text-decoration: none;
        }
        .btn-maroon:hover { opacity: 0.9; color: white; }

        .table-container { background: white; border-radius: 25px; padding: 2rem; box-shadow: 0 4px 12px rgba(0,0,0,0.03); }
        .custom-table td { padding: 15px; vertical-align: middle; border-bottom: 1px solid #f8f9fa; }
        
        /* Dynamic Badges based on Folder State */
        .badge-active { background-color: var(--info-blue); color: var(--info-text); border-radius: 12px; padding: 5px 15px; font-size: 0.85rem; font-weight: 600; }
        .badge-archived { background-color: var(--archive-gray); color: var(--archive-text); border-radius: 12px; padding: 5px 15px; font-size: 0.85rem; font-weight: 600; }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="page-header">
        <div class="header-title">
            <a href="dashboard.php" class="icon-circle shadow-sm">
                <i class="bi bi-chevron-left"></i>
            </a>
            <div class="header-text">
                <h2 class="fw-bold"><?php echo htmlspecialchars($folder_data['folder_name']); ?></h2>
                <p class="text-muted"><?php echo mysqli_num_rows($student_result); ?> Students Enrolled</p>
            </div>
        </div>
        
        <?php if ($folder_data['is_archived'] == 0): ?>
        <div class="action-buttons d-flex gap-2">
            <a href="qr_scanner.php?folder_id=<?php echo $folder_id; ?>" class="btn btn-maroon">
                <i class="bi bi-qr-code me-2"></i>Scan QR
            </a>
            <a href="generate_report.php?folder_id=<?php echo $folder_id; ?>" class="btn btn-maroon">
                <i class="bi bi-bar-chart-line me-2"></i>Report
            </a>
        </div>
        <?php else: ?>
            <span class="badge-archived fs-6"><i class="bi bi-archive me-2"></i>Archived Class</span>
        <?php endif; ?>
    </div>

    <div class="table-container">
        <div class="table-responsive">
            <table class="table custom-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Student Name</th>
                        <th>Student ID</th>
                        <th>State</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($student_result) > 0): 
                        $counter = 1;
                        while($row = mysqli_fetch_assoc($student_result)): ?>
                        <tr>
                            <td><?php echo $counter++; ?></td>
                            <td class="fw-bold"><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                            <td class="text-muted"><?php echo htmlspecialchars($row['student_id']); ?></td>
                            <td>
                                <?php if ($folder_data['is_archived'] == 0): ?>
                                    <span class="badge-active">Active</span>
                                <?php else: ?>
                                    <span class="badge-archived">Archived</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; 
                    else: ?>
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">No students enrolled in this class.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>