<?php
session_start();
require_once "config.php";

$searchQuery = isset($_GET['q']) ? trim($_GET['q']) : '';
$searchResults = [];

if ($searchQuery !== '') {
    $like = '%' . $searchQuery . '%';
    $stmt = $conn->prepare("SELECT id, title, author, year_published, category, cover_image FROM books WHERE title LIKE ? OR author LIKE ? OR category LIKE ? ORDER BY title ASC");
    $stmt->bind_param("sss", $like, $like, $like);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $searchResults[] = $row;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Search Books - School Library Management System</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .search-section { margin: 2rem; }
        .book-list { margin: 1rem 0; display: flex; flex-wrap: wrap; gap: 1.5rem; }
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
<div class="search-section">
    <h2>Search Results for "<?= htmlspecialchars($searchQuery) ?>"</h2>
    <?php if ($searchQuery === ''): ?>
        <div>Please enter a search term.</div>
    <?php elseif (empty($searchResults)): ?>
        <div>No books found for your search.</div>
    <?php else: ?>
        <div class="book-list">
            <?php foreach ($searchResults as $book): ?>
                <a class="book-link" href="book_details.php?id=<?= $book['id'] ?>">
                    <div class="book-card">
                        <img class="book-cover" src="<?= htmlspecialchars($book['cover_image'] ?? 'default_cover.png') ?>" alt="Book Cover">
                        <div class="book-title"><?= htmlspecialchars($book['title']) ?></div>
                        <div class="book-author"><?= htmlspecialchars($book['author']) ?></div>
                        <div class="book-year"><?= htmlspecialchars($book['year_published']) ?></div>
                        <div><?= htmlspecialchars($book['category']) ?></div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<?php include 'footer.php'; ?>
</body>
</html>
