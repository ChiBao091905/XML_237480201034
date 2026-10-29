<?php
$host = 'localhost';
$dbname = 'xml_contacts';
$username = 'root'; // Thay đổi theo cấu hình MySQL của bạn
$password = '';     // Thay đổi theo cấu hình MySQL của bạn

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Lỗi kết nối CSDL: " . $e->getMessage());
}
?>