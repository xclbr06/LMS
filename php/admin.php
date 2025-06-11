<?php
// Prevent browser caching for security
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Start session and include DB config
session_start();
require_once "config.php";

// --- AUTHENTICATION CHECK ---
// Ensure only logged-in admins can access this page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit();
}

// --- CATEGORY COUNTS ---
// Fetches all categories and their book counts for display and management
$categoryCounts = [];
$categoryCountResult = $conn->query("SELECT category, COUNT(*) as total_titles FROM books GROUP BY category");
while ($row = $categoryCountResult->fetch_assoc()) {
    $categoryCounts[$row['category']] = $row['total_titles'];
}

// --- BOOK MANAGEMENT: ADD BOOK ---
// Handles adding new books with validation
$bookAddError = $bookAddSuccess = "";
if (isset($_POST['add_book'])) {
    // Trim all fields to remove whitespace
    $title = trim($_POST['title'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $isbn = trim($_POST['isbn'] ?? '');
    $publisher = trim($_POST['publisher'] ?? '');
    $year_published = trim($_POST['year_published'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $cover_image = trim($_POST['cover_image'] ?? '');
    $copies = trim($_POST['copies'] ?? '');
    $shelf_location = trim($_POST['shelf_location'] ?? '');
    $total_rating = trim($_POST['total_rating'] ?? '');
    $total_borrow = trim($_POST['total_borrowed']); // Book borrow count
    $availability_status = trim($_POST['availability_status'] ?? '');

    // Validation for book fields
    if (
        $title === '' || $author === '' || $isbn === '' || $publisher === '' ||
        $year_published === '' || $category === '' || $cover_image === '' ||
        $copies === '' || $shelf_location === '' || $total_rating === '' || $total_borrow === '' || $availability_status === ''
    ) {
        $bookAddError = "Please fill in all required fields (no whitespace only).";
    } elseif (!preg_match('/^\d{13}$/', $isbn)) {
        $bookAddError = "ISBN must be exactly 13 digits.";
    } elseif (!preg_match('/^\d{4}$/', $year_published) || intval($year_published) < 1000 || intval($year_published) > intval(date('Y')) + 1) {
        $bookAddError = "Year must be a valid 4-digit year.";
    } elseif (!filter_var($cover_image, FILTER_VALIDATE_URL)) {
        $bookAddError = "Cover Image Path must be a valid URL.";
    } elseif (!preg_match('/^https?:\/\/.+\.(jpg|jpeg|png|gif|webp)$/i', $cover_image)) {
        $bookAddError = "Cover Image Path must be a valid image URL (jpg, jpeg, png, gif, webp).";
    } elseif (!preg_match('/^[A-Z]-\d{2}$/', $shelf_location)) {
        $bookAddError = "Shelf Location must be in the format A-01, Z-21, etc.";
    } elseif (!is_numeric($total_rating) || $total_rating < 0 || $total_rating > 5) {
        $bookAddError = "Rating must be a number between 0 and 5.";
    } elseif (!is_numeric($total_borrow) || $total_borrow < 0) {
        $bookAddError = "Total Borrowed must be a non-negative number.";
    } else {
        // Validate category exists
        $stmt = $conn->prepare("SELECT category FROM categories WHERE category = ?");
        $stmt->bind_param("s", $category);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows === 0) {
            $bookAddError = "Selected category does not exist.";
        } else {
            $stmt->close();
            // Insert new book into database
            $stmt = $conn->prepare("INSERT INTO books (
                title, 
                author, 
                isbn, 
                publisher, 
                year_published, 
                category, 
                cover_image, 
                copies, 
                shelf_location, 
                total_rating,
                total_borrow, 
                availability_status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param(
                "ssssissisdss",
                $title, 
                $author, 
                $isbn, 
                $publisher, 
                $year_published, 
                $category, 
                $cover_image, 
                $copies, 
                $shelf_location, 
                $total_rating,
                $total_borrow,
                $availability_status
            );
            if ($stmt->execute()) {
                $bookAddSuccess = "Book added successfully.";
                header("Location: admin.php?activeTab=inventory&bookAddSuccess=" . urlencode($bookAddSuccess));
                exit();
            } else {
                $bookAddError = "Failed to add book.";
            }
        }
        $stmt->close();
    }
}

// --- BOOK MANAGEMENT: EDIT BOOK ---
// Handles editing existing books
$editBookSuccess = $editBookError = "";
if (isset($_POST['edit_book'])) {
    // Validate that category exists
    $category = trim($_POST['category']);
    $stmt = $conn->prepare("SELECT category FROM categories WHERE category = ?");
    $stmt->bind_param("s", $category);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows === 0) {
        $editBookError = "Selected category does not exist.";
    } else {
        $stmt->close();
        // Update book details
        $stmt = $conn->prepare("UPDATE books SET title=?, author=?, category=?, copies=?, availability_status=? WHERE id=?");
        $stmt->bind_param("sssssi", $_POST['title'], $_POST['author'], $_POST['category'], 
                         $_POST['copies'], $_POST['availability_status'], $_POST['book_id']);
        if ($stmt->execute()) {
            $editBookSuccess = "Book updated successfully.";
        } else {
            $editBookError = "Failed to update book.";
        }
        $stmt->close();
    }
    header("Location: admin.php?activeTab=inventory&editBookSuccess=" . urlencode($editBookSuccess) . "&editBookError=" . urlencode($editBookError));
    exit();
}

// --- BOOK MANAGEMENT: DELETE BOOK ---
// Handles deleting a book
if (isset($_POST['delete_book'])) {
    $id = intval($_POST['book_id']);
    $stmt = $conn->prepare("DELETE FROM books WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin.php?activeTab=inventory&bookDeleteSuccess=Book+deleted+successfully");
    exit();
}

// --- USER MANAGEMENT: ADD USER ---
// Handles adding a new user
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

    // Basic validation for user fields
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
            $userAddError = "Email or School ID already exists.";
        } else {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            // Insert new user into database
            $stmt = $conn->prepare("INSERT INTO users (first_name, middle_name, last_name, email, student_teacher_id, password, phone, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssss", $first_name, $middle_name, $last_name, $email, $student_teacher_id, $passwordHash, $phone, $role);
            if ($stmt->execute()) {
                $userAddSuccess = "User added successfully.";
            } else {
                $userAddError = "Failed to add user.";
            }
            $stmt->close();
            header("Location: admin.php?activeTab=users&userAddSuccess=" . urlencode($userAddSuccess));
            exit();
        }
        $stmt->close();
    }
}

// --- USER MANAGEMENT: EDIT USER ---
// Handles editing an existing user
$editUserSuccess = $editUserError = "";
if (isset($_POST['edit_user'])) {
    if ($stmt = $conn->prepare("UPDATE users SET first_name=?, middle_name=?, last_name=?, email=?, student_teacher_id=?, phone=?, role=? WHERE id=?")) {
        $stmt->bind_param("sssssssi", $_POST['first_name'], $_POST['middle_name'], $_POST['last_name'], $_POST['email'], $_POST['student_teacher_id'], $_POST['phone'], $_POST['role'], $_POST['user_id']);
        if ($stmt->execute()) {
            $editUserSuccess = "User updated successfully.";
        } else {
            $editUserError = "Failed to update user.";
        }
        $stmt->close();
    } else {
        $editUserError = "Failed to update user.";
    }
    header("Location: admin.php?activeTab=users&editUserSuccess=" . urlencode($editUserSuccess) . "&editUserError=" . urlencode($editUserError));
    exit();
}

// --- USER MANAGEMENT: DELETE USER ---
// Handles deleting a user
if (isset($_POST['delete_user'])) {
    $id = intval($_POST['user_id']);
    $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin.php?activeTab=users&userDeleteSuccess=User+deleted+successfully");
    exit();
}

// --- CATEGORY MANAGEMENT: EDIT CATEGORY ---
// Handles editing a category and updating related books
if (isset($_POST['edit_category'])) {
    $original_category = trim($_POST['original_category']);
    $new_category = trim($_POST['category']);
    
    if ($original_category !== $new_category && !empty($new_category)) {
        // Start transaction for atomic update
        $conn->begin_transaction();
        try {
            // Update category in categories table
            $stmt = $conn->prepare("UPDATE categories SET category = ? WHERE category = ?");
            $stmt->bind_param("ss", $new_category, $original_category);
            $stmt->execute();
            $stmt->close();
            
            // Update category in books table
            $stmt = $conn->prepare("UPDATE books SET category = ? WHERE category = ?");
            $stmt->bind_param("ss", $new_category, $original_category);
            $stmt->execute();
            $stmt->close();
            
            $conn->commit();
            $categoryEditSuccess = "Category updated successfully.";
        } catch (Exception $e) {
            $conn->rollback();
            $categoryEditError = "Failed to update category.";
        }
    }
    header("Location: admin.php?activeTab=categories&categoryEditSuccess=" . urlencode($categoryEditSuccess ?? '') . "&categoryEditError=" . urlencode($categoryEditError ?? ''));
    exit();
}

// --- CATEGORY MANAGEMENT: ADD CATEGORY ---
// Handles adding a new category
$categoryAddError = $categoryAddSuccess = "";
if (isset($_POST['add_category'])) {
    $new_category = trim($_POST['new_category'] ?? '');
    if ($new_category === '') {
        $categoryAddError = "Category name cannot be empty or whitespace.";
    } elseif (strlen($new_category) > 100) {
        $categoryAddError = "Category name is too long.";
    } else {
        // Check if category already exists (case-insensitive)
        $stmt = $conn->prepare("SELECT id FROM categories WHERE LOWER(category) = LOWER(?) LIMIT 1");
        $stmt->bind_param("s", $new_category);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $categoryAddError = "Category already exists.";
        } else {
            $stmt->close();
            // Insert new category
            $stmt = $conn->prepare("INSERT INTO categories (category) VALUES (?)");
            $stmt->bind_param("s", $new_category);
            if ($stmt->execute()) {
                $categoryAddSuccess = "Category added successfully!";
                header("Location: admin.php?activeTab=categories&categoryAddSuccess=" . urlencode($categoryAddSuccess));
                exit();
            } else {
                $categoryAddError = "Failed to add category.";
            }
        }
        $stmt->close();
    }
    $activeTab = 'categories';
}

// --- FETCH CATEGORIES FOR DROPDOWN & TAB ---
// Loads all categories for dropdowns and tab display
$categories = [];
$categoryCounts = [];

// Get categories from the categories table
$stmt = $conn->prepare("SELECT category FROM categories ORDER BY category ASC");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $categories[] = ['category' => $row['category']];
}
$stmt->close();

// Get any additional categories from books table that aren't in categories table
$stmt = $conn->prepare("
    SELECT DISTINCT b.category 
    FROM books b 
    WHERE b.category NOT IN (SELECT category FROM categories)
    ORDER BY b.category ASC
");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $categories[] = ['category' => $row['category']];
    // Also insert these categories into the categories table
    $insertStmt = $conn->prepare("INSERT IGNORE INTO categories (category) VALUES (?)");
    $insertStmt->bind_param("s", $row['category']);
    $insertStmt->execute();
    $insertStmt->close();
}
$stmt->close();

// Get book counts for all categories
$stmt = $conn->prepare("
    SELECT category, COUNT(*) as total_titles 
    FROM books 
    GROUP BY category
");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $categoryCounts[$row['category']] = $row['total_titles'];
}
$stmt->close();

// Sort categories alphabetically for display
usort($categories, function($a, $b) {
    return strcasecmp($a['category'], $b['category']);
});

// --- RESERVATIONS CRUD ---

// --- ADD RESERVATION HANDLER ---
$reservationAddError = $reservationAddSuccess = "";
if (isset($_POST['add_reservation'])) {
    $user_id = intval($_POST['user_id'] ?? 0);
    $book_id = intval($_POST['book_id'] ?? 0);
    $borrow_start_date = trim($_POST['borrow_start_date'] ?? '');
    $due_date = trim($_POST['due_date'] ?? '');

    // Always use 30 days for all roles
    $borrow_limit = 5; // You can keep this or adjust as needed
    $borrow_period = 30;

    // Validate dates with correct period
    $start = new DateTime($borrow_start_date);
    $due = new DateTime($due_date);
    $maxDueDate = clone $start;
    $maxDueDate->modify("+$borrow_period days");

    // ENFORCE: due date must be >= start+1 and <= start+borrow_period
    $minDueDate = clone $start;
    $minDueDate->modify("+1 day");

    if ($start > $due) {
        $reservationAddError = "Return date must be after borrow start date.";
    } elseif ($due < $minDueDate) {
        $reservationAddError = "Return date must be at least 1 day after borrow start date.";
    } elseif ($due > $maxDueDate) {
        $reservationAddError = "Return date cannot exceed the maximum borrowing period of $borrow_period days.";
    } else {
        // ...rest of your reservation logic...
    }
}

// Edit Reservation
if (
    isset($_POST['edit_reservation']) &&
    isset($_POST['reservation_id'], $_POST['user_id'], $_POST['book_id'], $_POST['borrow_start_date'], $_POST['due_date'], $_POST['status'])
) {
    $id = $_POST['reservation_id'];
    $user_id = $_POST['user_id'];
    $new_book_id = $_POST['book_id'];
    $borrow_start_date = $_POST['borrow_start_date'];
    $due_date = $_POST['due_date'];
    $new_status = $_POST['status'];

    // Get previous status and book_id
    $prevStatus = '';
    $prevBookId = 0;
    $stmtPrev = $conn->prepare("SELECT status, book_id FROM reservations WHERE id=?");
    $stmtPrev->bind_param("i", $id);
    $stmtPrev->execute();
    $stmtPrev->bind_result($prevStatus, $prevBookId);
    $stmtPrev->fetch();
    $stmtPrev->close();

    // Validate user_id and book_id exist to avoid foreign key constraint errors
    $userExists = $conn->prepare("SELECT id FROM users WHERE id = ?");
    $userExists->bind_param("i", $user_id);
    $userExists->execute();
    $userExists->store_result();
    $userOk = $userExists->num_rows > 0;
    $userExists->close();

    $bookExists = $conn->prepare("SELECT id FROM books WHERE id = ?");
    $bookExists->bind_param("i", $new_book_id);
    $bookExists->execute();
    $bookExists->store_result();
    $bookOk = $bookExists->num_rows > 0;
    $bookExists->close();

    if ($userOk && $bookOk) {
        $stmt = $conn->prepare("UPDATE reservations SET user_id=?, book_id=?, borrow_start_date=?, due_date=?, status=? WHERE id=?");
        $stmt->bind_param("iisssi", $user_id, $new_book_id, $borrow_start_date, $due_date, $new_status, $id);
        if($stmt->execute()) {
            // If the book was changed, increment the old book's copies if it was reserved
            if ($prevBookId != $new_book_id && $prevStatus === 'reserved') {
                $conn->query("UPDATE books SET copies = copies + 1 WHERE id = $prevBookId");
            }
            // If status changed from 'reserved' to 'returned' or 'canceled', increment copies for the current book
            if ($prevStatus === 'reserved' && in_array($new_status, ['returned', 'canceled'])) {
                $conn->query("UPDATE books SET copies = copies + 1 WHERE id = $new_book_id");
                $book_stmt = $conn->prepare("UPDATE books SET availability_status='available' WHERE id=?");
                $book_stmt->bind_param("i", $new_book_id);
                $book_stmt->execute();
                $book_stmt->close();
                // Set session message for user
                if ($new_status === 'returned') {
                    $_SESSION['reservation_success_message'] = "Book returned successfully!";
                } elseif ($new_status === 'canceled') {
                    $_SESSION['reservation_success_message'] = "Reservation canceled successfully!";
                }
            }
            // If status changed from not reserved to reserved, decrement copies for the current book
            else if ($prevStatus !== 'reserved' && $new_status === 'reserved') {
                $conn->query("UPDATE books SET copies = GREATEST(copies - 1, 0) WHERE id = $new_book_id");
                $book_stmt = $conn->prepare("UPDATE books SET availability_status='not_available' WHERE id=? AND copies = 0");
                $book_stmt->bind_param("i", $new_book_id);
                $book_stmt->execute();
                $book_stmt->close();
            }
            $editReservationSuccess = "Reservation updated successfully.";
        } else {
            $editReservationError = "Failed to update reservation.";
        }
        $stmt->close();
    } else {
        $editReservationError = "Invalid user or book.";
    }
    header("Location: admin.php?activeTab=reservations&editReservationSuccess=" . urlencode($editReservationSuccess) . "&editReservationError=" . urlencode($editReservationError));
    exit();
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
        // Set status to available if copies is now > 0
        $conn->query("UPDATE books SET availability_status = 'available' WHERE id = $book_id AND copies > 0");
    }

    $stmt = $conn->prepare("DELETE FROM reservations WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin.php?activeTab=reservations&reservationDeleteSuccess=Reservation+deleted+successfully");
    exit();
}

// Search and Filter Block
// Handles search functionality across all tables
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

if ($categorySearch !== '') {
    $filteredCategories = [];
    foreach ($categories as $cat) {
        if (stripos($cat['category'], $categorySearch) !== false) {
            $filteredCategories[] = $cat;
        }
    }
    $categories = $filteredCategories;
}

// Sort categories
if ($categorySortOrder === 'DESC') {
    usort($categories, function($a, $b) {
        return strcasecmp($b['category'], $a['category']);
    });
} else {
    usort($categories, function($a, $b) {
        return strcasecmp($a['category'], $b['category']);
    });
}

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
            $studentIdErr = "This school ID is already registered.";
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
            header("Location: admin.php?activeTab=users&userAddSuccess=User added successfully");
            exit();
        } else {
            $emailErr = "Database error: " . htmlspecialchars($stmt->error);
        }
        $stmt->close();
    }
}

// Set flag to show modal if there are errors
$hasUserError = !empty($firstNameErr) || !empty($middleNameErr) || !empty($lastNameErr) || 
                !empty($emailErr) || !empty($studentIdErr) || !empty($passwordErr) || 
                !empty($confirmPasswordErr) || !empty($phoneErr);

// Initialize category delete variables
$categoryDeleteError = $categoryDeleteSuccess = "";

// --- CATEGORY DELETE HANDLER ---
if (isset($_POST['delete_category'])) {
    $category = $_POST['category'];
    
    // Check if category is in use
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM books WHERE category = ?");
    $stmt->bind_param("s", $category);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_assoc()['count'];
    
    if ($count > 0) {
        $categoryDeleteError = "Cannot delete category that has books assigned to it.";
    } else {
        $stmt = $conn->prepare("DELETE FROM categories WHERE category = ?");
        $stmt->bind_param("s", $category);
        if ($stmt->execute()) {
            $categoryDeleteSuccess = "Category deleted successfully.";
            // Redirect before any output
            header("Location: admin.php?activeTab=categories&categoryDeleteSuccess=" . urlencode($categoryDeleteSuccess));
            exit();
        } else {
            $categoryDeleteError = "Failed to delete category.";
        }
    }
    $stmt->close();
}

// Add this with your other book handlers
if (isset($_POST['edit_book_details'])) {
    $id = intval($_POST['book_id']);
    $isbn = trim($_POST['isbn']);
    $year_published = trim($_POST['year_published']);
    $total_rating = trim($_POST['total_rating']);
    $total_borrow = trim($_POST['total_borrow']);
    $shelf_location = trim($_POST['shelf_location']);
    $cover_image = trim($_POST['cover_image']);
    $synopsis = trim($_POST['synopsis']);

    // Validation
    $editDetailsError = '';
    if (!preg_match('/^\d{13}$/', $isbn)) {
        $editDetailsError = "ISBN must be exactly 13 digits.";
    } elseif (!preg_match('/^\d{4}$/', $year_published) || 
              intval($year_published) < 1000 || 
              intval($year_published) > intval(date('Y')) + 1) {
        $editDetailsError = "Invalid year.";
    } elseif (!is_numeric($total_rating) || $total_rating < 0 || $total_rating > 5) {
        $editDetailsError = "Rating must be between 0 and 5.";
    } elseif (!is_numeric($total_borrow) || $total_borrow < 0) {
        $editDetailsError = "Total borrowed must be non-negative.";
    } elseif (!preg_match('/^[A-Z]-\d{2}$/', $shelf_location)) {
        $editDetailsError = "Invalid shelf location format.";
    } elseif (!filter_var($cover_image, FILTER_VALIDATE_URL)) {
        $editDetailsError = "Invalid cover image URL.";
    }

    if (empty($editDetailsError)) {
        $stmt = $conn->prepare("UPDATE books SET 
            isbn=?, year_published=?, total_rating=?, total_borrow=?, 
            shelf_location=?, cover_image=?, synopsis=? 
            WHERE id=?");
        $stmt->bind_param("sisisssi", 
            $isbn, $year_published, $total_rating, $total_borrow,
            $shelf_location, $cover_image, $synopsis, $id
        );
        
        if ($stmt->execute()) {
            $editBookSuccess = "Book details updated successfully.";
        } else {
            $editBookError = "Failed to update book details.";
        }
        $stmt->close();
    } else {
        $editBookError = $editDetailsError;
    }
    
    header("Location: admin.php?activeTab=inventory&editBookSuccess=" . urlencode($editBookSuccess ?? '') . "&editBookError=" . urlencode($editBookError ?? ''));
    exit();
}

// --- HELPER FUNCTION: Get User's Current Reservations ---
// Returns the count of current reservations for a user
function getUserCurrentReservations($conn, $user_id) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM reservations WHERE user_id = ? AND status = 'reserved'");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    return $count;
}

// --- FINAL: Pass all variables to the HTML template ---
include __DIR__ . '/../templates/admin.html';
?>