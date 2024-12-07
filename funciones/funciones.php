<?php
//error_reporting(E_ALL); // Reportar todos los errores
//ini_set('display_errors', 1); // Mostrar los errores en pantalla
//ini_set('display_startup_errors', 1); // Mostrar errores durante el inicio del script

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

require $_SERVER['DOCUMENT_ROOT'] . '/security/vendor/autoload.php'; 

function valid_access($headers)
{
    $auth_header = isset($headers['Authorization']) ? $headers['Authorization'] : '';
    $token = isset($headers['Token']) ? str_replace('Bearer ', '', $headers['Token']) : '';
    if (empty($auth_header) || empty($token)) {
        http_response_code(403);
        echo json_encode(['error' => 'Credenciales o token no proporcionados']);
        exit();
    }
    try {
        $secret_key = getenv('SECRET_JWT_KEY');
        $decoded = JWT::decode($token, new Key($secret_key, 'HS256'));
    } catch (Exception $e) {
        http_response_code(403);
        echo json_encode(['error' => 'Token inválido: ' . $token ]);
        exit();
    }

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

    list($user, $password) = explode(':', base64_decode(substr($auth_header, 6)));

    if (!isset($valid_users[$user]) || $valid_users[$user] !== $password) {
        http_response_code(403);
        echo json_encode(['error' => 'Credenciales inválidas']);
        exit();
    }

    return $user;

}
?>
