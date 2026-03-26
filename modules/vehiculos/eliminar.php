<?php
require_once "../../includes/auth.php";
require_once "../../config/database.php";

if (!isset($_GET["id"])) {
    die("ID no especificado.");
}

$id = $_GET["id"];

try {

    $sql = "DELETE FROM vehiculos WHERE id_vehiculo = :id";
    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    header("Location: listar.php");
    exit();

} catch (PDOException $e) {

    die("Error al eliminar vehículo: " . $e->getMessage());

}
?>
