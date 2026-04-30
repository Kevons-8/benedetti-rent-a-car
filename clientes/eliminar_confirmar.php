<?php
include("../config/conexion.php");

if (!isset($_POST['id_cliente']) || empty($_POST['id_cliente'])) {
    header("Location: index.php");
    exit();
}

$id_cliente = intval($_POST['id_cliente']);

$sql = "DELETE FROM clientes WHERE id_cliente = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_cliente);

if ($stmt->execute()) {
    header("Location: index.php");
    exit();
} else {
    echo "Error al eliminar el cliente: " . $conn->error;
}
?>