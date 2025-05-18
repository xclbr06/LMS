<?php
require_once "config.php";

$email = $password = "";
$emailErr = $passwordErr = $loginErr = "";

// Start session at the top for consistency
session_start();

// Redirect to dashboard if already logged in
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Email Validation
    if (empty($_POST["email"])) {
        $emailErr = "Please enter your email address.";
    } elseif (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
        $emailErr = "Invalid email format.";
    } else {
        $email = htmlspecialchars(trim($_POST["email"]));
    }

    // Password Validation
    if (empty($_POST["password"])) {
        $passwordErr = "Please enter your password.";
    } else {
        $password = $_POST["password"];
    }

    // If no validation errors, proceed to check credentials
    if (empty($emailErr) && empty($passwordErr)) {
        // Fetch user by email
        $stmt = $conn->prepare("SELECT id, first_name, last_name, password, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 1) {
            $stmt->bind_result($id, $first_name, $last_name, $hashed_password, $role);
            $stmt->fetch();

            if (password_verify($password, $hashed_password)) {
                // Password is correct, set session variables
                $_SESSION["loggedin"] = true;
                $_SESSION["id"] = $id;
                $_SESSION["first_name"] = $first_name;
                $_SESSION["last_name"] = $last_name;
                $_SESSION["email"] = $email;
                $_SESSION["role"] = $role;
            
                // Always redirect to dashboard
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
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login Form</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<h2>Login Form</h2>
<?php if ($loginErr): ?>
    <p class="error"><?= htmlspecialchars($loginErr) ?></p>
<?php endif; ?>
<div class="form-section">
    <form method="post" action="login.php" autocomplete="off">
        <label>Email:
            <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required>
            <span class="error"><?= $emailErr ?></span>
            <br>
        </label>
        <label>Password:
            <input type="password" name="password" required>
            <span class="error"><?= $passwordErr ?></span>
            <br>
        </label>
        <button type="submit">Login</button>
        <div class="register-link">
            No account? <a href="register.php">Register Here!</a>
        </div>
    </form>
</div>
</body>
</html>