-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 19, 2025 at 05:18 PM
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
  `availability_status` enum('available','checked_out','reserved','lost') NOT NULL DEFAULT 'available',
  `total_borrow` int(11) DEFAULT 0,
  `total_rating` float DEFAULT 0,
  `synopsis` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `books`
--

INSERT INTO `books` (`id`, `title`, `author`, `isbn`, `publisher`, `year_published`, `category`, `cover_image`, `copies`, `shelf_location`, `availability_status`, `total_borrow`, `total_rating`, `synopsis`) VALUES
(1, 'Clean Code: A Handbook of Agile Software Craftsmanship', 'Robert C. Martin', '9780132350884', 'Prentice Hall', '2008', 'Technology', 'https://covers.openlibrary.org/b/isbn/9780132350884-L.jpg', 5, 'T-01', 'available', 9, 1.6, NULL),
(2, 'The Pragmatic Programmer', 'Andrew Hunt, David Thomas', '9780201616224', 'Addison-Wesley', '1999', 'Technology', 'https://covers.openlibrary.org/b/isbn/9780201616224-L.jpg', 4, 'T-02', 'available', 28, 4.2, NULL),
(3, 'Introduction to Algorithms', 'Thomas H. Cormen, Charles E. Leiserson, Ronald L. Rivest, Clifford Stein', '9780262033848', 'MIT Press', '2009', 'Technology', 'https://covers.openlibrary.org/b/isbn/9780262033848-L.jpg', 4, 'T-03', 'available', 5, 2.9, NULL),
(4, 'Design Patterns: Elements of Reusable Object-Oriented Software', 'Erich Gamma, Richard Helm, Ralph Johnson, John Vlissides', '9780201633610', 'Addison-Wesley', '1994', 'Technology', 'https://covers.openlibrary.org/b/isbn/9780201633610-L.jpg', 5, 'T-04', 'available', 12, 1, NULL),
(5, 'Artificial Intelligence: A Modern Approach', 'Stuart Russell, Peter Norvig', '9780136042594', 'Pearson', '2010', 'Technology', 'https://covers.openlibrary.org/b/isbn/9780136042594-L.jpg', 3, 'T-05', 'available', 14, 3.5, NULL),
(6, 'To Kill a Mockingbird', 'Harper Lee', '9780061120084', 'J.B. Lippincott & Co.', '1960', 'Fiction', 'https://covers.openlibrary.org/b/isbn/9780061120084-L.jpg', 5, 'F-01', 'available', 6, 1.3, NULL),
(7, '1984', 'George Orwell', '9780451524935', 'Secker & Warburg', '1949', 'Fiction', 'https://covers.openlibrary.org/b/isbn/9780451524935-L.jpg', 4, 'F-02', 'available', 10, 3.1, NULL),
(8, 'The Great Gatsby', 'F. Scott Fitzgerald', '9780743273565', 'Charles Scribner\'s Sons', '1925', 'Fiction', 'https://covers.openlibrary.org/b/isbn/9780743273565-L.jpg', 3, 'F-03', 'available', 25, 2.8, NULL),
(9, 'Pride and Prejudice', 'Jane Austen', '9780141439518', 'T. Egerton', '0000', 'Fiction', 'https://covers.openlibrary.org/b/isbn/9780141439518-L.jpg', 5, 'F-04', 'available', 13, 3.3, NULL),
(10, 'The Catcher in the Rye', 'J.D. Salinger', '9780316769488', 'Little, Brown and Company', '1951', 'Fiction', 'https://covers.openlibrary.org/b/isbn/9780316769488-L.jpg', 3, 'F-05', 'available', 11, 3.4, NULL),
(11, 'The Diary of a Young Girl', 'Anne Frank', '9780553296983', 'Contact Publishing', '1947', 'Historical', 'https://covers.openlibrary.org/b/olid/OL24210618M-L.jpg', 4, 'H-01', 'available', 12, 2.1, NULL),
(12, 'Team of Rivals: The Political Genius of Abraham Lincoln', 'Doris Kearns Goodwin', '9780743270755', 'Simon & Schuster', '2005', 'Historical', 'https://covers.openlibrary.org/b/isbn/9780743270755-L.jpg', 5, 'H-02', 'available', 23, 3.4, NULL),
(13, 'Guns, Germs, and Steel', 'Jared Diamond', '9780393317558', 'W. W. Norton & Company', '1997', 'Historical', 'https://covers.openlibrary.org/b/isbn/9780393317558-L.jpg', 3, 'H-03', 'available', 21, 1.4, NULL),
(14, 'The Wright Brothers', 'David McCullough', '9781476728742', 'Simon & Schuster', '2015', 'Historical', 'https://covers.openlibrary.org/b/isbn/9781476728742-L.jpg', 5, 'H-04', 'available', 5, 4, NULL),
(15, 'The Book Thief', 'Markus Zusak', '9780375842207', 'Picador', '2005', 'Historical', 'https://covers.openlibrary.org/b/isbn/9780375842207-L.jpg', 3, 'H-05', 'available', 12, 2.7, NULL),
(16, 'A Brief History of Time', 'Stephen Hawking', '9780553380163', 'Bantam Books', '1988', 'Science', 'https://covers.openlibrary.org/b/isbn/9780553380163-L.jpg', 4, 'S-01', 'available', 15, 4.6, NULL),
(17, 'The Selfish Gene', 'Richard Dawkins', '9780199291151', 'Oxford University Press', '1976', 'Science', 'https://covers.openlibrary.org/b/isbn/9780199291151-L.jpg', 5, 'S-02', 'available', 6, 2, NULL),
(18, 'The Origin of Species', 'Charles Darwin', '9781509827695', 'John Murray', '0000', 'Science', 'https://covers.openlibrary.org/b/olid/OL23278117M-L.jpg', 5, 'S-03', 'available', 7, 3.2, NULL),
(19, 'Cosmos', 'Carl Sagan', '9780345331359', 'Random House', '1980', 'Science', 'https://covers.openlibrary.org/b/isbn/9780345331359-L.jpg', 4, 'S-04', 'available', 12, 1, NULL),
(20, 'The Double Helix', 'James D. Watson', '9780743216302', 'Atheneum', '1968', 'Science', 'https://covers.openlibrary.org/b/isbn/9780743216302-L.jpg', 5, 'S-05', 'available', 7, 2.6, NULL),
(21, 'A Mathematician\'s Apology', 'G.H. Hardy', '9780521427067', 'Cambridge University Press', '1940', 'Mathematics', 'https://covers.openlibrary.org/b/isbn/9780521427067-L.jpg', 5, 'M-01', 'available', 23, 4.6, NULL),
(22, 'Flatland: A Romance of Many Dimensions', 'Edwin A. Abbott', '9780486272634', 'Seeley & Co.', '0000', 'Mathematics', 'https://covers.openlibrary.org/b/isbn/9780486272634-L.jpg', 5, 'M-02', 'available', 10, 2.4, NULL),
(23, 'The Princeton Companion to Mathematics', 'Timothy Gowers', '9780691118802', 'Princeton University Press', '2008', 'Mathematics', 'https://covers.openlibrary.org/b/isbn/9780691118802-L.jpg', 5, 'M-03', 'available', 28, 1.2, NULL),
(24, 'Fermat\'s Enigma', 'Simon Singh', '9780385493629', 'Fourth Estate', '1997', 'Mathematics', 'https://covers.openlibrary.org/b/olid/OL7508767M-L.jpg', 5, 'M-04', 'available', 29, 1.8, NULL),
(25, 'Journey through Genius: The Great Theorems of Mathematics', 'William Dunham', '9780140147391', 'Penguin Books', '1990', 'Mathematics', 'https://covers.openlibrary.org/b/isbn/9780140147391-L.jpg', 5, 'M-05', 'available', 30, 4.5, NULL),
(26, 'The Mythical Man-Month', 'Frederick P. Brooks Jr.', '9780201835953', 'Addison-Wesley', '1995', 'Technology', 'https://covers.openlibrary.org/b/isbn/9780201835953-L.jpg', 4, 'T-06', 'available', 17, 4.4, 'A classic on software engineering and project management. Brooks shares insights from his experience at IBM. The book is famous for the concept \"adding manpower to a late software project makes it later.\"'),
(27, 'Refactoring: Improving the Design of Existing Code', 'Martin Fowler', '9780201485677', 'Addison-Wesley', '1999', 'Technology', 'https://covers.openlibrary.org/b/isbn/9780201485677-L.jpg', 3, 'T-07', 'available', 12, 4.6, 'This book teaches how to improve code structure without changing its behavior. It introduces key refactoring techniques. Essential for maintainable software.'),
(28, 'Structure and Interpretation of Computer Programs', 'Harold Abelson, Gerald Jay Sussman', '9780262510875', 'MIT Press', '1996', 'Technology', 'https://covers.openlibrary.org/b/isbn/9780262510875-L.jpg', 5, 'T-08', 'available', 9, 4.2, 'A foundational text in computer science. It covers core programming concepts using Scheme. Known for its depth and clarity.'),
(29, 'The Art of Computer Programming', 'Donald E. Knuth', '9780201896831', 'Addison-Wesley', '2011', 'Technology', 'https://covers.openlibrary.org/b/isbn/9780201896831-L.jpg', 2, 'T-09', 'available', 21, 4.9, 'Knuth\'s multi-volume work is a cornerstone of computer science. It covers algorithms, data structures, and mathematical techniques. Highly regarded for its rigor.'),
(30, 'Don\'t Make Me Think', 'Steve Krug', '9780321965516', 'New Riders', '2014', 'Technology', 'https://covers.openlibrary.org/b/isbn/9780321965516-L.jpg', 4, 'T-10', 'available', 8, 4.1, 'A guide to web usability and user experience. Krug explains how to make websites intuitive. The book is practical and easy to read.'),
(31, 'Brave New World', 'Aldous Huxley', '9780060850524', 'Harper Perennial', '1932', 'Fiction', 'https://covers.openlibrary.org/b/isbn/9780060850524-L.jpg', 4, 'F-06', 'available', 13, 4.3, 'A dystopian vision of a future society. Explores themes of control, technology, and freedom. Huxley\'s classic remains thought-provoking.'),
(32, 'The Hobbit', 'J.R.R. Tolkien', '9780547928227', 'Houghton Mifflin', '1937', 'Fiction', 'https://covers.openlibrary.org/b/isbn/9780547928227-L.jpg', 5, 'F-07', 'available', 20, 4.8, 'Bilbo Baggins embarks on an unexpected adventure. He faces dragons, trolls, and discovers courage. A timeless fantasy classic.'),
(33, 'The Alchemist', 'Paulo Coelho', '9780061122415', 'HarperOne', '1988', 'Fiction', 'https://covers.openlibrary.org/b/isbn/9780061122415-L.jpg', 3, 'F-08', 'available', 11, 4.5, 'A young shepherd pursues his dreams across the desert. The story is about destiny and self-discovery. It inspires readers to follow their hearts.'),
(34, 'The Road', 'Cormac McCarthy', '9780307387899', 'Vintage', '2006', 'Fiction', 'https://covers.openlibrary.org/b/isbn/9780307387899-L.jpg', 2, 'F-09', 'available', 7, 4, 'A father and son journey through a post-apocalyptic world. Their bond is tested by hardship and hope. The novel is stark and moving.'),
(35, 'Never Let Me Go', 'Kazuo Ishiguro', '9781400078776', 'Vintage', '2005', 'Fiction', 'https://covers.openlibrary.org/b/isbn/9781400078776-L.jpg', 4, 'F-10', 'available', 10, 4.2, 'A haunting story of friendship and loss. Students at a mysterious school uncover their fate. Ishiguro\'s novel is poignant and unforgettable.'),
(36, 'Wolf Hall', 'Hilary Mantel', '9780312429980', 'Picador', '2009', 'Historical', 'https://covers.openlibrary.org/b/isbn/9780312429980-L.jpg', 3, 'H-06', 'available', 8, 4.4, 'A vivid portrait of Thomas Cromwell\'s rise in Tudor England. Mantel brings history to life with rich detail. Winner of the Man Booker Prize.'),
(37, 'The Pillars of the Earth', 'Ken Follett', '9780451225245', 'Signet', '1989', 'Historical', 'https://covers.openlibrary.org/b/isbn/9780451225245-L.jpg', 5, 'H-07', 'available', 15, 4.7, 'Set in 12th-century England, this epic follows the building of a cathedral. It weaves together love, betrayal, and ambition. Follett\'s storytelling is masterful.'),
(38, 'All the Light We Cannot See', 'Anthony Doerr', '9781501173219', 'Scribner', '2014', 'Historical', 'https://covers.openlibrary.org/b/isbn/9781501173219-L.jpg', 4, 'H-08', 'available', 12, 4.6, 'A blind French girl and a German boy cross paths during WWII. Their stories intertwine in occupied France. The novel is beautifully written and deeply moving.'),
(39, 'The Nightingale', 'Kristin Hannah', '9781250080400', 'St. Martin\'s Press', '2015', 'Historical', 'https://covers.openlibrary.org/b/isbn/9781250080400-L.jpg', 4, 'H-09', 'available', 10, 4.5, 'Two sisters struggle to survive and resist during the Nazi occupation of France. The story explores love, sacrifice, and resilience. A powerful tale of women in war.'),
(40, 'The Other Boleyn Girl', 'Philippa Gregory', '9780743227445', 'Touchstone', '2001', 'Historical', 'https://covers.openlibrary.org/b/isbn/9780743227445-L.jpg', 3, 'H-10', 'available', 9, 4.1, 'Mary Boleyn navigates the intrigue of Henry VIII\'s court. Her story is one of ambition and rivalry. Gregory\'s novel is rich in historical detail.'),
(41, 'Silent Spring', 'Rachel Carson', '9780618249060', 'Houghton Mifflin', '1962', 'Science', 'https://covers.openlibrary.org/b/isbn/9780618249060-L.jpg', 4, 'S-06', 'available', 14, 4.5, 'Carson\'s book launched the environmental movement. It exposes the dangers of pesticides. The work is both scientific and poetic.'),
(42, 'The Gene: An Intimate History', 'Siddhartha Mukherjee', '9781476733500', 'Scribner', '2016', 'Science', 'https://covers.openlibrary.org/b/isbn/9781476733500-L.jpg', 3, 'S-07', 'available', 11, 4.7, 'A sweeping history of the gene and genetics. Mukherjee blends science with personal stories. The book is accessible and enlightening.'),
(43, 'The Emperor of All Maladies', 'Siddhartha Mukherjee', '9781439170915', 'Scribner', '2010', 'Science', 'https://covers.openlibrary.org/b/isbn/9781439170915-L.jpg', 4, 'S-08', 'available', 10, 4.6, 'A biography of cancer from ancient times to the present. The book explores medical breakthroughs and setbacks. It is both informative and deeply human.'),
(44, 'The Immortal Life of Henrietta Lacks', 'Rebecca Skloot', '9781400052189', 'Crown', '2010', 'Science', 'https://covers.openlibrary.org/b/isbn/9781400052189-L.jpg', 5, 'S-09', 'available', 13, 4.4, 'The story of the woman behind the HeLa cells. Skloot investigates ethics, race, and science. The narrative is compelling and important.'),
(45, 'Astrophysics for People in a Hurry', 'Neil deGrasse Tyson', '9780393609394', 'W. W. Norton & Company', '2017', 'Science', 'https://covers.openlibrary.org/b/isbn/9780393609394-L.jpg', 4, 'S-10', 'available', 9, 4.3, 'Tyson explains the universe in bite-sized chapters. The book is witty, clear, and fascinating. Perfect for readers short on time.'),
(46, 'The Man Who Knew Infinity', 'Robert Kanigel', '9781476763491', 'Washington Square Press', '1991', 'Mathematics', 'https://covers.openlibrary.org/b/isbn/9781476763491-L.jpg', 4, 'M-06', 'available', 13, 4.5, 'The biography of mathematician Ramanujan. It explores his genius and struggles. A story of passion and discovery.'),
(47, 'In Pursuit of the Unknown: 17 Equations That Changed the World', 'Ian Stewart', '9780465029730', 'Basic Books', '2012', 'Mathematics', 'https://covers.openlibrary.org/b/isbn/9780465029730-L.jpg', 3, 'M-07', 'available', 8, 4.2, 'Stewart explores the impact of 17 key equations. The book connects math to real-world events. It is engaging and accessible.'),
(48, 'How Not to Be Wrong: The Power of Mathematical Thinking', 'Jordan Ellenberg', '9780143127536', 'Penguin Books', '2014', 'Mathematics', 'https://covers.openlibrary.org/b/isbn/9780143127536-L.jpg', 5, 'M-08', 'available', 11, 4.4, 'Ellenberg shows how math shapes our lives. The book is witty and insightful. It encourages logical thinking in everyday situations.'),
(49, 'The Joy of x', 'Steven Strogatz', '9780544105850', 'Mariner Books', '2012', 'Mathematics', 'https://covers.openlibrary.org/b/isbn/9780544105850-L.jpg', 4, 'M-09', 'available', 10, 4.3, 'Strogatz makes math fun and relatable. He explains concepts with real-life examples. The book is perfect for math enthusiasts and novices alike.'),
(50, 'Love and Math', 'Edward Frenkel', '9780465050741', 'Basic Books', '2013', 'Mathematics', 'https://covers.openlibrary.org/b/isbn/9780465050741-L.jpg', 3, 'M-10', 'available', 7, 4.1, 'Frenkel shares his journey into the world of mathematics. The book blends autobiography with mathematical ideas. It inspires a love for learning.');

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
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

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
