<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Welcome - School Library Management System</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .main-container {
            max-width: 450px;
            margin: 4rem auto 0 auto;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 16px rgba(44,62,80,0.10);
            padding: 2.5rem 2rem 2rem 2rem;
            text-align: center;
        }
        .logo-img {
            width: 90px;
            height: 90px;
            object-fit: contain;
            margin-bottom: 1rem;
        }
        .school-name {
            font-size: 1.4rem;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 0.3rem;
        }
        .system-title {
            font-size: 1.1rem;
            color: #636e72;
            margin-bottom: 1.2rem;
        }
        .welcome-msg {
            font-size: 1.05rem;
            margin-bottom: 2rem;
            color: #34495e;
        }
        .main-btn {
            display: inline-block;
            margin: 0 0.7rem;
            padding: 0.7rem 2.2rem;
            font-size: 1rem;
            border-radius: 6px;
            border: none;
            background: #0984e3;
            color: #fff;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.18s;
            text-decoration: none;
        }
        .main-btn:hover {
            background: #74b9ff;
            color: #222;
        }
    </style>
</head>
<body>
    <!-- Layered background image and blue overlay -->
<div class="body-bg">
    <img src="school.png" alt="Background" class="bg-img">
    <div class="bg-overlay"></div>
</div>
    <div class="main-container">
        <img src="iscp.png" alt="School Logo" class="logo-img">
        <div class="school-name">International State College of the Philippines</div>
        <div class="system-title">Library Management System</div>
        <div class="welcome-msg">
            Welcome to the School Library Management System.<br>
            Please login or register to continue.
        </div>
        <a href="login.php" class="main-btn">Login</a>
        <a href="register.php" class="main-btn">Register</a>
    </div>
</body>
</html>