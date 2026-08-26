<?php
require 'config.php';
header('Content-Type: application/json');
if ($db->connect_error) { echo json_encode(['error' => $db->connect_error]); exit; }

$db->query("DELETE FROM users WHERE username='admin'");
$hash = password_hash('admin123', PASSWORD_DEFAULT);
$e = 'admin@ecanteen.com'; $u = 'admin'; $r = 'admin';
$s = $db->prepare("INSERT INTO users (email,username,password,role) VALUES (?,?,?,?)");
$s->bind_param('ssss', $e, $u, $hash, $r);
echo $s->execute()
  ? json_encode(['ok'=>true, 'msg'=>'Admin created! Username: admin | Password: admin123'])
  : json_encode(['error'=>$s->error]);
$db->close();
