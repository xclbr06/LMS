<?php
session_start();
require_once "config.php";

// Handle logout request
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["logout"])) {
    $_SESSION = array();
    session_destroy();
    header("Location: login.php");
    exit();
}

// Helper: Get user's borrowed/reserved books
function getUserReservations($conn, $user_id) {
    $reservations = [];
    $sql = "SELECT r.id, b.title, b.author, b.year_published, b.category, b.cover_image, r.due_date, r.status
            FROM reservations r
            JOIN books b ON r.book_id = b.id
            WHERE r.user_id = ? AND r.status = 'reserved'
            ORDER BY r.due_date DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $reservations[] = $row;
    }
    $stmt->close();
    return $reservations;
}

// Helper: Search books
function searchBooks($conn, $query) {
    $books = [];
    $like = '%' . $query . '%';
    $sql = "SELECT id, title, author, year_published, category, copies, availability_status, cover_image FROM books
            WHERE title LIKE ? OR author LIKE ? OR category LIKE ? ORDER BY title ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $like, $like, $like);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $books[] = $row;
    }
    $stmt->close();
    return $books;
}

// Helper: Get book by ID
function getBookById($conn, $book_id) {
    $sql = "SELECT * FROM books WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $book_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $book = $result->fetch_assoc();
    $stmt->close();
    return $book;
}

// Retrieve and clear reservation success message from session
$reserveSuccess = "";
if (isset($_SESSION['reserveSuccess'])) {
    $reserveSuccess = $_SESSION['reserveSuccess'];
    unset($_SESSION['reserveSuccess']);
}

// Get user role
$user_id = $_SESSION['id'];
$user_role = $_SESSION['role'] ?? 'student'; // default to student if not set

// Set borrowing limits and periods based on role
if ($user_role === 'student') {
    $borrow_limit = 3;
    $borrow_period = 14;
} else { // teacher or admin
    $borrow_limit = 5;
    $borrow_period = 30;
}

// Handle reservation submission (now using due_date from calendar)
$reserveError = "";
if (isset($_POST['reserve_book']) && isset($_POST['book_id']) && isset($_POST['due_date'])) {
    $book_id = intval($_POST['book_id']);
    $due_date = $_POST['due_date'];

    // Check current active reservations for the user
    $userReservations = getUserReservations($conn, $user_id);
    $activeReservations = 0;
    foreach ($userReservations as $res) {
        if ($res['status'] === 'reserved' && strtotime($res['due_date']) >= strtotime(date('Y-m-d'))) {
            $activeReservations++;
        }
    }
    if ($activeReservations >= $borrow_limit) {
        $reserveError = "You have reached your borrowing limit of $borrow_limit books.";
    } else {
        // Set allowed date range
        $today = date('Y-m-d');
        $minDate = date('Y-m-d', strtotime('+1 day'));
        $maxDate = date('Y-m-d', strtotime("+$borrow_period days"));
        if ($due_date < $minDate || $due_date > $maxDate) {
            $reserveError = "The date you have chosen is invalid. It is not within your allowed borrowing period.";
        } else {
            // Check book availability
            $book = getBookById($conn, $book_id);
            if (!$book) {
                $reserveError = "Book not found.";
            } elseif ($book['copies'] < 1 || $book['availability_status'] != 'available') {
                $reserveError = "Book is not available for reservation.";
            } else {
                // Insert reservation
                $stmt = $conn->prepare("INSERT INTO reservations (user_id, book_id, reserved_at, due_date, status) VALUES (?, ?, NOW(), ?, 'reserved')");
                $stmt->bind_param("iis", $user_id, $book_id, $due_date);
                if ($stmt->execute()) {
                    // Decrement book copies
                    $conn->query("UPDATE books SET copies = copies - 1 WHERE id = $book_id");
                    $_SESSION['reserveSuccess'] = "Book reserved successfully! Due date: " . $due_date;
                    // Redirect to avoid form resubmission and show success
                    // After successful reservation
                    header("Location: reservation.php?show_borrowed=1");
                    exit();
                } else {
                    $reserveError = "Failed to reserve book. Please try again.";
                }
                $stmt->close();
            }
        }
    }
    // Ensure the reservation form is shown again if there is an error
    if ($reserveError) {
        $_POST['show_reserve'] = true;
    }
    // Ensure the selected book is shown again if there is an error
    if (isset($_POST['reserve_book']) && $reserveError && isset($_POST['book_id'])) {
        $selectedBook = getBookById($conn, intval($_POST['book_id']));
    }
}

// Check for overdue books
$overdueMsg = "";
$userReservations = getUserReservations($conn, $user_id);
foreach ($userReservations as $res) {
    if (strtotime($res['due_date']) < strtotime(date('Y-m-d'))) {
        $overdueMsg = "You are passed the due date! Please return the book/s to the library.";
        break;
    }
}

// Book search logic
$searchResults = [];
$searchQuery = "";
if (isset($_POST['search_book']) && !empty(trim($_POST['search_query']))) {
    $searchQuery = trim($_POST['search_query']);
    $searchResults = searchBooks($conn, $searchQuery);
}

// Book selection logic
$selectedBook = $selectedBook ?? null;
if (isset($_POST['select_book']) && isset($_POST['book_id'])) {
    $selectedBook = getBookById($conn, intval($_POST['book_id']));
}

// Pre-select book if coming from book_details.php
$preselectBookId = isset($_GET['reserve_book_id']) ? intval($_GET['reserve_book_id']) : null;
if ($preselectBookId) {
    $selectedBook = getBookById($conn, $preselectBookId);
    $_POST['show_reserve'] = true; // Show the reservation form
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Reservation - School Library Management System</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .reservation-section { margin: 2rem; }
        .reservation-buttons { margin-bottom: 2rem; }
        .reservation-buttons button { margin-right: 1rem; }
        .search-bar { margin: 1.5rem 0; }
        .book-list { margin: 1rem 0; }
        .book-card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(44,62,80,0.08);
            width: 350px;
            text-align: left;
            padding: 1rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: flex-start;
            gap: 1rem;
        }
        .book-cover {
            width: 70px;
            height: 100px;
            object-fit: cover;
            border-radius: 4px;
        }
        .book-details {
            flex: 1;
            color: black;
        }
        .success { color: #27ae60; }
        .error { color: #e74c3c; }
        .overdue { color: #e67e22; font-weight: bold; }
        .due-date { font-weight: bold; color: #2980b9; }
        .dropdown { margin: 1rem 0; }
        .borrow-info { font-size: 0.95em; margin-bottom: 1em; }
    </style>
</head>
<body>
    <!-- Layered background image and blue overlay -->
<div class="body-bg">
    <img src="school.png" alt="Background" class="bg-img">
    <div class="bg-overlay"></div>
</div>
<?php include 'navbar.php'; ?>
<div class="reservation-section">
    <?php if ($overdueMsg): ?>
        <div class="overdue"><?= $overdueMsg ?></div>
    <?php endif; ?>

    <div class="reservation-buttons">
        <form method="post" style="display:inline;">
            <button type="submit" name="show_reserve">Reserve a Book</button>
        </form>
        <form method="post" style="display:inline;">
            <button type="submit" name="show_borrowed">Check Reserved Book Details</button>
        </form>
    </div>

    <?php
    // Show reservation form
    if (isset($_POST['show_reserve']) || isset($_POST['search_book']) || isset($_POST['select_book']) || isset($_GET['show_reserve'])):
    ?>
        <h3>Reserve a Book</h3>
        <div class="borrow-info">
            <?php if ($user_role === 'student'): ?>
                Borrowing limit: 3 books, Borrowing period: 14 days
            <?php else: ?>
                Borrowing limit: 5 books, Borrowing period: 30 days
            <?php endif; ?>
        </div>
        <?php if ($reserveSuccess): ?>
            <div class="success" style="margin-top:1rem;"><?= $reserveSuccess ?></div>
        <?php endif; ?>

        <?php if (!$reserveSuccess): ?>
            <!-- Book Search Bar -->
            <form method="post" class="search-bar">
                <input type="text" name="search_query" placeholder="Search for a book..." value="<?= htmlspecialchars($searchQuery) ?>" required>
                <button type="submit" name="search_book">Search</button>
            </form>

            <!-- Book Search Results -->
            <?php if ($searchResults): ?>
                <div class="book-list">
                    <?php foreach ($searchResults as $book): ?>
                        <form method="post" style="margin-bottom:0;">
                            <div class="book-card">
                                <img class="book-cover" src="<?= htmlspecialchars($book['cover_image'] ?? 'default_cover.png') ?>" alt="Book Cover">
                                <div class="book-details">
                                    <div><strong><?= htmlspecialchars($book['title']) ?></strong></div>
                                    <div>Author: <?= htmlspecialchars($book['author']) ?></div>
                                    <div>Year: <?= htmlspecialchars($book['year_published']) ?></div>
                                    <div>Category: <?= htmlspecialchars($book['category']) ?></div>
                                    <input type="hidden" name="book_id" value="<?= $book['id'] ?>">
                                    <button type="submit" name="select_book">Select</button>
                                </div>
                            </div>
                        </form>
                    <?php endforeach; ?>
                </div>
            <?php elseif ($searchQuery): ?>
                <div>No books found for your search.</div>
            <?php endif; ?>

            <!-- Book Selection and Reservation -->
            <?php if ($selectedBook): ?>
                <div class="book-card">
                    <img class="book-cover" src="<?= htmlspecialchars($selectedBook['cover_image'] ?? 'default_cover.png') ?>" alt="Book Cover">
                    <div class="book-details">
                        <div><strong><?= htmlspecialchars($selectedBook['title']) ?></strong></div>
                        <div>Author: <?= htmlspecialchars($selectedBook['author']) ?></div>
                        <div>Year: <?= htmlspecialchars($selectedBook['year_published']) ?></div>
                        <div>Category: <?= htmlspecialchars($selectedBook['category']) ?></div>
                        <div>Copies Left: <?= htmlspecialchars($selectedBook['copies']) ?></div>
                        <div>Status: <?= htmlspecialchars($selectedBook['availability_status']) ?></div>
                    </div>
                </div>
                <?php if ($selectedBook['copies'] > 0 && $selectedBook['availability_status'] == 'available'): ?>
                    <form method="post">
                        <input type="hidden" name="book_id" value="<?= $selectedBook['id'] ?>">
                        <div class="dropdown">
                            <label>Return Date:
                            <input type="date" name="due_date" id="due_date_picker" required data-period="<?= $borrow_period ?>">
                            </label>
                            <?php if ($reserveError): ?>
                                <div class="error" style="margin-top:0.5rem;"><?= $reserveError ?></div>
                            <?php endif; ?>
                        </div>
                        <button type="submit" name="reserve_book">Reserve</button>
                    </form>
                <?php else: ?>
                    <div class="error">This book is not available for reservation.</div>
                <?php endif; ?>
            <?php endif; ?>
        <?php endif; ?>

    <?php
    // Show borrowed/reserved books
    elseif (isset($_POST['show_borrowed']) || isset($_GET['show_borrowed'])):
        $userReservations = getUserReservations($conn, $user_id);
    ?>
        <h3>Your Reserved Books</h3>
        <?php if (empty($userReservations)): ?>
            <div>No Borrowed Books at this moment.</div>
        <?php else: ?>
            <?php foreach ($userReservations as $res): ?>
                <div class="book-card">
                    <img class="book-cover" src="<?= htmlspecialchars($res['cover_image'] ?? 'default_cover.png') ?>" alt="Book Cover">
                    <div class="book-details">
                        <div><strong><?= htmlspecialchars($res['title']) ?></strong></div>
                        <div>Author: <?= htmlspecialchars($res['author']) ?></div>
                        <div>Year: <?= htmlspecialchars($res['year_published']) ?></div>
                        <div>Category: <?= htmlspecialchars($res['category']) ?></div>
                        <div>Status: <?= htmlspecialchars($res['status']) ?></div>
                        <div class="due-date">Expected Return Date: <?= htmlspecialchars($res['due_date']) ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    <?php endif; ?>
</div>
<script src="reservation.js"></script>
<?php include 'footer.php'; ?>
</body>
</html>