<?php
// Database configuration
define('DB_SERVER', 'localhost'); 
define('DB_USERNAME', 'root');    
define('DB_PASSWORD', '');        
define('DB_NAME', 'lms'); 

// Attempt to connect to MySQL database
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>