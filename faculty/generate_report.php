<?php
include '../db_config.php';

if (isset($_GET['folder_id'])) {
    $folder_id = mysqli_real_escape_string($conn, $_GET['folder_id']);
    
    // Fetch students
    $query = "SELECT s.student_id, s.first_name, s.last_name, s.course_section, s.email 
              FROM students s 
              JOIN folder_students fs ON s.id = fs.student_id 
              WHERE fs.folder_id = '$folder_id'";
    
    $result = mysqli_query($conn, $query);

    // Set headers for download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="Attendance_Report_'.date('Y-m-d').'.csv"');

    $output = fopen('php://output', 'w');
    
    // Column Headers
    fputcsv($output, array('Student ID', 'First Name', 'Last Name', 'Section', 'Email'));

    // Data Rows
    while ($row = mysqli_fetch_assoc($result)) {
        fputcsv($output, $row);
    }
    
    fclose($output);
    exit();
}
?>