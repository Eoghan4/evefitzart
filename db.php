<?php
require_once __DIR__ . '/config.php';

try {
    $conn = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ]
    );
} catch (PDOException $e) {
    if (ENVIRONMENT === 'production') {
        error_log("Database connection failed: " . $e->getMessage());
        die("We're experiencing technical difficulties. Please try again later.");
    } else {
        die("DB connection failed: " . $e->getMessage());
    }
}
?>
