<?php
include '../db_config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize Inputs
    $student_id = mysqli_real_escape_string($conn, $_POST['student_id']);
    $folder_id = mysqli_real_escape_string($conn, $_POST['folder_id']);

    // 1. Verify Student in 'students' table
    $query = "SELECT first_name, last_name, student_id FROM students WHERE student_id = '$student_id' LIMIT 1";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $student = mysqli_fetch_assoc($result);

        // 2. Prevent duplicate scan for the same day/session if needed
        // (Optional check: if student already scanned for this folder_id today)

        // 3. Insert into attendance log
        // Assuming your log table is named 'attendance_records'
        $insert = "INSERT INTO attendance_records (student_id, folder_id, status, scanned_at) 
                   VALUES ('$student_id', '$folder_id', 'Present', NOW())";
        
        if (mysqli_query($conn, $insert)) {
            echo json_encode([
                'status' => 'success',
                'first_name' => $student['first_name'],
                'last_name' => $student['last_name'],
                'student_id' => $student['student_id']
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Database error: Could not log attendance.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Unrecognized QR: Student ID ' . $student_id . ' not found.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>