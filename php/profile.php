<?php
session_start();
require_once "config.php";

// Prevent browser caching for security
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Redirect to login if not logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['id'];
$role = $_SESSION['role'];

// Fetch user info
$stmt = $conn->prepare("SELECT first_name, middle_name, last_name, email, student_teacher_id, phone, role FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($first_name, $middle_name, $last_name, $email, $student_teacher_id, $phone, $user_role);
$stmt->fetch();
$stmt->close();

$successMsg = $errorMsg = "";

// Handle profile update (for user or admin)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $new_first_name = htmlspecialchars(trim($_POST["first_name"]));
    $new_middle_name = htmlspecialchars(trim($_POST["middle_name"]));
    $new_last_name = htmlspecialchars(trim($_POST["last_name"]));
    $new_email = htmlspecialchars(trim($_POST["email"]));
    $new_student_id = htmlspecialchars(trim($_POST["student_teacher_id"]));
    $new_phone = htmlspecialchars(trim($_POST["phone"]));

    // Validation (add more as needed)
    if (empty($new_first_name) || empty($new_last_name) || empty($new_email) || empty($new_student_id)) {
        $errorMsg = "Please fill in all required fields.";
    } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $errorMsg = "Invalid email format.";
    } elseif (!preg_match("/^\d{4}-\d{4}$/", $new_student_id)) {
        $errorMsg = "Student ID must be in the format 1234-5678.";
    } else {
        // Check for duplicate email/student_teacher_id (excluding self)
        $stmt = $conn->prepare("SELECT id FROM users WHERE (email = ? OR student_teacher_id = ?) AND id != ?");
        $stmt->bind_param("ssi", $new_email, $new_student_id, $user_id);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errorMsg = "Email or Student ID already in use.";
        } else {
            // Update user info
            $stmt = $conn->prepare("UPDATE users SET first_name=?, middle_name=?, last_name=?, email=?, student_teacher_id=?, phone=? WHERE id=?");
            $stmt->bind_param("ssssssi", $new_first_name, $new_middle_name, $new_last_name, $new_email, $new_student_id, $new_phone, $user_id);
            if ($stmt->execute()) {
                $successMsg = "Profile updated successfully.";
                // Update session info
                $_SESSION["first_name"] = $new_first_name;
                $_SESSION["last_name"] = $new_last_name;
                // Refresh variables for display
                $first_name = $new_first_name;
                $middle_name = $new_middle_name;
                $last_name = $new_last_name;
                $email = $new_email;
                $student_teacher_id = $new_student_id;
                $phone = $new_phone;
            } else {
                $errorMsg = "Failed to update profile.";
            }
            $stmt->close();
        }
    }
}

include __DIR__ . '/../templates/profile.html';
