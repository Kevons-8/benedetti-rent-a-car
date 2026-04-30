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
    <title>Lista de Vehículos | Benedetti Rent a Car</title>
    <link rel="stylesheet" href="/benedetti-rent-a-car/assets/css/style.css">
</head>

<body class="admin-vehicle-page">

<main class="admin-vehicle-bg">
    <div class="admin-vehicle-overlay"></div>

    <section class="admin-vehicle-container">

        <div class="admin-vehicle-header">
            <div>
                <span class="admin-vehicle-badge">Inventario administrativo</span>
                <h1>Lista de vehículos</h1>
                <p>Consulta rápidamente el inventario y abre la ficha completa de cada vehículo.</p>
            </div>

            <div class="admin-vehicle-actions">
                <a href="crear.php" class="admin-btn admin-btn-primary">Registrar vehículo</a>
                <a href="../../admin/dashboard.php" class="admin-btn admin-btn-dark">Dashboard</a>
            </div>
        </div>

        <div class="admin-table-card">
            <div class="admin-table-header">
                <h2>Vehículos registrados</h2>
                <p>Total de vehículos: <?php echo count($vehiculos); ?></p>
            </div>

            <?php if (count($vehiculos) > 0): ?>
                <div class="admin-table-wrap admin-table-wrap-compact">
                    <table class="admin-table admin-table-compact">
                        <thead>
                            <tr>
                                <th>Imagen</th>
                                <th>Código</th>
                                <th>Vehículo</th>
                                <th>Placa</th>
                                <th>Estado</th>
                                <th>Precio día</th>
                                <th>Ver</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($vehiculos as $vehiculo): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($vehiculo["imagen"])): ?>
                                            <img
                                                src="/benedetti-rent-a-car/assets/img/vehiculos/<?php echo htmlspecialchars($vehiculo["imagen"]); ?>"
                                                alt="<?php echo htmlspecialchars($vehiculo["marca"] . ' ' . $vehiculo["modelo"]); ?>"
                                                class="admin-vehicle-thumb"
                                            >
                                        <?php else: ?>
                                            <div class="admin-no-image">Sin imagen</div>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <strong><?php echo htmlspecialchars($vehiculo["codigo_vehiculo"]); ?></strong>
                                    </td>

                                    <td>
                                        <a href="ver.php?id=<?php echo $vehiculo['id_vehiculo']; ?>" class="vehicle-name-link">
                                            <?php echo htmlspecialchars($vehiculo["marca"]); ?>
                                        </a><br>
                                        <span><?php echo htmlspecialchars($vehiculo["modelo"]); ?></span>
                                    </td>

                                    <td><?php echo htmlspecialchars($vehiculo["placa"]); ?></td>

                                    <td>
                                        <span class="admin-status admin-status-<?php echo htmlspecialchars($vehiculo["estado"]); ?>">
                                            <?php echo htmlspecialchars(ucfirst($vehiculo["estado"])); ?>
                                        </span>
                                    </td>

                                    <td>
                                        $<?php echo number_format((float)$vehiculo["precio_dia"], 0, ',', '.'); ?>
                                    </td>

                                    <td>
                                        <a href="ver.php?id=<?php echo $vehiculo['id_vehiculo']; ?>" class="admin-action availability">
                                            Ver ficha
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            <?php else: ?>
                <div class="admin-empty-box">
                    <h3>No hay vehículos registrados</h3>
                    <p>Registra tu primer vehículo para comenzar.</p>
                    <a href="crear.php" class="admin-btn admin-btn-primary">Registrar vehículo</a>
                </div>
            <?php endif; ?>
        </div>

    </section>
</main>

</body>
</html>