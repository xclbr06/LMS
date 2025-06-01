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

// Define categories
$categories = [
    "Technology",
    "Fiction",
    "Historical",
    "Science",
    "Mathematics"
];

// Helper function to fetch up to 7 books for a category
function getBooksByCategory($conn, $category, $limit = 7) {
    $books = [];
    $sql = "SELECT * FROM books WHERE category = ? ORDER BY title ASC LIMIT $limit";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $category);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $books[] = $row;
    }
    $stmt->close();
    return $books;
}

// Fetch books for each category
$categoryBooks = [];
foreach ($categories as $cat) {
    $categoryBooks[$cat] = getBooksByCategory($conn, $cat, 7);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Categories - School Library Management System</title>
    <link rel="stylesheet" href="../styles/categories.css">
</head>
<body>
<div class="body-bg">
    <img src="../img/school.jpg" alt="Background" class="bg-img">
    <div class="bg-overlay"></div>
</div>
<?php include 'navbar.php'; ?>
<!-- Categories Content -->
    <div class="dashboard-section">
        <?php foreach ($categories as $cat): ?>
            <div class="section-title"><?= htmlspecialchars($cat) ?></div>
            <div class="books-row">
                <?php if (count($categoryBooks[$cat]) > 0): ?>
                    <?php foreach ($categoryBooks[$cat] as $book): ?>
                        <a class="book-link" href="book_details.php?id=<?= $book['id'] ?>">
                            <div class="book-card">
                                <img class="book-cover" src="<?= htmlspecialchars($book['cover_image'] ?? 'default_cover.png') ?>" alt="Book Cover">
                                <div class="book-title"><?= htmlspecialchars($book['title']) ?></div>
                                <div class="book-author"><?= htmlspecialchars($book['author']) ?></div>
                                <div class="book-year"><?= htmlspecialchars($book['year_published']) ?></div>
                                <div class="book-rating">Rating: <?= htmlspecialchars($book['total_rating']) ?></div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div>No books found in this category.</div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php include '../templates/footer.html'; ?>
</body>
</html>
