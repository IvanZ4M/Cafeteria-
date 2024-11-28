<?php
session_start();
include '../config/db.php'; // Asegúrate de que la ruta a tu archivo de configuración es correcta

// Inicializar variables
$turno_formateado = "N/A"; // Valor por defecto
$combos = []; //Array para almacenar los combos

// Verificar si se recibió el ID de la orden
if (isset($_GET['orden'])) {
    $id_orden = $_GET['orden'];

    // Verificar si la conexión es exitosa
    if ($conn->connect_error) {
        die("Conexión fallida: " . $conn->connect_error);
    }

    // Obtener el número de turno y los combos asociados a la orden, incluyendo la cantidad
    $query = "SELECT o.Turno, c.Nombre, co.cantidad, o.Total
              FROM orden o 
              JOIN com_ord co ON o.ID_Orden = co.ID_Orden 
              JOIN combo c ON co.ID_Combo = c.ID_Combo 
              WHERE o.ID_Orden = ?";

    // Preparar la consulta
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("i", $id_orden); // Asumiendo que ID_Orden es un entero
        $stmt->execute();
        $result = $stmt->get_result();

        // Verificar si se obtuvo un resultado
        if ($result->num_rows > 0) {
            $numero_turno = null;
            $total=null;

            // Guardar todos los combos y cantidades en el array
            while ($order = $result->fetch_assoc()) {
                if ($numero_turno === null) {
                    $numero_turno = $order['Turno'];
                    $turno_formateado = sprintf("%03d", $numero_turno);
                }

                $total=$order['Total'];

                $combos[] = array(
                    'Nombre' => $order['Nombre'],
                    'cantidad' => $order['cantidad']

                );
            }
        } else {
            echo "No se encontraron registros para la orden seleccionada.";
        }

        $stmt->close();
    } else {
        echo "Error al preparar la consulta: " . $conn->error;
    }

    $conn->close();
} else {
    echo "No se ha seleccionado ninguna orden.";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>POS - Confirmación de Orden</title>
    <link rel="stylesheet" href="pos2.css">
</head>
<body>

    <img src="mcdonalds.png" class="mclogo" alt=""> 
    
    <h1>¡Tu Orden ha sido Registrada!</h1>
    <div class="tablitaparacentrar"> 
        <div class="turno-container">
            <div class="turno-box">
                Número de Turno: <?php echo htmlspecialchars($turno_formateado); ?>
            </div>
            <div class="total-box">
                Total a Pagar: $<?php echo htmlspecialchars($total); ?> 
            </div>
            <div class="combo-name">
                Combos:
                <ul>
                    <?php foreach ($combos as $combo) : ?>
                        <li>
                        <?php
                        if ($combo['cantidad'] > 1) {
                            echo htmlspecialchars($combo['Nombre'] . ' x' . $combo['cantidad']);
                        } else {
                            echo htmlspecialchars($combo['Nombre']);
                        }
                        ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
    <form method="POST" action="pos.php">
        <button type="submit" class="next-btn">SIGUIENTE</button>
    </form>
</body>
</html>

