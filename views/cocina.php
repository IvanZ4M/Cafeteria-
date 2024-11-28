<?php
session_start();
include '../config/db.php';

// Conexión a la base de datos
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Obtener las órdenes que están en estado 'pendiente' o 'retirar' junto con la descripción y cantidad de productos
$query = "
    SELECT o.ID_Orden, o.Turno, o.Estado, 
    GROUP_CONCAT(CONCAT(c.Nombre, ' (', c.Productos, ') x', co.cantidad) SEPARATOR ', ') AS Descripcion
    FROM orden o
    JOIN com_ord co ON o.ID_Orden = co.ID_Orden
    JOIN combo c ON co.ID_Combo = c.ID_Combo
    WHERE o.Estado IN ('pendiente', 'retirar')
    GROUP BY o.ID_Orden
";

if ($result = $conn->query($query)) {
    // Obtener las órdenes en estado 'pendiente' o 'retirar'
    $ordenes = [];
    while ($row = $result->fetch_assoc()) {
        $ordenes[] = $row;
    }
} else {
    echo "Error al obtener las órdenes: " . $conn->error;
}

// Verificar si se presionó el botón "Listo"
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['listo'])) {
    $id_orden = $_POST['id_orden'];

    // Cambiar el estado de la orden a 'retirar'
    $update_query = "UPDATE orden SET Estado = 'retirar' WHERE ID_Orden = ?";
    if ($stmt = $conn->prepare($update_query)) {
        $stmt->bind_param("i", $id_orden);
        $stmt->execute();
        // Redirigir para evitar reenvío de formulario
        header("Location: cocina.php");
        exit();
    } else {
        echo "Error al actualizar el estado de la orden: " . $conn->error;
    }
}

// Verificar si se presionó el botón "Completada"
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['completada'])) {
    $id_orden = $_POST['id_orden'];

    // Cambiar el estado de la orden a 'completada'
    $update_query = "UPDATE orden SET Estado = 'completada' WHERE ID_Orden = ?";
    if ($stmt = $conn->prepare($update_query)) {
        $stmt->bind_param("i", $id_orden);
        $stmt->execute();
        // Redirigir para evitar reenvío de formulario
        header("Location: cocina.php");
        exit();
    } else {
        echo "Error al actualizar el estado de la orden: " . $conn->error;
    }
}

header("refresh:10;url=cocina.php");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cocina - Cambiar Estado de Orden</title>
    <link rel="stylesheet" href="cocina.css">
    <style>
        .descripcion {
            text-align: left;
            font-size: 14px;
            padding-left: 10px;
        }
        
        .descripcion ul {
            list-style-type: none;
            padding-left: 0;
        }

        .descripcion li {
            margin-bottom: 5px;
            border-bottom: 1px solid #ccc;
            padding: 5px 0;
        }
    </style>
</head>
<body>
    <img src="mcdonalds.png" class="mclogo" alt=""> 
    <h1>Órdenes en Preparación</h1>
    
    <?php if (!empty($ordenes)): ?>
        <div class="orden-preparacion">
        <table>
            <thead>
                <tr>
                    <th>Turno</th>
                    <th>Descripción</th> <!-- Nueva columna para la descripción -->
                    <th>Estado</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ordenes as $orden): ?>
                    <tr>
                        <td><?php echo str_pad($orden['Turno'], 3, '0', STR_PAD_LEFT); ?></td>
                        <td class="descripcion">
                            <ul>
                                <?php 
                                // Separar los combos y productos
                                $combos = explode(', ', $orden['Descripcion']);
                                foreach ($combos as $combo): ?>
                                    <li><?php echo $combo; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </td>
                        <td><?php echo $orden['Estado']; ?></td>
                        <td>
                            <?php if ($orden['Estado'] == 'retirar'): ?>
                                <form method="POST" action="cocina.php">
                                    <input type="hidden" name="id_orden" value="<?php echo $orden['ID_Orden']; ?>">
                                    <button type="submit" name="completada" class="completada-btn">Completada</button>
                                </form>
                            <?php else: ?>
                                <form method="POST" action="cocina.php">
                                    <input type="hidden" name="id_orden" value="<?php echo $orden['ID_Orden']; ?>">
                                    <button type="submit" name="listo" class="listo-btn">Listo</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    <?php else: ?>
        <p>No hay órdenes pendientes.</p>
    <?php endif; ?>
</body>
</html>
