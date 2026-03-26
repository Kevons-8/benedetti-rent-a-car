<?php
include("../config/conexion.php");

$id = $_GET['id'];

$sql = "DELETE FROM clientes WHERE id_cliente = $id";

if ($conn->query($sql) === TRUE) {
    header("Location: index.php");
    exit();
} else {
    echo "Error al eliminar el cliente: " . $conn->error;
}
?>
