<?php
// Quick test - directly test signup and login
require 'config.php';
header('Content-Type: text/html');

echo "<h3>DB Connection: ";
if ($db->connect_error) {
    echo "FAILED - " . $db->connect_error;
    exit;
}
echo "OK</h3>";

// Check if users table exists
$result = $db->query("SHOW TABLES LIKE 'users'");
echo "<p>Users table exists: " . ($result->num_rows > 0 ? 'YES' : 'NO - run migrate.php first!') . "</p>";

if ($result->num_rows === 0) exit;

// Try a test signup
$testUser = 'testuser_' . time();
$hash = password_hash('test123', PASSWORD_DEFAULT);
$s = $db->prepare("INSERT INTO users (email,username,password,role) VALUES (?,?,?,?)");
$email = $testUser . '@test.com';
$role = 'student';
$s->bind_param('ssss', $email, $testUser, $hash, $role);

if ($s->execute()) {
    echo "<p style='color:green'>✓ Test signup worked! User: $testUser</p>";
    
    // Now test login
    $s2 = $db->prepare("SELECT username,password,role FROM users WHERE username=?");
    $s2->bind_param('s', $testUser);
    $s2->execute();
    $row = $s2->get_result()->fetch_assoc();
    
    if ($row && password_verify('test123', $row['password'])) {
        echo "<p style='color:green'>✓ Test login worked!</p>";
    } else {
        echo "<p style='color:red'>✗ Login failed</p>";
    }
    
    // Clean up test user
    $db->query("DELETE FROM users WHERE username='$testUser'");
    echo "<p>Test user cleaned up.</p>";
} else {
    echo "<p style='color:red'>✗ Signup failed: " . $s->error . "</p>";
}

// Show all users
echo "<h3>All registered users:</h3><ul>";
$all = $db->query("SELECT id,username,email,role,created_at FROM users");
if ($all && $all->num_rows > 0) {
    while ($u = $all->fetch_assoc()) {
        echo "<li><b>{$u['username']}</b> ({$u['email']}) - {$u['role']} - {$u['created_at']}</li>";
    }
} else {
    echo "<li>No users yet</li>";
}
echo "</ul>";

// Test what API returns for signup
echo "<h3>Raw API test:</h3>";
echo "<p>Testing JSON encode: " . json_encode(['ok' => true, 'role' => 'student']) . "</p>";

$db->close();
