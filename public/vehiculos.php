<?php
$base = (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false)
    ? '/benedetti-rent-a-car'
    : '';

if ($base === '') {
    require_once __DIR__ . '/../config/conexion.php';
    $stmt = $conexion->prepare("SELECT * FROM vehiculos WHERE estado = 'disponible' ORDER BY id_vehiculo ASC");
    $stmt->execute();
    $vehiculos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    require_once __DIR__ . '/../config/database.php';
    $stmt = $conn->prepare("SELECT * FROM vehiculos WHERE estado = 'disponible' ORDER BY id_vehiculo ASC");
    $stmt->execute();
    $resultado = $stmt->get_result();
    $vehiculos = $resultado->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

require_once __DIR__ . '/../views/partials/header.php';
require_once __DIR__ . '/../views/partials/navbar.php';
?>

<main>

    <section class="vehicles-hero" style="background-image: url('<?php echo $base; ?>/assets/img/fondo_vehiculos.png');">
        <div class="vehicles-hero-overlay"></div>
        <div class="container vehicles-hero-content">
            <span class="vehicles-badge">Catálogo Benedetti Rent a Car</span>
            <h1>Encuentra el vehículo perfecto para ti</h1>
            <p>Explora nuestro catálogo y elige el vehículo ideal para tu viaje en Barranquilla.</p>
        </div>
    </section>

    <section class="vehicles-page-section">
        <div class="vehicles-section-overlay"></div>
        <div class="container vehicles-section-content">

            <div class="section-header vehicles-header">
                <h1>Vehículos disponibles para tu viaje</h1>
                <p class="catalogo-texto">
                    Conoce nuestros vehículos disponibles en Barranquilla. Elige el que mejor se adapte a tu necesidad.
                </p>
            </div>

            <?php if (!empty($vehiculos)): ?>
                <div class="vehicle-grid vehicle-grid-premium">
                    <?php foreach ($vehiculos as $vehiculo): ?>
                        <?php
                            $marcaModelo = trim(($vehiculo['marca'] ?? '') . ' ' . ($vehiculo['modelo'] ?? ''));
                            $imagenVehiculo = !empty($vehiculo['imagen']) ? $vehiculo['imagen'] : null;
                        ?>
                        <article class="vehiculo-card vehiculo-card-premium hover-lift">

                            <div class="vehiculo-img-wrap">
                                <?php if ($imagenVehiculo): ?>
                                    <img
                                        src="<?php echo $base; ?>/assets/img/vehiculos/<?php echo htmlspecialchars($imagenVehiculo); ?>"
                                        alt="<?php echo htmlspecialchars($marcaModelo); ?>"
                                        class="vehiculo-img"
                                    >
                                <?php else: ?>
                                    <div class="vehiculo-sin-imagen">Imagen no disponible</div>
                                <?php endif; ?>
                            </div>

                            <div class="vehiculo-info vehiculo-info-premium">
                                <h3><?php echo htmlspecialchars($marcaModelo); ?></h3>

                                <div class="vehiculo-meta">
                                    <span><?php echo htmlspecialchars($vehiculo['capacidad'] ?? ''); ?> personas</span>
                                    <span><?php echo htmlspecialchars($vehiculo['transmision'] ?? ''); ?></span>
                                    <span><?php echo htmlspecialchars($vehiculo['anio'] ?? ''); ?></span>
                                </div>

                                <p><strong>Categoría:</strong> <?php echo htmlspecialchars($vehiculo['categoria'] ?? ''); ?></p>

                                <p class="estado-vehiculo">
                                    <strong>Estado:</strong>
                                    <span class="<?php echo ($vehiculo['estado'] === 'disponible') ? 'estado-disponible' : 'estado-no-disponible'; ?>">
                                        <?php echo htmlspecialchars(ucfirst($vehiculo['estado'] ?? '')); ?>
                                    </span>
                                </p>

                                <div class="vehiculo-footer">
                                    <p class="precio">
                                        $<?php echo number_format((float)$vehiculo['precio_dia'], 0, ',', '.'); ?>
                                        <span>/ día</span>
                                    </p>
                                    <a href="<?php echo $base; ?>/public/reserva.php?id_vehiculo=<?php echo urlencode($vehiculo['id_vehiculo']); ?>" class="btn btn-primary">
                                        Reservar
                                    </a>
                                </div>
                            </div>

                        </article>
                    <?php endforeach; ?>
                </div>

            <?php else: ?>
                <div class="placeholder-box">
                    <p>No hay vehículos disponibles en este momento.</p>
                </div>
            <?php endif; ?>

        </div>
    </section>

</main>

<?php require_once __DIR__ . '/../views/partials/footer.php'; ?>