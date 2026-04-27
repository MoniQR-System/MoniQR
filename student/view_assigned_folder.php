<?php
session_start();
include '../db_config.php';

// Security check
if (!isset($_SESSION['student_id']) || !isset($_GET['id'])) {
    header("Location: student_dashboard.php");
    exit();
}

$student_id = $_SESSION['student_id'];
$folder_id = mysqli_real_escape_string($conn, $_GET['id']);

// Fetch folder details
$query = "SELECT ff.*, f.name AS faculty_name 
          FROM faculty_folders ff
          JOIN faculty f ON ff.faculty_id = f.id
          WHERE ff.id = '$folder_id'";

$result = mysqli_query($conn, $query);
$folder = mysqli_fetch_assoc($result);

if (!$folder) {
    echo "Folder not found.";
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Folder View</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="p-5">

    <a href="student_dashboard.php" class="btn btn-secondary mb-4">← Back</a>

    <h2><?php echo htmlspecialchars($folder['folder_name']); ?></h2>
    <p><strong>Instructor:</strong> <?php echo htmlspecialchars($folder['faculty_name']); ?></p>

    <hr>

    <h5>Folder Content</h5>
    <p>This is where you will display files, QR attendance, etc.</p>

</body>
</html>