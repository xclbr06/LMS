<?php
session_start();
require_once "config.php";

// Only allow admin
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit();
}

// --- BOOKS CRUD ---
$bookAddError = $bookAddSuccess = "";
if (isset($_POST['add_book'])) {
    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $isbn = trim($_POST['isbn']);
    $publisher = trim($_POST['publisher']);
    $year_published = intval($_POST['year_published']);
    $category = trim($_POST['category']);
    $cover_image = trim($_POST['cover_image']);
    $copies = intval($_POST['copies']);
    $shelf_location = trim($_POST['shelf_location']);
    $availability_status = trim($_POST['availability_status']);

    if (empty($title) || empty($author) || empty($isbn) || empty($publisher) || empty($year_published) || empty($category) || empty($copies) || empty($shelf_location) || empty($availability_status)) {
        $bookAddError = "Please fill in all required fields.";
    } else {
        $stmt = $conn->prepare("INSERT INTO books (title, author, isbn, publisher, year_published, category, cover_image, copies, shelf_location, availability_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssississ", $title, $author, $isbn, $publisher, $year_published, $category, $cover_image, $copies, $shelf_location, $availability_status);
        if ($stmt->execute()) {
            $bookAddSuccess = "Book added successfully.";
        } else {
            $bookAddError = "Failed to add book.";
        }
        $stmt->close();
        header("Location: admin.php");
        exit();
    }
}

// Edit Book
if (isset($_POST['edit_book'])) {
    $id = intval($_POST['book_id']);
    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $isbn = trim($_POST['isbn']);
    $publisher = trim($_POST['publisher']);
    $year_published = intval($_POST['year_published']);
    $category = trim($_POST['category']);
    $cover_image = trim($_POST['cover_image']);
    $copies = intval($_POST['copies']);
    $shelf_location = trim($_POST['shelf_location']);
    $availability_status = trim($_POST['availability_status']);

    $stmt = $conn->prepare("UPDATE books SET title=?, author=?, isbn=?, publisher=?, year_published=?, category=?, cover_image=?, copies=?, shelf_location=?, availability_status=? WHERE id=?");
    $stmt->bind_param("ssssississi", $title, $author, $isbn, $publisher, $year_published, $category, $cover_image, $copies, $shelf_location, $availability_status, $id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin.php");
    exit();
}

// Delete Book
if (isset($_POST['delete_book'])) {
    $id = intval($_POST['book_id']);
    $stmt = $conn->prepare("DELETE FROM books WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin.php");
    exit();
}

// --- USERS CRUD (add/edit/delete) ---
$userAddError = $userAddSuccess = "";
if (isset($_POST['add_user'])) {
    $first_name = trim($_POST['first_name']);
    $middle_name = trim($_POST['middle_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $student_id = trim($_POST['student_id']);
    $phone = trim($_POST['phone']);
    $role = trim($_POST['role']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Basic validation
    if (empty($first_name) || empty($last_name) || empty($email) || empty($student_id) || empty($role) || empty($password) || empty($confirm_password)) {
        $userAddError = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $userAddError = "Invalid email format.";
    } elseif ($password !== $confirm_password) {
        $userAddError = "Passwords do not match.";
    } else {
        // Check for duplicate email or student_id
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR student_id = ?");
        $stmt->bind_param("ss", $email, $student_id);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $userAddError = "Email or Student ID already exists.";
        } else {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (first_name, middle_name, last_name, email, student_id, password, phone, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssss", $first_name, $middle_name, $last_name, $email, $student_id, $passwordHash, $phone, $role);
            if ($stmt->execute()) {
                $userAddSuccess = "User added successfully.";
            } else {
                $userAddError = "Failed to add user.";
            }
            $stmt->close();
            header("Location: admin.php");
            exit();
        }
        $stmt->close();
    }
}

// Edit User
if (isset($_POST['edit_user'])) {
    $id = intval($_POST['user_id']);
    $first_name = trim($_POST['first_name']);
    $middle_name = trim($_POST['middle_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $student_id = trim($_POST['student_id']);
    $phone = trim($_POST['phone']);
    $role = trim($_POST['role']);

    $stmt = $conn->prepare("UPDATE users SET first_name=?, middle_name=?, last_name=?, email=?, student_id=?, phone=?, role=? WHERE id=?");
    $stmt->bind_param("sssssssi", $first_name, $middle_name, $last_name, $email, $student_id, $phone, $role, $id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin.php");
    exit();
}

if (isset($_POST['delete_user'])) {
    $id = intval($_POST['user_id']);
    $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin.php");
    exit();
}

// --- CATEGORIES CRUD (add/delete) ---
$catAddError = $catAddSuccess = "";
if (isset($_POST['add_category'])) {
    $category = trim($_POST['category']);
    if (empty($category)) {
        $catAddError = "Category name required.";
    } else {
        $exists = $conn->prepare("SELECT 1 FROM books WHERE category=? LIMIT 1");
        $exists->bind_param("s", $category);
        $exists->execute();
        $exists->store_result();
        if ($exists->num_rows == 0) {
            $stmt = $conn->prepare("INSERT INTO books (title, author, isbn, publisher, year_published, category, copies, shelf_location, availability_status) VALUES ('Category Placeholder', '', '', '', 2000, ?, 0, '', 'available')");
            $stmt->bind_param("s", $category);
            $stmt->execute();
            $stmt->close();
            $catAddSuccess = "Category added.";
        } else {
            $catAddError = "Category already exists.";
        }
        $exists->close();
        header("Location: admin.php");
        exit();
    }
}
if (isset($_POST['delete_category'])) {
    $category = trim($_POST['category']);
    $stmt = $conn->prepare("DELETE FROM books WHERE category=?");
    $stmt->bind_param("s", $category);
    $stmt->execute();
    $stmt->close();
    header("Location: admin.php");
    exit();
}

// --- RESERVATIONS CRUD ---
// Edit Reservation
if (isset($_POST['edit_reservation'])) {
    $id = intval($_POST['reservation_id']);
    $user_id = intval($_POST['user_id']);
    $book_id = intval($_POST['book_id']);
    $due_date = $_POST['due_date'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("SELECT status, book_id FROM reservations WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($old_status, $old_book_id);
    $stmt->fetch();
    $stmt->close();

    if ($old_book_id != $book_id) {
        $conn->query("UPDATE books SET copies = copies + 1 WHERE id = $old_book_id");
        $conn->query("UPDATE books SET copies = copies - 1 WHERE id = $book_id AND copies > 0");
    }
    if ($old_status == 'reserved' && $status == 'returned') {
        $conn->query("UPDATE books SET copies = copies + 1 WHERE id = $book_id");
    }
    if ($old_status == 'returned' && $status == 'reserved') {
        $conn->query("UPDATE books SET copies = copies - 1 WHERE id = $book_id AND copies > 0");
    }

    $stmt = $conn->prepare("UPDATE reservations SET user_id=?, book_id=?, due_date=?, status=? WHERE id=?");
    $stmt->bind_param("iissi", $user_id, $book_id, $due_date, $status, $id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin.php");
    exit();
}

// Delete Reservation
if (isset($_POST['delete_reservation'])) {
    $id = intval($_POST['reservation_id']);
    $stmt = $conn->prepare("SELECT book_id, status FROM reservations WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($book_id, $status);
    $stmt->fetch();
    $stmt->close();

    if ($status == 'reserved') {
        $conn->query("UPDATE books SET copies = copies + 1 WHERE id = $book_id");
    }

    $stmt = $conn->prepare("DELETE FROM reservations WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin.php");
    exit();
}

// Fetch all books, users, and reservations for dropdowns
$booksList = $conn->query("SELECT id, title FROM books ORDER BY title ASC")->fetch_all(MYSQLI_ASSOC);
$usersList = $conn->query("SELECT id, first_name, last_name FROM users ORDER BY first_name ASC")->fetch_all(MYSQLI_ASSOC);

// Fetch all books
$books = $conn->query("SELECT * FROM books ORDER BY id DESC")->fetch_all(MYSQLI_ASSOC);
// Fetch all reservations
$reservations = $conn->query("SELECT r.*, u.first_name, u.last_name, b.title FROM reservations r JOIN users u ON r.user_id = u.id JOIN books b ON r.book_id = b.id ORDER BY r.id DESC")->fetch_all(MYSQLI_ASSOC);
// Fetch all categories (distinct)
$categories = $conn->query("SELECT DISTINCT category FROM books")->fetch_all(MYSQLI_ASSOC);
// Fetch all users
$users = $conn->query("SELECT * FROM users ORDER BY id DESC")->fetch_all(MYSQLI_ASSOC);

// Pass all variables to the HTML template
include __DIR__ . '/../templates/admin.html';
