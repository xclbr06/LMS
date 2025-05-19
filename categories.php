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
    // Directly inject $limit (safe because it's an integer)
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
    <link rel="stylesheet" href="style.css">
    <style>
        .dashboard-section {
            margin: 2rem;
        }
        .section-title {
            font-size: 1.3rem;
            font-weight: bold;
            margin-bottom: 1rem;
        }
        .books-row {
            display: flex;
            gap: 1.5rem;
            margin-bottom: 2.5rem;
        }
        .book-card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(44,62,80,0.08);
            width: 170px;
            text-align: center;
            padding: 1rem 0.5rem 1rem 0.5rem;
            transition: box-shadow 0.2s;
        }
        .book-card:hover {
            box-shadow: 0 4px 16px rgba(44,62,80,0.18);
        }
        .book-cover {
            width: 100px;
            height: 140px;
            object-fit: cover;
            border-radius: 4px;
            margin-bottom: 0.7rem;
        }
        .book-title {
            font-size: 1.05rem;
            font-weight: bold;
            margin-bottom: 0.2rem;
            color: #34495e;
        }
        .book-author {
            font-size: 0.97rem;
            color: #7f8c8d;
            margin-bottom: 0.1rem;
        }
        .book-year {
            font-size: 0.93rem;
            color: #b2bec3;
        }
        .book-rating {
            font-size: 0.9rem;
            color: #f39c12;
            margin-top: 0.3rem;
        }
        a.book-link {
            text-decoration: none;
            color: inherit;
        }
    </style>
</head>
<body>
    <!-- Layered background image and blue overlay -->
<div class="body-bg">
    <img src="school.png" alt="Background" class="bg-img">
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
<?php include 'footer.php'; ?>
</body>
</html>
