<?php
$host     = "127.0.0.1";
$port     = 3306;
$username = "root";
$pass     = "";
$dbname   = "puresolarenergy";

// MySQLi Connection (used by Login.php)
$conn = new mysqli($host, $username, $pass, $dbname, $port);

if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

// PDO Connection (used by catalog and dashboard pages)
try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}
?>