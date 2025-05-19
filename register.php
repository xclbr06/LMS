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
    } else {
        $email = htmlspecialchars(trim($_POST["email"]));
    }

    // Student ID Validation
    if (empty($_POST["student_id"])) {
        $studentIdErr = "Student ID is required.";
        $validForm = false;
    } elseif (!preg_match("/^\d{4}-\d{4}$/", $_POST["student_id"])) {
        $studentIdErr = "Student ID must be in the format 1234-5678.";
        $validForm = false;
    } else {
        $studentId = htmlspecialchars(trim($_POST["student_id"]));
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
        // Accepts numbers, spaces, dashes, parentheses, and plus sign
        if (!preg_match("/^[0-9\-\(\)\/\+\s]{7,20}$/", $_POST["phone"])) {
            $phoneErr = "Invalid phone number format.";
            $validForm = false;
        } else {
            $phone = htmlspecialchars(trim($_POST["phone"]));
        }
    }

    // Check for duplicate email and student ID if form is valid
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

        // Check for duplicate student ID
        $stmt = $conn->prepare("SELECT id FROM users WHERE student_id = ?");
        $stmt->bind_param("s", $studentId);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $studentIdErr = "This student ID is already registered.";
            $validForm = false;
        }
        $stmt->close();
    }

    // If valid, insert into database
    if ($validForm) {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $role = "student"; // Automatically assign 'student' role
        $stmt = $conn->prepare("INSERT INTO users (first_name, middle_name, last_name, email, student_id, password, phone, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $firstName, $middleName, $lastName, $email, $studentId, $passwordHash, $phone, $role);
        if ($stmt->execute()) {
            $success = true;
        } else {
            $emailErr = "Database error: " . htmlspecialchars($stmt->error);
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Registration Form</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Layered background image and blue overlay -->
<div class="body-bg">
    <img src="school.png" alt="Background" class="bg-img">
    <div class="bg-overlay"></div>
</div>
<h2>Registration Form</h2>
<?php if ($success): ?>
    <p class="success">Registration successful! You can now <a href="login.php">login</a>.</p>
<?php else: ?>
    <div class="form-section">
        <form method="post" action="register.php">
            <label>First Name:
                <input type="text" name="first_name" value="<?= htmlspecialchars($firstName) ?>">
                <span class="error"><?= $firstNameErr ?></span>
                <br>
            </label>
            <label>Middle Name (optional):
                <input type="text" name="middle_name" value="<?= htmlspecialchars($middleName) ?>">
                <span class="error"><?= $middleNameErr ?></span>
                <br>
            </label>
            <label>Last Name:
                <input type="text" name="last_name" value="<?= htmlspecialchars($lastName) ?>">
                <span class="error"><?= $lastNameErr ?></span>
                <br>
            </label>
            <label>Email:
                <input type="email" name="email" value="<?= htmlspecialchars($email) ?>">
                <span class="error"><?= $emailErr ?></span>
                <br>
            </label>
            <label>Student ID:
                <input type="text" name="student_id" value="<?= htmlspecialchars($studentId) ?>">
                <span class="error"><?= $studentIdErr ?></span>
                <br>
            </label>
            <label>Password:
                <input type="password" name="password">
                <span class="error"><?= $passwordErr ?></span>
                <br>
            </label>
            <label>Confirm Password:
                <input type="password" name="confirm_password">
                <span class="error"><?= $confirmPasswordErr ?></span>
                <br>
            </label>
            <label>Phone Number (optional):
                <input type="text" name="phone" value="<?= htmlspecialchars($phone) ?>">
                <span class="error"><?= $phoneErr ?></span>
                <br>
            </label>
            <button type="submit">Register</button>
            <div class="login-link">
                Already have an account? <a href="login.php">Login Here!</a>
            </div>
        </form>
    </div>
<?php endif; ?>
</body>
</html>