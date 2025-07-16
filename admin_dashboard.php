<?php
session_start();
include 'config.php';

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

$flash = '';
if (isset($_SESSION['flash'])) {
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
}

// Handle employee deletion
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM attendance WHERE user_id = $id");
    $conn->query("DELETE FROM users WHERE id = $id");
    $_SESSION['flash'] = "Employee deleted successfully.";
    header("Location: admin_dashboard.php");
    exit();
}

// Handle Leave deletion
if (isset($_GET['leave_delete'])) {
    $leaveRequestId = intval($_GET['leave_delete']);
    $conn->query("DELETE FROM leave_requests WHERE id = $leaveRequestId");
     $_SESSION['flash'] = "Leave request deleted.";
    header("Location: admin_dashboard.php");
    exit();
}


// ADD employee 
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_employee'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $pass = $_POST['password'];
    $conn->query("INSERT INTO users (name, email, password) VALUES ('$name', '$email', '$pass')");
    $_SESSION['flash'] = "Employee Added Successfully.";
    header("Location: admin_dashboard.php");
}

// Handle employee update
if (isset($_POST['edit'])) {
    $id = intval($_POST['id']);
    $name = $_POST['name'];
    $email = $_POST['email'];

    if (!empty($_POST['password'])) {
        $password = $_POST['password'];
        $conn->query("UPDATE users SET name='$name', email='$email', password='$password' WHERE id=$id");
    } else {
        $conn->query("UPDATE users SET name='$name', email='$email' WHERE id=$id");
    }
 $_SESSION['flash'] = "Employee updated successfully.";
    header("Location: admin_dashboard.php");
    exit();
}

// Handle leave status update
if (isset($_POST['update_leave_status'])) {
    $leave_id = intval($_POST['leave_id']);
    $new_status = $_POST['status'];
    $conn->query("UPDATE leave_requests SET status='$new_status' WHERE id=$leave_id");
     $_SESSION['flash'] = "Leave status updated.";
}

// Fetch employees and leave requests
$employees = $conn->query("SELECT * FROM users");
$leaveRequests = $conn->query("SELECT lr.id, lr.user_id, u.name, lr.leave_date, lr.leave_type, lr.status 
                               FROM leave_requests lr 
                               JOIN users u ON lr.user_id = u.id");
?>

<!DOCTYPE html>
<html>

<head>
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
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
                        <a href="export_attendance.php" class="btn btn-outline-info me-2">Export Attendance (CSV)</a>
                    </li>
                    <li class="nav-item">
                        <a href="logout.php" class="btn btn-outline-danger">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container my-5">
        <h2 class="text-center mb-4">Admin Dashboard</h2>
        <?php if (!empty($flash)): ?>
        <div class="alert alert-success alert-dismissible fade show text-center" role="alert">
            <?= htmlspecialchars($flash) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>

        <!-- Employee Section -->
        <div class="card mb-5 text-center">
            <div class="card-header bg-success text-white">All Employees</div>
            <div class="card-body">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Password</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $employees->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= $row['name'] ?></td>
                            <td><?= $row['email'] ?></td>
                            <td><?= $row['password'] ?></td>

                            <td>
                                <button class="btn btn-warning btn-sm"
                                    onclick="showEditForm(<?= $row['id'] ?>, '<?= $row['name'] ?>', '<?= $row['email'] ?>')">Edit</button>
                                <a href="?delete=<?= $row['id'] ?>" class="btn btn-danger btn-sm"
                                    onclick="return confirm('Delete this user?')">Delete</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>

                <h5 class="mt-4">Add New Employee</h5>
                <form method="post" class="row g-2">
                    <div class="col-md-3">
                        <input type="text" name="name" class="form-control" placeholder="Name" required>
                    </div>
                    <div class="col-md-3">
                        <input type="email" name="email" class="form-control" placeholder="Email" required>
                    </div>
                    <div class="col-md-3">
                        <input type="password" name="password" class="form-control" placeholder="Password" required>
                    </div>
                    <div class="col-md-3">
                        <button type="submit"  name="add_employee" class="btn btn-success">Add Employee</button>
                    </div>
                </form>

                <!-- Edit Form -->
                <div id="editForm" class="mt-4 d-none">
                    <h5>Edit Employee</h5>
                    <form method="post" class="row g-2">
                        <input type="hidden" name="id" id="editId">
                        <div class="col-md-3">
                            <input type="text" name="name" id="editName" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <input type="email" name="email" id="editEmail" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <input type="password" name="password" class="form-control"
                                placeholder="Leave blank to keep current password">
                        </div>
                        <div class="col-md-3">
                            <button type="submit" name="edit" class="btn btn-warning">Update</button>
                            <button type="button" class="btn btn-secondary" onclick="hideEditForm()">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Leave Request Section -->
        <div class="card text-center">
            <div class="card-header bg-success text-white">Manage Leave Requests</div>
            <div class="card-body">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>S.No</th>
                            <th>User</th>
                            <th>Leave Date</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Change Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i=1; while ($row = $leaveRequests->fetch_assoc()): ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><?= $row['name'] ?></td>
                            <td><?= $row['leave_date'] ?></td>
                            <td><?= $row['leave_type'] ?></td>
                            <td><?= $row['status'] ?></td>
                            <td>
                                <form method="post" class="d-flex gap-2 justify-content-center align-items-center">
                                    <input type="hidden" name="leave_id" value="<?= $row['id'] ?>">
                                    <select name="status" class="form-select form-select-sm w-auto">
                                        <option value="Pending" <?= $row['status'] == 'Pending' ? 'selected' : '' ?>>
                                            Pending</option>
                                        <option value="Approved" <?= $row['status'] == 'Approved' ? 'selected' : '' ?>>
                                            Approved</option>
                                        <option value="Rejected" <?= $row['status'] == 'Rejected' ? 'selected' : '' ?>>
                                            Rejected</option>
                                    </select>
                                    <button type="submit" name="update_leave_status"
                                        class="btn btn-primary btn-sm">Update</button>
                                    <a href="?leave_delete=<?= $row['id'] ?>" class="btn btn-danger btn-sm"
                                        onclick="return confirm('Delete this Request?')">Delete</a>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>


    </div>

    <script>
    function showEditForm(id, name, email) {
        document.getElementById('editForm').classList.remove('d-none');
        document.getElementById('editId').value = id;
        document.getElementById('editName').value = name;
        document.getElementById('editEmail').value = email;
    }

    function hideEditForm() {
        document.getElementById('editForm').classList.add('d-none');
    }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>