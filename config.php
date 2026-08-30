<?php
// ╔═══════════════════════════════════════════════════════════════╗
// ║               config.php — Database Connection               ║
// ║                                                              ║
// ║  PURPOSE: This file creates a connection to the MySQL        ║
// ║  database. Every other PHP file uses "require 'config.php'"  ║
// ║  to get access to the $db variable (database connection).    ║
// ║                                                              ║
// ║  HOW IT WORKS:                                               ║
// ║  - If running on your computer (localhost / XAMPP) →         ║
// ║    it connects to your local MySQL with no password.         ║
// ║  - If running on a live server (InfinityFree hosting) →      ║
// ║    it connects to the remote MySQL with a password.          ║
// ╚═══════════════════════════════════════════════════════════════╝

// -----------------------------------------------------------
// STEP 1: Check WHERE the website is running
// -----------------------------------------------------------
// $_SERVER['HTTP_HOST'] tells us the domain name.
// "localhost" or "127.0.0.1" means = running on YOUR computer (XAMPP).
// Anything else means = running on a live web server.

if ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['HTTP_HOST'] === '127.0.0.1') {

    // -----------------------------------------------------------
    // OPTION A: LOCAL (XAMPP) — your own computer
    // -----------------------------------------------------------
    // new mysqli(server, username, password, database_name)
    //   server   = 'localhost'   → MySQL is on this computer
    //   username = 'root'        → default XAMPP user (no password)
    //   password = ''            → empty string = no password
    //   database = 'ecanteen'    → the database name we created
    $db = new mysqli('localhost', 'root', '', 'ecanteen');

} else {

    // -----------------------------------------------------------
    // OPTION B: LIVE SERVER (InfinityFree hosting)
    // -----------------------------------------------------------
    // When you upload your project to a real web host,
    // it uses their MySQL server with a real username & password.
    $db = new mysqli('sql111.infinityfree.com', 'if0_41975746', '5fBKvpqqB7y', 'if0_41975746_ecanteen');
}

// -----------------------------------------------------------
// WHAT HAPPENS NEXT?
// -----------------------------------------------------------
// After this file runs, the variable $db is available.
// Other files do: require 'config.php';
// Then they can use $db to run SQL queries like:
//   $db->query("SELECT * FROM menu_items");
//
// If the connection FAILED, $db->connect_error will contain
// the error message (other files check this before proceeding).
// -----------------------------------------------------------
