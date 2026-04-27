<?php
include '../db_config.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['folder_id'])) {
    $folder_id = mysqli_real_escape_string($conn, $_POST['folder_id']);
    // Set is_archived back to 0
    $query = "UPDATE faculty_folders SET is_archived = 0 WHERE id = '$folder_id'";
    mysqli_query($conn, $query);
    header("Location: dashboard.php");
}
?>