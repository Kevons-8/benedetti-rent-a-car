<?php
include("../../config/conexion.php");

$sql = "SELECT * FROM vehiculos ORDER BY id_vehiculo DESC";
$resultado = $conn->query($sql);
$total_vehiculos = $resultado ? $resultado->num_rows : 0;
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Lista de Vehículos | Benedetti Rent a Car</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>

<body class="admin-vehicle-page">

    <main class="admin-vehicle-bg">

        <div class="admin-vehicle-overlay"></div>

        <div class="admin-vehicle-container">

            <div class="admin-vehicle-header">

                <div>
                    <span class="admin-vehicle-badge">Inventario administrativo</span>
                    <h1>Lista de vehículos</h1>
                    <p>
                        Consulta rápidamente el inventario y abre la ficha completa de cada vehículo.
                    </p>
                </div>

                <div class="admin-vehicle-actions">
                    <a href="crear.php" class="admin-btn admin-btn-primary">
                        Registrar vehículo
                    </a>

                    <a href="../../admin/dashboard.php" class="admin-btn admin-btn-dark">
                        Dashboard
                    </a>
                </div>

            </div>

            <div class="admin-table-card">

                <div class="admin-table-header">
                    <div>
                        <h2>Vehículos registrados</h2>
                    </div>

                    <p>Total de vehículos: <?php echo $total_vehiculos; ?></p>
                </div>

                <?php if ($resultado && $resultado->num_rows > 0) { ?>

                    <div class="admin-table-wrap admin-table-wrap-compact">
                        <table class="admin-table admin-table-compact vehicles-list-table">

                            <thead>
                                <tr>
                                    <th class="col-img">Imagen</th>
                                    <th>Código</th>
                                    <th>Vehículo</th>
                                    <th>Placa</th>
                                    <th>Estado</th>
                                    <th>Precio día</th>
                                    <th>Ver</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php while ($vehiculo = $resultado->fetch_assoc()) { ?>

                                    <tr>

                                        <td class="col-img">
                                            <?php if (!empty($vehiculo['imagen'])) { ?>

                                                <img 
    src="../../assets/img/vehiculos/<?php echo htmlspecialchars($vehiculo['imagen']); ?>" 
    alt="Imagen vehículo"
    class="vehicle-thumb-force"
    style="width:110px; height:72px; object-fit:cover; border-radius:14px; display:block;"
>

                                            <?php } else { ?>

                                                <div class="admin-no-image">
                                                    Sin imagen
                                                </div>

                                            <?php } ?>
                                        </td>

                                        <td>
                                            <strong>
                                                <?php echo htmlspecialchars($vehiculo['codigo_vehiculo'] ?? 'VEH-' . $vehiculo['id_vehiculo']); ?>
                                            </strong>
                                        </td>

                                        <td>
                                            <a href="ver.php?id=<?php echo $vehiculo['id_vehiculo']; ?>" class="vehicle-name-link">
                                                <?php echo htmlspecialchars($vehiculo['marca']); ?><br>
                                                <span><?php echo htmlspecialchars($vehiculo['modelo']); ?></span>
                                            </a>
                                        </td>

                                        <td>
                                            <?php echo htmlspecialchars($vehiculo['placa']); ?>
                                        </td>

                                        <td>
                                            <?php
                                                $estado = strtolower($vehiculo['estado']);

                                                if ($estado == "disponible") {
                                                    $clase_estado = "admin-status-disponible";
                                                } elseif ($estado == "reservado") {
                                                    $clase_estado = "admin-status-reservado";
                                                } elseif ($estado == "mantenimiento") {
                                                    $clase_estado = "admin-status-mantenimiento";
                                                } else {
                                                    $clase_estado = "admin-status-inactivo";
                                                }
                                            ?>

                                            <span class="admin-status <?php echo $clase_estado; ?>">
                                                <?php echo htmlspecialchars($vehiculo['estado']); ?>
                                            </span>
                                        </td>

                                        <td>
                                            <strong>
                                                $<?php echo number_format($vehiculo['precio_dia'], 0, ',', '.'); ?>
                                            </strong>
                                        </td>

                                        <td>
                                            <div class="admin-table-actions">
                                                <a href="ver.php?id=<?php echo $vehiculo['id_vehiculo']; ?>" class="admin-action edit">
                                                    Ver ficha
                                                </a>
                                            </div>
                                        </td>

                                    </tr>

                                <?php } ?>
                            </tbody>

                        </table>
                    </div>

                <?php } else { ?>

                    <div class="admin-empty-box">
                        <h3>No hay vehículos registrados</h3>
                        <p>Cuando registres vehículos, aparecerán en esta sección.</p>

                        <a href="crear.php" class="admin-btn admin-btn-primary">
                            Registrar primer vehículo
                        </a>
                    </div>

                <?php } ?>

            </div>

        </div>

    </main>

</body>
</html>