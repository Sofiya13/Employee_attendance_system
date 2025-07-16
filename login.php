<?php
session_start();
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $password = $_POST['password'];
    
    // Admin Login Check
    if ($name === 'admin' && $password === '123') {
        $_SESSION['admin'] = true;
        header("Location: admin_dashboard.php");
        exit();
    }

    // Employee Login Check
    $sql = "SELECT * FROM users WHERE name='$name' AND password='$password'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['user_name'] = $row['name'];
        header("Location: dashboard.php");
        exit();
    } else {
        echo "<div class='alert alert-danger' role='alert'>Invalid login credentials.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(to right, #5f2c82, #49a09d); /* Custom gradient */
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'Arial', sans-serif;
        }

        .card {
            width: 100%;
            max-width: 400px;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            background-color: #ffffff;
        }

        .card h2 {
            text-align: center;
            margin-bottom: 1.5rem;
            color: #5f2c82; /* Custom text color */
        }

        .form-control {
            border-radius: 5px;
            margin-bottom: 1rem;
            border: 1px solid #ccc;
        }

        .btn {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            background-color: #49a09d; /* Custom button color */
            color: #fff;
            border: none;
            font-size: 1.1rem;
            transition: background-color 0.3s ease;
        }

        .btn:hover {
            background-color: #5f2c82;
            cursor: pointer;
        }

        .alert {
            margin-top: 1rem;
        }

        .fade-in {
            animation: fadeIn 1s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        .forgot-password {
            text-align: center;
            margin-top: 1rem;
        }

        .forgot-password a {
            color: #49a09d; /* Custom link color */
            text-decoration: none;
        }

        .forgot-password a:hover {
            text-decoration: underline;
        }

        .logo {
            display: block;
            margin: 0 auto;
            max-width: 200px;
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>
    <div class="card fade-in">
        <!-- Custom Logo -->
        <img src="logo.png" alt="Logo" class="logo">
        
        <h2>Login</h2>
        <form method="post">
            <input type="text" name="name" class="form-control" placeholder="Enter your name" required><br>
            <input type="password" name="password" class="form-control" placeholder="Password" required><br>
            <input type="submit" value="Login" class="btn">
        </form>
        
        <!-- Forgot Password Link (Contact Admin) -->
        <div class="forgot-password">
            <p>If you forgot your password, please <a href="mailto:sofiyashaikh2003@gmail.com">contact the admin</a>.</p>
        </div>
    </div>

    <!-- Bootstrap JS (Optional) -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html>
