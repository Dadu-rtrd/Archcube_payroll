<?php
$host = 'localhost';
$dbname = 'archcubeV1';
$username = 'root';
$password = 'admin';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $conn;
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit;
}
