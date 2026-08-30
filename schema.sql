-- ╔═══════════════════════════════════════════════════════════════╗
-- ║              schema.sql — Database Schema + Seed Data        ║
-- ║                                                              ║
-- ║  PURPOSE: This file defines the database structure and       ║
-- ║  inserts some sample menu items. You can run this manually   ║
-- ║  in phpMyAdmin (XAMPP → MySQL → Import this file).           ║
-- ║                                                              ║
-- ║  NOTE: The migrate.php script does the same thing            ║
-- ║  automatically — you don't need BOTH. This file is just      ║
-- ║  a backup / reference.                                       ║
-- ╚═══════════════════════════════════════════════════════════════╝


-- -----------------------------------------------------------
-- STEP 1: Create the database (if it doesn't exist yet)
-- -----------------------------------------------------------
CREATE DATABASE IF NOT EXISTS ecanteen;

-- Switch to using the ecanteen database for all following commands
USE ecanteen;


-- -----------------------------------------------------------
-- STEP 2: Create the menu_items table
-- -----------------------------------------------------------
-- This table stores every food/drink/snack available in the canteen.
--
-- Column explanations:
--   id           = unique number, auto-assigned (1, 2, 3...)
--   name         = item name, e.g., "Masala Tea"
--   description  = short description of the item
--   price        = price in rupees (whole number, no decimals)
--   category     = one of: 'food', 'beverage', 'snacks'
--   qty          = current stock quantity
--   is_available = 1 means available, 0 means sold out
--   icon         = SVG path data string (for displaying an icon)

CREATE TABLE IF NOT EXISTS menu_items (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  name         VARCHAR(150) NOT NULL,
  description  VARCHAR(255) DEFAULT '',
  price        INT NOT NULL DEFAULT 0,
  category     ENUM('food','beverage','snacks') NOT NULL,
  qty          INT NOT NULL DEFAULT 0,
  is_available TINYINT(1) NOT NULL DEFAULT 1,
  icon         TEXT
);


-- -----------------------------------------------------------
-- STEP 3: Create the orders table
-- -----------------------------------------------------------
-- This table stores one row for each order placed.
--
-- Column explanations:
--   id      = unique order number
--   code    = human-readable order code like "ORD-1693456789"
--   type    = order type: "Open Order" or "Table Order"
--   total   = total price of all items in this order
--   items   = JSON string listing all items (legacy format)
--   created = date & time the order was placed (auto-filled)

CREATE TABLE IF NOT EXISTS orders (
  id      INT AUTO_INCREMENT PRIMARY KEY,
  code    VARCHAR(20) NOT NULL,
  type    VARCHAR(50) NOT NULL,
  total   INT NOT NULL DEFAULT 0,
  items   TEXT NOT NULL,                       -- stores JSON array of line items
  created TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- -----------------------------------------------------------
-- STEP 4: Insert sample menu items (seed data)
-- -----------------------------------------------------------
-- These are some example items so the menu isn't empty
-- when you first set up the project.
--
-- Format: (name, description, price, category, qty, is_available, icon)
-- The "icon" column stores SVG path data — these are the shapes
-- used to draw small icons on the menu page.

INSERT INTO menu_items (name, description, price, category, qty, is_available, icon) VALUES

-- Item 1: Traditional Kanji (₹40, Food, 15 in stock)
('Traditional Kanji', 'Served hot with green gram & pickle', 40, 'food', 15, 1, 'M12 2C8.14 2 5 5.14 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.86-3.14-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z'),

-- Item 2: Special Meal Thali (₹80, Food, 8 in stock)
('Special Meal Thali', 'Rice, Sambar, Aviyal, Thoran & Curd', 80, 'food', 8, 1, 'M11 9H9V2H7v7H5V2H3v7c0 2.12 1.46 3.9 3.45 4.35V22h2.1v-8.65C10.54 12.9 12 11.12 12 9V2h-1v7zm8-7h-2c-1.1 0-2 .9-2 2v6c0 1.66 1.34 3 3 3v8h2V2z'),

-- Item 3: Special Masala Tea (₹15, Beverage, 25 in stock)
('Special Masala Tea', 'Brewed with fresh ginger & cardamom', 15, 'beverage', 25, 1, 'M20 3H4v10c0 2.21 1.79 4 4 4h6c2.21 0 4-1.79 4-4v-3h2c1.11 0 2-.89 2-2V5c0-1.11-.89-2-2-2zm0 5h-2V5h2v3zM4 19h16v2H4z'),

-- Item 4: Fresh Lime Juice (₹25, Beverage, 10 in stock)
('Fresh Lime Juice', 'Chilled refreshing mint lime', 25, 'beverage', 10, 1, 'M12 2c-5.52 0-10 4.48-10 10s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z'),

-- Item 5: Pazham Pori (₹20, Snacks, 0 in stock = SOLD OUT)
('Pazham Pori', 'Crispy banana fritters', 20, 'snacks', 0, 0, 'M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z'),

-- Item 6: Samosa (₹20, Snacks, 12 in stock)
('Samosa', 'Spiced potato filling', 20, 'snacks', 12, 1, 'M12 2l-5.5 9h11zM12 22l5.5-9h-11z');
