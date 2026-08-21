<?php
// Replace 'YOUR_VPANEL_PASSWORD' with your actual vPanel password
$db = new mysqli('sql111.infinityfree.com', 'if0_41975746', '5fBKvpqqB7y', 'if0_41975746_ecanteen');
header('Content-Type: application/json');
if ($db->connect_error) die(json_encode(['error' => 'db fail']));

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (($_GET['action'] ?? '') === 'menu') {
        echo json_encode($db->query("SELECT id, name, description AS `desc`, price, category, qty, is_available AS isAvailable, icon FROM menu_items")->fetch_all(MYSQLI_ASSOC));
    } else {
        $orders = $db->query("SELECT id, order_code AS id, created AS date, order_type AS type, table_num AS `table`, total FROM orders ORDER BY id DESC")->fetch_all(MYSQLI_ASSOC);
        foreach ($orders as &$o) {
            $oid = $o['id'];
            $o['items'] = $db->query("SELECT item_name AS name, qty, price FROM order_items WHERE order_id=$oid")->fetch_all(MYSQLI_ASSOC);
        }
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
        $s = $db->prepare("INSERT INTO orders (order_code, order_type, table_num, total) VALUES (?,?,?,?)");
        $s->bind_param('ssii', $code, $o['type'], $o['table'], $o['total']);
        $s->execute();
        $oid = $db->insert_id;

        foreach ($o['items'] as $it) {
            $s2 = $db->prepare("INSERT INTO order_items (order_id, item_name, qty, price) VALUES (?,?,?,?)");
            $s2->bind_param('isii', $oid, $it['name'], $it['qty'], $it['price']);
            $s2->execute();

            $s3 = $db->prepare("UPDATE menu_items SET qty=GREATEST(0, qty-?), is_available=IF(qty-?>0, 1, 0) WHERE name=?");
            $s3->bind_param('iis', $it['qty'], $it['qty'], $it['name']);
            $s3->execute();
        }
        echo json_encode(['ok' => true]);
    }
}
$db->close();
