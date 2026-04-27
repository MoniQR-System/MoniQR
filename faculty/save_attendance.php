<?php
include '../db_config.php';
date_default_timezone_set('Asia/Manila');

if (isset($_POST['student_id']) && isset($_POST['folder_id'])) {
    $student_id_num = mysqli_real_escape_string($conn, $_POST['student_id']);
    $folder_id = mysqli_real_escape_string($conn, $_POST['folder_id']);
    $today = date('Y-m-d H:i:s');

    // 1. Find internal student ID
    $query = "SELECT id, first_name, last_name FROM students WHERE student_id = '$student_id_num'";
    $res = mysqli_query($conn, $query);
    
    if ($student = mysqli_fetch_assoc($res)) {
        $s_id = $student['id'];
        $name = $student['first_name'] . " " . $student['last_name'];
        
        // 2. Insert into attendance table (create this table if you haven't)
        $sql = "INSERT INTO attendance (student_id, folder_id, status, scanned_at) 
                VALUES ('$s_id', '$folder_id', 'Present', '$today')";
        
        if (mysqli_query($conn, $sql)) {
            echo "✅ Attendance recorded for: " . $name;
        } else {
            echo "❌ Error saving record.";
        }
    } else {
        echo "⚠️ Student ID not found.";
    }
}
?>