<?php
require_once "../../includes/auth.php";
require_once "../../config/database.php";

if (!isset($_GET["id"])) {
    die("ID de reserva no especificado.");
}

$id_reserva = $_GET["id"];

try {
    /* ==========================
    OBTENER LA RESERVA
    ========================== */
    $sqlReserva = "SELECT id_reserva, id_vehiculo, estado_reserva 
                   FROM reservas 
                   WHERE id_reserva = :id_reserva";
    $stmtReserva = $conexion->prepare($sqlReserva);
    $stmtReserva->bindParam(':id_reserva', $id_reserva);
    $stmtReserva->execute();

    $reserva = $stmtReserva->fetch(PDO::FETCH_ASSOC);

    if (!$reserva) {
        die("Reserva no encontrada.");
    }

    $conexion->beginTransaction();

    /* ==========================
    ELIMINAR RESERVA
    ========================== */
    $sqlEliminar = "DELETE FROM reservas WHERE id_reserva = :id_reserva";
    $stmtEliminar = $conexion->prepare($sqlEliminar);
    $stmtEliminar->bindParam(':id_reserva', $id_reserva);
    $stmtEliminar->execute();

    /* ==========================
    LIBERAR VEHICULO
    ========================== */
    $sqlVehiculo = "UPDATE vehiculos 
                    SET estado = 'disponible' 
                    WHERE id_vehiculo = :id_vehiculo";
    $stmtVehiculo = $conexion->prepare($sqlVehiculo);
    $stmtVehiculo->bindParam(':id_vehiculo', $reserva["id_vehiculo"]);
    $stmtVehiculo->execute();

    $conexion->commit();

    header("Location: listar.php");
    exit();

} catch (PDOException $e) {
    if ($conexion->inTransaction()) {
        $conexion->rollBack();
    }
    die("Error al eliminar la reserva: " . $e->getMessage());
}
?>