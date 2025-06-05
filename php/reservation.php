<?php
session_start();
require_once "config.php";

// Helper functions
function getUserReservations($conn, $user_id) {
    $reservations = [];
    $sql = "SELECT r.id, b.title, b.author, b.year_published, b.category, b.cover_image, r.due_date, r.status, r.borrow_start_date
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

function searchBooks($conn, $query) {
    $books = [];
    $like = '%' . $query . '%';
    $sql = "SELECT id, title, author, year_published, category, copies, availability_status, cover_image, total_rating FROM books
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

function getOverdueMsg($conn, $user_id) {
    $reservations = getUserReservations($conn, $user_id);
    foreach ($reservations as $res) {
        if (strtotime($res['due_date']) < strtotime(date('Y-m-d'))) {
            return "You are passed the due date! Please return the book/s to the library.";
        }
    }
    return "";
}

// User info
$user_id = $_SESSION['id'];
$user_role = $_SESSION['role'] ?? 'student';
$borrow_limit = ($user_role === 'student') ? 3 : 5;
$borrow_period = ($user_role === 'student') ? 14 : 30;

// State variables
$showReserve = false;
$showBorrowed = false;
$reserveSuccess = "";
$reserveError = "";
$overdueMsg = getOverdueMsg($conn, $user_id);
$searchResults = [];
$searchQuery = "";
$selectedBook = null;

// --- Borrow Start Date Range Calculation ---
$today = date('Y-m-d');
$borrowStartMin = $today;
$borrowStartMax = date('Y-m-d', strtotime('+7 days'));

// Handle state switching
if (isset($_POST['show_reserve']) || isset($_POST['search_book']) || isset($_POST['select_book']) || isset($_GET['show_reserve']) || isset($_GET['reserve_book_id'])) {
    $showReserve = true;
} elseif (isset($_POST['show_borrowed']) || isset($_GET['show_borrowed'])) {
    $showBorrowed = true;
} else {
    $showReserve = true; // default
}

// Handle reservation
if (isset($_POST['reserve_book']) && isset($_POST['book_id']) && isset($_POST['due_date']) && isset($_POST['borrow_start_date'])) {
    $book_id = intval($_POST['book_id']);
    $due_date = $_POST['due_date'];
    $borrow_start_date = $_POST['borrow_start_date'];

    // Check active reservations
    $userReservations = getUserReservations($conn, $user_id);
    if (count($userReservations) >= $borrow_limit) {
        $reserveError = "You have reached your borrowing limit of $borrow_limit books.";
    } else {
        // Validate borrow_start_date
        if ($borrow_start_date < $borrowStartMin || $borrow_start_date > $borrowStartMax) {
            $reserveError = "Borrowing start date must be from today until this week's Saturday.";
        } else {
            // Validate due_date (must be after borrow_start_date and within borrow period)
            $minDueDate = date('Y-m-d', strtotime($borrow_start_date . ' +1 day'));
            $maxDueDate = date('Y-m-d', strtotime($borrow_start_date . " +$borrow_period days"));
            if ($due_date < $minDueDate || $due_date > $maxDueDate) {
                $reserveError = "Return date must be at least one day after the borrowing start date and within your allowed borrowing period.";
            } else {
                $book = getBookById($conn, $book_id);
                if (!$book) {
                    $reserveError = "Book not found.";
                } elseif ($book['copies'] < 1 || $book['availability_status'] != 'available') {
                    $reserveError = "Book is not available for reservation.";
                } else {
                    $stmt = $conn->prepare("INSERT INTO reservations (user_id, book_id, borrow_start_date, reserved_at, due_date, status) VALUES (?, ?, ?, NOW(), ?, 'reserved')");
                    $stmt->bind_param("iiss", $user_id, $book_id, $borrow_start_date, $due_date);
                    if ($stmt->execute()) {
                        $conn->query("UPDATE books SET copies = copies - 1 WHERE id = $book_id");
                        $_SESSION['reserve_success'] = "Book reserved successfully! Borrow start: $borrow_start_date, Due date: $due_date";
                        header("Location: reservation.php");
                        exit();
                    } else {
                        $reserveError = "Failed to reserve book. Please try again.";
                    }
                    $stmt->close();
                }
            }
        }
    }
    if ($reserveError) {
        $showReserve = true;
        $showBorrowed = false;
        $selectedBook = getBookById($conn, $book_id);
        // Pass back the attempted dates for form repopulation
        $form_borrow_start_date = $borrow_start_date;
        $form_due_date = $due_date;
    }
}

// Book search
if (isset($_POST['search_book']) && !empty(trim($_POST['search_query']))) {
    $searchQuery = trim($_POST['search_query']);
    $searchResults = searchBooks($conn, $searchQuery);
    $showReserve = true;
}

// Book selection
if (isset($_POST['select_book']) && isset($_POST['book_id'])) {
    $selectedBook = getBookById($conn, intval($_POST['book_id']));
    $showReserve = true;
}

// Pre-select book if coming from dashboard, categories, or book_details.php
if (isset($_GET['reserve_book_id'])) {
    $_SESSION['preselect_book_id'] = intval($_GET['reserve_book_id']);
    header("Location: reservation.php");
    exit();
}

// Use pre-selected book from session if available
if (isset($_SESSION['preselect_book_id'])) {
    $selectedBook = getBookById($conn, $_SESSION['preselect_book_id']);
    $showReserve = true;
    unset($_SESSION['preselect_book_id']); // Only use once
}

// Fetch reservations if needed
$userReservations = [];
if ($showBorrowed) {
    $userReservations = getUserReservations($conn, $user_id);
}

// Pass borrow start date range and form values to template
include __DIR__ . '/../templates/reservation.html';
?>