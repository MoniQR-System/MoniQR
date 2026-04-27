<?php
include '../db_config.php';
session_start();

// Security check
if (!isset($_SESSION['faculty_id'])) {
    exit("Unauthorized");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['folder_id'])) {
    $folder_id = mysqli_real_escape_string($conn, $_POST['folder_id']);
    $faculty_id = $_SESSION['faculty_id'];

    // Update the folder status to 1 (Archived)
    // We include faculty_id to ensure a user can only archive their own folders
    $query = "UPDATE faculty_folders SET is_archived = 1 WHERE id = '$folder_id' AND faculty_id = '$faculty_id'";
    
    if (mysqli_query($conn, $query)) {
        // Redirect back with a success flag
        header("Location: dashboard.php?archived=true");
        exit();
    } else {
        echo "Error updating record: " . mysqli_error($conn);
    }
} else {
    header("Location: dashboard.php");
    exit();
}
?>