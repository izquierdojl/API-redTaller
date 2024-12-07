<?php

require $_SERVER['DOCUMENT_ROOT'] . '/security/vendor/autoload.php'; 
require $_SERVER['DOCUMENT_ROOT'] . '/funciones/database.php'; 

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header('Content-Type: application/json; charset=utf-8');

$valid_users = [];

$conDb = BD_conectar();

$nif_param = isset($_GET['nif']) ? $_GET['nif'] : null;

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

if (!isset($valid_users[$user]) || $valid_users[$user] !== $password) {
    http_response_code(403);
    echo json_encode(['error' => 'Credenciales inválidas']);
    exit();
}

$conDb = BD_conectar();

if ($nif_param) { // un nif en concreto
    $sql = "SELECT nif, nombre, domicilio, cp, pob, pro, tel, email, movil, id FROM cliente WHERE nif = ?";
    $stmt = $conDb->prepare($sql);
    $stmt->bind_param("s", $nif_param); // string
    $stmt->execute();
    $result = $stmt->get_result();
    $clientes = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $clientes[] = $row;
        }
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Cliente no encontrado']);
        exit();
    }
} else { // todos los clientes
    $sql = "SELECT nif, nombre, domicilio, cp, pob, pro, tel, email, movil, id FROM cliente";
    $result = $conDb->query($sql);
    $clientes = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $clientes[] = $row;
        }
    }
}

$conDb->close();

echo json_encode($clientes);
?>
