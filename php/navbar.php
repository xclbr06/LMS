<link rel="stylesheet" href="../styles/navbar-style.css">
<nav class="navbar">
    <div class="navbar-left">
        <img src="../img/iscp.png" alt="School Logo" class="school-logo">
        <span class="school-name">ISCP Library Management System</span>
    </div>
    <div class="navbar-links">
        <a href="dashboard.php">Home</a>
        <a href="categories.php">Categories</a>
        <a href="all_books.php">All Books</a>
        <a href="reservation.php">Reservation</a>
        <a href="profile.php">Profile</a>
        <form class="navbar-search" method="get" action="search_books.php" style="display:inline;">
            <input type="text" name="q" placeholder="Search books..." required>
            <button type="submit">Search</button>
        </form>
    </div>
    <div class="navbar-right">
        <div class="welcome-navbar">
            Welcome, <strong><?= isset($_SESSION["first_name"]) && isset($_SESSION["last_name"]) ? 
                htmlspecialchars($_SESSION["first_name"] . ' ' . $_SESSION["last_name"]) : 
                'Guest' ?></strong>!
        </div>
        <form method="post" action="logout.php" style="display:inline;">
            <button class="logout-btn" type="submit" name="logout">Logout</button>
        </form>
    </div>
</nav>

