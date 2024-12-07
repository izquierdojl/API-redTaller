<?php


require $_SERVER['DOCUMENT_ROOT'] . '/funciones/database.php'; 
require $_SERVER['DOCUMENT_ROOT'] . '/funciones/funciones.php'; 

header('Content-Type: application/json; charset=utf-8');

$headers = getallheaders();
$nif_access = valid_access($headers);

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
