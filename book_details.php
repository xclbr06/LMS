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
            max-width: 700px;
            margin: 2.5rem auto 2rem auto;
            background: rgba(255,255,255,0.97);
            padding: 2.2rem 2rem 2rem 2rem;
            border-radius: 14px;
            box-shadow: 0 4px 24px rgba(9,132,227,0.13);
        }
        .details-header {
            display: flex;
            gap: 2.2rem;
            align-items: flex-start;
            flex-wrap: wrap;
        }
        .details-cover {
            width: 160px;
            height: 225px;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: 0 2px 12px rgba(9,132,227,0.15);
            background: #eaf6fb;
            flex-shrink: 0;
        }
        .details-info {
            flex: 1;
            min-width: 220px;
            display: flex;
            align-items: flex-start;
        }
        .details-table {
            width: 100%;
            margin-top: 0.5rem;
            margin-bottom: 0.2rem;
            border-collapse: collapse;
            font-size: 1.07rem;
            background: #f7fafc;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 1px 6px rgba(9,132,227,0.07);
        }
        .details-table th, .details-table td {
            border: 1.5px solid #0984e3;
            padding: 0.55rem 1rem;
            vertical-align: top;
        }
        .details-table th {
            background: #0984e3;
            color: #fff;
            font-weight: 700;
            width: 140px;
            text-align: left;
        }
        .details-table td {
            color: #222f3e;
            background: #fff;
        }
        .details-info h2 {
            margin: 0 0 1.1rem 0;
            color: #0984e3;
            font-size: 2rem;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        .details-synopsis {
            margin-top: 1.7rem;
            font-size: 1.08rem;
            color: #34495e;
            background: #f7fafc;
            padding: 1.1rem 1rem;
            border-radius: 8px;
            box-shadow: 0 1px 6px rgba(9,132,227,0.07);
        }
        .reserve-link {
            display: inline-block;
            margin: 2.2rem auto 0 auto;
            padding: 0.7rem 1.7rem;
            background: #ffb347;
            color: #222f3e;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
            font-size: 1.08rem;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 8px rgba(9,132,227,0.10);
            transition: background 0.2s, color 0.2s;
            text-align: center;
        }
        .reserve-btn-center {
            display: flex;
            justify-content: center;
        }
        .reserve-link:hover {
            background: #0984e3;
            color: #fff;
        }
        @media (max-width: 800px) {
            .details-header {
                flex-direction: column;
                align-items: center;
                gap: 1.2rem;
            }
            .details-section {
                padding: 1.2rem 0.5rem;
            }
            .details-info h2 {
                text-align: center;
            }
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
        <a href="<?= $backTo ?>" title="Back" style="position: absolute; right: 32px; top: 32px; font-size: 2rem; color: #0984e3; text-decoration: none; z-index: 10;">
            &#8592;
        </a>
        <div style="display: flex; flex-direction: column; align-items: center;">
            <h2 class="details-title"
                style="text-align:center; color:#0984e3; font-size:2rem; font-weight:700; letter-spacing:0.5px; margin-bottom:1.1rem; margin-top:0rem; width:100%;">
                <?= htmlspecialchars($book['title']) ?>
            </h2>
            <div class="details-header" style="align-items: center; justify-content: center; width:100%;">
                <div style="display: flex; align-items: center; justify-content: center; width:100%;">
                    <div style="flex-shrink:0; display:flex; align-items:center; height:100%;">
                        <img class="details-cover" src="<?= htmlspecialchars($book['cover_image'] ?? 'default_cover.png') ?>" alt="Book Cover">
                    </div>
                    <div class="details-info" style="width:100%; margin-left:2.2rem;">
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
    <?php include 'footer.php'; ?>
</body>
</html>
