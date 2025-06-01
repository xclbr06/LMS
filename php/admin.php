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
    $id = $_POST['book_id'];
    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $category = trim($_POST['category']);
    $copies = $_POST['copies'];
    $availability_status = $_POST['availability_status'];

    $stmt = $conn->prepare("UPDATE books SET title=?, author=?, category=?, copies=?, availability_status=? WHERE id=?");
    $stmt->bind_param("sssisi", $title, $author, $category, $copies, $availability_status, $id);
    $stmt->execute();
    $stmt->close();
    // Redirect after edit to ensure UI updates and prevent resubmission
    header("Location: admin.php?activeTab=inventory");
    exit();
}

// Delete Book
if (isset($_POST['delete_book'])) {
    $id = intval($_POST['book_id']);
    $stmt = $conn->prepare("DELETE FROM books WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin.php?activeTab=inventory");
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
            header("Location: admin.php?activeTab=users");
            exit();
        }
        $stmt->close();
    }
}

// Edit User
if (isset($_POST['edit_user'])) {
    $id = $_POST['user_id'];
    $first_name = trim($_POST['first_name']);
    $middle_name = trim($_POST['middle_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $student_teacher_id = trim($_POST['student_teacher_id']);
    $phone = trim($_POST['phone']);
    $role = $_POST['role'];

    $stmt = $conn->prepare("UPDATE users SET first_name=?, middle_name=?, last_name=?, email=?, student_teacher_id=?, phone=?, role=? WHERE id=?");
    $stmt->bind_param("sssssssi", $first_name, $middle_name, $last_name, $email, $student_teacher_id, $phone, $role, $id);
    $stmt->execute();
    $stmt->close();
    // Redirect after edit to ensure UI updates and prevent resubmission
    header("Location: admin.php?activeTab=users");
    exit();
}

if (isset($_POST['delete_user'])) {
    $id = intval($_POST['user_id']);
    $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin.php?activeTab=users");
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

// --- ADD RESERVATION HANDLER ---
$reservationAddError = $reservationAddSuccess = "";
if (isset($_POST['add_reservation'])) {
    $user_id = intval($_POST['user_id'] ?? 0);
    $book_id = intval($_POST['book_id'] ?? 0);
    $borrow_start_date = trim($_POST['borrow_start_date'] ?? '');
    $due_date = trim($_POST['due_date'] ?? '');
    $status = trim($_POST['status'] ?? '');

    // Basic validation
    if (!$user_id || !$book_id || !$borrow_start_date || !$due_date || !$status) {
        $reservationAddError = "Please fill in all required fields.";
    } else {
        // Check if book exists and is available
        $stmt = $conn->prepare("SELECT copies, availability_status FROM books WHERE id=?");
        $stmt->bind_param("i", $book_id);
        $stmt->execute();
        $stmt->bind_result($copies, $availability_status);
        if ($stmt->fetch()) {
            if ($copies < 1 || $availability_status == 'not_available') {
                $reservationAddError = "Book is not available for reservation.";
            } else {
                $stmt->close();
                // Insert reservation with borrow_start_date
                $stmt = $conn->prepare("INSERT INTO reservations (user_id, book_id, borrow_start_date, reserved_at, due_date, status) VALUES (?, ?, ?, NOW(), ?, ?)");
                $stmt->bind_param("iisss", $user_id, $book_id, $borrow_start_date, $due_date, $status);
                if ($stmt->execute()) {
                    // Decrement book copies and set status if reserved
                    if ($status == 'reserved') {
                        $conn->query("UPDATE books SET copies = copies - 1 WHERE id = $book_id");
                        $conn->query("UPDATE books SET availability_status = 'not_available' WHERE id = $book_id AND copies = 0");
                    }
                    $reservationAddSuccess = "Reservation added successfully.";
                    // Redirect to avoid resubmission and show in table
                    header("Location: admin.php?activeTab=reservations");
                    exit();
                } else {
                    $reservationAddError = "Failed to add reservation.";
                }
            }
        } else {
            $reservationAddError = "Book not found.";
        }
        $stmt->close();
    }
}

// Edit Reservation
if (
    isset($_POST['edit_reservation']) &&
    isset($_POST['reservation_id'], $_POST['user_id'], $_POST['book_id'], $_POST['borrow_start_date'], $_POST['due_date'], $_POST['status'])
) {
    $id = $_POST['reservation_id'];
    $user_id = $_POST['user_id'];
    $book_id = $_POST['book_id'];
    $borrow_start_date = $_POST['borrow_start_date'];
    $due_date = $_POST['due_date'];
    $status = $_POST['status'];

    // Validate user_id and book_id exist to avoid foreign key constraint errors
    $userExists = $conn->prepare("SELECT id FROM users WHERE id = ?");
    $userExists->bind_param("i", $user_id);
    $userExists->execute();
    $userExists->store_result();
    $userOk = $userExists->num_rows > 0;
    $userExists->close();

    $bookExists = $conn->prepare("SELECT id FROM books WHERE id = ?");
    $bookExists->bind_param("i", $book_id);
    $bookExists->execute();
    $bookExists->store_result();
    $bookOk = $bookExists->num_rows > 0;
    $bookExists->close();

    if ($userOk && $bookOk) {
        $stmt = $conn->prepare("UPDATE reservations SET user_id=?, book_id=?, borrow_start_date=?, due_date=?, status=? WHERE id=?");
        $stmt->bind_param("iisssi", $user_id, $book_id, $borrow_start_date, $due_date, $status, $id);
        if($stmt->execute()) {
            // Update book availability based on reservation status
            if(in_array($status, ['returned', 'canceled'])) {
                $book_stmt = $conn->prepare("UPDATE books SET availability_status='available' WHERE id=?");
                $book_stmt->bind_param("i", $book_id);
                $book_stmt->execute();
                $book_stmt->close();
            } else if($status == 'reserved') {
                $book_stmt = $conn->prepare("UPDATE books SET availability_status='not_available' WHERE id=?");
                $book_stmt->bind_param("i", $book_id);
                $book_stmt->execute();
                $book_stmt->close();
            }
        }
        $stmt->close();
        // Redirect after edit to ensure UI updates and prevent resubmission
        header("Location: admin.php?activeTab=reservations");
        exit();
    }
    // else: Optionally set an error message if user or book does not exist
}

// Delete Reservation
if (isset($_POST['delete_reservation']) && isset($_POST['reservation_id'])) {
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
    header("Location: admin.php?activeTab=reservations");
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

// --- CATEGORIES SEARCH & SORT (USING BOOKS TABLE) ---
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

$firstNameErr = $middleNameErr = $lastNameErr = $emailErr = $studentIdErr = $passwordErr = $confirmPasswordErr = $phoneErr = "";
$firstName = $middleName = $lastName = $email = $studentId = $phone = "";
$success = false;

// Handle user addition and validation
$hasUserError = !empty($firstNameErr) || !empty($middleNameErr) || !empty($lastNameErr) || !empty($emailErr) || !empty($studentIdErr) || !empty($passwordErr) || !empty($confirmPasswordErr) || !empty($phoneErr);
// If Add User was submitted or there was a validation error, force Users tab active
if (
    ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user']))
    || $hasUserError
) {
    $activeTab = 'users';
} else {
    $activeTab = $_GET['activeTab'] ?? 'inventory';
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add_user"])) {
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

    // Student ID Validation
    if (empty($_POST["student_teacher_id"])) {
        $studentIdErr = "Student ID is required.";
        $validForm = false;
    } elseif (!preg_match("/^\d{4}-\d{4}$/", $_POST["student_teacher_id"])) {
        $studentIdErr = "Student ID must be in the format 1234-5678.";
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
        $stmt = $conn->prepare("SELECT id FROM users WHERE student_teacher_id = ?");
        $stmt->bind_param("s", $studentId);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $studentIdErr = "This student/teacher ID is already registered.";
            $validForm = false;
        }
        $stmt->close();
    }

    // If valid, insert into database
    if ($validForm) {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $role = $_POST["role"] ?? "student";
        $stmt = $conn->prepare("INSERT INTO users (first_name, middle_name, last_name, email, student_teacher_id, password, phone, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $firstName, $middleName, $lastName, $email, $studentId, $passwordHash, $phone, $role);
        if ($stmt->execute()) {
            $success = true;
            // Optionally, clear form fields
            $firstName = $middleName = $lastName = $email = $studentId = $phone = "";
        } else {
            $emailErr = "Database error: " . htmlspecialchars($stmt->error);
        }
        $stmt->close();
    }
}

$hasUserError = !empty($firstNameErr) || !empty($middleNameErr) || !empty($lastNameErr) || !empty($emailErr) || !empty($studentIdErr) || !empty($passwordErr) || !empty($confirmPasswordErr) || !empty($phoneErr);
// Pass all variables to the HTML template
include __DIR__ . '/../templates/admin.html';
?>