<?php
session_start();
require_once "config.php";

$searchQuery = isset($_GET['q']) ? trim($_GET['q']) : '';
$searchResults = [];

if ($searchQuery !== '') {
    $like = '%' . $searchQuery . '%';
    $stmt = $conn->prepare("SELECT id, title, author, year_published, category, cover_image, total_borrow, total_rating FROM books WHERE title LIKE ? OR author LIKE ? OR category LIKE ? ORDER BY title ASC");
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
    <link rel="stylesheet" href="../styles/style.css">
    <link rel="stylesheet" href="../styles/search_books.css">
</head>
<body>
    <div class="body-bg">
        <img src="../img/school.png" alt="Background" class="bg-img">
        <div class="bg-overlay"></div>
    </div>
    <?php include '../php/navbar.php'; ?>
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
                            <div class="book-borrow">Borrowed: <?= htmlspecialchars($book['total_borrow']) ?></div>
                            <div class="book-rating">Rating: <?= htmlspecialchars($book['total_rating']) ?></div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php include '../templates/footer.html'; ?>
</body>
</html>
