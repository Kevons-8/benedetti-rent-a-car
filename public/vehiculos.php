<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../views/partials/header.php';
require_once __DIR__ . '/../views/partials/navbar.php';

// Consulta para mostrar solo los 3 vehículos que quieres en el catálogo
$sql = "SELECT * FROM vehiculos 
        WHERE (marca = 'Renault' AND modelo = 'Kwid')
           OR (marca = 'Kia' AND modelo = 'Picanto')
           OR (marca = 'Suzuki' AND modelo LIKE '%Swift%')
        ORDER BY FIELD(marca, 'Renault', 'Kia', 'Suzuki')";

$stmt = $conexion->prepare($sql);
$stmt->execute();
$vehiculos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="page-section">
    <div class="container">
        <h1>Vehículos disponibles para tu viaje</h1>
        <p class="catalogo-texto">
            Conoce nuestros vehículos disponibles en Barranquilla. Elige el que mejor se adapte a tu necesidad.
        </p>

        <div class="vehiculos-grid">

            <?php if (count($vehiculos) > 0): ?>
                <?php foreach ($vehiculos as $vehiculo): ?>

                    <div class="vehiculo-card">

                        <?php if (!empty($vehiculo['imagen'])): ?>
                            <img
                                src="/benedetti-rent-a-car/assets/img/<?php echo htmlspecialchars($vehiculo['imagen']); ?>"
                                alt="<?php echo htmlspecialchars($vehiculo['marca'] . ' ' . $vehiculo['modelo']); ?>"
                                class="vehiculo-img"
                            >
                        <?php else: ?>
                            <div class="vehiculo-sin-imagen">Sin imagen disponible</div>
                        <?php endif; ?>

                        <div class="vehiculo-info">
                            <h3>
                                <?php echo htmlspecialchars($vehiculo['marca'] . ' ' . $vehiculo['modelo']); ?>
                            </h3>

                            <p><strong>Color:</strong> <?php echo htmlspecialchars($vehiculo['color']); ?></p>
                            <p><strong>Capacidad:</strong> <?php echo htmlspecialchars($vehiculo['capacidad']); ?> personas</p>
                            <p><strong>Transmisión:</strong> <?php echo htmlspecialchars($vehiculo['transmision']); ?></p>
                            <p><strong>Categoría:</strong> <?php echo htmlspecialchars($vehiculo['categoria']); ?></p>
                            <p><strong>Año:</strong> <?php echo htmlspecialchars($vehiculo['anio']); ?></p>

                            <p class="precio">
                                $<?php echo number_format($vehiculo['precio_dia'], 0, ',', '.'); ?> / día
                            </p>

                            <p class="estado-vehiculo">
                                <strong>Estado:</strong>
                                <span class="<?php echo ($vehiculo['estado'] === 'disponible') ? 'estado-disponible' : 'estado-no-disponible'; ?>">
                                    <?php echo htmlspecialchars(ucfirst($vehiculo['estado'])); ?>
                                </span>
                            </p>

                            <?php if ($vehiculo['estado'] === 'disponible'): ?>
                                <a href="reserva.php?id_vehiculo=<?php echo urlencode($vehiculo['id_vehiculo']); ?>" class="btn btn-primary">
                                    Reservar
                                </a>
                            <?php else: ?>
                                <button class="btn btn-disabled" disabled>No disponible</button>
                            <?php endif; ?>
                        </div>

                    </div>

                <?php endforeach; ?>
            <?php else: ?>
                <p>No se encontraron vehículos para mostrar en este momento.</p>
            <?php endif; ?>

        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../views/partials/footer.php'; ?>