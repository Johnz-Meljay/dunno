<?php
$host = "localhost";
$user = "root";
$pass = ""; // XAMPP default has no password
$dbname = "tetris_db";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>