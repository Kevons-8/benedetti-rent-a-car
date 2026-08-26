<?php
require_once "../../includes/auth.php";
require_once "../../config/database.php";

if (!isset($_GET["id"])) {
    die("ID de vehículo no especificado.");
}

$id = $_GET["id"];

try {
    $sql = "SELECT * FROM vehiculos WHERE id_vehiculo = :id";
    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    $vehiculo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$vehiculo) {
        die("Vehículo no encontrado.");
    }
} catch (PDOException $e) {
    die("Error al obtener vehículo: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ficha del Vehículo | Benedetti Rent a Car</title>
    <link rel="stylesheet" href="/benedetti-rent-a-car/assets/css/style.css">
</head>

<body class="admin-vehicle-page">

<main class="admin-vehicle-bg">
    <div class="admin-vehicle-overlay"></div>

    <section class="admin-vehicle-container">

        <div class="admin-vehicle-header">
            <div>
                <span class="admin-vehicle-badge">Ficha técnica</span>
                <h1>Detalle del vehículo</h1>
                <p>Consulta la información completa del vehículo registrado en el inventario.</p>
            </div>

            <div class="admin-vehicle-actions">
                <a href="listar.php" class="admin-btn admin-btn-secondary">Volver a vehículos</a>
                <a href="../../admin/dashboard.php" class="admin-btn admin-btn-dark">Dashboard</a>
            </div>
        </div>

        <div class="admin-detail-card">

            <div class="admin-detail-preview">
                <?php if (!empty($vehiculo["imagen"])): ?>
                    <img 
                        src="/benedetti-rent-a-car/assets/img/vehiculos/<?php echo htmlspecialchars($vehiculo["imagen"]); ?>" 
                        alt="<?php echo htmlspecialchars($vehiculo["marca"] . " " . $vehiculo["modelo"]); ?>"
                    >
                <?php else: ?>
                    <div class="admin-no-image">Sin imagen</div>
                <?php endif; ?>
            </div>

            <div class="admin-detail-info">
                <span class="admin-detail-status admin-status-<?php echo htmlspecialchars($vehiculo["estado"]); ?>">
                    <?php echo htmlspecialchars(ucfirst($vehiculo["estado"])); ?>
                </span>

                <h2>
                    <?php echo htmlspecialchars($vehiculo["marca"] . " " . $vehiculo["modelo"]); ?>
                </h2>

                <p>
                    Información general, operativa y comercial del vehículo dentro del inventario de Benedetti Rent a Car.
                </p>

                <div class="admin-detail-grid">
                    <p><strong>Código:</strong> <?php echo htmlspecialchars($vehiculo["codigo_vehiculo"]); ?></p>
                    <p><strong>Placa:</strong> <?php echo htmlspecialchars($vehiculo["placa"]); ?></p>
                    <p><strong>Color:</strong> <?php echo htmlspecialchars($vehiculo["color"]); ?></p>
                    <p><strong>Capacidad:</strong> <?php echo htmlspecialchars($vehiculo["capacidad"]); ?> personas</p>
                    <p><strong>Transmisión:</strong> <?php echo htmlspecialchars(ucfirst($vehiculo["transmision"])); ?></p>
                    <p><strong>Categoría:</strong> <?php echo htmlspecialchars(ucfirst($vehiculo["categoria"])); ?></p>
                    <p><strong>Año:</strong> <?php echo htmlspecialchars($vehiculo["anio"]); ?></p>
                    <p><strong>Precio día:</strong> $<?php echo number_format((float)$vehiculo["precio_dia"], 0, ',', '.'); ?></p>
                    <p>
                        <strong>Precio desde día 3:</strong>
                        <?php if ($vehiculo["precio_especial_3_dias"] !== null): ?>
                            $<?php echo number_format((float)$vehiculo["precio_especial_3_dias"], 0, ',', '.'); ?>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </p>
                    <p><strong>Creado:</strong> <?php echo htmlspecialchars($vehiculo["created_at"]); ?></p>
                </div>

                <div class="admin-detail-actions">
                    <a href="editar.php?id=<?php echo $vehiculo['id_vehiculo']; ?>" class="admin-btn admin-btn-primary">
                        Editar vehículo
                    </a>

                    <a href="disponibilidad.php?id=<?php echo $vehiculo['id_vehiculo']; ?>" class="admin-btn admin-btn-secondary">
                        Disponibilidad
                    </a>

                    <a href="eliminar.php?id=<?php echo $vehiculo['id_vehiculo']; ?>" class="admin-btn admin-btn-danger">
                        Eliminar
                    </a>
                </div>
            </div>

        </div>

    </section>
</main>

</body>
</html>