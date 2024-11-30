<?php

require $_SERVER['DOCUMENT_ROOT'] . '/security/vendor/autoload.php'; 
require $_SERVER['DOCUMENT_ROOT'] . '/funciones/database.php'; 

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header('Content-Type: application/json; charset=utf-8');

$secret_key = getenv('SECRET_JWT_KEY');

$valid_users = [];

$conDb = BD_conectar();

$sql = "SELECT nif, password FROM cliente";
$result = $conDb->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $valid_users[$row['nif']] = $row['password'];
    }
}

$conDb->close();

$headers = getallheaders();
$auth_header = isset($headers['Authorization']) ? $headers['Authorization'] : '';
$token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : '';

if (empty($auth_header) || empty($token)) {
    http_response_code(403);
    echo json_encode(['error' => 'Credenciales o token no proporcionados']);
    exit();
}

list($user, $password) = explode(':', base64_decode(substr($auth_header, 6)));
$hashedPassword = hash('sha256', $password);

if (!isset($valid_users[$user]) || $valid_users[$user] !== $hashedPassword) {
    http_response_code(403);
    echo json_encode(['error' => 'Credenciales inv�lidas']);
    exit();
}

$conDb = BD_conectar();

$sql = "SELECT nif, nombre, domicilio, cp, pob, pro, tel, email, movil, id FROM taller";
$result = $conDb->query($sql);
$talleres = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $talleres[] = $row;
    }
}
$conDb->close();


echo json_encode($talleres);
?>