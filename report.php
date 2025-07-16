<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$today = date('Y-m-d');

// Fetch Attendance History
$attendanceResult = $conn->query("SELECT * FROM attendance WHERE user_id=$userId");

// Fetch Leave Records
$leaveResult = $conn->query("SELECT * FROM leave_requests WHERE user_id=$userId");

// Fetch Holidays
$holidayResult = $conn->query("SELECT * FROM holidays");

function calculateTotalTime($clockInTime, $clockOutTime) {
    $clockIn = new DateTime($clockInTime);
    $clockOut = new DateTime($clockOutTime);
    $interval = $clockIn->diff($clockOut);
    return $interval->format('%h hours %i minutes');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance and Leave Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f1f3f5;
            font-family: 'Arial', sans-serif;
            color: #333;
        }

        .container {
            margin-top: 3rem;
            background-color: #ffffff;
            padding: 2.5rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            max-width: 1000px;
        }

        h2 {
            font-size: 2rem;
            font-weight: 600;
            color: #4b79a1;
            text-align: center;
            margin-bottom: 1.5rem;
        }

        h4 {
            font-size: 1.4rem;
            margin-top: 1.5rem;
            color:black;
        }

        .table {
            margin-top: 2rem;
            border-radius: 10px;
            border: none;
            overflow: hidden;
        }

        .table th, .table td {
            padding: 15px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }

        .table thead {
            background: linear-gradient(135deg, #007bff, #00c6ff);
            color: white;
        }

        .table tbody tr:nth-of-type(odd) {
            background-color: #f8f9fa;
        }

        .status {
            font-weight: bold;
            padding: 6px 12px;
            border-radius: 5px;
            text-transform: capitalize;
            display: inline-block;
        }

        .status.present {
            background-color: #28a745;
            color: white;
        }

        .status.absent {
            background-color: #dc3545;
            color: white;
        }

        .status.pending {
            background-color: #ffc107;
            color: white;
        }

        .btn {
            background-color: #007bff;
            color: white;
            padding: 12px 24px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: bold;
            transition: background-color 0.3s;
            display: inline-block;
            text-align: center;
            margin-top: 1rem;
        }

        .btn:hover {
            background-color: #0056b3;
        }

        .btn:focus {
            outline: none;
            box-shadow: none;
        }

        .table-responsive {
            max-height: 400px;
            overflow-y: auto;
        }

        .back-btn {
            text-decoration: none;
            font-size: 1.2rem;
            font-weight: 500;
        }

    </style>
</head>
<body>
    <div class="container">
        <h2>Attendance, Leave, and Holiday Report</h2>
        
        <!-- Attendance History -->
        <h4>Attendance History</h4>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Clock In</th>
                        <th>Clock Out</th>
                        <th>Status</th>
                        <th>Total Time Worked</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $attendanceResult->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['date'] ?></td>
                            <td><?= date("h:i:s A", strtotime($row['clock_in'])) ?></td>
                            <td><?= $row['clock_out'] ? date("h:i:s A", strtotime($row['clock_out'])) : "Not Clocked Out" ?></td>
                            <td class="status <?= $row['clock_out'] ? 'present' : 'absent' ?>">
                                <?= $row['clock_out'] ? "Present" : "Absent" ?>
                            </td>
                            <td>
                                <?php 
                                    if ($row['clock_in'] && $row['clock_out']) {
                                        echo calculateTotalTime($row['clock_in'], $row['clock_out']);
                                    } else {
                                        echo 'N/A';
                                    }
                                ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Leave Requests -->
        <h4>Leave Requests</h4>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Leave Type</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $leaveResult->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['leave_date'] ?></td>
                            <td><?= $row['leave_type'] ?></td>
                            <td class="status <?= strtolower($row['status']) ?>">
                                <?= $row['status'] ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div class="text-center mt-4">
            <a href="dashboard.php" class="btn back-btn">Back to Dashboard</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
