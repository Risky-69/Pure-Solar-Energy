<?php
$host     = "localhost";
$username = "root";
$pass = ""; // Or your MySQL password
$dbname   = "puresolarenergy";

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>