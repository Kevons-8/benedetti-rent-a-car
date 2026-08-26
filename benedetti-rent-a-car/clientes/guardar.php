<?php
include("../config/conexion.php");

$tipo_documento = $_POST['tipo_documento'];
$numero_documento = $_POST['numero_documento'];
$nombre = $_POST['nombre'];
$apellido = $_POST['apellido'];
$telefono = $_POST['telefono'];
$correo = $_POST['correo'];
$direccion = $_POST['direccion'];
$licencia_conduccion = $_POST['licencia_conduccion'];

$sql = "INSERT INTO clientes (
            tipo_documento,
            numero_documento,
            nombre,
            apellido,
            telefono,
            correo,
            direccion,
            licencia_conduccion
        ) VALUES (
            '$tipo_documento',
            '$numero_documento',
            '$nombre',
            '$apellido',
            '$telefono',
            '$correo',
            '$direccion',
            '$licencia_conduccion'
        )";

if ($conn->query($sql) === TRUE) {
    header("Location: index.php");
    exit();
} else {
    echo "Error al guardar el cliente: " . $conn->error;
}
?>
