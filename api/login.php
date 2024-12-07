<?php

require $_SERVER['DOCUMENT_ROOT'] . '/funciones/database.php'; 
require $_SERVER['DOCUMENT_ROOT'] . '/funciones/funciones.php'; 

header('Content-Type: application/json; charset=utf-8');

$headers = getallheaders();
$nif_access = valid_access($headers);
echo json_encode(['result' => 'OK' , 'description' => 'Succesful access '.$nif_access ]);
?>