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
    <link rel="stylesheet" href="style.css">
    <style>
        .details-section {
            max-width: 600px;
            margin: 2rem auto;
            background: #fff;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(44,62,80,0.08);
        }
        .details-header {
            display: flex;
            gap: 2rem;
            align-items: flex-start;
        }
        .details-cover {
            width: 150px;
            height: 210px;
            object-fit: cover;
            border-radius: 6px;
            box-shadow: 0 2px 8px rgba(44,62,80,0.10);
        }
        .details-info {
            flex: 1;
        }
        .details-info h2 {
            margin: 0 0 0.5rem 0;
        }
        .details-table {
            width: 100%;
            margin-top: 1rem;
            border-collapse: collapse;
        }
        .details-table td {
            padding: 0.3rem 0.7rem;
            vertical-align: top;
        }
        .details-synopsis {
            margin-top: 1.5rem;
            font-size: 1.05rem;
            color: #34495e;
            background: #f9f9f9;
            padding: 1rem;
            border-radius: 6px;
        }
        .reserve-link {
            display: inline-block;
            margin-top: 1.5rem;
            padding: 0.6rem 1.2rem;
            background: #2980b9;
            color: #fff;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            transition: background 0.2s;
        }
        .reserve-link:hover {
            background: #1c5d8c;
        }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="details-section">
    <div class="details-header">
        <img class="details-cover" src="<?= htmlspecialchars($book['cover_image'] ?? 'default_cover.png') ?>" alt="Book Cover">
        <div class="details-info">
            <h2><?= htmlspecialchars($book['title']) ?></h2>
            <table class="details-table">
                <tr><td><strong>Author:</strong></td><td><?= htmlspecialchars($book['author']) ?></td></tr>
                <tr><td><strong>ISBN:</strong></td><td><?= htmlspecialchars($book['isbn']) ?></td></tr>
                <tr><td><strong>Publisher:</strong></td><td><?= htmlspecialchars($book['publisher']) ?></td></tr>
                <tr><td><strong>Year Published:</strong></td><td><?= htmlspecialchars($book['year_published']) ?></td></tr>
                <tr><td><strong>Category:</strong></td><td><?= htmlspecialchars($book['category']) ?></td></tr>
                <tr><td><strong>Copies Left:</strong></td><td><?= htmlspecialchars($book['copies']) ?></td></tr>
                <tr><td><strong>Status:</strong></td><td><?= htmlspecialchars($book['availability_status']) ?></td></tr>
                <tr><td><strong>Shelf Location:</strong></td><td><?= htmlspecialchars($book['shelf_location']) ?></td></tr>
            </table>
        </div>
    </div>
    <div class="details-synopsis">
        <strong>Synopsis:</strong><br>
        <?= nl2br(htmlspecialchars($book['synopsis'] ?? 'No synopsis available.')) ?>
    </div>
    <a class="reserve-link" href="reservation.php?reserve_book_id=<?= $book['id'] ?>">Reserve this Book</a>
</div>
<?php include 'footer.php'; ?>
</body>
</html>
