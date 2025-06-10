<?php
require_once "config.php";

// Initialize variables
$firstName = $middleName = $lastName = $email = $studentId = $password = $confirmPassword = $phone = "";
$firstNameErr = $middleNameErr = $lastNameErr = $emailErr = $studentIdErr = $passwordErr = $confirmPasswordErr = $phoneErr = "";
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $validForm = true;

    // First Name Validation
    if (empty($_POST["first_name"])) {
        $firstNameErr = "First name is required.";
        $validForm = false;
    } elseif (!preg_match("/^[a-zA-Z-' ]{2,50}$/", $_POST["first_name"])) {
        $firstNameErr = "First name must be 2-50 letters.";
        $validForm = false;
    } else {
        $firstName = htmlspecialchars(trim($_POST["first_name"]));
    }

    // Middle Name Validation (Optional)
    if (!empty($_POST["middle_name"])) {
        if (!preg_match("/^[a-zA-Z-' ]{0,50}$/", $_POST["middle_name"])) {
            $middleNameErr = "Middle name must be up to 50 letters.";
            $validForm = false;
        } else {
            $middleName = htmlspecialchars(trim($_POST["middle_name"]));
        }
    }

    // Last Name Validation
    if (empty($_POST["last_name"])) {
        $lastNameErr = "Last name is required.";
        $validForm = false;
    } elseif (!preg_match("/^[a-zA-Z-' ]{2,50}$/", $_POST["last_name"])) {
        $lastNameErr = "Last name must be 2-50 letters.";
        $validForm = false;
    } else {
        $lastName = htmlspecialchars(trim($_POST["last_name"]));
    }

    // Email Validation
    if (empty($_POST["email"])) {
        $emailErr = "Please enter a valid email address.";
        $validForm = false;
    } elseif (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
        $emailErr = "Invalid email format.";
        $validForm = false;
    } elseif (!preg_match('/^[a-zA-Z0-9._%+-]+@iscp\.edu\.ph$/', $_POST["email"])) {
        $emailErr = "Only @iscp.edu.ph email addresses are allowed.";
        $validForm = false;
    } else {
        $email = htmlspecialchars(trim($_POST["email"]));
    }

    // School ID Validation
    if (empty($_POST["student_teacher_id"])) {
        $studentIdErr = "School ID is required.";
        $validForm = false;
    } elseif (!preg_match("/^\d{4}-\d{4}$/", $_POST["student_teacher_id"])) {
        $studentIdErr = "School ID must be in the format 1234-5678.";
        $validForm = false;
    } else {
        $studentId = htmlspecialchars(trim($_POST["student_teacher_id"]));
    }

    // Password Validation
    if (empty($_POST["password"])) {
        $passwordErr = "Password is required.";
        $validForm = false;
    } elseif (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,20}$/", $_POST["password"])) {
        $passwordErr = "Password must be 8-20 chars, 1 uppercase, 1 number, 1 special char.";
        $validForm = false;
    } else {
        $password = $_POST["password"];
    }

    // Confirm Password Validation
    if (empty($_POST["confirm_password"])) {
        $confirmPasswordErr = "Please confirm your password.";
        $validForm = false;
    } elseif ($_POST["password"] !== $_POST["confirm_password"]) {
        $confirmPasswordErr = "Passwords do not match.";
        $validForm = false;
    } else {
        $confirmPassword = $_POST["confirm_password"];
    }

    // Phone Number Validation (Optional)
    if (!empty($_POST["phone"])) {
        if (!preg_match("/^[0-9\-\(\)\/\+\s]{7,20}$/", $_POST["phone"])) {
            $phoneErr = "Invalid phone number format.";
            $validForm = false;
        } else {
            $phone = htmlspecialchars(trim($_POST["phone"]));
        }
    }

    // Check for duplicate email and school ID if form is valid
    if ($validForm) {
        // Check for duplicate email
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $emailErr = "This email is already registered.";
            $validForm = false;
        }
        $stmt->close();

        // Check for duplicate school ID
        $stmt = $conn->prepare("SELECT id FROM users WHERE student_teacher_id = ?");
        $stmt->bind_param("s", $studentId);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $studentIdErr = "This School ID is already registered.";
            $validForm = false;
        }
        $stmt->close();
    }

    // If valid, insert into database
    if ($validForm) {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $role = "student";
        $stmt = $conn->prepare("INSERT INTO users (first_name, middle_name, last_name, email, student_teacher_id, password, phone, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $firstName, $middleName, $lastName, $email, $studentId, $passwordHash, $phone, $role);
        if ($stmt->execute()) {
            header("Location: login.php?registered=1");
            exit();
        } else {
            $emailErr = "Database error: " . htmlspecialchars($stmt->error);
        }
        $stmt->close();
    }
}

include __DIR__ . '/../templates/register.html';