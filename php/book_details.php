<?php
session_start();
require_once "config.php";

// Get book ID from query
$book_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$book = null;

if ($book_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM books WHERE id = ?");
    $stmt->bind_param("i", $book_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $book = $result->fetch_assoc();
    $stmt->close();
}

if (!$book) {
    echo "<h2>Book not found.</h2>";
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($book['title']) ?> - Book Details</title>
    <link rel="stylesheet" href="../styles/style.css">
    <link rel="stylesheet" href="../styles/book_details.css">
</head>
<body>
    <div class="body-bg">
        <img src="../img/school.jpg" alt="Background" class="bg-img">
        <div class="bg-overlay"></div>
    </div>
    <?php include 'navbar.php'; ?>
    <div class="details-section">
        <?php
        // Determine where to go back
        $backTo = 'dashboard.php';
        if (isset($_SERVER['HTTP_REFERER'])) {
            if (strpos($_SERVER['HTTP_REFERER'], 'categories.php') !== false) {
                $backTo = 'categories.php';
            } elseif (strpos($_SERVER['HTTP_REFERER'], 'dashboard.php') !== false) {
                $backTo = 'dashboard.php';
            }
        }
        ?>
        <a href="<?= $backTo ?>" title="Back" class="back-link">&#8592;</a>
        <div>
            <h2 class="details-title"><?= htmlspecialchars($book['title']) ?></h2>
            <div class="details-header">
                <div class="details-header-flex">
                    <div class="details-cover-container">
                        <img class="details-cover" src="<?= htmlspecialchars($book['cover_image'] ?? 'default_cover.png') ?>" alt="Book Cover">
                    </div>
                    <div class="details-info">
                        <table class="details-table">
                            <tr><th>Author</th><td><?= htmlspecialchars($book['author']) ?></td></tr>
                            <tr><th>ISBN</th><td><?= htmlspecialchars($book['isbn']) ?></td></tr>
                            <tr><th>Publisher</th><td><?= htmlspecialchars($book['publisher']) ?></td></tr>
                            <tr><th>Year Published</th><td><?= htmlspecialchars($book['year_published']) ?></td></tr>
                            <tr><th>Category</th><td><?= htmlspecialchars($book['category']) ?></td></tr>
                            <tr><th>Copies Left</th><td><?= htmlspecialchars($book['copies']) ?></td></tr>
                            <tr><th>Status</th><td><?= htmlspecialchars($book['availability_status']) ?></td></tr>
                            <tr><th>Shelf Location</th><td><?= htmlspecialchars($book['shelf_location']) ?></td></tr>
                            <tr><th>Total Borrowed</th><td><?= htmlspecialchars($book['total_borrow']) ?></td></tr>
                            <tr><th>Rating</th><td><?= htmlspecialchars($book['total_rating']) ?></td></tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="details-synopsis">
            <strong>Synopsis:</strong><br>
            <?= !empty($book['synopsis']) ? nl2br(htmlspecialchars($book['synopsis'])) : 'No synopsis available.' ?>
        </div>
        <div class="reserve-btn-center">
            <a class="reserve-link" href="reservation.php?reserve_book_id=<?= $book['id'] ?>">Reserve this Book</a>
        </div>
    </div>
    <?php include '../templates/footer.html'; ?>
</body>
</html>
