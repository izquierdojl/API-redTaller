<?php

require $_SERVER['DOCUMENT_ROOT'] . '/funciones/database.php'; 
require $_SERVER['DOCUMENT_ROOT'] . '/funciones/funciones.php'; 

header('Content-Type: application/json; charset=utf-8');

$headers = getallheaders();
$nif_access = valid_access($headers);

$nif_param = isset($_GET['nif']) ? $_GET['nif'] : null;
$search_param = isset($_GET['search']) ? $_GET['search'] : null;

$conDb = BD_conectar();

if ($nif_param) {
    $sql = "SELECT nif, nombre, domicilio, cp, pob, pro, tel, email, movil, id FROM taller WHERE nif = ?";
    $stmt = $conDb->prepare($sql);
    $stmt->bind_param("s", $nif_param); //"s" para string
    $stmt->execute();
    $result = $stmt->get_result();
    $talleres = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $talleres[] = $row;
        }
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Taller no encontrado']);
        exit();
    }
} elseif($search_param)
{
    $search_param = '%' . $search_param . '%';  
    $sql = "SELECT nif, nombre, domicilio, cp, pob, pro, tel, email, movil, id 
            FROM taller 
            WHERE 
            (nif LIKE ? OR 
             nombre LIKE ? OR 
             domicilio LIKE ? OR 
             pob LIKE ? OR 
             pro LIKE ?) ORDER BY nombre";
     $stmt = $conDb->prepare($sql);
     $stmt->bind_param("sssss", $search_param, $search_param, $search_param, $search_param, $search_param);
     $stmt->execute();     
     $result = $stmt->get_result();
     $talleres = [];
     if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
           $talleres[] = $row;
        }
    }
}else
{
    $sql = "SELECT nif, nombre, domicilio, cp, pob, pro, tel, email, movil, id FROM taller ORDER BY nombre";
    $result = $conDb->query($sql);
    $talleres = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $talleres[] = $row;
        }
    }
}

$conDb->close();

echo json_encode($talleres);
?>
