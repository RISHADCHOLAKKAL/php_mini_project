<?php
header('Content-Type: application/json');

$db = new mysqli('localhost', 'root', '', 'ecanteen');
if ($db->connect_error) die(json_encode(['error' => 'db fail']));

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $a = $_GET['action'] ?? '';

    if ($a === 'menu') {
        $r = $db->query("SELECT * FROM menu_items");
        $out = [];
        while ($row = $r->fetch_assoc()) {
            $out[] = [
                'id' => (int)$row['id'],
                'name' => $row['name'],
                'desc' => $row['description'],
                'price' => (int)$row['price'],
                'category' => $row['category'],
                'qty' => (int)$row['qty'],
                'isAvailable' => (bool)$row['is_available'],
                'icon' => $row['icon']
            ];
        }
        echo json_encode($out);
    }

    elseif ($a === 'sales') {
        $orders = [];
        $r = $db->query("SELECT * FROM orders ORDER BY created DESC");
        while ($o = $r->fetch_assoc()) {
            $oid = (int)$o['id'];
            $items = [];
            $ir = $db->query("SELECT item_name,qty,price FROM order_items WHERE order_id=$oid");
            while ($i = $ir->fetch_assoc()) {
                $items[] = ['name' => $i['item_name'], 'qty' => (int)$i['qty'], 'price' => (int)$i['price']];
            }
            $orders[] = [
                'id' => $o['order_code'],
                'date' => $o['created'],
                'type' => $o['order_type'],
                'table' => $o['table_num'] ? (int)$o['table_num'] : null,
                'total' => (int)$o['total'],
                'items' => $items
            ];
        }
        echo json_encode($orders);
    }

    else { echo json_encode(['error' => 'bad action']); }
}

elseif ($method === 'POST') {
    $d = json_decode(file_get_contents('php://input'), true);
    $a = $d['action'] ?? '';

    if ($a === 'save_item') {
        $i = $d['item'];
        if (!empty($i['id'])) {
            $st = $db->prepare("UPDATE menu_items SET name=?,description=?,price=?,category=?,qty=?,is_available=? WHERE id=?");
            $st->bind_param('ssisiii', $i['name'], $i['desc'], $i['price'], $i['category'], $i['qty'], $i['isAvailable'], $i['id']);
        } else {
            $st = $db->prepare("INSERT INTO menu_items(name,description,price,category,qty,is_available,icon) VALUES(?,?,?,?,?,?,?)");
            $icon = $i['icon'] ?? '';
            $st->bind_param('ssissis', $i['name'], $i['desc'], $i['price'], $i['category'], $i['qty'], $i['isAvailable'], $icon);
        }
        $st->execute();
        echo json_encode(['ok' => true, 'id' => $db->insert_id ?: $i['id']]);
    }

    elseif ($a === 'record_sale') {
        $o = $d['order'];
        $code = 'ORD-' . time();
        $st = $db->prepare("INSERT INTO orders(order_code,order_type,table_num,total) VALUES(?,?,?,?)");
        $st->bind_param('ssii', $code, $o['type'], $o['table'], $o['total']);
        $st->execute();
        $oid = $db->insert_id;
        $si = $db->prepare("INSERT INTO order_items(order_id,item_name,qty,price) VALUES(?,?,?,?)");
        foreach ($o['items'] as $it) {
            $si->bind_param('isii', $oid, $it['name'], $it['qty'], $it['price']);
            $si->execute();
        }
        // deduct stock
        foreach ($o['items'] as $it) {
            $db->query("UPDATE menu_items SET qty=GREATEST(0,qty-{$it['qty']}), is_available=IF(qty-{$it['qty']}>0,1,0) WHERE name='" . $db->real_escape_string($it['name']) . "'");
        }
        echo json_encode(['ok' => true, 'id' => $code]);
    }

    else { echo json_encode(['error' => 'bad action']); }
}

$db->close();
