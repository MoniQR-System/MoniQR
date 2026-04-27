<?php
session_start();
include '../db_config.php';

// Check if student is logged in and folder_id is provided
if (!isset($_SESSION['student_id']) || !isset($_GET['folder_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$student_id = $_SESSION['student_id'];
$folder_id = (int)$_GET['folder_id'];

// 1. Get Summary Totals
// Assumes you have an 'attendance_logs' table
$summary_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) as present,
    SUM(CASE WHEN status = 'Late' THEN 1 ELSE 0 END) as late,
    SUM(CASE WHEN status = 'Absent' THEN 1 ELSE 0 END) as absent
    FROM attendance_logs 
    WHERE student_id = ? AND folder_id = ?";

$stmt = mysqli_prepare($conn, $summary_query);
mysqli_stmt_bind_param($stmt, "ii", $student_id, $folder_id);
mysqli_stmt_execute($stmt);
$summary = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

// 2. Get History Logs
$history_query = "SELECT attendance_date as date, attendance_time as time, status 
                  FROM attendance_logs 
                  WHERE student_id = ? AND folder_id = ? 
                  ORDER BY attendance_date DESC, attendance_time DESC";

$stmt_h = mysqli_prepare($conn, $history_query);
mysqli_stmt_bind_param($stmt_h, "ii", $student_id, $folder_id);
mysqli_stmt_execute($stmt_h);
$result_h = mysqli_stmt_get_result($stmt_h);

$history = [];
while($row = mysqli_fetch_assoc($result_h)) {
    $history[] = $row;
}

// Return data as JSON for the Student Dashboard's JavaScript
echo json_encode([
    'total' => $summary['total'] ?? 0,
    'present' => $summary['present'] ?? 0,
    'late' => $summary['late'] ?? 0,
    'absent' => $summary['absent'] ?? 0,
    'history' => $history
]);