<?php
session_start();
require_once "config.php";

// Fetch all books in random order
$books = [];
$stmt = $conn->prepare("SELECT id, title, author, year_published, category, cover_image, total_rating FROM books ORDER BY RAND()");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $books[] = $row;
}
$stmt->close();

include __DIR__ . '/../templates/all_books.html';