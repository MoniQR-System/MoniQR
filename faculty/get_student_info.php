<?php
include '../db_config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = mysqli_real_escape_string($conn, $_POST['student_id']);
    $folder_id = mysqli_real_escape_string($conn, $_POST['folder_id']);

    // Change 'students_table' and 'id_column' to your actual database names
    $query = "SELECT name FROM students_table WHERE id_column = '$student_id' LIMIT 1";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $student = mysqli_fetch_assoc($result);
        $name = $student['name'];

        // Mark Attendance
        $sql = "INSERT INTO attendance (student_id, folder_id, time_in) VALUES ('$student_id', '$folder_id', NOW())";
        mysqli_query($conn, $sql);

        echo json_encode([
            'status' => 'success',
            'name' => $name,
            'student_id' => $student_id
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Student ID not found in database.'
        ]);
    }
}
?>