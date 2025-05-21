<?php
session_start();
require_once "config.php";

// Helper functions
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

// Handle state switching
if (isset($_POST['show_reserve']) || isset($_POST['search_book']) || isset($_POST['select_book']) || isset($_GET['show_reserve']) || isset($_GET['reserve_book_id'])) {
    $showReserve = true;
} elseif (isset($_POST['show_borrowed']) || isset($_GET['show_borrowed'])) {
    $showBorrowed = true;
} else {
    $showReserve = true; // default
}

// Handle reservation
if (isset($_POST['reserve_book']) && isset($_POST['book_id']) && isset($_POST['due_date'])) {
    $book_id = intval($_POST['book_id']);
    $due_date = $_POST['due_date'];

    // Check active reservations
    $userReservations = getUserReservations($conn, $user_id);
    if (count($userReservations) >= $borrow_limit) {
        $reserveError = "You have reached your borrowing limit of $borrow_limit books.";
    } else {
        $today = date('Y-m-d');
        $minDate = date('Y-m-d', strtotime('+1 day'));
        $maxDate = date('Y-m-d', strtotime("+$borrow_period days"));
        if ($due_date < $minDate || $due_date > $maxDate) {
            $reserveError = "The date you have chosen is invalid. It is not within your allowed borrowing period.";
        } else {
            $book = getBookById($conn, $book_id);
            if (!$book) {
                $reserveError = "Book not found.";
            } elseif ($book['copies'] < 1 || $book['availability_status'] != 'available') {
                $reserveError = "Book is not available for reservation.";
            } else {
                $stmt = $conn->prepare("INSERT INTO reservations (user_id, book_id, reserved_at, due_date, status) VALUES (?, ?, NOW(), ?, 'reserved')");
                $stmt->bind_param("iis", $user_id, $book_id, $due_date);
                if ($stmt->execute()) {
                    $conn->query("UPDATE books SET copies = copies - 1 WHERE id = $book_id");
                    $reserveSuccess = "Book reserved successfully! Due date: " . $due_date;
                    $showBorrowed = true;
                    $showReserve = false;
                } else {
                    $reserveError = "Failed to reserve book. Please try again.";
                }
                $stmt->close();
            }
        }
    }
    if ($reserveError) {
        $showReserve = true;
        $showBorrowed = false;
        $selectedBook = getBookById($conn, $book_id);
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

// Pre-select book if coming from book_details.php
if (isset($_GET['reserve_book_id'])) {
    $selectedBook = getBookById($conn, intval($_GET['reserve_book_id']));
    $showReserve = true;
}

// Fetch reservations if needed
$userReservations = [];
if ($showBorrowed) {
    $userReservations = getUserReservations($conn, $user_id);
}

include __DIR__ . '/../templates/reservation.html';
?>