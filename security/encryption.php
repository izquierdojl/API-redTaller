<?php

//error_reporting(E_ALL);
//ini_set('display_errors', 1);
//ini_set('log_errors', 1); 
//ini_set('error_log', '/var/www/redtaller.jlizquierdo.com/error.log'); // Ruta al archivo de log

require 'vendor/autoload.php'; // autoload de Composer para JWT
require $_SERVER['DOCUMENT_ROOT'] . '/funciones/database.php'; // funciones de base de datos


use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header('Content-Type: application/json');

// Clave secreta para generar y validar los tokens
$secret_key = getenv('SECRET_JWT_KEY');

$valid_users = [];

$conDb = BD_conectar();

// Obtener password de master de configuraci�n, codificada en AES-256-CBC, tiene que descifrarla y luego hashearla
$sql = "SELECT master_password FROM config LIMIT 1";
$result = $conDb->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
      $decryptedPassword = openssl_decrypt($row['master_password'], 'AES-256-CBC', base64_decode(getenv('SECRET_CIPHER_KEY')), 0, base64_decode(getenv('SECRET_CIPHER_VECTOR')));    
      $valid_users['master'] = hash( 'sha256' , $decryptedPassword  ) ;
    }
}

// Obtener usuarios y contrase�as de la tabla `taller`
$sql = "SELECT nif, password FROM taller";
$result = $conDb->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $valid_users[$row['nif']] = $row['password'];
    }
}

$conDb->close();

// headers de la petici�n
$headers = getallheaders();
$auth_header = isset($headers['Authorization']) ? $headers['Authorization'] : '';

$token = isset($headers['Token']) ? str_replace('Bearer ', '', $headers['Token']) : '';

// Verificamos que vienen datos
if (empty($auth_header) || empty($token)) {
    http_response_code(403);
    echo json_encode(['error' => 'Credenciales o token no proporcionados']);
    exit();
}

// Verificar las credenciales de usuario y contrase�a
list($user, $password) = explode(':', base64_decode(substr($auth_header, 6)));

$hashedPassword = hash( 'sha256', $password );

if (!isset($valid_users[$user]) || $valid_users[$user] !== $hashedPassword ) {
    http_response_code(403);
    echo json_encode(['error' => 'Credenciales inválidas']);
    exit();
}

try {
    // Validar el token
    $decoded = JWT::decode($token, new Key($secret_key, 'HS256'));
} catch (Exception $e) {
    http_response_code(403);
    echo json_encode(['error' => 'Token inválido: ' . $e->getMessage()]);
    exit();
}

// Define la clave de cifrado y el Vector
$key = getenv('SECRET_CIPHER_KEY');
$iv = getenv('SECRET_CIPHER_VECTOR');

// Devuelve la clave y el IV en formato JSON
echo json_encode(['key' => $key, 'iv' => $iv]);

?>
