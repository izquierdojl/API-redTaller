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
    echo json_encode(['result' => 'KO' , 'description' => 'No valid token' ] );
    exit();
}

list($user, $password) = explode(':', base64_decode(substr($auth_header, 6)));
$hashedPassword = hash('sha256', $password);

if (!isset($valid_users[$user]) || $valid_users[$user] !== $hashedPassword) {
    http_response_code(403);
    echo json_encode(['result' => 'KO' , 'description' => 'No valid user' ]);
    exit();
}

echo json_encode(['result' => 'OK' , 'description' => 'Succesful access' ]);
?>