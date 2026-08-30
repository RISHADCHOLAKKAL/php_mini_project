<?php
// ╔═══════════════════════════════════════════════════════════════╗
// ║            create_admin.php — Create Admin Account           ║
// ║                                                              ║
// ║  PURPOSE: This script creates (or resets) the admin user.    ║
// ║  Run this ONCE by visiting: http://localhost/ecanteen/       ║
// ║  create_admin.php in your browser.                           ║
// ║                                                              ║
// ║  After running, you can login with:                          ║
// ║    Username: admin                                           ║
// ║    Password: admin123                                        ║
// ╚═══════════════════════════════════════════════════════════════╝

// -----------------------------------------------------------
// STEP 1: Connect to the database
// -----------------------------------------------------------
// "require" loads the config.php file, which gives us the $db variable.
require 'config.php';

// Tell the browser that our response will be in JSON format.
header('Content-Type: application/json');

// -----------------------------------------------------------
// STEP 2: Check if database connection worked
// -----------------------------------------------------------
// $db->connect_error is NULL if connection is OK,
// or contains an error message if it failed.
if ($db->connect_error) {
    // Send the error as JSON and stop the script
    echo json_encode(['error' => $db->connect_error]);
    exit;   // "exit" stops PHP from running any more code
}

// -----------------------------------------------------------
// STEP 3: Delete the old admin (if one exists)
// -----------------------------------------------------------
// This ensures we start fresh — no duplicate admin accounts.
$db->query("DELETE FROM users WHERE username='admin'");

// -----------------------------------------------------------
// STEP 4: Hash (encrypt) the password
// -----------------------------------------------------------
// NEVER store passwords as plain text!
// password_hash() turns "admin123" into a long random-looking string
// that can't be reversed. Example: "$2y$10$abcdef..."
// When the admin logs in later, password_verify() checks the match.
$hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);

// -----------------------------------------------------------
// STEP 5: Prepare the admin account details
// -----------------------------------------------------------
$email    = 'admin@ecanteen.com';   // admin's email address
$username = 'admin';                // admin's login username
$role     = 'admin';                // role = "admin" (not "student")

// -----------------------------------------------------------
// STEP 6: Insert the new admin into the database
// -----------------------------------------------------------
// We use a "prepared statement" with ? placeholders.
// This protects against SQL injection (a common hack).
$stmt = $db->prepare("INSERT INTO users (email, username, password, role)
                      VALUES (?, ?, ?, ?)");

// bind_param('ssss', ...) tells MySQL:
//   s = string → email is a string
//   s = string → username is a string
//   s = string → hashedPassword is a string
//   s = string → role is a string
$stmt->bind_param('ssss', $email, $username, $hashedPassword, $role);

// -----------------------------------------------------------
// STEP 7: Run the query and show the result
// -----------------------------------------------------------
// $stmt->execute() returns TRUE if it worked, FALSE if it failed.
if ($stmt->execute()) {
    // SUCCESS — admin was created
    echo json_encode([
        'ok'  => true,
        'msg' => 'Admin created! Username: admin | Password: admin123'
    ]);
} else {
    // FAILED — show what went wrong
    echo json_encode([
        'error' => $stmt->error
    ]);
}

// -----------------------------------------------------------
// STEP 8: Close the database connection (good practice)
// -----------------------------------------------------------
$db->close();
