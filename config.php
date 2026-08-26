<?php
// Auto-detect: localhost (XAMPP) or InfinityFree
if ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['HTTP_HOST'] === '127.0.0.1') {
    $db = new mysqli('localhost', 'root', '', 'ecanteen');
} else {
    $db = new mysqli('sql111.infinityfree.com', 'if0_41975746', '5fBKvpqqB7y', 'if0_41975746_ecanteen');
}
