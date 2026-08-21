-- ecanteen database schema

CREATE DATABASE IF NOT EXISTS ecanteen;
USE ecanteen;

-- users
CREATE TABLE users (
  id        INT AUTO_INCREMENT PRIMARY KEY,
  email     VARCHAR(255) NOT NULL UNIQUE,
  username  VARCHAR(100) NOT NULL UNIQUE,
  password  VARCHAR(255) NOT NULL,
  role      ENUM('student','admin') DEFAULT 'student',
  created   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- menu items
CREATE TABLE menu_items (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  name         VARCHAR(150) NOT NULL,
  description  VARCHAR(255) DEFAULT '',
  price        INT NOT NULL DEFAULT 0,
  category     ENUM('food','beverage','snacks') NOT NULL,
  qty          INT NOT NULL DEFAULT 0,
  is_available TINYINT(1) NOT NULL DEFAULT 1,
  icon         TEXT
);

-- orders
CREATE TABLE orders (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  order_code VARCHAR(20) NOT NULL,
  order_type VARCHAR(50) NOT NULL,
  table_num  INT DEFAULT NULL,
  total      INT NOT NULL DEFAULT 0,
  created    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- order line items
CREATE TABLE order_items (
  id        INT AUTO_INCREMENT PRIMARY KEY,
  order_id  INT NOT NULL,
  item_name VARCHAR(150) NOT NULL,
  qty       INT NOT NULL,
  price     INT NOT NULL,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

-- seed menu
INSERT INTO menu_items (name, description, price, category, qty, is_available, icon) VALUES
('Traditional Kanji',  'Served hot with green gram & pickle',     40, 'food',     15, 1, 'M12 2C8.14 2 5 5.14 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.86-3.14-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z'),
('Special Meal Thali', 'Rice, Sambar, Aviyal, Thoran & Curd',     80, 'food',      8, 1, 'M11 9H9V2H7v7H5V2H3v7c0 2.12 1.46 3.9 3.45 4.35V22h2.1v-8.65C10.54 12.9 12 11.12 12 9V2h-1v7zm8-7h-2c-1.1 0-2 .9-2 2v6c0 1.66 1.34 3 3 3v8h2V2z'),
('Special Masala Tea',  'Brewed with fresh ginger & cardamom',    15, 'beverage', 25, 1, 'M20 3H4v10c0 2.21 1.79 4 4 4h6c2.21 0 4-1.79 4-4v-3h2c1.11 0 2-.89 2-2V5c0-1.11-.89-2-2-2zm0 5h-2V5h2v3zM4 19h16v2H4z'),
('Fresh Lime Juice',    'Chilled refreshing mint lime',            25, 'beverage', 10, 1, 'M12 2c-5.52 0-10 4.48-10 10s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z'),
('Pazham Pori',         'Crispy banana fritters',                  20, 'snacks',    0, 0, 'M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z'),
('Samosa',              'Spiced potato filling',                   20, 'snacks',   12, 1, 'M12 2l-5.5 9h11zM12 22l5.5-9h-11z');

-- seed admin account (password: admin123)
INSERT INTO users (email, username, password, role) VALUES
('admin@kanjipura.com', 'admin', '$2y$10$EIXe1GKl8hq7F8hVzFz5aOQFJf3e9cZ2h3pUz5q1oWj8Q6f1t8hLK', 'admin');
