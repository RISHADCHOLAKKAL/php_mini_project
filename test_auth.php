<?php
// ╔═══════════════════════════════════════════════════════════════╗
// ║        test_auth.php — Test Signup & Login (Debug Tool)      ║
// ║                                                              ║
// ║  PURPOSE: This script tests if signup and login work         ║
// ║  correctly. Visit http://localhost/ecanteen/test_auth.php     ║
// ║  to see the test results as an HTML page.                    ║
// ║                                                              ║
// ║  It does these tests:                                        ║
// ║    1. Check database connection                              ║
// ║    2. Check if the "users" table exists                      ║
// ║    3. Create a temporary test user                           ║
// ║    4. Try to log in with that test user                      ║
// ║    5. Delete the test user (clean up)                        ║
// ║    6. Show all registered users in the database              ║
// ╚═══════════════════════════════════════════════════════════════╝

// -----------------------------------------------------------
// STEP 1: Connect to the database
// -----------------------------------------------------------
require 'config.php';

// This time we return HTML (not JSON), because we want to show
// a readable test page with colors and checkmarks.
header('Content-Type: text/html');

// -----------------------------------------------------------
// STEP 2: Test database connection
// -----------------------------------------------------------
echo "<h3>DB Connection: ";
if ($db->connect_error) {
    echo "FAILED - " . $db->connect_error;
    exit;   // stop here — no point testing further
}
echo "OK</h3>";

// -----------------------------------------------------------
// STEP 3: Check if the 'users' table exists
// -----------------------------------------------------------
// SHOW TABLES LIKE 'users' returns 1 row if the table exists,
// or 0 rows if it doesn't.
$result = $db->query("SHOW TABLES LIKE 'users'");
$tableExists = $result->num_rows > 0;   // true or false
echo "<p>Users table exists: " . ($tableExists ? 'YES' : 'NO - run migrate.php first!') . "</p>";

// If the table doesn't exist, there's nothing more we can test
if (!$tableExists) {
    exit;
}

// -----------------------------------------------------------
// STEP 4: Create a temporary test user
// -----------------------------------------------------------
// We use the current timestamp to make a unique username
// so it doesn't conflict with real users.
// Example: "testuser_1693456789"
$testUsername = 'testuser_' . time();
$testEmail   = $testUsername . '@test.com';
$testRole    = 'student';

// Hash the test password (same as real signup does)
$hashedPassword = password_hash('test123', PASSWORD_DEFAULT);

// Insert the test user into the database
$stmt = $db->prepare("INSERT INTO users (email, username, password, role)
                      VALUES (?, ?, ?, ?)");
$stmt->bind_param('ssss', $testEmail, $testUsername, $hashedPassword, $testRole);

if ($stmt->execute()) {
    // ✓ Signup worked!
    echo "<p style='color:green'>✓ Test signup worked! User: $testUsername</p>";

    // -----------------------------------------------------------
    // STEP 5: Test login with the test user
    // -----------------------------------------------------------
    // Look up the user we just created
    $loginStmt = $db->prepare("SELECT username, password, role FROM users WHERE username=?");
    $loginStmt->bind_param('s', $testUsername);
    $loginStmt->execute();
    $row = $loginStmt->get_result()->fetch_assoc();

    // password_verify() checks if "test123" matches the hashed password
    if ($row && password_verify('test123', $row['password'])) {
        echo "<p style='color:green'>✓ Test login worked!</p>";
    } else {
        echo "<p style='color:red'>✗ Login failed</p>";
    }

    // -----------------------------------------------------------
    // STEP 6: Clean up — delete the test user
    // -----------------------------------------------------------
    // We don't want test users cluttering the database
    $db->query("DELETE FROM users WHERE username='$testUsername'");
    echo "<p>Test user cleaned up.</p>";

} else {
    // Signup failed — show error
    echo "<p style='color:red'>✗ Signup failed: " . $stmt->error . "</p>";
}

// -----------------------------------------------------------
// STEP 7: Show all registered users
// -----------------------------------------------------------
echo "<h3>All registered users:</h3><ul>";

$allUsers = $db->query("SELECT id, username, email, role, created_at FROM users");

if ($allUsers && $allUsers->num_rows > 0) {
    // Loop through each user and display their info
    while ($user = $allUsers->fetch_assoc()) {
        echo "<li><b>{$user['username']}</b> ({$user['email']}) - {$user['role']} - {$user['created_at']}</li>";
    }
} else {
    echo "<li>No users yet</li>";
}
echo "</ul>";

// -----------------------------------------------------------
// STEP 8: Quick JSON encoding test
// -----------------------------------------------------------
echo "<h3>Raw API test:</h3>";
echo "<p>Testing JSON encode: " . json_encode(['ok' => true, 'role' => 'student']) . "</p>";

// -----------------------------------------------------------
// STEP 9: Close the database connection
// -----------------------------------------------------------
$db->close();
