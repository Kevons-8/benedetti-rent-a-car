<?php
require_once "../../includes/auth.php";
require_once "../../config/database.php";

try {
    $sql = "SELECT * FROM vehiculos ORDER BY id_vehiculo DESC";
    $stmt = $conexion->prepare($sql);
    $stmt->execute();
    $vehiculos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al obtener vehículos: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Vehículos</title>
</head>
<body>

    <h1>Lista de Vehículos</h1>

    <a href="crear.php">Registrar nuevo vehículo</a>
    <br><br>

    <a href="../../admin/dashboard.php">Volver al Dashboard</a>
    <br><br>

    <?php if (count($vehiculos) > 0): ?>
        <table border="1" cellpadding="10" cellspacing="0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Código</th>
                    <th>Marca</th>
                    <th>Modelo</th>
                    <th>Color</th>
                    <th>Capacidad</th>
                    <th>Transmisión</th>
                    <th>Categoría</th>
                    <th>Año</th>
                    <th>Placa</th>
                    <th>Precio por día</th>
                    <th>Precio desde día 3</th>
                    <th>Estado</th>
                    <th>Creado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($vehiculos as $vehiculo): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($vehiculo["id_vehiculo"]); ?></td>
                        <td><?php echo htmlspecialchars($vehiculo["codigo_vehiculo"]); ?></td>
                        <td><?php echo htmlspecialchars($vehiculo["marca"]); ?></td>
                        <td><?php echo htmlspecialchars($vehiculo["modelo"]); ?></td>
                        <td><?php echo htmlspecialchars($vehiculo["color"]); ?></td>
                        <td><?php echo htmlspecialchars($vehiculo["capacidad"]); ?></td>
                        <td><?php echo htmlspecialchars($vehiculo["transmision"]); ?></td>
                        <td><?php echo htmlspecialchars($vehiculo["categoria"]); ?></td>
                        <td><?php echo htmlspecialchars($vehiculo["anio"]); ?></td>
                        <td><?php echo htmlspecialchars($vehiculo["placa"]); ?></td>
                        <td><?php echo htmlspecialchars($vehiculo["precio_dia"]); ?></td>
                        <td>
                            <?php
                            if ($vehiculo["precio_especial_3_dias"] !== null) {
                                echo htmlspecialchars($vehiculo["precio_especial_3_dias"]);
                            } else {
                                echo "-";
                            }
                            ?>
                        </td>
                        <td><?php echo htmlspecialchars($vehiculo["estado"]); ?></td>
                        <td><?php echo htmlspecialchars($vehiculo["created_at"]); ?></td>
                        <td>
                            <a href="editar.php?id=<?php echo $vehiculo['id_vehiculo']; ?>">Editar</a> |
                            <a href="eliminar.php?id=<?php echo $vehiculo['id_vehiculo']; ?>" onclick="return confirm('¿Seguro que quieres eliminar este vehículo?');">Eliminar</a> |
                            <a href="disponibilidad.php?id=<?php echo $vehiculo['id_vehiculo']; ?>">Disponibilidad</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No hay vehículos registrados.</p>
    <?php endif; ?>

</body>
</html>