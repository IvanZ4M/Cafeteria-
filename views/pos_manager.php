<?php
// Conexión a la base de datos
$conn = new mysqli("localhost", "root", "", "mc");

// Verificar si se ha enviado la acción para el POS
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_pos = $_POST['pos_id'];
    $accion = $_POST['accion'];

    // Determinar si se debe activar o desactivar el POS
    if ($accion == 'activar') {
        $query = "UPDATE pos SET Status = 1 WHERE ID_Pos = ?";
    } else {
        $query = "UPDATE pos SET Status = 0 WHERE ID_Pos = ?";
    }

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_pos);
    $stmt->execute();

    // Verificar si la operación fue exitosa
    if ($stmt->affected_rows > 0) {
        echo "<p>POS actualizado correctamente.</p>";
    } else {
        echo "<p>No se encontró el POS con el ID ingresado.</p>";
    }
}
header("Refresh: 5; url=gerente.php");
exit;

?>
