<?php
include 'config.php';

// Set headers to force download as CSV
header('Content-Type: text/csv');
header('Content-Disposition: attachment;filename=attendance_report.csv');

// Open output stream
$output = fopen("php://output", "w");

// Write CSV column headers
fputcsv($output, ['User ID', 'Date', 'Clock In', 'Clock Out']);

// Fetch and format attendance data
$res = $conn->query("SELECT user_id, date, clock_in, clock_out FROM attendance ORDER BY date DESC");

while ($row = $res->fetch_assoc()) {
    $formattedDate = date('Y-m-d', strtotime($row['date']));
    $clockIn = $row['clock_in'] ? date('h:i:s A', strtotime($row['clock_in'])) : '';
    $clockOut = $row['clock_out'] ? date('h:i:s A', strtotime($row['clock_out'])) : '';

    fputcsv($output, [
        $row['user_id'],
        $formattedDate,
        $clockIn,
        $clockOut
    ]);
}

fclose($output);
exit;
?>
