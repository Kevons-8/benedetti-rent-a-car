<?php
require_once "../../includes/auth.php";
require_once "../../config/database.php";

try {
    $sql = "SELECT 
                reservas.id_reserva,
                reservas.codigo_reserva,
                reservas.fecha_inicio,
                reservas.fecha_fin,
                reservas.estado_reserva,
                reservas.total_pago,
                clientes.nombre,
                clientes.apellido,
                vehiculos.marca,
                vehiculos.modelo
            FROM reservas
            INNER JOIN clientes ON reservas.id_cliente = clientes.id_cliente
            INNER JOIN vehiculos ON reservas.id_vehiculo = vehiculos.id_vehiculo
            ORDER BY reservas.id_reserva DESC";

    $stmt = $conexion->prepare($sql);
    $stmt->execute();
    $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error al obtener reservas: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Reservas</title>
</head>
<body>

    <h1>Lista de Reservas</h1>

    <a href="crear.php">Crear nueva reserva</a>
    <br><br>

    <a href="../../admin/dashboard.php">Volver al Dashboard</a>
    <br><br>

    <?php if (count($reservas) > 0): ?>
        <table border="1" cellpadding="10" cellspacing="0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Código Reserva</th>
                    <th>Cliente</th>
                    <th>Vehículo</th>
                    <th>Fecha Inicio</th>
                    <th>Fecha Fin</th>
                    <th>Estado</th>
                    <th>Total Pago</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reservas as $reserva): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($reserva["id_reserva"]); ?></td>
                        <td><?php echo htmlspecialchars($reserva["codigo_reserva"]); ?></td>
                        <td><?php echo htmlspecialchars($reserva["nombre"] . " " . $reserva["apellido"]); ?></td>
                        <td><?php echo htmlspecialchars($reserva["marca"] . " " . $reserva["modelo"]); ?></td>
                        <td><?php echo htmlspecialchars($reserva["fecha_inicio"]); ?></td>
                        <td><?php echo htmlspecialchars($reserva["fecha_fin"]); ?></td>
                        <td><?php echo htmlspecialchars($reserva["estado_reserva"]); ?></td>
                        <td>$<?php echo number_format((float)$reserva["total_pago"], 0, ',', '.'); ?></td>
                        <td>
    <a href="ver.php?id=<?php echo $reserva['id_reserva']; ?>">Ver detalle</a>
    |
    <a href="finalizar.php?id=<?php echo $reserva['id_reserva']; ?>">
        <?php echo ($reserva["estado_reserva"] === "finalizada") ? "Revisar / Ajustar Cobro" : "Finalizar / Cobro"; ?>
    </a>
    |
    <a href="eliminar.php?id=<?php echo $reserva['id_reserva']; ?>"
       onclick="return confirm('¿Seguro que deseas eliminar esta reserva? Esta acción liberará el vehículo si corresponde.');">
       Eliminar
    </a>
</td>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No hay reservas registradas.</p>
    <?php endif; ?>

</body>
</html>