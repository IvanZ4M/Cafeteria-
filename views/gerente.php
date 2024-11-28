<!DOCTYPE html> 
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="gerente.css">
    <title>Document</title>
</head>
<body>

<img src="mcdonalds.png" class="mclogo" alt="">     

<?php
session_start(); // Inicia la sesión

// Conexión a la base de datos
include '../config/db.php'; 

// Generar el token CSRF si no existe
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Se prepara consulta para inicializar a 0 el estado del empleado
$initialStateQuery = "UPDATE empleado SET estado = 0";

// Función para mostrar la tabla de POS
function mostrarTablaPOS($conn) {
    $posQuery = "SELECT ID_POS, status FROM pos";
    $posResult = $conn->query($posQuery);

    // Mostrar la tabla de POS
    echo '<table border="1">
            <tr>
                <th>ID del POS</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>';

    // Mostrar cada POS en una fila de la tabla
    while ($posRow = $posResult->fetch_assoc()) {
        echo '<tr>
                <td>' . $posRow['ID_POS'] . '</td>
                <td>' . ($posRow['status'] ? 'Activado' : 'Desactivado') . '</td>
                <td>
                    <form action="" method="post" style="display:inline;">
                        <input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">
                        <input type="hidden" name="pos_id" value="' . $posRow['ID_POS'] . '">
                        <button type="submit" name="accion" value="activar" class="activar">Activar</button>
                        <button type="submit" name="accion" value="desactivar" class="desactivar">Desactivar</button>
                    </form>
                </td>
            </tr>';
    }

    echo '</table>';
}

// Procesar el formulario de inicio de sesión del gerente
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verificar el token CSRF solo para POST
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        header("Location: menu.php");
        die("Error: Token CSRF no válidolll.");
    }
    
    // Verificar si se ha solicitado cerrar sesión
    if (isset($_POST['cerrar_sesion'])) {
        // Actualizar el estado y uso
        $query = "UPDATE pos SET status = 0, uso = 0";
        $conn->query($query);

        // Verifica si la consulta fue exitosa
        if ($conn->affected_rows > 0) {
            echo "Se han actualizado los valores de status y uso en la tabla pos.";
        } else {
            echo "No se actualizaron los valores de status y uso.";
        }
        
        // Inicializar todos los estados a 0 al inicio
        $conn->query($initialStateQuery);

        session_destroy(); // Destruir la sesión
        header("Location: menu.php"); 
        exit(); 
    }

    // Comprobar si se ha enviado la matrícula del gerente
    if (isset($_POST['matricula'])) {
        $matricula = $_POST['matricula'];
        $_SESSION['matricula'] = $matricula;

        if (!ctype_digit($matricula)) {
            $_SESSION['error'] = "La matrícula debe contener solo números.";
            header("Location: menu.php");  
            exit();
        } else {
            // Inicializar todos los estados a 0 al inicio
            $conn->query($initialStateQuery);

            // Buscar al gerente en la base de datos
            $query = "SELECT Nombre, AP, AM FROM empleado WHERE ID_Empleado = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $matricula);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // Datos del gerente
                $row = $result->fetch_assoc();
                $nombreCompleto = $row['Nombre'] . ' ' . $row['AP'] . ' ' . $row['AM'];
                $_SESSION['nombreCompleto'] = $nombreCompleto;

                // Actualizar el estado de la matrícula confirmada
                $updateStateQuery = "UPDATE empleado SET estado = 1 WHERE ID_Empleado = ?";
                $updateStmt = $conn->prepare($updateStateQuery);
                $updateStmt->bind_param("i", $matricula);
                $updateStmt->execute();

                // Mostrar opciones del gerente
                echo "<h1>Bienvenido, <u>$nombreCompleto</u> </h1>";
                echo "<p>Usted está encargado de las órdenes.</p>";

                echo '<form action="menu.php" method="post" style="display:inline;">
                        <input type="hidden" name="matricula" value="'.$matricula.'">
                        <input type="hidden" name="nombrecompleto" value="'.$nombreCompleto.'">
                      </form>';

                // Mostrar el botón de cerrar sesión
                echo '<form action="" method="post" style="position:absolute; top:10px; right:10px">
                        <input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">
                        <button type="submit" name="cerrar_sesion" class="cerrar">Cerrar Sesión</button>
                      </form>';

                // Mostrar la tabla de POS
                mostrarTablaPOS($conn);

                echo '<form action="menu.php" method="post" style="display:inline;">
                <button type="submit" class="menu">Ir a Menú</button>
              </form>';

            } else {
                $_SESSION['error'] = "No se encontró un gerente con la matrícula ingresada...";
                header("Location: menu.php");
                exit();
            }
        }
    }

    // Verificar si se ha enviado la acción para el POS
    if (isset($_POST['pos_id']) && isset($_POST['accion'])) {
        $id_pos = $_POST['pos_id'];
        $accion = $_POST['accion'];

        // Determinar el nuevo estado del POS
        $status = ($accion == 'activar') ? 1 : 0;
        $query = "UPDATE pos SET status = ? WHERE ID_POS = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $status, $id_pos);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            // Redirigir a la misma página para evitar resubmisión del formulario
            header("Location: gerente.php");          
            exit;  
        } else {
            header("Location: gerente.php");     
            exit;  
        }
    }
} elseif (isset($_SESSION['nombreCompleto'])) {
    // Si ya hay un nombre completo en la sesión, usarlo
    $nombreCompleto = $_SESSION['nombreCompleto'];

    // Mostrar opciones del gerente
    echo "<h1>Bienvenido, $nombreCompleto</h1>";
    echo "<p>Usted está encargado de las órdenes.</p>";

    // Mostrar el botón de cerrar sesión
    echo '<form action="" method="post" style="position:absolute; top:10px; right:10px">
        <input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">
        <button type="submit" name="cerrar_sesion" class="cerrar">Cerrar Sesión</button>
      </form>';

    // Mostrar la tabla de POS
    mostrarTablaPOS($conn);

    echo '<form action="menu.php" method="post" style="display:inline;">
    <button type="submit" class="menu">Ir a Menú</button>
  </form>';
  
} else {
    header("Location: menu.php"); // Redireccionar
} 

// Cerrar la conexión a la base de datos
$conn->close();
?>


</body>
</html>
