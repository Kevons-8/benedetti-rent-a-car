<?php
include("../config/conexion.php");

$id_cliente = $_POST['id_cliente'];
$tipo_documento = $_POST['tipo_documento'];
$numero_documento = $_POST['numero_documento'];
$nombre = $_POST['nombre'];
$apellido = $_POST['apellido'];
$telefono = $_POST['telefono'];
$correo = $_POST['correo'];
$direccion = $_POST['direccion'];
$licencia_conduccion = $_POST['licencia_conduccion'];

$sql = "UPDATE clientes SET
            tipo_documento = '$tipo_documento',
            numero_documento = '$numero_documento',
            nombre = '$nombre',
            apellido = '$apellido',
            telefono = '$telefono',
            correo = '$correo',
            direccion = '$direccion',
            licencia_conduccion = '$licencia_conduccion'
        WHERE id_cliente = $id_cliente";

if ($conn->query($sql) === TRUE) {
    header("Location: index.php");
    exit();
} else {
    echo "Error al actualizar el cliente: " . $conn->error;
}
?>
