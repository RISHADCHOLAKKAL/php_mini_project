-- ╔═══════════════════════════════════════════════════════════════╗
-- ║        schema.sql — Full Database Dump (phpMyAdmin Export)   ║
-- ║                                                              ║
-- ║  PURPOSE: This is the COMPLETE database backup exported      ║
-- ║  from phpMyAdmin. It contains:                               ║
-- ║    1. Table structures (CREATE TABLE)                        ║
-- ║    2. Sample/real data (INSERT INTO)                         ║
-- ║    3. Indexes and constraints (PRIMARY KEY, FOREIGN KEY)     ║
-- ║    4. Auto-increment values                                  ║
-- ║                                                              ║
-- ║  HOW TO USE:                                                  ║
-- ║    1. Open phpMyAdmin (http://localhost/phpmyadmin)           ║
-- ║    2. Click "Import" tab at the top                          ║
-- ║    3. Choose this file (schema.sql)                          ║
-- ║    4. Click "Go" / "Import"                                  ║
-- ║                                                              ║
-- ║  OR use the migrate.php script instead (easier method).      ║
-- ╚═══════════════════════════════════════════════════════════════╝


-- -----------------------------------------------------------
-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 24, 2026 at 06:49 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12
-- -----------------------------------------------------------


-- -----------------------------------------------------------
-- CONFIGURATION: Set up the MySQL environment
-- -----------------------------------------------------------
-- These settings ensure the import works correctly
-- regardless of your MySQL server's default settings.

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
  -- NO_AUTO_VALUE_ON_ZERO: Prevents MySQL from auto-generating
  -- an ID when you explicitly insert 0 as an ID value.

START TRANSACTION;
  -- START TRANSACTION: Groups all the following queries together.
  -- If something fails, nothing gets partially saved.

SET time_zone = "+00:00";
  -- Set timezone to UTC (Coordinated Universal Time)
  -- so all timestamps are consistent.


-- Save the current character set settings so we can restore them later
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;

-- Use UTF-8 encoding (supports all languages, emojis, special characters)
/*!40101 SET NAMES utf8mb4 */;


-- ═══════════════════════════════════════════════════════════════
-- DATABASE: `ecanteen`
-- ═══════════════════════════════════════════════════════════════


-- ───────────────────────────────────────────────────────────────
-- TABLE 1: `menu_items`
-- ───────────────────────────────────────────────────────────────
-- Stores every food/drink/snack item in the canteen menu.
--
-- Columns:
--   id           → unique item number (auto-generated: 1, 2, 3...)
--   name         → item name (e.g., "Masala Tea")
--   description  → short description of the item
--   price        → price in rupees (whole number, no decimals)
--   category     → one of: 'food', 'beverage', 'snacks'
--   qty          → how many are currently in stock
--   is_available → 1 = available for ordering, 0 = sold out
--   icon         → SVG path data string (for drawing the item's icon)
-- ───────────────────────────────────────────────────────────────

CREATE TABLE `menu_items` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` varchar(255) DEFAULT '',
  `price` int(11) NOT NULL DEFAULT 0,
  `category` enum('food','beverage','snacks') NOT NULL,
  `qty` int(11) NOT NULL DEFAULT 0,
  `is_available` tinyint(1) NOT NULL DEFAULT 1,
  `icon` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- Insert sample menu items into the table
-- Format: (id, name, description, price, category, qty, is_available, icon)

INSERT INTO `menu_items` (`id`, `name`, `description`, `price`, `category`, `qty`, `is_available`, `icon`) VALUES

-- Item 1: Traditional Kanji (₹40, Food, 75 in stock, Available)
(1, 'Traditional Kanji', 'Served hot with green gram & pickle', 40, 'food', 75, 1, 'M12 2C8.14 2 5 5.14 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.86-3.14-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z'),

-- Item 2: Special Meal Thali (₹80, Food, 0 in stock, SOLD OUT)
(2, 'Special Meal Thali', 'Rice, Sambar, Aviyal, Thoran & Curd', 80, 'food', 0, 0, 'M11 9H9V2H7v7H5V2H3v7c0 2.12 1.46 3.9 3.45 4.35V22h2.1v-8.65C10.54 12.9 12 11.12 12 9V2h-1v7zm8-7h-2c-1.1 0-2 .9-2 2v6c0 1.66 1.34 3 3 3v8h2V2z'),

-- Item 3: Special Masala Tea (₹15, Beverage, 24 in stock, Available)
(3, 'Special Masala Tea', 'Brewed with fresh ginger & cardamom', 15, 'beverage', 24, 1, 'M20 3H4v10c0 2.21 1.79 4 4 4h6c2.21 0 4-1.79 4-4v-3h2c1.11 0 2-.89 2-2V5c0-1.11-.89-2-2-2zm0 5h-2V5h2v3zM4 19h16v2H4z'),

-- Item 4: Fresh Lime Juice (₹25, Beverage, 10 in stock, Available)
(4, 'Fresh Lime Juice', 'Chilled refreshing mint lime', 25, 'beverage', 10, 1, 'M12 2c-5.52 0-10 4.48-10 10s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z'),

-- Item 5: Pazham Pori (₹20, Snacks, 0 in stock, SOLD OUT)
(5, 'Pazham Pori', 'Crispy banana fritters', 20, 'snacks', 0, 0, 'M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z'),

-- Item 6: Samosa (₹20, Snacks, 0 in stock, SOLD OUT)
(6, 'Samosa', 'Spiced potato filling', 20, 'snacks', 0, 0, 'M12 2l-5.5 9h11zM12 22l5.5-9h-11z'),

-- Item 7: Usman Pacha (₹60, Beverage, 59 in stock, Available)
(7, 'usman pacha', 'nalla pacha', 60, 'beverage', 59, 1, 'M11 9H9V2H7v7H5V2H3v7c0 2.12 1.46 3.9 3.45 4.35V22h2.1v-8.65C10.54 12.9 12 11.12 12 9V2h-1v7zm8-7h-2c-1.1 0-2 .9-2 2v6c0 1.66 1.34 3 3 3v8h2V2z');


-- ───────────────────────────────────────────────────────────────
-- TABLE 2: `orders`
-- ───────────────────────────────────────────────────────────────
-- Stores one row for each order placed by a customer.
-- Each order has a unique order code (like a receipt number).
--
-- Columns:
--   id         → unique order number (auto-generated)
--   order_code → human-readable code like "ORD-1787320825"
--   order_type → type of order: "Open Order" or "Table Order"
--   table_num  → table number (NULL if it's an open order)
--   total      → total price of all items in this order (in ₹)
--   created    → date & time when the order was placed (auto-filled)
-- ───────────────────────────────────────────────────────────────

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `order_code` varchar(20) NOT NULL,
  `order_type` varchar(50) NOT NULL,
  `table_num` int(11) DEFAULT NULL,
  `total` int(11) NOT NULL DEFAULT 0,
  `created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- Insert past orders (sales history)
-- Format: (id, order_code, order_type, table_num, total, created)

INSERT INTO `orders` (`id`, `order_code`, `order_type`, `table_num`, `total`, `created`) VALUES

-- Order 1: ₹800 total, placed on Aug 21 at 2:00 PM
(1, 'ORD-1787320825', 'Open Order', NULL, 800, '2026-08-21 14:00:25'),

-- Order 2: ₹160 total, placed on Aug 21 at 2:00 PM
(2, 'ORD-1787320831', 'Open Order', NULL, 160, '2026-08-21 14:00:31'),

-- Order 3: ₹240 total, placed on Aug 21 at 2:01 PM
(3, 'ORD-1787320898', 'Open Order', NULL, 240, '2026-08-21 14:01:38'),

-- Order 4: ₹280 total, placed on Aug 21 at 2:23 PM
(4, 'ORD-1787322189', 'Open Order', NULL, 280, '2026-08-21 14:23:09'),

-- Order 5: ₹400 total, placed on Aug 21 at 2:23 PM
(5, 'ORD-1787322202', 'Open Order', NULL, 400, '2026-08-21 14:23:22'),

-- Order 6: ₹15 total (just 1 tea), placed on Aug 21 at 2:28 PM
(6, 'ORD-1787322517', 'Open Order', NULL, 15, '2026-08-21 14:28:37'),

-- Order 7: ₹60 total, placed on Aug 24 at 4:30 AM
(7, 'ORD-1787545803', 'Open Order', NULL, 60, '2026-08-24 04:30:03'),

-- Order 8: ₹80 total, placed on Aug 24 at 4:46 AM
(8, 'ORD-1787546792', 'Open Order', NULL, 80, '2026-08-24 04:46:32');


-- ───────────────────────────────────────────────────────────────
-- TABLE 3: `order_items`
-- ───────────────────────────────────────────────────────────────
-- Stores each individual item inside an order.
-- One order can have MANY items (one-to-many relationship).
--
-- Example: If Order #4 has "5x Kanji + 1x Thali",
-- that creates 2 rows in this table.
--
-- Columns:
--   id        → unique row number (auto-generated)
--   order_id  → which order this item belongs to (links to orders.id)
--   item_name → name of the food/drink ordered
--   qty       → how many of this item were ordered
--   price     → price per unit at the time of ordering
-- ───────────────────────────────────────────────────────────────

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `item_name` varchar(150) NOT NULL,
  `qty` int(11) NOT NULL,
  `price` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- Insert the items for each order
-- Format: (id, order_id, item_name, qty, price)

INSERT INTO `order_items` (`id`, `order_id`, `item_name`, `qty`, `price`) VALUES

-- Order #1 items: 20x Traditional Kanji @ ₹40 each = ₹800
(1, 1, 'Traditional Kanji', 20, 40),

-- Order #2 items: 2x Special Meal Thali @ ₹80 each = ₹160
(2, 2, 'Special Meal Thali', 2, 80),

-- Order #3 items: 12x Samosa @ ₹20 each = ₹240
(3, 3, 'Samosa', 12, 20),

-- Order #4 items: 5x Kanji (₹200) + 1x Thali (₹80) = ₹280
(4, 4, 'Traditional Kanji', 5, 40),
(5, 4, 'Special Meal Thali', 1, 80),

-- Order #5 items: 5x Special Meal Thali @ ₹80 each = ₹400
(6, 5, 'Special Meal Thali', 5, 80),

-- Order #6 items: 1x Special Masala Tea @ ₹15 = ₹15
(7, 6, 'Special Masala Tea', 1, 15),

-- Order #7 items: 1x Usman Pacha @ ₹60 = ₹60
(8, 7, 'usman pacha', 1, 60),

-- Order #8 items: 1x Special Meal Thali @ ₹80 = ₹80
(9, 8, 'Special Meal Thali', 1, 80);


-- ───────────────────────────────────────────────────────────────
-- TABLE 4: `users`
-- ───────────────────────────────────────────────────────────────
-- Stores all user accounts (students and admins).
--
-- Columns:
--   id       → unique user number (auto-generated)
--   email    → user's email address (must be unique)
--   username → login username (must be unique)
--   password → hashed (encrypted) password
--              (the long "$2y$10$..." string is the encrypted version)
--   role     → 'student' or 'admin'
--   created  → when the account was created (auto-filled)
-- ───────────────────────────────────────────────────────────────

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('student','admin') DEFAULT 'student',
  `created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- Insert user accounts
-- Format: (id, email, username, password_hash, role, created)
-- NOTE: Passwords are HASHED — you can't read the original password
--       from the hash. The hashes below correspond to:
--         User "hello" → original password unknown (set during signup)
--         User "admin" → original password is "admin123"

INSERT INTO `users` (`id`, `email`, `username`, `password`, `role`, `created`) VALUES

-- Student account
(3, 'diyaf848@gmail.com', 'hello', '$2y$10$YR3h5phEkZFJ9Pd49uL4CuCK8Vd3K6Gh734juSOBYiLpPLTmovXBC', 'student', '2026-08-24 04:29:29'),

-- Admin account (password: admin123)
(4, 'admin@ecanteen.com', 'admin', '$2y$10$KC/D3h67.R6v0xwpttINY.O5F7ZxggdUK9OpbDbhSe2rL/EpY5Seq', 'admin', '2026-08-24 04:39:36');


-- ═══════════════════════════════════════════════════════════════
-- INDEXES (for faster searching)
-- ═══════════════════════════════════════════════════════════════
-- An INDEX is like a book's table of contents — it helps MySQL
-- find rows much faster. PRIMARY KEY is the main unique identifier.
-- UNIQUE KEY prevents duplicate values in a column.

-- menu_items: id is the primary key
ALTER TABLE `menu_items`
  ADD PRIMARY KEY (`id`);

-- orders: id is the primary key
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

-- order_items: id is the primary key, order_id is indexed for fast lookups
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);
  -- KEY `order_id` creates an index on the order_id column
  -- This makes queries like "SELECT * FROM order_items WHERE order_id = 5"
  -- much faster (especially when there are thousands of rows)

-- users: id is primary key, email and username must be unique
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`);
  -- UNIQUE KEY ensures no two users can have the same email or username


-- ═══════════════════════════════════════════════════════════════
-- AUTO_INCREMENT VALUES
-- ═══════════════════════════════════════════════════════════════
-- AUTO_INCREMENT tells MySQL what number to use for the NEXT new row.
-- For example, if AUTO_INCREMENT=8 for menu_items, the next item
-- you insert will get id = 8.

ALTER TABLE `menu_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
  -- Next menu item will get id = 8

ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
  -- Next order will get id = 9

ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
  -- Next order item will get id = 10

ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
  -- Next user will get id = 5


-- ═══════════════════════════════════════════════════════════════
-- FOREIGN KEY CONSTRAINTS
-- ═══════════════════════════════════════════════════════════════
-- A FOREIGN KEY creates a LINK between two tables.
-- Here, order_items.order_id → orders.id
--
-- This means:
--   1. You can't insert an order_item with an order_id that
--      doesn't exist in the orders table.
--   2. ON DELETE CASCADE: If you delete an order, ALL its
--      order_items are automatically deleted too.
--      (No orphan items left behind!)

ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

-- Finalize the transaction — save all changes
COMMIT;


-- Restore the original character set settings
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
