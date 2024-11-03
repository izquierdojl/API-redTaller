<?php

function BD_conectar()
{
    $servername = "localhost";
    $username = "redtaller";
    $password = getenv('MARIADB_PASSWORD');
    $dbname = "redtaller";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Conexión fallida: " . $conn->connect_error);
    }

    return $conn;
    
}

?>
