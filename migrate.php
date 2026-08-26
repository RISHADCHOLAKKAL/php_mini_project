<?php
require 'config.php';
header('Content-Type: application/json');
if ($db->connect_error) { echo json_encode(['error' => $db->connect_error]); exit; }

// Create database if localhost
if ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['HTTP_HOST'] === '127.0.0.1') {
  $db->query("CREATE DATABASE IF NOT EXISTS ecanteen");
  $db->select_db("ecanteen");
}

$tables = [
  "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('student','admin') NOT NULL DEFAULT 'student',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  )",
  "CREATE TABLE IF NOT EXISTS menu_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    description VARCHAR(255) DEFAULT '',
    price INT NOT NULL DEFAULT 0,
    category ENUM('food','beverage','snacks') NOT NULL,
    qty INT NOT NULL DEFAULT 0,
    is_available TINYINT(1) NOT NULL DEFAULT 1,
    icon TEXT
  )",
  "CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_code VARCHAR(20) NOT NULL,
    order_type VARCHAR(50) NOT NULL,
    table_num INT DEFAULT 0,
    total INT NOT NULL DEFAULT 0,
    created TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  )",
  "CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    item_name VARCHAR(150) NOT NULL,
    qty INT NOT NULL DEFAULT 1,
    price INT NOT NULL DEFAULT 0
  )"
];

$ok = [];
foreach ($tables as $sql) {
  preg_match('/EXISTS\s+(\w+)/', $sql, $m);
  if ($db->query($sql)) { $ok[] = $m[1] . ' ✓'; }
  else { $ok[] = $m[1] . ' ✗ ' . $db->error; }
}
echo json_encode(['ok' => true, 'tables' => $ok]);
$db->close();
