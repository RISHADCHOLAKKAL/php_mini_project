<?php
require 'config.php';
header('Content-Type: application/json');
if ($db->connect_error) die(json_encode(['error' => 'db fail']));

function reply($data) { echo json_encode($data); exit; }

$d = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $d['action'] ?? $_GET['action'] ?? '';

switch ($action) {
  case 'menu':
    reply($db->query("SELECT id, name, description AS `desc`, price, category, qty, is_available AS isAvailable, icon FROM menu_items")->fetch_all(MYSQLI_ASSOC));

  case 'sales':
    $rows = $db->query("SELECT id, order_code, created AS date, order_type AS type, table_num AS `table`, total FROM orders ORDER BY id DESC")->fetch_all(MYSQLI_ASSOC);
    foreach ($rows as &$r) {
      $r['items'] = $db->query("SELECT item_name AS name, qty, price FROM order_items WHERE order_id={$r['id']}")->fetch_all(MYSQLI_ASSOC);
    }
    reply($rows);

  case 'signup':
    $user = trim($d['username'] ?? '');
    $email = trim($d['email'] ?? '');
    $chk = $db->prepare("SELECT username FROM users WHERE username=? OR email=?");
    $chk->bind_param('ss', $user, $email);
    $chk->execute();
    if ($chk->get_result()->fetch_assoc()) reply(['error' => 'Username or Email already exists']);

    $hash = password_hash($d['password'] ?? '', PASSWORD_DEFAULT);
    $s = $db->prepare("INSERT INTO users (email, username, password, role) VALUES (?, ?, ?, 'student')");
    $s->bind_param('sss', $email, $user, $hash);
    $s->execute();
    reply(['ok' => true, 'role' => 'student']);

  case 'login':
    $s = $db->prepare("SELECT username, password, role FROM users WHERE username=?");
    $s->bind_param('s', $d['username']);
    $s->execute();
    $u = $s->get_result()->fetch_assoc();
    if (!$u || !password_verify($d['password'] ?? '', $u['password'])) reply(['error' => 'Incorrect username or password']);
    reply(['ok' => true, 'role' => $u['role'], 'username' => $u['username']]);

  case 'save_item':
    $i = $d['item'];
    if (!empty($i['id'])) {
      $s = $db->prepare("UPDATE menu_items SET name=?, description=?, price=?, category=?, qty=?, is_available=? WHERE id=?");
      $s->bind_param('ssisiii', $i['name'], $i['desc'], $i['price'], $i['category'], $i['qty'], $i['isAvailable'], $i['id']);
    } else {
      $icon = $i['icon'] ?? '';
      $s = $db->prepare("INSERT INTO menu_items (name, description, price, category, qty, is_available, icon) VALUES (?, ?, ?, ?, ?, ?, ?)");
      $s->bind_param('ssissis', $i['name'], $i['desc'], $i['price'], $i['category'], $i['qty'], $i['isAvailable'], $icon);
    }
    $s->execute();
    reply(['ok' => true]);

  case 'record_sale':
    $o = $d['order'];
    $code = 'ORD-' . time();
    $s = $db->prepare("INSERT INTO orders (order_code, order_type, table_num, total) VALUES (?, ?, ?, ?)");
    $s->bind_param('ssii', $code, $o['type'], $o['table'], $o['total']);
    $s->execute();
    $oid = $db->insert_id;
    foreach ($o['items'] as $it) {
      $s2 = $db->prepare("INSERT INTO order_items (order_id, item_name, qty, price) VALUES (?, ?, ?, ?)");
      $s2->bind_param('isii', $oid, $it['name'], $it['qty'], $it['price']);
      $s2->execute();
      $name = $db->real_escape_string($it['name']);
      $qty = (int)$it['qty'];
      $db->query("UPDATE menu_items SET qty=GREATEST(0, qty-$qty), is_available=IF(qty>$qty, 1, 0) WHERE name='$name'");
    }
    reply(['ok' => true]);

  default:
    reply(['error' => 'invalid action']);
}
