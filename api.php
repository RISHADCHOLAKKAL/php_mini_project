<?php
// ╔═══════════════════════════════════════════════════════════════╗
// ║              api.php — The Backend API                       ║
// ║                                                              ║
// ║  PURPOSE: This is the MAIN backend file. It handles ALL      ║
// ║  data operations for the e-canteen app.                      ║
// ║                                                              ║
// ║  HOW IT WORKS:                                               ║
// ║  The frontend (HTML pages) send requests to this file        ║
// ║  with an "action" parameter. Based on the action, this       ║
// ║  file does different things:                                 ║
// ║                                                              ║
// ║  ACTIONS:                                                    ║
// ║    "menu"        → Returns all menu items                    ║
// ║    "sales"       → Returns all past orders                  ║
// ║    "signup"      → Creates a new student account             ║
// ║    "login"       → Checks username & password                ║
// ║    "save_item"   → Adds or updates a menu item               ║
// ║    "record_sale" → Records a new order & reduces stock       ║
// ╚═══════════════════════════════════════════════════════════════╝


// ─── STEP 1: SETUP ─────────────────────────────────────────────

// Load the database connection from config.php
// After this line, the $db variable is available for database queries.
require 'config.php';

// Tell the browser: "The response from this file is JSON, not HTML."
// JSON = JavaScript Object Notation (a data format like: {"key": "value"})
header('Content-Type: application/json');

// Check if the database connection failed
// $db->connect_error is NULL if connection is OK, or an error message if it failed.
if ($db->connect_error) {
    // Send an error message as JSON and stop the script
    echo json_encode(['error' => 'db fail']);
    exit;    // "exit" = stop running PHP, nothing after this line executes
}


// ─── STEP 2: DEFINE THE reply() HELPER FUNCTION ────────────────

// This function sends a JSON response and STOPS the script.
// We use it throughout the file to send data back to the browser.
//
// EXAMPLE USAGE:
//   reply(['ok' => true]);
//   → This sends: {"ok": true} to the browser
//
//   reply(['error' => 'Something went wrong']);
//   → This sends: {"error": "Something went wrong"} to the browser
//
// PARAMETERS:
//   $data = a PHP array that gets converted to JSON
function reply($data)
{
    echo json_encode($data);   // convert PHP array → JSON string and print it
    exit;                      // stop the script (no more code runs after this)
}


// ─── STEP 3: READ THE INCOMING REQUEST ─────────────────────────

// The frontend can send data in TWO ways:
//
// WAY 1: POST request with JSON body
//   → Used by: signup, login, save_item, record_sale
//   → The data is in the "body" of the HTTP request
//   → php://input reads the raw body text
//   → json_decode() converts JSON text → PHP array
//
// WAY 2: GET request with URL parameters
//   → Used by: menu, sales
//   → The action is in the URL like: api.php?action=menu
//   → $_GET['action'] reads it from the URL

// Read the JSON body (if any)
// file_get_contents('php://input') reads the raw POST body
// json_decode(..., true) converts it to a PHP associative array
// ?? [] means: if there's no body, use an empty array instead
$input  = json_decode(file_get_contents('php://input'), true) ?? [];

// Determine which action to perform
// First try the JSON body, then fall back to the URL parameter
// ?? means "if the left side is null, use the right side"
$action = $input['action'] ?? $_GET['action'] ?? '';


// ─── STEP 4: HANDLE EACH ACTION ───────────────────────────────

// switch() is like a series of if/else statements.
// It checks $action and jumps to the matching "case".
switch ($action) {


    // ═══════════════════════════════════════════════════════════
    // ACTION: "menu" — Get All Menu Items
    // ═══════════════════════════════════════════════════════════
    // Called by: menu.html, open-order.html, staff.html
    // Method:    GET request to api.php?action=menu
    // Returns:   Array of all items from the menu_items table
    //
    // The SQL query uses "AS" to rename columns for the frontend:
    //   description → desc         (shorter name)
    //   is_available → isAvailable  (JavaScript-style naming)
    // ───────────────────────────────────────────────────────────
    case 'menu':

        // Run a SQL SELECT query to get all menu items
        $result = $db->query("SELECT id, name, description AS `desc`, price,
                                     category, qty, is_available AS isAvailable,
                                     icon
                              FROM menu_items");

        // fetch_all(MYSQLI_ASSOC) converts the result into an array of arrays
        // MYSQLI_ASSOC means: use column names as keys (not numbers)
        // Example result: [{"id":1, "name":"Tea", "price":15}, ...]
        $menuItems = $result->fetch_all(MYSQLI_ASSOC);

        // Send the menu items back to the browser as JSON
        reply($menuItems);
        break;   // "break" exits the switch — prevents falling through to next case


    // ═══════════════════════════════════════════════════════════
    // ACTION: "sales" — Get Sales History
    // ═══════════════════════════════════════════════════════════
    // Called by: sales-report.html
    // Method:    GET request to api.php?action=sales
    // Returns:   Array of all orders, each with its items included
    //
    // This does TWO queries:
    //   1. Get all orders from the "orders" table
    //   2. For each order, get its items from "order_items" table
    // ───────────────────────────────────────────────────────────
    case 'sales':

        // Query 1: Get all orders, newest first (ORDER BY id DESC)
        $result = $db->query("SELECT id, order_code, created AS date,
                                     order_type AS type,
                                     total
                              FROM orders
                              ORDER BY id DESC");

        // Convert to PHP array
        $orders = $result->fetch_all(MYSQLI_ASSOC);

        // Query 2: For each order, fetch its individual items
        // The & before $order means "pass by reference"
        // This lets us MODIFY the $order variable directly
        // (without &, we'd be modifying a copy and changes would be lost)
        foreach ($orders as &$order) {
            $orderId = $order['id'];

            // Get all items that belong to this specific order
            $itemsResult = $db->query("SELECT item_name AS name, qty, price
                                       FROM order_items
                                       WHERE order_id = $orderId");

            // Attach the items array to the order object
            // Now each order looks like: { id, order_code, ..., items: [...] }
            $order['items'] = $itemsResult->fetch_all(MYSQLI_ASSOC);
        }

        // Send all orders (with their items) back to the browser
        reply($orders);
        break;


    // ═══════════════════════════════════════════════════════════
    // ACTION: "signup" — Create a New Student Account
    // ═══════════════════════════════════════════════════════════
    // Called by: index.html (signup form)
    // Method:    POST request with { action, email, username, password }
    // Returns:   { ok: true, role: "student" } on success
    //            { error: "..." } on failure
    // ───────────────────────────────────────────────────────────
    case 'signup':

        // Get the username and email from the request, trimming whitespace
        // trim() removes spaces from the beginning and end of a string
        // ?? '' means: if the value is missing, use an empty string
        $username = trim($input['username'] ?? '');
        $email    = trim($input['email']    ?? '');

        // ── Check if the username or email already exists ──
        // We use a "prepared statement" with ? placeholders
        // This prevents SQL injection (a common security attack)
        $check = $db->prepare("SELECT username FROM users WHERE username=? OR email=?");

        // bind_param('ss', ...) replaces the ? placeholders with actual values
        // 'ss' means: both parameters are strings (s = string)
        $check->bind_param('ss', $username, $email);

        // Run the query
        $check->execute();

        // Get the result — if a matching user exists, $existing will be an array
        // If no match, $existing will be NULL
        $existing = $check->get_result()->fetch_assoc();

        if ($existing) {
            // A user with this username or email already exists!
            reply(['error' => 'Username or Email already exists']);
        }

        // ── Create the new user ──
        // Hash (encrypt) the password before storing it
        // password_hash() creates a one-way encrypted version of the password
        // Even if someone steals the database, they can't see the real passwords
        $hashedPassword = password_hash($input['password'] ?? '', PASSWORD_DEFAULT);

        // Insert the new user into the "users" table
        // The role is hardcoded as 'student' — admins are created via schema.sql
        $stmt = $db->prepare("INSERT INTO users (email, username, password, role)
                              VALUES (?, ?, ?, 'student')");
        $stmt->bind_param('sss', $email, $username, $hashedPassword);
        $stmt->execute();

        // Send success response
        reply(['ok' => true, 'role' => 'student']);
        break;


    // ═══════════════════════════════════════════════════════════
    // ACTION: "login" — Authenticate a User
    // ═══════════════════════════════════════════════════════════
    // Called by: signin.html, index.html (admin login)
    // Method:    POST request with { action, username, password }
    // Returns:   { ok: true, role: "student"/"admin", username: "..." }
    //            { error: "Incorrect username or password" }
    // ───────────────────────────────────────────────────────────
    case 'login':

        // Look up the user by username
        $stmt = $db->prepare("SELECT username, password, role FROM users WHERE username=?");
        $stmt->bind_param('s', $input['username']);    // 's' = string parameter
        $stmt->execute();

        // Get the user's row from the database
        // $user will be NULL if no user was found with that username
        $user = $stmt->get_result()->fetch_assoc();

        // Check TWO things:
        //   1. Does the user exist? (!$user means "user not found")
        //   2. Does the password match? password_verify() checks the hash
        if (!$user || !password_verify($input['password'] ?? '', $user['password'])) {
            // Either username doesn't exist OR password is wrong
            // We use the same error message for both (for security — don't reveal which one)
            reply(['error' => 'Incorrect username or password']);
        }

        // Login successful! Send back the user's info
        reply([
            'ok'       => true,
            'role'     => $user['role'],       // "student" or "admin"
            'username' => $user['username']
        ]);
        break;


    // ═══════════════════════════════════════════════════════════
    // ACTION: "save_item" — Add or Update a Menu Item
    // ═══════════════════════════════════════════════════════════
    // Called by: staff.html (admin stock management)
    // Method:    POST request with { action, item: { name, desc, price, ... } }
    // Returns:   { ok: true }
    //
    // If item.id exists → UPDATE the existing item
    // If item.id is empty → INSERT a brand new item
    // ───────────────────────────────────────────────────────────
    case 'save_item':

        // Get the item data from the request
        $item = $input['item'];

        // Check if this is an UPDATE (existing item) or INSERT (new item)
        if (!empty($item['id'])) {

            // ── UPDATE: Modify an existing menu item ──
            // The WHERE id=? ensures we only update the correct item
            $stmt = $db->prepare("UPDATE menu_items
                                  SET name=?, description=?, price=?, category=?,
                                      qty=?, is_available=?
                                  WHERE id=?");

            // bind_param type codes:
            //   s = string (name)
            //   s = string (description)
            //   i = integer (price)
            //   s = string (category)
            //   i = integer (qty)
            //   i = integer (is_available: 0 or 1)
            //   i = integer (id)
            $stmt->bind_param('ssisiii',
                $item['name'],
                $item['desc'],
                $item['price'],
                $item['category'],
                $item['qty'],
                $item['isAvailable'],
                $item['id']
            );

        } else {

            // ── INSERT: Add a brand new menu item ──
            $icon = $item['icon'] ?? '';    // icon is optional, default to empty string

            $stmt = $db->prepare("INSERT INTO menu_items
                                  (name, description, price, category, qty, is_available, icon)
                                  VALUES (?, ?, ?, ?, ?, ?, ?)");

            // bind_param type codes for new item:
            //   s = string (name)
            //   s = string (description)
            //   i = integer (price)
            //   s = string (category)
            //   s = string (qty) — note: 's' works for numbers too when coming from JSON
            //   i = integer (is_available)
            //   s = string (icon — SVG path data)
            $stmt->bind_param('ssissis',
                $item['name'],
                $item['desc'],
                $item['price'],
                $item['category'],
                $item['qty'],
                $item['isAvailable'],
                $icon
            );
        }

        // Run the INSERT or UPDATE query
        $stmt->execute();

        // Send success response
        reply(['ok' => true]);
        break;


    // ═══════════════════════════════════════════════════════════
    // ACTION: "record_sale" — Place a New Order
    // ═══════════════════════════════════════════════════════════
    // Called by: open-order.html (when user clicks "Place Order")
    // Method:    POST request with { action, order: { type, table, total, items } }
    // Returns:   { ok: true }
    //
    // This action does THREE things:
    //   1. Creates a new row in the "orders" table
    //   2. Creates rows in "order_items" for each item
    //   3. REDUCES the stock quantity in "menu_items"
    // ───────────────────────────────────────────────────────────
    case 'record_sale':

        // Get the order data from the request
        $order = $input['order'];

        // Generate a unique order code using the current timestamp
        // time() returns the number of seconds since Jan 1, 1970
        // Example: "ORD-1693456789"
        $orderCode = 'ORD-' . time();

        // ── Step A: Insert the order header ──
        $stmt = $db->prepare("INSERT INTO orders (order_code, order_type, table_num, total)
                              VALUES (?, 'Open Order', 0, ?)");
        $stmt->bind_param('si',
            $orderCode,            // s = string (order code)
            $order['total']        // i = integer (total price)
        );
        $stmt->execute();

        // Get the auto-generated ID of the order we just inserted
        // This ID links the order to its items in the order_items table
        $orderId = $db->insert_id;

        // ── Step B: Insert each ordered item and reduce stock ──
        foreach ($order['items'] as $item) {

            // Insert one row into the order_items table
            $itemStmt = $db->prepare("INSERT INTO order_items (order_id, item_name, qty, price)
                                      VALUES (?, ?, ?, ?)");
            $itemStmt->bind_param('isii',
                $orderId,          // i = integer (links to orders table)
                $item['name'],     // s = string (item name)
                $item['qty'],      // i = integer (quantity ordered)
                $item['price']     // i = integer (price per unit)
            );
            $itemStmt->execute();

            // ── Step C: Reduce the stock in menu_items ──
            // real_escape_string() prevents SQL injection in the item name
            $safeName = $db->real_escape_string($item['name']);

            // (int) converts the value to an integer for safety
            $qty = (int) $item['qty'];

            // GREATEST(0, qty - $qty) → ensures stock never goes below 0
            //   Example: if stock is 5 and they order 3 → 5 - 3 = 2
            //   Example: if stock is 2 and they order 5 → GREATEST(0, -3) = 0
            //
            // IF(qty > $qty, 1, 0) → set is_available:
            //   If remaining stock > ordered qty → still available (1)
            //   Otherwise → mark as sold out (0)
            $db->query("UPDATE menu_items
                        SET qty = GREATEST(0, qty - $qty),
                            is_available = IF(qty > $qty, 1, 0)
                        WHERE name = '$safeName'");
        }

        // Send success response
        reply(['ok' => true]);
        break;


    // ═══════════════════════════════════════════════════════════
    // DEFAULT: Unknown Action
    // ═══════════════════════════════════════════════════════════
    // If the action doesn't match any of the cases above,
    // send an error message.
    // ───────────────────────────────────────────────────────────
    default:
        reply(['error' => 'invalid action']);
        break;
}
