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
    $student_teacher_id = trim($_POST['student_teacher_id']);
    $phone = trim($_POST['phone']);
    $role = trim($_POST['role']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Basic validation
    if (empty($first_name) || empty($last_name) || empty($email) || empty($student_teacher_id) || empty($role) || empty($password) || empty($confirm_password)) {
        $userAddError = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $userAddError = "Invalid email format.";
    } elseif ($password !== $confirm_password) {
        $userAddError = "Passwords do not match.";
    } else {
        // Check for duplicate email or student_teacher_id
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR student_teacher_id = ?");
        $stmt->bind_param("ss", $email, $student_teacher_id);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $userAddError = "Email or Student ID already exists.";
        } else {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (first_name, middle_name, last_name, email, student_teacher_id, password, phone, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssss", $first_name, $middle_name, $last_name, $email, $student_teacher_id, $passwordHash, $phone, $role);
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
    $student_teacher_id = trim($_POST['student_teacher_id']);
    $phone = trim($_POST['phone']);
    $role = trim($_POST['role']);

    $stmt = $conn->prepare("UPDATE users SET first_name=?, middle_name=?, last_name=?, email=?, student_teacher_id=?, phone=?, role=? WHERE id=?");
    $stmt->bind_param("sssssssi", $first_name, $middle_name, $last_name, $email, $student_teacher_id, $phone, $role, $id);
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

// --- Edit Category ---
if (isset($_POST['edit_category'])) {
    $original_category = trim($_POST['original_category']);
    $new_category = trim($_POST['category']);
    if ($original_category !== $new_category && !empty($new_category)) {
        $stmt = $conn->prepare("UPDATE books SET category=? WHERE category=?");
        $stmt->bind_param("ss", $new_category, $original_category);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: admin.php?activeTab=categories");
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

// --- INVENTORY SEARCH & SORT ---
$inventorySearch = trim($_GET['inventorySearch'] ?? '');
$inventorySortField = $_GET['inventorySortField'] ?? 'id';
$inventorySortOrder = $_GET['inventorySortOrder'] ?? 'asc';

$allowedInventoryFields = ['id', 'title', 'author', 'category', 'availability_status'];
if (!in_array($inventorySortField, $allowedInventoryFields)) $inventorySortField = 'id';
$inventorySortOrder = ($inventorySortOrder === 'desc') ? 'DESC' : 'ASC';

$inventoryWhere = '';
$inventoryParams = [];
if ($inventorySearch !== '') {
    $inventoryWhere = "WHERE id LIKE ? OR title LIKE ? OR author LIKE ? OR category LIKE ? OR availability_status LIKE ?";
    $searchParam = "%$inventorySearch%";
    $inventoryParams = array_fill(0, 5, $searchParam);
}
$inventorySql = "SELECT * FROM books $inventoryWhere ORDER BY $inventorySortField $inventorySortOrder";
$inventoryStmt = $conn->prepare($inventorySql);
if ($inventoryWhere) {
    $inventoryStmt->bind_param(str_repeat('s', 5), ...$inventoryParams);
}
$inventoryStmt->execute();
$books = $inventoryStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$inventoryStmt->close();

// --- CATEGORIES SEARCH & SORT ---
$categorySearch = trim($_GET['categorySearch'] ?? '');
$categorySortOrder = ($_GET['categorySortOrder'] ?? 'asc') === 'desc' ? 'DESC' : 'ASC';

$categoryWhere = '';
$categoryParams = [];
if ($categorySearch !== '') {
    $categoryWhere = "WHERE category LIKE ?";
    $categoryParams[] = "%$categorySearch%";
}
$categorySql = "SELECT DISTINCT category FROM books $categoryWhere ORDER BY category $categorySortOrder";
$categoryStmt = $conn->prepare($categorySql);
if ($categoryWhere) {
    $categoryStmt->bind_param('s', ...$categoryParams);
}
$categoryStmt->execute();
$categories = $categoryStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$categoryStmt->close();

// --- USERS SEARCH & SORT ---
$userSearch = trim($_GET['userSearch'] ?? '');
$userSortField = $_GET['userSortField'] ?? 'first_name';
$userSortOrder = ($_GET['userSortOrder'] ?? 'asc') === 'desc' ? 'DESC' : 'ASC';

$allowedUserFields = ['first_name', 'last_name', 'email', 'role'];
if (!in_array($userSortField, $allowedUserFields)) $userSortField = 'first_name';

$userWhere = '';
$userParams = [];
if ($userSearch !== '') {
    $userWhere = "WHERE first_name LIKE ? OR middle_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR student_teacher_id LIKE ? OR phone LIKE ? OR role LIKE ?";
    $searchParam = "%$userSearch%";
    $userParams = array_fill(0, 7, $searchParam);
}
$userSql = "SELECT * FROM users $userWhere ORDER BY $userSortField $userSortOrder";
$userStmt = $conn->prepare($userSql);
if ($userWhere) {
    $userStmt->bind_param(str_repeat('s', 7), ...$userParams);
}
$userStmt->execute();
$users = $userStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$userStmt->close();

// --- RESERVATIONS SEARCH & SORT ---
$reservationSearch = trim($_GET['reservationSearch'] ?? '');
$reservationSortField = $_GET['reservationSortField'] ?? 'user';
$reservationSortOrder = ($_GET['reservationSortOrder'] ?? 'asc') === 'desc' ? 'DESC' : 'ASC';

$allowedReservationFields = ['user', 'book', 'due_date', 'status'];
if (!in_array($reservationSortField, $allowedReservationFields)) $reservationSortField = 'user';

// For sorting, use SQL aliases
$reservationFieldMap = [
    'user' => 'u.first_name',
    'book' => 'b.title',
    'due_date' => 'r.due_date',
    'status' => 'r.status'
];
$reservationOrderBy = $reservationFieldMap[$reservationSortField] . " $reservationSortOrder";

$reservationWhere = '';
$reservationParams = [];
if ($reservationSearch !== '') {
    $reservationWhere = "WHERE r.id LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ? OR b.title LIKE ? OR r.reserved_at LIKE ? OR r.due_date LIKE ? OR r.status LIKE ?";
    $searchParam = "%$reservationSearch%";
    $reservationParams = array_fill(0, 7, $searchParam);
}
$reservationSql = "SELECT r.*, u.first_name, u.last_name, b.title 
    FROM reservations r 
    JOIN users u ON r.user_id = u.id 
    JOIN books b ON r.book_id = b.id 
    $reservationWhere 
    ORDER BY $reservationOrderBy";
$reservationStmt = $conn->prepare($reservationSql);
if ($reservationWhere) {
    $reservationStmt->bind_param(str_repeat('s', 7), ...$reservationParams);
}
$reservationStmt->execute();
$reservations = $reservationStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$reservationStmt->close();

// Fetch all books, users for dropdowns (unchanged)
$booksList = $conn->query("SELECT id, title FROM books ORDER BY title ASC")->fetch_all(MYSQLI_ASSOC);
$usersList = $conn->query("SELECT id, first_name, last_name FROM users ORDER BY first_name ASC")->fetch_all(MYSQLI_ASSOC);

// Pass all variables to the HTML template
include __DIR__ . '/../templates/admin.html';
