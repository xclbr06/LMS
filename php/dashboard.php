<?php
session_start();

// Check if user is logged in, otherwise redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: login.php");
    exit();
}

// Handle logout request
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["logout"])) {
    $_SESSION = array();
    session_destroy();
    header("Location: login.php");
    exit();
}

require_once "config.php";

// Most Popular (by total_borrow)
$popularBooks = [];
$stmt = $conn->prepare("SELECT id, title, author, cover_image, total_borrow FROM books ORDER BY total_borrow DESC LIMIT 7");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) $popularBooks[] = $row;
$stmt->close();

// Most Rated (by total_rating)
$mostRatedBooks = [];
$stmt = $conn->prepare("SELECT id, title, author, cover_image, total_rating FROM books ORDER BY total_rating DESC LIMIT 7");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) $mostRatedBooks[] = $row;
$stmt->close();

// New Releases (by year_published)
$newReleases = [];
$stmt = $conn->prepare("SELECT id, title, author, cover_image, year_published FROM books ORDER BY year_published DESC LIMIT 7");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) $newReleases[] = $row;
$stmt->close();

// Helper function to fetch 7 random books for a section
function getRandomBooks($conn, $limit = 7) {
    $books = [];
    $sql = "SELECT id, title, author, year_published, category, cover_image FROM books ORDER BY RAND() LIMIT ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $books[] = $row;
    }
    $stmt->close();
    return $books;
}

// Function to get overdue message for a user
function getOverdueMsg($conn, $user_id) {
    $sql = "SELECT COUNT(*) as overdue_count FROM reservations WHERE user_id = ? AND status = 'reserved' AND due_date < CURDATE()";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    if ($row['overdue_count'] > 0) {
        return "You are past the due date! Please return the book(s) to the library.";
    }
    return "";
}

// Fetch books for each section
$randomBooks = getRandomBooks($conn, 7);

// Fetch overdue message for the logged-in user
$user_id = $_SESSION['id'];
$overdueMsg = getOverdueMsg($conn, $user_id);

// Pass all variables to the HTML template
include __DIR__ . '/../templates/dashboard.html';
