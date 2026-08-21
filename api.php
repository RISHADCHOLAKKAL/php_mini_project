<?php
$db = new mysqli('localhost', 'root', '', 'ecanteen');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (($_GET['action'] ?? '') === 'menu') {
        echo json_encode($db->query("SELECT id, name, description AS `desc`, price, category, qty, is_available AS isAvailable, icon FROM menu_items")->fetch_all(MYSQLI_ASSOC));
    } else {
        $orders = $db->query("SELECT code AS id, created AS date, type, NULL AS `table`, total, items FROM orders ORDER BY id DESC")->fetch_all(MYSQLI_ASSOC);
        foreach ($orders as &$o) $o['items'] = json_decode($o['items'], true);
        echo json_encode($orders);
    }
} else {
    $d = json_decode(file_get_contents('php://input'), true);
    if ($d['action'] === 'save_item') {
        $i = $d['item'];
        if (!empty($i['id'])) {
            $s = $db->prepare("UPDATE menu_items SET name=?, description=?, price=?, category=?, qty=?, is_available=? WHERE id=?");
            $s->bind_param('ssisiii', $i['name'], $i['desc'], $i['price'], $i['category'], $i['qty'], $i['isAvailable'], $i['id']);
        } else {
            $s = $db->prepare("INSERT INTO menu_items (name, description, price, category, qty, is_available, icon) VALUES (?,?,?,?,?,?,?)");
            $icon = $i['icon'] ?? '';
            $s->bind_param('ssissis', $i['name'], $i['desc'], $i['price'], $i['category'], $i['qty'], $i['isAvailable'], $icon);
        }
        $s->execute();
        echo json_encode(['ok' => true]);
    } else {
        $o = $d['order'];
        $code = 'ORD-' . time();
        $items = json_encode($o['items']);
        $s = $db->prepare("INSERT INTO orders (code, type, total, items) VALUES (?,?,?,?)");
        $s->bind_param('ssis', $code, $o['type'], $o['total'], $items);
        $s->execute();
        foreach ($o['items'] as $it) {
            $s2 = $db->prepare("UPDATE menu_items SET qty=GREATEST(0, qty-?), is_available=IF(qty-?>0, 1, 0) WHERE name=?");
            $s2->bind_param('iis', $it['qty'], $it['qty'], $it['name']);
            $s2->execute();
        }
        echo json_encode(['ok' => true]);
    }
}
$db->close();
