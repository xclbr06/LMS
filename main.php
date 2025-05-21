<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Welcome - School Library Management System</title>
    <link rel="stylesheet" href="styles/main.css">
</head>
<body>
<div class="body-bg">
    <img src="img/school.png" alt="Background" class="bg-img">
    <div class="bg-overlay"></div>
</div>
    <div class="main-container">
        <img src="img/iscp.png" alt="School Logo" class="logo-img">
        <div class="school-name">International State College of the Philippines</div>
        <div class="system-title">Library Management System</div>
        <div class="welcome-msg">
            Welcome to the School Library Management System.<br>
            Please login or register to continue.
        </div>
        <a href="php/login.php" class="main-btn">Login</a>
        <a href="php/register.php" class="main-btn">Register</a>
    </div>
</body>
</html>