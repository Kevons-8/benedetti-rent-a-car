<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../views/partials/header.php';
require_once __DIR__ . '/../views/partials/navbar.php';

$sql = "SELECT * FROM vehiculos WHERE estado = 'disponible' ORDER BY id_vehiculo ASC";
$stmt = $conexion->prepare($sql);
$stmt->execute();
$vehiculos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<main>

    <!-- HERO DE VEHÍCULOS -->
    <section class="vehicles-hero">
        <div class="vehicles-hero-overlay"></div>

        <div class="container vehicles-hero-content">
            <h1>Encuentra el vehículo perfecto para ti</h1>
            <p>
                Explora nuestro catálogo y elige el vehículo ideal para tu viaje en Barranquilla.
            </p>
        </div>
    </section>

    <!-- LISTADO -->
    <section class="page-section">
        <div class="container">
            <div class="section-header">
                <h1>Vehículos disponibles para tu viaje</h1>
                <p class="catalogo-texto">
                    Conoce nuestros vehículos disponibles en Barranquilla. Elige el que mejor se adapte a tu necesidad.
                </p>
            </div>

            <?php if (!empty($vehiculos)): ?>
                <div class="vehicle-grid">
                    <?php foreach ($vehiculos as $vehiculo): ?>
                        <?php
                            $marcaModelo = trim(($vehiculo['marca'] ?? '') . ' ' . ($vehiculo['modelo'] ?? ''));
                            $imagenVehiculo = !empty($vehiculo['imagen']) ? $vehiculo['imagen'] : null;

                            if (
                                strtolower(trim($vehiculo['marca'] ?? '')) === 'mazda' &&
                                strtolower(trim($vehiculo['modelo'] ?? '')) === '2'
                            ) {
                                $imagenVehiculo = 'Mazda_2_sedan.png';
                            }
                        ?>

                        <div class="vehiculo-card hover-lift">
                            <?php if ($imagenVehiculo): ?>
                                <img
                                    src="/benedetti-rent-a-car/assets/img/<?php echo htmlspecialchars($imagenVehiculo); ?>"
                                    alt="<?php echo htmlspecialchars($marcaModelo); ?>"
                                    class="vehiculo-img"
                                >
                            <?php else: ?>
                                <div class="vehiculo-sin-imagen">
                                    Imagen no disponible
                                </div>
                            <?php endif; ?>

                            <div class="vehiculo-info">
                                <h3><?php echo htmlspecialchars($marcaModelo); ?></h3>

                                <p><strong>Capacidad:</strong> <?php echo htmlspecialchars($vehiculo['capacidad']); ?> personas</p>
                                <p><strong>Transmisión:</strong> <?php echo htmlspecialchars($vehiculo['transmision']); ?></p>
                                <p><strong>Categoría:</strong> <?php echo htmlspecialchars($vehiculo['categoria']); ?></p>
                                <p><strong>Año:</strong> <?php echo htmlspecialchars($vehiculo['anio']); ?></p>

                                <p class="precio">
                                    $<?php echo number_format($vehiculo['precio_dia'], 0, ',', '.'); ?> / día
                                </p>

                                <p class="estado-vehiculo">
                                    <strong>Estado:</strong>
                                    <span class="<?php echo $vehiculo['estado'] === 'disponible' ? 'estado-disponible' : 'estado-no-disponible'; ?>">
                                        <?php echo htmlspecialchars(ucfirst($vehiculo['estado'])); ?>
                                    </span>
                                </p>

                                <a href="/benedetti-rent-a-car/public/reserva.php?id_vehiculo=<?php echo urlencode($vehiculo['id_vehiculo']); ?>" class="btn btn-primary">
                                    Reservar
                                </a>
                            </div>
                        </div>

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