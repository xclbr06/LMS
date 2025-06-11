<?php
session_start();
require_once "config.php";

// Prevent browser caching for security
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

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