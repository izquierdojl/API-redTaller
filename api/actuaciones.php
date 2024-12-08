<?php
error_reporting(E_ALL); // Reportar todos los errores
ini_set('display_errors', 1); // Mostrar los errores en pantalla
ini_set('display_startup_errors', 1); // Mostrar errores durante el inicio del script

require $_SERVER['DOCUMENT_ROOT'] . '/funciones/database.php'; 
require $_SERVER['DOCUMENT_ROOT'] . '/funciones/funciones.php'; 

header('Content-Type: application/json; charset=utf-8');

$headers = getallheaders();
$nif_access = valid_access($headers);
$search_param = null;

$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : null;
$id = isset($_GET['id']) ? $_GET['id'] : null;

$conDb = BD_conectar();

switch ($tipo)
{
    case "detalle":
        {
            $sql = "SELECT actuacion_detalle.descripcion,
                           actuacion_detalle.id,
                           IF(actuacion_detalle.imagen is not NULL,'S','N') AS existe_imagen
                    FROM actuacion_detalle
                    WHERE actuacion_detalle.id_actuacion = ?
                    ORDER BY linea";
            $stmt = $conDb->prepare($sql);
            $stmt->bind_param("i", $id); //"i" para integer
            $stmt->execute();
            $result = $stmt->get_result();
            $salida = [];
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $salida[] = $row;
                }
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Detalle de Actuaciones no encontradas']);
                exit();
            }
        }
        break;
    case "imagen":
        {
            $sql = "SELECT actuacion_detalle.imagen
                    FROM actuacion_detalle
                    WHERE actuacion_detalle.id = ? " ;
            $stmt = $conDb->prepare($sql);
            $stmt->bind_param("i", $id); //"i" para integer
            $stmt->execute();
            $result = $stmt->get_result();
            $salida = [];
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $salida[] = $row;
                }
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Imagen de Detalle de Actuaciones no encontradas']);
                exit();
            }
        }
        break;
    default:
        if ($nif_access) {
            $sql = "SELECT actuacion.id,
                        actuacion.fecha,
                        actuacion.matricula,
                        matricula.modelo,
                        matricula.marca,
                        actuacion.nif_cliente,
                        cliente.nombre as nom_cliente,
                        actuacion.nif_taller,
                        taller.nombre as nom_taller,
                        actuacion.km,
                        actuacion.tipo 
                    FROM actuacion
                        LEFT JOIN matricula ON matricula.matricula = actuacion.matricula
                        LEFT JOIN cliente ON cliente.nif = actuacion.nif_cliente
                        LEFT JOIN taller ON taller.nif = actuacion.nif_taller
                    WHERE actuacion.nif_cliente = ? ";
            if( $search_param )                    
            {
                $sql .= "(
                            actuacion.matricula LIKE ?
                            OR matricula.marca LIKE ?
                            OR matricula.modelo LIKE ?
                        )";
            }
            $sql .= "ORDER BY actuacion.fecha DESC" ;
            $stmt = $conDb->prepare($sql);
            $stmt->bind_param("s", $nif_access); //"s" para string
            $stmt->execute();
            $result = $stmt->get_result();
            $salida = [];
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $salida[] = $row;
                }
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Actuaciones no encontradas']);
                exit();
            }

        }
}
$conDb->close();

echo json_encode($salida);
?>
