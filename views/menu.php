<?php 
session_start();
include '../config/db.php'; 

// Genera token CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
    // Consulta para obtener el nombre del gerente o cocinero según la matrícula
    $consulta = "SELECT Nombre,AP, AM, estado, ID_Empleado FROM empleado WHERE estado = 1";
    $stmt = $conn->prepare($consulta);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $row = $resultado->fetch_assoc();
        $estatus = $row['estado'];
        $id_empleado=null;

        if ($estatus == 0) {
            $nombreCompleto = "Sin Asignar";
            unset($_SESSION['matricula']);
        } else {
            $id_empleado=$row['ID_Empleado'];
            $_SESSION['matricula']= $id_empleado;
            
            $nombreCompleto = $row['Nombre'] . ' ' . $row['AP'] . ' ' . $row['AM'];
        }
    } else {
        $nombreCompleto = "Sin Asignar"; // Si no se encuentra la matrícula
        unset($_SESSION['matricula']);
    }


// Si hay un error, lo mostramos
if (isset($_SESSION['error'])) {
    echo "<script>
    setTimeout(function(){
    alert('" . addslashes($_SESSION['error']) . "' );
    },200);
    </script>";
    unset($_SESSION['error']);  // Limpiar el mensaje de error después de mostrarlo
}

// Consultar POS disponibles
$consulta = "SELECT ID_Pos FROM pos WHERE uso = 0";
$resultado = $conn->query($consulta);
$posDisponibles = [];
if ($resultado->num_rows > 0) {
    while ($fila = $resultado->fetch_assoc()) {
        $posDisponibles[] = $fila['ID_Pos'];
    }
}

//echo $_SESSION['matricula'];





$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selecciona una vista</title>
    <link rel="stylesheet" href="roles.css">
    <script>
        // Función para validar si la matrícula está presente en la sesión antes de ir a POS
        function validarMatricula() {
            var matricula = "<?php echo isset($_SESSION['matricula']) ? $_SESSION['matricula'] : ''; ?>";
            if (!matricula) {
                alert("No ha seleccionado un encargado. Por favor, ingrese una matrícula.");
                return false;  // Evita que el formulario se envíe
            }
            abrirModal(); 
            return false; 
        }

        // Función para abrir el modal
        function abrirModal() {
            document.getElementById('miModal').style.display = 'block';
        }

        // Función para cerrar el modal
        function cerrarModal() {
            document.getElementById('miModal').style.display = 'none';
        }

        // Función para enviar el ID del POS seleccionado
        function enviarPOS(posId) {
            var form = document.getElementById('form-pos');
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'posSeleccionado'; 
            input.value = posId;// Asigna el valor del POS seleccionado
            form.appendChild(input);
            
            // Almacenar ID_Pos en la sesión
            var posSessionInput = document.createElement('input');
            posSessionInput.type = 'hidden';
            posSessionInput.name = 'sessionPos';
            posSessionInput.value = posId;
            form.appendChild(posSessionInput);

            form.submit();                    
            cerrarModal();                  
        }
    </script>
</head>
<body>

    <img src="mcdonalds.png" class="mclogo" alt=""> 
    <h1>Selecciona una vista para continuar</h1>

    <div class="roles-main"> 
        <!-- Formulario para seleccionar POS -->
        <form id="form-pos" action="pos.php" method="post" onsubmit="return validarMatricula();">
            <input type="hidden" name="matricula" value="<?php echo isset($_SESSION['matricula']) ? htmlspecialchars($_SESSION['matricula']) : ''; ?>">
            <button type="submit" class="botn">POS (Punto de Venta)</button>
           
            <div id="miModal">
                <div class="modal-content">
                    <span class="close" onclick="cerrarModal()">&times;</span>
                    <h3>¿Qué POS soy?</h3>
                    <ul>
                        <?php
                        $_SESSION['clave'] = 0;
                        if (empty($posDisponibles)) {
                            echo "<li>No hay POS disponibles.</li>";
                        } else {
                            foreach ($posDisponibles as $pos) {
                                echo "<li><button type='button' class='enviar' onclick='enviarPOS($pos)'>POS " . $pos . "</button></li>";
                            }
                        }
                        ?>
                    </ul>
                </div>
            </div>
        </form>
        
        <br>

        <!-- Botón para ir a la pantalla de turnos -->
        <form action="turnos.php" method="get">
            <button type="submit" class="botn">Pantalla de Turnos</button>
        </form>
        <br>

        <!-- Botón para ir a la pantalla del cocinero -->
        <form action="cocina.php" method="get">
            <button type="submit" class="botn">Pantalla del Cocinero</button>
        </form>
        <br>
    </div>

    <br>

    <div>  <!-- Formulario para ingresar la matrícula del gerente -->
        <form action="gerente.php" method="post">
            <label for="matricula">Matrícula del Gerente: </label>
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="text" id="matricula" name="matricula" class="matricula" required> <!-- autocomplete="off" -->
            <button type="submit" class="ingresar">Ingresar</button>
        </form>
    </div>

    <br><br>

    <div>
        Gerente Asignado: <?php echo $nombreCompleto; ?>
    </div>

</body>
</html>
