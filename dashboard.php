<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$flash = '';
if (isset($_SESSION['flash'])) {
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
}

$userId = $_SESSION['user_id'];

$today = date('Y-m-d');

// Query for attendance data
$res = $conn->query("SELECT * FROM attendance WHERE user_id=$userId AND date='$today'");
$data = $res->fetch_assoc();

$clockedIn = false;
$clockedOut = false;
$clockInTime = '';
$clockOutTime = '';

if ($data) {
    if ($data['clock_in']) {
        $clockedIn = true;
        $clockInTime = $data['clock_in'];
    }
    if ($data['clock_out']) {
        $clockedOut = true;
        $clockOutTime = $data['clock_out'];
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Handle clock-in
    if (isset($_POST['clock_in']) && !$data) {
        $conn->query("INSERT INTO attendance (user_id, date, clock_in) VALUES ($userId, '$today', NOW())");
        header("Location: dashboard.php");
        exit();
    }

    // Handle clock-out
    if (isset($_POST['clock_out']) && $data && !$data['clock_out']) {
        $conn->query("UPDATE attendance SET clock_out = NOW() WHERE id = " . $data['id']);
        header("Location: dashboard.php");
        exit();
    }

    // Handle leave request
    if (isset($_POST['request_leave'])) {
        $leave_date = $_POST['leave_date'];
        $leave_type = $_POST['leave_type'];
        $status = 'Pending';

        $conn->query("INSERT INTO leave_requests (user_id, leave_date, leave_type, status) VALUES ($userId, '$leave_date', '$leave_type', '$status')");
       $_SESSION['flash'] = "request sent successfully.";
        header("Location: dashboard.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Attendance System</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">Attendance System</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a href="report.php" class="btn btn-outline-info me-2">Attendance Report</a>
                    </li>
                    <li class="nav-item">
                        <a href="logout.php" class="btn btn-outline-danger">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <?php if (!empty($flash)): ?>
    <div class="alert alert-success alert-dismissible fade show text-center" role="alert">
        <?= htmlspecialchars($flash) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>
        <h2 class="text-center mb-4">Welcome! Today's Attendance</h2>

        <!-- Attendance Status -->
        <div class="card shadow-sm p-4 mb-4">
            <?php if (!$clockedIn): ?>
            <p class="status-text text-danger">You have not clocked in yet for today.</p>
            <form method="post">
                <button name="clock_in" class="btn btn-success w-100">Clock In</button>
            </form>
            <?php elseif (!$clockedOut): ?>
            <p class="status-text text-info">You are clocked in. Time: <?= date("h:i:s A", strtotime($clockInTime)) ?>
            </p>
            <form method="post">
                <button name="clock_out" class="btn btn-danger w-100">Clock Out</button>
            </form>
            <div class="timer mt-3">
                <p><strong>Time elapsed: </strong><span id="elapsed-time"></span></p>
            </div>
            <?php else: ?>
            <p class="status-text text-success">You clocked out at: <?= date("h:i:s A", strtotime($clockOutTime)) ?></p>
            <?php endif; ?>
        </div>

        <!-- Leave Request Section -->
        <div class="card shadow-sm p-4">
            <h3 class="mb-3">Request Leave</h3>
            <form method="post">
                <label for="leave_date" class="form-label">Leave Date:</label>
                <input type="date" name="leave_date" id="leave_date" class="form-control" required>

                <label for="leave_type" class="form-label mt-3">Leave Type:</label>
                <select name="leave_type" id="leave_type" class="form-select" required>
                    <option value="Sick">Sick</option>
                    <option value="Vacation">Vacation</option>
                    <option value="Personal">Personal</option>
                </select>

                <div class="text-center mt-4">
                    <button name="request_leave" class="btn btn-warning w-50">Request Leave</button>
                </div>
            </form>
        </div>
    </div>

   <script>
<?php if ($clockedIn && !$clockedOut): ?>
    var clockInTime = new Date("<?= date('Y-m-d\TH:i:s', strtotime($clockInTime)) ?>");

    function updateTime() {
        var now = new Date();
        var diffMs = now - clockInTime;
        var totalSeconds = Math.floor(diffMs / 1000);

        var hours = Math.floor(totalSeconds / 3600);
        var minutes = Math.floor((totalSeconds % 3600) / 60);
        var seconds = totalSeconds % 60;

        document.getElementById('elapsed-time').innerText =
            hours + "h " + minutes + "m " + seconds + "s";
    }

    updateTime();
    setInterval(updateTime, 1000); 
<?php endif; ?>
</script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <style>
    body {
        font-family: 'Arial', sans-serif;
        background: #f4f7fa;
    }

    .navbar {
        background-color: #333;
    }

    .navbar .nav-link {
        color: #fff;
    }

    .navbar .nav-link.active {
        font-weight: bold;
    }

    .container {
        max-width: 900px;
        margin: 0 auto;
    }

    h2 {
        font-size: 28px;
        color: #333;
        margin-bottom: 30px;
        font-weight: bold;
    }

    .card {
        background-color: #ffffff;
        border-radius: 10px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        margin-bottom: 30px;
    }

    .status-text {
        font-size: 18px;
        font-weight: 600;
    }

    .timer {
        font-size: 18px;
    }

    .btn {
        padding: 12px 24px;
        font-size: 16px;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .btn-success {
        background-color: #28a745;
        color: white;
    }

    .btn-danger {
        background-color: #dc3545;
        color: white;
    }

    .btn-warning {
        background-color: #ffc107;
        color: white;
    }

    .btn-animated:hover {
        transform: scale(1.05);
    }

    .form-label {
        font-size: 16px;
        font-weight: bold;
    }

    .form-control,
    .form-select {
        border-radius: 8px;
    }
    </style>
</body>

</html>