-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 16, 2025 at 02:34 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `lms`
--

-- --------------------------------------------------------

--
-- Table structure for table `books`
--

CREATE TABLE `books` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `author` varchar(255) NOT NULL,
  `isbn` varchar(20) NOT NULL,
  `publisher` varchar(100) NOT NULL,
  `year_published` year(4) NOT NULL,
  `category` varchar(100) NOT NULL,
  `cover_image` varchar(255) DEFAULT NULL,
  `copies` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `shelf_location` varchar(50) NOT NULL,
  `availability_status` enum('available','checked_out','reserved','lost') NOT NULL DEFAULT 'available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `books`
--

INSERT INTO `books` (`id`, `title`, `author`, `isbn`, `publisher`, `year_published`, `category`, `cover_image`, `copies`, `shelf_location`, `availability_status`) VALUES
(1, 'Clean Code: A Handbook of Agile Software Craftsmanship', 'Robert C. Martin', '9780132350884', 'Prentice Hall', '2008', 'Technology', 'https://covers.openlibrary.org/b/isbn/9780132350884-L.jpg', 5, 'T-01', 'available'),
(2, 'The Pragmatic Programmer', 'Andrew Hunt, David Thomas', '9780201616224', 'Addison-Wesley', '1999', 'Technology', 'https://covers.openlibrary.org/b/isbn/9780201616224-L.jpg', 4, 'T-02', 'available'),
(3, 'Introduction to Algorithms', 'Thomas H. Cormen, Charles E. Leiserson, Ronald L. Rivest, Clifford Stein', '9780262033848', 'MIT Press', '2009', 'Technology', 'https://covers.openlibrary.org/b/isbn/9780262033848-L.jpg', 4, 'T-03', 'available'),
(4, 'Design Patterns: Elements of Reusable Object-Oriented Software', 'Erich Gamma, Richard Helm, Ralph Johnson, John Vlissides', '9780201633610', 'Addison-Wesley', '1994', 'Technology', 'https://covers.openlibrary.org/b/isbn/9780201633610-L.jpg', 5, 'T-04', 'available'),
(5, 'Artificial Intelligence: A Modern Approach', 'Stuart Russell, Peter Norvig', '9780136042594', 'Pearson', '2010', 'Technology', 'https://covers.openlibrary.org/b/isbn/9780136042594-L.jpg', 3, 'T-05', 'available'),
(6, 'To Kill a Mockingbird', 'Harper Lee', '9780061120084', 'J.B. Lippincott & Co.', '1960', 'Fiction', 'https://covers.openlibrary.org/b/isbn/9780061120084-L.jpg', 5, 'F-01', 'available'),
(7, '1984', 'George Orwell', '9780451524935', 'Secker & Warburg', '1949', 'Fiction', 'https://covers.openlibrary.org/b/isbn/9780451524935-L.jpg', 4, 'F-02', 'available'),
(8, 'The Great Gatsby', 'F. Scott Fitzgerald', '9780743273565', 'Charles Scribner\'s Sons', '1925', 'Fiction', 'https://covers.openlibrary.org/b/isbn/9780743273565-L.jpg', 3, 'F-03', 'available'),
(9, 'Pride and Prejudice', 'Jane Austen', '9780141439518', 'T. Egerton', '0000', 'Fiction', 'https://covers.openlibrary.org/b/isbn/9780141439518-L.jpg', 5, 'F-04', 'available'),
(10, 'The Catcher in the Rye', 'J.D. Salinger', '9780316769488', 'Little, Brown and Company', '1951', 'Fiction', 'https://covers.openlibrary.org/b/isbn/9780316769488-L.jpg', 3, 'F-05', 'available'),
(11, 'The Diary of a Young Girl', 'Anne Frank', '9780553296983', 'Contact Publishing', '1947', 'Historical', 'https://covers.openlibrary.org/b/olid/OL24210618M-L.jpg', 4, 'H-01', 'available'),
(12, 'Team of Rivals: The Political Genius of Abraham Lincoln', 'Doris Kearns Goodwin', '9780743270755', 'Simon & Schuster', '2005', 'Historical', 'https://covers.openlibrary.org/b/isbn/9780743270755-L.jpg', 5, 'H-02', 'available'),
(13, 'Guns, Germs, and Steel', 'Jared Diamond', '9780393317558', 'W. W. Norton & Company', '1997', 'Historical', 'https://covers.openlibrary.org/b/isbn/9780393317558-L.jpg', 3, 'H-03', 'available'),
(14, 'The Wright Brothers', 'David McCullough', '9781476728742', 'Simon & Schuster', '2015', 'Historical', 'https://covers.openlibrary.org/b/isbn/9781476728742-L.jpg', 5, 'H-04', 'available'),
(15, 'The Book Thief', 'Markus Zusak', '9780375842207', 'Picador', '2005', 'Historical', 'https://covers.openlibrary.org/b/isbn/9780375842207-L.jpg', 3, 'H-05', 'available'),
(16, 'A Brief History of Time', 'Stephen Hawking', '9780553380163', 'Bantam Books', '1988', 'Science', 'https://covers.openlibrary.org/b/isbn/9780553380163-L.jpg', 4, 'S-01', 'available'),
(17, 'The Selfish Gene', 'Richard Dawkins', '9780199291151', 'Oxford University Press', '1976', 'Science', 'https://covers.openlibrary.org/b/isbn/9780199291151-L.jpg', 5, 'S-02', 'available'),
(18, 'The Origin of Species', 'Charles Darwin', '9781509827695', 'John Murray', '0000', 'Science', 'https://covers.openlibrary.org/b/olid/OL23278117M-L.jpg', 5, 'S-03', 'available'),
(19, 'Cosmos', 'Carl Sagan', '9780345331359', 'Random House', '1980', 'Science', 'https://covers.openlibrary.org/b/isbn/9780345331359-L.jpg', 4, 'S-04', 'available'),
(20, 'The Double Helix', 'James D. Watson', '9780743216302', 'Atheneum', '1968', 'Science', 'https://covers.openlibrary.org/b/isbn/9780743216302-L.jpg', 5, 'S-05', 'available'),
(21, 'A Mathematician\'s Apology', 'G.H. Hardy', '9780521427067', 'Cambridge University Press', '1940', 'Mathematics', 'https://covers.openlibrary.org/b/isbn/9780521427067-L.jpg', 5, 'M-01', 'available'),
(22, 'Flatland: A Romance of Many Dimensions', 'Edwin A. Abbott', '9780486272634', 'Seeley & Co.', '0000', 'Mathematics', 'https://covers.openlibrary.org/b/isbn/9780486272634-L.jpg', 5, 'M-02', 'available'),
(23, 'The Princeton Companion to Mathematics', 'Timothy Gowers', '9780691118802', 'Princeton University Press', '2008', 'Mathematics', 'https://covers.openlibrary.org/b/isbn/9780691118802-L.jpg', 5, 'M-03', 'available'),
(24, 'Fermat\'s Enigma', 'Simon Singh', '9780385493629', 'Fourth Estate', '1997', 'Mathematics', 'https://covers.openlibrary.org/b/olid/OL7508767M-L.jpg', 5, 'M-04', 'available'),
(25, 'Journey through Genius: The Great Theorems of Mathematics', 'William Dunham', '9780140147391', 'Penguin Books', '1990', 'Mathematics', 'https://covers.openlibrary.org/b/isbn/9780140147391-L.jpg', 5, 'M-05', 'available');

-- --------------------------------------------------------

--
-- Table structure for table `reservations`
--

CREATE TABLE `reservations` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `book_id` int(10) UNSIGNED NOT NULL,
  `reserved_at` datetime NOT NULL,
  `due_date` date NOT NULL,
  `status` enum('reserved','returned','cancelled') NOT NULL DEFAULT 'reserved'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reservations`
--

INSERT INTO `reservations` (`id`, `user_id`, `book_id`, `reserved_at`, `due_date`, `status`) VALUES
(7, 2, 19, '2025-05-16 14:07:02', '2025-05-17', 'returned'),
(8, 2, 19, '2025-05-16 14:09:30', '2025-05-18', 'reserved'),
(9, 2, 2, '2025-05-16 14:13:50', '2025-05-30', 'reserved');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `student_id` varchar(9) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role` enum('student','teacher','admin') NOT NULL DEFAULT 'student',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `middle_name`, `last_name`, `email`, `student_id`, `password`, `phone`, `role`, `created_at`) VALUES
(2, 'Justin', '', 'Marinas', 'jk@gmail.com', '2324-0696', '$2y$10$JWkC9U3dic24ATLxd3yRQujey7PuSsVAjovk8WyFM4YwBlL2TzZX.', '', 'admin', '2025-05-08 06:31:07');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_isbn` (`isbn`);

--
-- Indexes for table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `book_id` (`book_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_email` (`email`),
  ADD UNIQUE KEY `unique_student_id` (`student_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `books`
--
ALTER TABLE `books`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `reservations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reservations_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
