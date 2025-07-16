<?php
include 'config.php';
if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

header('Content-Type: text/csv');
header('Content-Disposition: attachment;filename=attendance_report.csv');

$output = fopen("php://output", "w");
fputcsv($output, ['User ID', 'Date', 'Clock In', 'Clock Out']);

$res = $conn->query("SELECT user_id, date, clock_in, clock_out FROM attendance ORDER BY date DESC");
while ($row = $res->fetch_assoc()) {
    fputcsv($output, $row);
}
fclose($output);
exit;
?>