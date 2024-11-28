<?php

define("DBHOST","localhost");
define("DBPORT","3306");
define("DBUSER","root");
define("DBPASS","");
define("DBNAME","mc");

// Crea la conexión con la base de datos
$conn = new mysqli(DBHOST, DBUSER, DBPASS, DBNAME, DBPORT);

// Verifica si la conexión fue exitosa
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error); // Si falla, muestra el error
}

?>