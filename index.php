<?php
//llamada a const(es ruta relativa)
    require("config/const.php");
    require_once(__ROOT__ ."/services/dbcon.php");


    $dbc = conectar();

    if ($dbc != null) {
       header("refresh:5;url=views/menu.php");//redirecciona
    } else {
        die("se produjo un error, contacte con el Admin");
    }
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selecciona una vista</title>
    <link rel="stylesheet" href="index.css">
</head>
<body>
    <h1>Cargando, por favor espere un momento...</h1>

    <br>

    <div class="loading-screen"> 
        <img src="./views/loading screen.gif" alt="" class="cargaMcDonalds">
        <img src="./views/we-can-do-it.gif" alt="" class="cargaDefault">

    </div>

</body>
</html>