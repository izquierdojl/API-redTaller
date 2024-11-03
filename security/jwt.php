<?php
//error_reporting(E_ALL);
//ini_set('display_errors', 1);

require 'vendor/autoload.php'; // Incluye autoload de Composer para JWT

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header('Content-Type: application/json');

$secret_key = getenv('SECRET_JWT_KEY');

// Generar y mostrar el token
$issued_at = time();
$expiration_time = $issued_at + 300; // token válido por 5 minutos
$payload = array(
    "iat" => $issued_at,
    "exp" => $expiration_time,
    "data" => array(
    "id" => 123,
    "username" => "redtaller"
    )
);

try {
    $jwt = JWT::encode($payload, $secret_key, 'HS256');
} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
    exit();
}


$response = [
    "mensaje" => "Clave secreta cargada",
    "jwt" => $jwt
];

echo json_encode($response);
?>
