<?php
require_once "config.php";

$email = $password = "";
$emailErr = $passwordErr = $loginErr = "";

session_start();

if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST["email"])) {
        $emailErr = "Please enter your email address.";
    } elseif (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
        $emailErr = "Invalid email format.";
    } else {
        $email = htmlspecialchars(trim($_POST["email"]));
    }

    if (empty($_POST["password"])) {
        $passwordErr = "Please enter your password.";
    } else {
        $password = $_POST["password"];
    }

    if (empty($emailErr) && empty($passwordErr)) {
        $stmt = $conn->prepare("SELECT id, first_name, last_name, password, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 1) {
            $stmt->bind_result($id, $first_name, $last_name, $hashed_password, $role);
            $stmt->fetch();

            if (password_verify($password, $hashed_password)) {
                $_SESSION["loggedin"] = true;
                $_SESSION["id"] = $id;
                $_SESSION["first_name"] = $first_name;
                $_SESSION["last_name"] = $last_name;
                $_SESSION["email"] = $email;
                $_SESSION["role"] = $role;
                header("Location: dashboard.php");
                exit();
            } else {
                $loginErr = "Invalid email or password.";
            }
        } else {
            $loginErr = "Invalid email or password.";
        }
        $stmt->close();
    }
}

include __DIR__ . '/../templates/login.html';