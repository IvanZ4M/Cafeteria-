<?php
session_start();
// Conexión a la base de datos
include '../config/db.php'; 
// Verificar si se ha enviado el ID_Pos
if (isset($_POST['sessionPos'])) {
    $_SESSION['ID_Pos'] = $_POST['sessionPos']; // Almacenar el ID_Pos en la sesión
}

// Utiliza el ID_Pos almacenado en la sesión para crear órdenes
if (isset($_SESSION['ID_Pos'])) {
    $id_pos = $_SESSION['ID_Pos'];
} else {
    die("No se ha encontrado el ID del POS en la sesión.");
}



// Obtener el ID_Pos y su estado
$query_id_pos = "SELECT status, uso FROM pos WHERE ID_Pos = $id_pos LIMIT 1";
$result_id_pos = $conn->query($query_id_pos);
if ($result_id_pos->num_rows > 0) {
    $pos_data = $result_id_pos->fetch_assoc();
    $id_sta = $pos_data['status'];
    $uso = $pos_data['uso'];
} else {
    die("No se encontró el ID del POS.");
}

// Verificar si pos es 0
if ($id_sta == 0) {
    echo "<h1 style='color:red; text-align:center;'>POS no disponible</h1>";
    header("refresh:5; url=menu.php");
    exit();
}
// Verificar si el POS está disponible
if (isset($_SESSION['clave'])) {
    $clave = $_SESSION['clave'];
} else {
    $clave = 0; // O asignar un valor por defecto si no existe
}

if ($uso == 1 && $clave == 0) {
   
    echo "<h1 style='color:red; text-align:center;'>POS ya está en uso por otro equipo</h1>";
    header("refresh:5; url=menu.php");
    exit();
}
$_SESSION['clave'] = 1; // Almacenar el nuevo valor en la sesión

// Cambiar el estado de uso a 1
$update_status_query = "UPDATE pos SET uso = 1 WHERE ID_Pos = ?";
$stmt_update = $conn->prepare($update_status_query);
$stmt_update->bind_param("i", $id_pos);
$stmt_update->execute();
$stmt_update->close();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['combo'])) {
    $selected_combos = $_POST['combo']; 
    $quantities = $_POST['quantities']; 

    // Validar que los combos y cantidades sean arrays y que no estén vacíos
    if (!is_array($selected_combos) || !is_array($quantities) || empty($selected_combos) || empty($quantities)) {
        die("Error: datos inválidos.");
    }

    // Iniciar una transacción
    $conn->begin_transaction();

    try {
        // Bloquear la fila que contiene el turno hasta que la transacción se complete
        $query_turno = "SELECT COALESCE(Turno, 0) AS LastTurno FROM orden ORDER BY ID_Orden DESC LIMIT 1 FOR UPDATE";
        $result_turno = $conn->query($query_turno);
        $turno = $result_turno->fetch_assoc()['LastTurno'];

        // Reiniciar turnos si llegan a 1000
        if ($turno >= 999) {
             $turno = 1;
        } else {
             $turno += 1; // Incrementar para el siguiente turno
        }


       

        $estado = 'pendiente'; // Estado inicial
        $total = 0; 

        // Preparar la consulta para obtener los precios de los combos
        $query_combo = "SELECT Precio FROM combo WHERE ID_Combo = ?";
        $stmt_combo = $conn->prepare($query_combo);

        foreach ($selected_combos as $index => $selected_combo) {
            $cantidad = (int)$quantities[$index]; // Asegurar que la cantidad sea un número entero
            if ($cantidad <= 0) {
                continue; // Saltar combos con cantidad 0
            }

            // Ejecutar consulta para obtener el precio del combo
            $stmt_combo->bind_param("i", $selected_combo);
            $stmt_combo->execute();
            $stmt_combo->bind_result($precio);
            if ($stmt_combo->fetch()) {
                $total += $precio * $cantidad;
            } else {
                throw new Exception("El combo con ID $selected_combo no existe.");
            }
        }
        $stmt_combo->close();

        // Insertar la orden
        $insert_order_query = "INSERT INTO orden (Turno, ID_Pos, Estado, Total) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_order_query);
        $stmt->bind_param("iisd", $turno, $id_pos, $estado, $total);
        $stmt->execute();
        $id_orden = $stmt->insert_id; // Obtener el ID de la orden creada

        // Preparar la consulta para insertar en com_ord
        $insert_com_ord_query = "INSERT INTO com_ord (ID_Orden, ID_Combo, cantidad) VALUES (?, ?, ?)";
        $stmt_com_ord = $conn->prepare($insert_com_ord_query);

        foreach ($selected_combos as $index => $selected_combo) {
            $cantidad = (int)$quantities[$index];
            if ($cantidad > 0) {
                $stmt_com_ord->bind_param("iii", $id_orden, $selected_combo, $cantidad);
                $stmt_com_ord->execute();

                // Insertar en la tabla pos_com si no existe
                $check_pos_com_query = "SELECT COUNT(*) FROM pos_com WHERE ID_Combo = ? AND ID_Pos = ?";
                $stmt_check = $conn->prepare($check_pos_com_query);
                $stmt_check->bind_param("ii", $selected_combo, $id_pos);
                $stmt_check->execute();
                $stmt_check->bind_result($count);
                $stmt_check->fetch();
                $stmt_check->close();

                if ($count == 0) {
                    $insert_pos_com_query = "INSERT INTO pos_com (ID_Combo, ID_Pos) VALUES (?, ?)";
                    $stmt_pos_com = $conn->prepare($insert_pos_com_query);
                    $stmt_pos_com->bind_param("ii", $selected_combo, $id_pos);
                    $stmt_pos_com->execute();
                    $stmt_pos_com->close();
                }
            }
        }

        $stmt_com_ord->close();

        // Insertar el ID de empleado en ord_emp
        $id_empleado = $_SESSION['matricula'];
        $insert_ord_emp_query = "INSERT INTO ord_emp (ID_Empleado, ID_Orden) VALUES (?, ?)";
        $stmt_ord_emp = $conn->prepare($insert_ord_emp_query);
        $stmt_ord_emp->bind_param("ii", $id_empleado, $id_orden);
        $stmt_ord_emp->execute();
        $stmt_ord_emp->close();

        // Commit de la transacción
        $conn->commit();

        // Redirigir a la página de confirmación
        header("Location: pos2.php?orden=" . $id_orden);
        exit();

    } catch (Exception $e) {
        // Revertir la transacción si hay algún error
        $conn->rollback();
        echo "Error al procesar la orden: " . $e->getMessage();
    }
}


// Obtener combos disponibles
$query = "SELECT * FROM combo";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS - Selección de Combo</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            background-color: #dfdecbe6;
            margin : 0;
            padding: 0;
        }
        h1 {
            margin: 50px 0;
            font-size: 49px;
            color: #333;
            margin-top:10px;
        }
        .combo-container {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            margin-bottom: 30px;
        }
        .combo-list {
            list-style-type: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        .combo-item {
            background-color: #f5a623;
            padding: 15px;
            border-radius: 10px;
            font-size: 18px;
            color: white;
            cursor: pointer;
            transition: background-color 0.3s ease;
            width: 300px;
            text-align: left;
        }
        .combo-item:hover {
            background-color: #f58520;
        }
        .combo-item.selected {
            background-color: #d83232;
        }
        .pay-btn {
            background-color: #d83232;
            border: none;
            padding: 15px 40px;
            font-size: 18px;
            color: white;
            border-radius: 10px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .pay-btn:hover {
            background-color: #900C3F;
        }
        .form-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f2e2;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .divmayor {
            display: flex;
           /* justify-content: space-between;
            align-items: center;*/
            margin-bottom: 10px;
        }
        .quantity-container {
            display: flex;
            align-items: center;
            justify-content: space-around;
            display: none;
            margin-left: 10px;
          
        }
        .quantity-btn {
            background-color: #f5a623;
            border: none;
            padding: 10px;
            color: white;
            cursor: pointer;
            font-size: 18px;
            border-radius: 5px;
        }
        .quantity-btn:hover {
            background-color: #f58520;
        }
        .quantity-display {
            width: 30px;
            text-align: center;
            font-size: 18px;
        }
        .mclogo{
            width: 20vh;
            height: 17vh;
            margin-top: 15px;
        }

@media (max-width: 500px) {
    .combo-container {
            display:flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            margin-bottom: 30px;
        }
        .combo-list {
            list-style-type: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        .combo-item {
        background-color: #f5a623;
        padding: 15px;
        border-radius: 10px;
        font-size: 18px;
        color: white;
        cursor: pointer;
        transition: background-color 0.3s ease;
        width: 80%;
        text-align: left;
    }
}
    </style>
</head>
<body>

    <img src="mcdonalds.png" class="mclogo" alt=""> 
    <h1>¿Qué te gustaría pedir?</h1>

    <div class="form-container">
        <form action="" method="POST" id="orderForm">
            <div class="combo-container">
                <label for="combo" style="font-size: 22px; color: #333; margin-bottom: 15px;">Selecciona tu combo</label>
                <ul class="combo-list">
                    <?php while ($row = $result->fetch_assoc()) { ?>
                        <div class="divmayor">
                            <img src="<?php echo $row['Imagen']; ?>" alt="Imagen del combo" class="combo-img" style="width:50px; height:50px; margin-right:10px;">
                            <li class="combo-item" data-id="<?php echo $row['ID_Combo']; ?>">
                                <strong><?php echo $row['Nombre']; ?> - $<?php echo $row['Precio']; ?></strong>
                                <div class="combo-description"><?php echo $row['Productos']; ?></div>
                            </li>
                            <div class="quantity-container">
                                <button type="button" class="quantity-btn decrement">-</button>
                                <span class="quantity-display">1</span>
                                <button type="button" class="quantity-btn increment">+</button>
                                <input type="hidden" name="quantities[]" value="1">
                            </div>
                            <input type="hidden" name="combo[]" value="<?php echo $row['ID_Combo']; ?>" class="combo-hidden">
                        </div>
                    <?php } ?>
                </ul>
            </div>
            <button type="submit" class="pay-btn">Pagar</button>
        </form>
    </div>

    <script>
    const comboItems = document.querySelectorAll('.combo-item');
    const orderForm = document.getElementById('orderForm');

    comboItems.forEach((comboItem) => {
        const quantityContainer = comboItem.closest('.divmayor').querySelector('.quantity-container');
        const quantityDisplay = quantityContainer.querySelector('.quantity-display');
        const decrementBtn = quantityContainer.querySelector('.decrement');
        const incrementBtn = quantityContainer.querySelector('.increment');
        const quantityInput = quantityContainer.querySelector('input[name="quantities[]"]');

        comboItem.addEventListener('click', () => {
            comboItem.classList.toggle('selected');
            const isSelected = comboItem.classList.contains('selected');
            quantityContainer.style.display = isSelected ? 'flex' : 'none';
            
            if (isSelected) {
               // Si es 0 (de una selección previa), la restablecemos a 1
                if (quantityDisplay.textContent === '0') {
                    quantityDisplay.textContent = '1';
                    quantityInput.value = '1';
                }
            } else {
                // Si el combo se deselecciona, mantenemos la cantidad actual pero escondemos el contador
                quantityDisplay.textContent = '1'; // Al deseleccionar, siempre vuelve a 1
                quantityInput.value = '0'; // Establecer el valor oculto a 0 para evitar que se envíe
            }
        });

        decrementBtn.addEventListener('click', () => {
            let currentQuantity = parseInt(quantityDisplay.textContent);
            // Asegurar que la cantidad nunca sea menor que 1
            if (currentQuantity > 1) {
                currentQuantity--;
                quantityDisplay.textContent = currentQuantity;
                quantityInput.value = currentQuantity;
            }
        });

        incrementBtn.addEventListener('click', () => {
            let currentQuantity = parseInt(quantityDisplay.textContent);
            if (currentQuantity < 500) { 
            currentQuantity++;
            quantityDisplay.textContent = currentQuantity;
            quantityInput.value = currentQuantity;
        }
        });
    });

    

    // Eliminar combos no seleccionados antes de enviar el formulario
    orderForm.addEventListener('submit', (event) => {
        let notcombo = false;
        comboItems.forEach((comboItem) => {
            const isSelected = comboItem.classList.contains('selected');
            const comboHiddenInput = comboItem.closest('.divmayor').querySelector('.combo-hidden');
            const quantityContainer = comboItem.closest('.divmayor').querySelector('.quantity-container');
            const selectedCombos = document.querySelectorAll('.combo-item.selected');
          
            if (!isSelected) {
                // Eliminar el input de combo y la cantidad si no está seleccionado
                comboHiddenInput.remove();
                quantityContainer.remove();
            }
           
        if (selectedCombos.length === 0) {
         notcombo= true;
        }
            
        });
        if (notcombo==true){
            alert('Por favor, selecciona al menos un combo antes de proceder con el pago.');
        }
    });
</script>



</body>
</html>
