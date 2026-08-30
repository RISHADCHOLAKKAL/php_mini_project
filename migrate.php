<?php
// ╔═══════════════════════════════════════════════════════════════╗
// ║           migrate.php — Create Database Tables               ║
// ║                                                              ║
// ║  PURPOSE: This script creates all the database tables        ║
// ║  needed for the e-canteen app. Run this ONCE by visiting:    ║
// ║  http://localhost/ecanteen/migrate.php in your browser.      ║
// ║                                                              ║
// ║  It creates 4 tables: users, menu_items, orders, order_items ║
// ║  If a table already exists, it is NOT overwritten (safe to   ║
// ║  run multiple times).                                        ║
// ╚═══════════════════════════════════════════════════════════════╝

// -----------------------------------------------------------
// STEP 1: Connect to the database
// -----------------------------------------------------------
require 'config.php';                        // gives us the $db variable
header('Content-Type: application/json');     // response will be JSON

// Check if connection failed
if ($db->connect_error) {
    echo json_encode(['error' => $db->connect_error]);
    exit;
}

// -----------------------------------------------------------
// STEP 2: Create the database (only on localhost / XAMPP)
// -----------------------------------------------------------
// On a live server, the database is pre-created by the hosting company.
// On XAMPP (your computer), we need to create it ourselves.
if ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['HTTP_HOST'] === '127.0.0.1') {
    $db->query("CREATE DATABASE IF NOT EXISTS ecanteen");
    $db->select_db("ecanteen");   // switch to the ecanteen database
}

// -----------------------------------------------------------
// STEP 3: Define all the tables we need
// -----------------------------------------------------------
// Each string in this array is a SQL command to create a table.
// "IF NOT EXISTS" means: only create if it doesn't already exist.

$tables = [

    // ─── TABLE 1: users ─────────────────────────────────────
    // Stores all user accounts (students and admins).
    // Columns:
    //   id         → unique number for each user (auto-increments: 1, 2, 3...)
    //   email      → user's email (must be unique — no duplicates allowed)
    //   username   → user's login name (must be unique)
    //   password   → hashed (encrypted) password
    //   role       → either 'student' or 'admin'
    //   created_at → date & time the account was created (auto-filled)
    "CREATE TABLE IF NOT EXISTS users (
        id         INT AUTO_INCREMENT PRIMARY KEY,
        email      VARCHAR(255) NOT NULL UNIQUE,
        username   VARCHAR(100) NOT NULL UNIQUE,
        password   VARCHAR(255) NOT NULL,
        role       ENUM('student','admin') NOT NULL DEFAULT 'student',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",

    // ─── TABLE 2: menu_items ────────────────────────────────
    // Stores every food/drink/snack item in the canteen menu.
    // Columns:
    //   id           → unique item number
    //   name         → item name (e.g., "Masala Tea")
    //   description  → short description
    //   price        → price in rupees (stored as whole number)
    //   category     → 'food', 'beverage', or 'snacks'
    //   qty          → how many are in stock right now
    //   is_available → 1 = available, 0 = sold out
    //   icon         → SVG path data for the item's icon
    "CREATE TABLE IF NOT EXISTS menu_items (
        id           INT AUTO_INCREMENT PRIMARY KEY,
        name         VARCHAR(150) NOT NULL,
        description  VARCHAR(255) DEFAULT '',
        price        INT NOT NULL DEFAULT 0,
        category     ENUM('food','beverage','snacks') NOT NULL,
        qty          INT NOT NULL DEFAULT 0,
        is_available TINYINT(1) NOT NULL DEFAULT 1,
        icon         TEXT
    )",

    // ─── TABLE 3: orders ────────────────────────────────────
    // Stores each order's header (one row per order).
    // Columns:
    //   id         → unique order number
    //   order_code → human-readable code like "ORD-1693456789"
    //   order_type → "Open Order" or "Table Order"
    //   table_num  → table number (0 if it's an open order)
    //   total      → total price of the entire order
    //   created    → when the order was placed (auto-filled)
    "CREATE TABLE IF NOT EXISTS orders (
        id         INT AUTO_INCREMENT PRIMARY KEY,
        order_code VARCHAR(20) NOT NULL,
        order_type VARCHAR(50) NOT NULL,
        table_num  INT DEFAULT 0,
        total      INT NOT NULL DEFAULT 0,
        created    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",

    // ─── TABLE 4: order_items ───────────────────────────────
    // Stores each individual item inside an order.
    // Example: If an order has "2x Tea + 1x Samosa", that's 2 rows here.
    // Columns:
    //   id        → unique row number
    //   order_id  → links back to the 'orders' table (which order this belongs to)
    //   item_name → name of the menu item
    //   qty       → how many of this item were ordered
    //   price     → price per unit at the time of ordering
    "CREATE TABLE IF NOT EXISTS order_items (
        id        INT AUTO_INCREMENT PRIMARY KEY,
        order_id  INT NOT NULL,
        item_name VARCHAR(150) NOT NULL,
        qty       INT NOT NULL DEFAULT 1,
        price     INT NOT NULL DEFAULT 0
    )"
];

// -----------------------------------------------------------
// STEP 4: Run each CREATE TABLE query
// -----------------------------------------------------------
$results = [];   // we'll collect the result of each table creation

foreach ($tables as $sql) {

    // Extract the table name from the SQL using a regular expression.
    // preg_match looks for the word right after "EXISTS " in the SQL string.
    // Example: "CREATE TABLE IF NOT EXISTS users (" → captures "users"
    preg_match('/EXISTS\s+(\w+)/', $sql, $matches);
    $tableName = $matches[1];   // e.g., "users", "menu_items", etc.

    // Try to run the SQL query
    if ($db->query($sql)) {
        // ✓ means success
        $results[] = $tableName . ' ✓';
    } else {
        // ✗ means failure — show the error
        $results[] = $tableName . ' ✗ ' . $db->error;
    }
}

// -----------------------------------------------------------
// STEP 5: Send the results back as JSON
// -----------------------------------------------------------
echo json_encode([
    'ok'     => true,
    'tables' => $results   // e.g., ["users ✓", "menu_items ✓", ...]
]);

// -----------------------------------------------------------
// STEP 6: Close the database connection
// -----------------------------------------------------------
$db->close();
