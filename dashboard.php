
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

// Helper function to fetch 5 random books for a section
function getRandomBooks($conn, $limit = 5) {
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

// Helper function to fetch 5 most recent books by year
function getNewReleasesBooks($conn, $limit = 5) {
    $books = [];
    $sql = "SELECT id, title, author, year_published, category, cover_image FROM books ORDER BY year_published DESC, id DESC LIMIT ?";
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

// Fetch books for each section
$mostPopularBooks = getRandomBooks($conn, 5);
$mostBorrowedBooks = getRandomBooks($conn, 5);
$mostRatedBooks = getRandomBooks($conn, 5);
$newReleasesBooks = getNewReleasesBooks($conn, 5);
$randomBooks = getRandomBooks($conn, 5);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - School Library Management System</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .dashboard-section {
            margin: 2rem;
        }
        .welcome {
            margin-bottom: 2rem;
            font-size: 1.2rem;
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
        a.book-link {
            text-decoration: none;
            color: inherit;
        }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="dashboard-section">
    <div class="section-title">Most Popular</div>
    <div class="books-row">
        <?php foreach ($mostPopularBooks as $book): ?>
            <a class="book-link" href="book_details.php?id=<?= $book['id'] ?>">
                <div class="book-card">
                    <img class="book-cover" src="<?= htmlspecialchars($book['cover_image'] ?? 'default_cover.png') ?>" alt="Book Cover">
                    <div class="book-title"><?= htmlspecialchars($book['title']) ?></div>
                    <div class="book-author"><?= htmlspecialchars($book['author']) ?></div>
                    <div class="book-year"><?= htmlspecialchars($book['year_published']) ?></div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="section-title">Most Borrowed</div>
    <div class="books-row">
        <?php foreach ($mostBorrowedBooks as $book): ?>
            <a class="book-link" href="book_details.php?id=<?= $book['id'] ?>">
                <div class="book-card">
                    <img class="book-cover" src="<?= htmlspecialchars($book['cover_image'] ?? 'default_cover.png') ?>" alt="Book Cover">
                    <div class="book-title"><?= htmlspecialchars($book['title']) ?></div>
                    <div class="book-author"><?= htmlspecialchars($book['author']) ?></div>
                    <div class="book-year"><?= htmlspecialchars($book['year_published']) ?></div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="section-title">Most Rated</div>
    <div class="books-row">
        <?php foreach ($mostRatedBooks as $book): ?>
            <a class="book-link" href="book_details.php?id=<?= $book['id'] ?>">
                <div class="book-card">
                    <img class="book-cover" src="<?= htmlspecialchars($book['cover_image'] ?? 'default_cover.png') ?>" alt="Book Cover">
                    <div class="book-title"><?= htmlspecialchars($book['title']) ?></div>
                    <div class="book-author"><?= htmlspecialchars($book['author']) ?></div>
                    <div class="book-year"><?= htmlspecialchars($book['year_published']) ?></div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="section-title">New Releases</div>
    <div class="books-row">
        <?php foreach ($newReleasesBooks as $book): ?>
            <a class="book-link" href="book_details.php?id=<?= $book['id'] ?>">
                <div class="book-card">
                    <img class="book-cover" src="<?= htmlspecialchars($book['cover_image'] ?? 'default_cover.png') ?>" alt="Book Cover">
                    <div class="book-title"><?= htmlspecialchars($book['title']) ?></div>
                    <div class="book-author"><?= htmlspecialchars($book['author']) ?></div>
                    <div class="book-year"><?= htmlspecialchars($book['year_published']) ?></div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="section-title">Random</div>
    <div class="books-row">
        <?php foreach ($randomBooks as $book): ?>
            <a class="book-link" href="book_details.php?id=<?= $book['id'] ?>">
                <div class="book-card">
                    <img class="book-cover" src="<?= htmlspecialchars($book['cover_image'] ?? 'default_cover.png') ?>" alt="Book Cover">
                    <div class="book-title"><?= htmlspecialchars($book['title']) ?></div>
                    <div class="book-author"><?= htmlspecialchars($book['author']) ?></div>
                    <div class="book-year"><?= htmlspecialchars($book['year_published']) ?></div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</div>
<?php include 'footer.php'; ?>
</body>
</html>
