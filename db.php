<?php
$host = 'localhost';
$dbname = 'kontrakan_digital';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}

// Fungsi untuk mencegah SQL injection
function sanitize($data) {
    global $pdo;
    return htmlspecialchars(strip_tags(trim($data)));
}
?>
