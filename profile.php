
<?php
session_start();
require_once "config.php";

// Redirect to login if not logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['id'];
$role = $_SESSION['role'];

// Fetch user info
$stmt = $conn->prepare("SELECT first_name, middle_name, last_name, email, student_id, phone, role FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($first_name, $middle_name, $last_name, $email, $student_id, $phone, $user_role);
$stmt->fetch();
$stmt->close();

$successMsg = $errorMsg = "";

// Handle profile update (for user or admin)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $new_first_name = htmlspecialchars(trim($_POST["first_name"]));
    $new_middle_name = htmlspecialchars(trim($_POST["middle_name"]));
    $new_last_name = htmlspecialchars(trim($_POST["last_name"]));
    $new_email = htmlspecialchars(trim($_POST["email"]));
    $new_student_id = htmlspecialchars(trim($_POST["student_id"]));
    $new_phone = htmlspecialchars(trim($_POST["phone"]));

    // Validation (add more as needed)
    if (empty($new_first_name) || empty($new_last_name) || empty($new_email) || empty($new_student_id)) {
        $errorMsg = "Please fill in all required fields.";
    } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $errorMsg = "Invalid email format.";
    } elseif (!preg_match("/^\d{4}-\d{4}$/", $new_student_id)) {
        $errorMsg = "Student ID must be in the format 1234-5678.";
    } else {
        // Check for duplicate email/student_id (excluding self)
        $stmt = $conn->prepare("SELECT id FROM users WHERE (email = ? OR student_id = ?) AND id != ?");
        $stmt->bind_param("ssi", $new_email, $new_student_id, $user_id);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errorMsg = "Email or Student ID already in use.";
        } else {
            // Update user info
            $stmt = $conn->prepare("UPDATE users SET first_name=?, middle_name=?, last_name=?, email=?, student_id=?, phone=? WHERE id=?");
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
                $student_id = $new_student_id;
                $phone = $new_phone;
            } else {
                $errorMsg = "Failed to update profile.";
            }
            $stmt->close();
        }
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Profile - School Library Management System</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            /* background remains unchanged */
            color: #181818; /* darker text for better readability */
        }
        .profile-section { 
            max-width: 500px; 
            margin: 2rem auto; 
            background: #fff; 
            padding: 2rem; 
            border-radius: 8px; 
            box-shadow: 0 2px 8px rgba(44,62,80,0.08);
            color: #181818; /* darker text */
        }
        .profile-section h2 { 
            text-align: center; 
            color: #111111; /* even darker for headings */
            letter-spacing: 1px;
        }
        .profile-section label { 
            display: block; 
            margin-top: 1rem; 
            color: #222; /* darker label */
            font-weight: 600;
        }
        .profile-section input { 
            width: 100%; 
            padding: 0.5rem; 
            margin-top: 0.2rem; 
            border: 1px solid #888;
            border-radius: 4px;
            color: #181818; /* input text */
            background: #f9f9f9;
        }
        .profile-section input:focus {
            border-color: #0056b3;
            outline: none;
            background: #fff;
        }
        .profile-section .success { 
            color: #155724; 
            background: #d4edda;
            border: 1px solid #c3e6cb;
            padding: 0.75rem 1rem;
            border-radius: 4px;
            margin-top: 1rem;
            font-weight: 600;
        }
        .profile-section .error { 
            color: #721c24; 
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 0.75rem 1rem;
            border-radius: 4px;
            margin-top: 1rem;
            font-weight: 600;
        }
        .profile-section button[type="submit"] {
            margin-top: 1.5rem;
            width: 100%;
            padding: 0.7rem;
            background: #181818;
            color: #fff;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.2s;
        }
        .profile-section button[type="submit"]:hover {
            background: #0056b3;
        }
        .admin-link { 
            margin-top: 2rem; 
            text-align: center; 
        }
        .admin-link a { 
            color: #0056b3; 
            font-weight: bold; 
            text-decoration: none; 
            font-size: 1.05rem;
        }
        .admin-link a:hover { 
            text-decoration: underline; 
            color: #003366;
        }
    </style>
</head>
<body>
    <!-- Layered background image and blue overlay -->
<div class="body-bg">
    <img src="school.png" alt="Background" class="bg-img">
    <div class="bg-overlay"></div>
</div>
<?php include 'navbar.php'; ?>
<div class="profile-section">
    <h2>Profile</h2>
    <?php if ($successMsg): ?><div class="success"><?= $successMsg ?></div><?php endif; ?>
    <?php if ($errorMsg): ?><div class="error"><?= $errorMsg ?></div><?php endif; ?>

    <form method="post">
        <label>First Name:
            <input type="text" name="first_name" value="<?= htmlspecialchars($first_name) ?>" required>
        </label>
        <label>Middle Name:
            <input type="text" name="middle_name" value="<?= htmlspecialchars($middle_name) ?>">
        </label>
        <label>Last Name:
            <input type="text" name="last_name" value="<?= htmlspecialchars($last_name) ?>" required>
        </label>
        <label>Email:
            <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required>
        </label>
        <label>Student ID:
            <input type="text" name="student_id" value="<?= htmlspecialchars($student_id) ?>" required>
        </label>
        <label>Phone Number:
            <input type="text" name="phone" value="<?= htmlspecialchars($phone) ?>">
        </label>
        <button type="submit" name="update_profile">Update Profile</button>
    </form>

    <?php if ($role === "admin"): ?>
        <div class="admin-link">
            <a href="admin.php">Go to Admin Panel</a>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
