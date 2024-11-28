<?php
session_start();
include '../config/db.php';

// Conexión a la base de datos
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Obtener las órdenes que están en estado 'pendiente' y 'retirar'
$query = "SELECT o.ID_Orden, o.Turno, o.Estado 
          FROM orden o 
          WHERE o.Estado IN ('pendiente', 'retirar')";

if ($result = $conn->query($query)) {
    // Aquí estamos obteniendo las órdenes en estado 'pendiente' y 'retirar'
    $ordenes = ['en_preparacion' => [], 'retirar' => []];
    while ($row = $result->fetch_assoc()) {
        if ($row['Estado'] == 'pendiente') {
            $ordenes['en_preparacion'][] = $row;
        } else if ($row['Estado'] == 'retirar') {
            $ordenes['retirar'][] = $row;
        }
    }
} else {
    echo "Error al obtener las órdenes: " . $conn->error;
}
header("refresh:5;url=turnos.php");

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Turnos</title>
    <link rel="stylesheet" href="turnos.css">
</head>
<body>
<img src="mcdonalds.png" class="mclogo" alt=""> 

    <h1>Órdenes</h1>
    
    <div class="estado">
        <!-- En Preparación -->
        <div class="preparacion">
            <h2>EN PREPARACION</h2>
            <div class="ordenesCol">
                <?php 
                $enPreparacionCount = 0;
                if (!empty($ordenes['en_preparacion'])): 
                    foreach ($ordenes['en_preparacion'] as $orden): 
                        if ($enPreparacionCount < 14): ?>
                            <div class="orden" style="background-color: red;">
                                <?php 
                                    // Mostrar el turno con formato de 3 dígitos
                                    echo str_pad($orden['Turno'], 3, '0', STR_PAD_LEFT); 
                                ?>
                            </div>
                            <?php $enPreparacionCount++; ?>
                        <?php endif;
                    endforeach; 
                else: ?>
                    <p>No hay órdenes en preparación.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Retirar -->
        <div class="retirar">
            <h2>RETIRAR</h2>
            <div class="ordenesCol">
                <?php 
                $retirarCount = 0;
                if (!empty($ordenes['retirar'])): 
                    foreach ($ordenes['retirar'] as $orden): 
                        if ($retirarCount < 14): ?>
                            <div class="orden" style="background-color: green;">
                                <?php 
                                    // Mostrar el turno con formato de 3 dígitos
                                    echo str_pad($orden['Turno'], 3, '0', STR_PAD_LEFT); 
                                ?>
                            </div>
                            <?php $retirarCount++; ?>
                        <?php endif;
                    endforeach; 
                else: ?>
                    <p>No hay órdenes para retirar.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>

</html>
