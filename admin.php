<?php
session_start();
require_once "config.php";

// Only allow admin
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit();
}

// --- BOOKS CRUD ---
// Add Book
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

?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel - School Library Management System</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .admin-section { margin: 2rem; }
        .section-title {
            font-size: 1.3rem;
            font-weight: bold;
            margin-bottom: 0.7rem;
            color: #fff;
            background: #2d3e50;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            letter-spacing: 1px;
        }
        table { width: 100%; border-collapse: collapse; margin-bottom: 2rem; }
        th, td { border: 1px solid #ccc; padding: 0.5rem; text-align: left; }
        th {
            background: #34495e;
            color: #fff;
            font-weight: bold;
            font-size: 1.05em;
            letter-spacing: 0.5px;
        }
        h1 {text-align: center; margin-bottom: 1rem;}
        h2 { margin-top: 2rem; }
        .crud-form input, .crud-form select { margin-right: 0.5rem; margin-bottom: 0.5rem; }
        .crud-form button { margin-left: 0.5rem; }
        .success { color: #27ae60; }
        .error { color: #e74c3c; }
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0; top: 0; width: 100vw; height: 100vh;
            background: rgba(0,0,0,0.4);
            align-items: center; justify-content: center;
        }
        .modal-content {
            background: #fff;
            padding: 2rem 2rem 1rem 2rem;
            border-radius: 8px;
            min-width: 320px;
            max-width: 95vw;
            position: relative;
            box-shadow: 0 4px 32px rgba(44,62,80,0.18);
        }
        .modal-close {
            position: absolute;
            top: 0.7rem;
            right: 1.2rem;
            font-size: 1.5rem;
            color: #888;
            cursor: pointer;
            font-weight: bold;
        }
        .modal .section-title {
            margin-top: 0;
            margin-bottom: 1rem;
        }
        .modal .error, .modal .success { margin-bottom: 1rem; }
        .modal input, .modal select { margin-bottom: 0.7rem; }
        .modal .crud-form button { margin-left: 0; }
        .add-btn {
            display: inline-block;
            margin: 0.5rem 0 1.5rem 0;
            padding: 0.5rem 1.5rem;
            background: #2d3e50;
            color: #fff;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            cursor: pointer;
            font-weight: bold;
            transition: background 0.2s;
        }
        .add-btn:hover { background: #34495e; }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="admin-section">
    <h1>Admin Panel</h1>

    <div>
        <div class="section-title">Inventory</div>
        <table>
            <tr>
                <th>ID</th><th>Title</th><th>Author</th><th>Category</th><th>Copies</th><th>Status</th><th>Actions</th>
            </tr>
            <?php foreach ($books as $book): ?>
            <tr>
                <form method="post">
                    <td><?= $book['id'] ?><input type="hidden" name="book_id" value="<?= $book['id'] ?>"></td>
                    <td><input type="text" name="title" value="<?= htmlspecialchars($book['title']) ?>" required></td>
                    <td><input type="text" name="author" value="<?= htmlspecialchars($book['author']) ?>" required></td>
                    <td><input type="text" name="category" value="<?= htmlspecialchars($book['category']) ?>" required></td>
                    <td><input type="number" name="copies" value="<?= $book['copies'] ?>" required style="width:60px;"></td>
                    <td>
                        <select name="availability_status" required>
                            <option value="available" <?= $book['availability_status']=='available'?'selected':''; ?>>Available</option>
                            <option value="checked_out" <?= $book['availability_status']=='checked_out'?'selected':''; ?>>Checked Out</option>
                            <option value="reserved" <?= $book['availability_status']=='reserved'?'selected':''; ?>>Reserved</option>
                            <option value="lost" <?= $book['availability_status']=='lost'?'selected':''; ?>>Lost</option>
                        </select>
                    </td>
                    <td>
                        <input type="hidden" name="isbn" value="<?= htmlspecialchars($book['isbn']) ?>">
                        <input type="hidden" name="publisher" value="<?= htmlspecialchars($book['publisher']) ?>">
                        <input type="hidden" name="year_published" value="<?= $book['year_published'] ?>">
                        <input type="hidden" name="cover_image" value="<?= htmlspecialchars($book['cover_image']) ?>">
                        <input type="hidden" name="shelf_location" value="<?= htmlspecialchars($book['shelf_location']) ?>">
                        <button type="submit" name="edit_book">Save</button>
                        <button type="submit" name="delete_book" onclick="return confirm('Delete this book?')">Delete</button>
                    </td>
                </form>
            </tr>
            <?php endforeach; ?>
        </table>
        <button class="add-btn" id="openAddBookModal">Add Books</button>
    </div>

    <div>
        <div class="section-title">Categories</div>
        <table>
            <tr>
                <th>Category</th><th>Actions</th>
            </tr>
            <?php foreach ($categories as $cat): ?>
            <tr>
                <form method="post">
                    <td><input type="text" name="category" value="<?= htmlspecialchars($cat['category']) ?>" readonly></td>
                    <td>
                        <button type="submit" name="delete_category" onclick="return confirm('Delete this category and all its books?')">Delete</button>
                    </td>
                </form>
            </tr>
            <?php endforeach; ?>
        </table>
        <button class="add-btn" id="openAddCategoryModal">Add Category</button>
    </div>

    <div>
        <div class="section-title">Users</div>
        <table>
            <tr>
                <th>ID</th><th>First Name</th><th>Middle Name</th><th>Last Name</th><th>Email</th><th>Student ID</th><th>Phone</th><th>Role</th><th>Actions</th>
            </tr>
            <?php foreach ($users as $user): ?>
            <tr>
                <form method="post">
                    <td><?= $user['id'] ?><input type="hidden" name="user_id" value="<?= $user['id'] ?>"></td>
                    <td><input type="text" name="first_name" value="<?= htmlspecialchars($user['first_name']) ?>" required></td>
                    <td><input type="text" name="middle_name" value="<?= htmlspecialchars($user['middle_name']) ?>"></td>
                    <td><input type="text" name="last_name" value="<?= htmlspecialchars($user['last_name']) ?>" required></td>
                    <td><input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required></td>
                    <td><input type="text" name="student_id" value="<?= htmlspecialchars($user['student_id']) ?>" required></td>
                    <td><input type="text" name="phone" value="<?= htmlspecialchars($user['phone']) ?>"></td>
                    <td>
                        <select name="role" required>
                            <option value="student" <?= $user['role']=='student'?'selected':''; ?>>Student</option>
                            <option value="teacher" <?= $user['role']=='teacher'?'selected':''; ?>>Teacher</option>
                            <option value="admin" <?= $user['role']=='admin'?'selected':''; ?>>Admin</option>
                        </select>
                    </td>
                    <td>
                        <button type="submit" name="edit_user">Save</button>
                        <button type="submit" name="delete_user" onclick="return confirm('Delete this user?')">Delete</button>
                    </td>
                </form>
            </tr>
            <?php endforeach; ?>
        </table>
        <button class="add-btn" id="openAddUserModal">Add User</button>
    </div>

    <div>
        <div class="section-title">Reservations</div>
        <table>
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Book</th>
                <th>Reserved At</th>
                <th>Due Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($reservations as $res): ?>
            <tr>
                <form method="post">
                    <td>
                        <?= $res['id'] ?>
                        <input type="hidden" name="reservation_id" value="<?= $res['id'] ?>">
                    </td>
                    <td>
                        <select name="user_id" required>
                            <?php foreach ($usersList as $user): ?>
                                <option value="<?= $user['id'] ?>" <?= $user['id'] == $res['user_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td>
                        <select name="book_id" required>
                            <?php foreach ($booksList as $book): ?>
                                <option value="<?= $book['id'] ?>" <?= $book['id'] == $res['book_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($book['title']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td><?= htmlspecialchars($res['reserved_at']) ?></td>
                    <td>
                        <input type="date" name="due_date" value="<?= htmlspecialchars($res['due_date']) ?>" required>
                    </td>
                    <td>
                        <select name="status" required>
                            <option value="reserved" <?= $res['status'] == 'reserved' ? 'selected' : '' ?>>Reserved</option>
                            <option value="returned" <?= $res['status'] == 'returned' ? 'selected' : '' ?>>Returned</option>
                            <option value="cancelled" <?= $res['status'] == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                    </td>
                    <td>
                        <button type="submit" name="edit_reservation">Save</button>
                        <button type="submit" name="delete_reservation" onclick="return confirm('Delete this reservation?')">Delete</button>
                    </td>
                </form>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>

<!-- MODALS -->
<div class="modal" id="addBookModal">
    <div class="modal-content">
        <span class="modal-close" onclick="closeModal('addBookModal')">&times;</span>
        <div class="section-title">Add Books</div>
        <?php if (!empty($bookAddError)): ?>
            <div class="error"><?= $bookAddError ?></div>
        <?php elseif (!empty($bookAddSuccess)): ?>
            <div class="success"><?= $bookAddSuccess ?></div>
        <?php endif; ?>
        <form class="crud-form" method="post">
            <input type="text" name="title" placeholder="Title" required>
            <input type="text" name="author" placeholder="Author" required>
            <input type="text" name="isbn" placeholder="ISBN" required>
            <input type="text" name="publisher" placeholder="Publisher" required>
            <input type="number" name="year_published" placeholder="Year" required>
            <input type="text" name="category" placeholder="Category" required>
            <input type="text" name="cover_image" placeholder="Cover Image Path">
            <input type="number" name="copies" placeholder="Copies" required>
            <input type="text" name="shelf_location" placeholder="Shelf Location" required>
            <select name="availability_status" required>
                <option value="available">Available</option>
                <option value="checked_out">Checked Out</option>
                <option value="reserved">Reserved</option>
                <option value="lost">Lost</option>
            </select>
            <button type="submit" name="add_book">Add</button>
        </form>
    </div>
</div>

<div class="modal" id="addUserModal">
    <div class="modal-content">
        <span class="modal-close" onclick="closeModal('addUserModal')">&times;</span>
        <div class="section-title">Add User</div>
        <?php if (!empty($userAddError)): ?>
            <div class="error"><?= $userAddError ?></div>
        <?php elseif (!empty($userAddSuccess)): ?>
            <div class="success"><?= $userAddSuccess ?></div>
        <?php endif; ?>
        <form class="crud-form" method="post">
            <input type="text" name="first_name" placeholder="First Name" required>
            <input type="text" name="middle_name" placeholder="Middle Name">
            <input type="text" name="last_name" placeholder="Last Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="text" name="student_id" placeholder="Student ID" required>
            <input type="text" name="phone" placeholder="Phone">
            <select name="role" required>
                <option value="student">Student</option>
                <option value="teacher">Teacher</option>
                <option value="admin">Admin</option>
            </select>
            <input type="password" name="password" placeholder="Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            <button type="submit" name="add_user">Add</button>
        </form>
    </div>
</div>

<div class="modal" id="addCategoryModal">
    <div class="modal-content">
        <span class="modal-close" onclick="closeModal('addCategoryModal')">&times;</span>
        <div class="section-title">Add Category</div>
        <?php if (!empty($catAddError)): ?>
            <div class="error"><?= $catAddError ?></div>
        <?php elseif (!empty($catAddSuccess)): ?>
            <div class="success"><?= $catAddSuccess ?></div>
        <?php endif; ?>
        <form class="crud-form" method="post">
            <input type="text" name="category" placeholder="New Category" required>
            <button type="submit" name="add_category">Add</button>
        </form>
    </div>
</div>

<script src="adminPanel.js"></script>
</body>
</html>
