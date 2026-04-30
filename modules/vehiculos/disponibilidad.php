<?php
require_once "../../includes/auth.php";
require_once "../../config/database.php";

if (!isset($_GET["id"])) {
    die("ID de vehículo no especificado.");
}

$id_vehiculo = (int) $_GET["id"];

$mes = isset($_GET["mes"]) ? (int) $_GET["mes"] : (int) date("m");
$anio = isset($_GET["anio"]) ? (int) $_GET["anio"] : (int) date("Y");

if ($mes < 1 || $mes > 12) {
    $mes = (int) date("m");
}

if ($anio < 2000 || $anio > 2100) {
    $anio = (int) date("Y");
}

$mes_anterior = $mes - 1;
$anio_anterior = $anio;

if ($mes_anterior < 1) {
    $mes_anterior = 12;
    $anio_anterior--;
}

$mes_siguiente = $mes + 1;
$anio_siguiente = $anio;

if ($mes_siguiente > 12) {
    $mes_siguiente = 1;
    $anio_siguiente++;
}

try {
    $sqlVehiculo = "SELECT * FROM vehiculos WHERE id_vehiculo = :id_vehiculo";
    $stmtVehiculo = $conexion->prepare($sqlVehiculo);
    $stmtVehiculo->bindParam(':id_vehiculo', $id_vehiculo, PDO::PARAM_INT);
    $stmtVehiculo->execute();

    $vehiculo = $stmtVehiculo->fetch(PDO::FETCH_ASSOC);

    if (!$vehiculo) {
        die("Vehículo no encontrado.");
    }
} catch (PDOException $e) {
    die("Error al obtener vehículo: " . $e->getMessage());
}

$primer_dia_mes = sprintf('%04d-%02d-01', $anio, $mes);
$ultimo_dia_mes = date('Y-m-t', strtotime($primer_dia_mes));
$primer_dia_mes_inicio = $primer_dia_mes . " 00:00:00";
$ultimo_dia_mes_fin = $ultimo_dia_mes . " 23:59:59";

try {
    $sqlReservas = "SELECT id_reserva, codigo_reserva, fecha_inicio, fecha_fin, estado_reserva
                    FROM reservas
                    WHERE id_vehiculo = :id_vehiculo
                      AND estado_reserva IN ('pendiente', 'confirmada')
                      AND fecha_inicio <= :ultimo_dia_mes_fin
                      AND fecha_fin >= :primer_dia_mes_inicio
                    ORDER BY fecha_inicio ASC";

    $stmtReservas = $conexion->prepare($sqlReservas);
    $stmtReservas->bindParam(':id_vehiculo', $id_vehiculo, PDO::PARAM_INT);
    $stmtReservas->bindParam(':ultimo_dia_mes_fin', $ultimo_dia_mes_fin);
    $stmtReservas->bindParam(':primer_dia_mes_inicio', $primer_dia_mes_inicio);
    $stmtReservas->execute();

    $reservas = $stmtReservas->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al obtener reservas del vehículo: " . $e->getMessage());
}

$detalle_dias = [];

foreach ($reservas as $reserva) {
    $inicio_ts = strtotime($reserva["fecha_inicio"]);
    $fin_ts = strtotime($reserva["fecha_fin"]);

    $inicio_fecha = date('Y-m-d', $inicio_ts);
    $fin_fecha = date('Y-m-d', $fin_ts);

    $inicio_hora = date('H:i', $inicio_ts);
    $fin_hora = date('H:i', $fin_ts);

    $inicio_dia_ts = strtotime($inicio_fecha);
    $fin_dia_ts = strtotime($fin_fecha);

    for ($fecha = $inicio_dia_ts; $fecha <= $fin_dia_ts; $fecha += 86400) {
        $dia_formato = date('Y-m-d', $fecha);

        if ($dia_formato < $primer_dia_mes || $dia_formato > $ultimo_dia_mes) {
            continue;
        }

        $texto = "Reservado";

        if ($inicio_fecha === $fin_fecha) {
            $texto = "Reservado de $inicio_hora a $fin_hora";
        } elseif ($dia_formato === $inicio_fecha) {
            $texto = "Reservado desde $inicio_hora";
        } elseif ($dia_formato === $fin_fecha) {
            $texto = "Reservado hasta $fin_hora";
        } else {
            $texto = "Reservado todo el día";
        }

        $detalle_dias[$dia_formato][] = [
            "codigo_reserva" => $reserva["codigo_reserva"],
            "texto" => $texto
        ];
    }
}

$dias_semana = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];

$nombres_meses = [
    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
];

$timestamp_primer_dia = strtotime($primer_dia_mes);
$numero_dias_mes = (int) date('t', $timestamp_primer_dia);
$dia_semana_inicio = (int) date('N', $timestamp_primer_dia);
$total_reservas_mes = count($reservas);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Disponibilidad del Vehículo | Benedetti Rent a Car</title>
    <link rel="stylesheet" href="/benedetti-rent-a-car/assets/css/style.css">
</head>

<body class="admin-vehicle-page">

<main class="admin-vehicle-bg">
    <div class="admin-vehicle-overlay"></div>

    <section class="admin-vehicle-container">

        <div class="admin-vehicle-header">
            <div>
                <span class="admin-vehicle-badge">Calendario operativo</span>
                <h1>Disponibilidad del vehículo</h1>
                <p>Consulta los días disponibles y reservados de esta unidad.</p>
            </div>

            <div class="admin-vehicle-actions">
                <a href="ver.php?id=<?php echo $id_vehiculo; ?>" class="admin-btn admin-btn-secondary">Ver ficha</a>
                <a href="listar.php" class="admin-btn admin-btn-dark">Volver a vehículos</a>
            </div>
        </div>

        <div class="admin-availability-card">

            <div class="admin-availability-summary">
                <div class="admin-availability-image">
                    <?php if (!empty($vehiculo["imagen"])): ?>
                        <img 
                            src="/benedetti-rent-a-car/assets/img/vehiculos/<?php echo htmlspecialchars($vehiculo["imagen"]); ?>"
                            alt="<?php echo htmlspecialchars($vehiculo["marca"] . " " . $vehiculo["modelo"]); ?>"
                        >
                    <?php else: ?>
                        <div class="admin-no-image">Sin imagen</div>
                    <?php endif; ?>
                </div>

                <div class="admin-availability-info">
                    <span class="admin-detail-status admin-status-<?php echo htmlspecialchars($vehiculo["estado"]); ?>">
                        <?php echo htmlspecialchars(ucfirst($vehiculo["estado"])); ?>
                    </span>

                    <h2><?php echo htmlspecialchars($vehiculo["marca"] . " " . $vehiculo["modelo"]); ?></h2>

                    <div class="admin-availability-grid">
                        <p><strong>Código:</strong> <?php echo htmlspecialchars($vehiculo["codigo_vehiculo"]); ?></p>
                        <p><strong>Placa:</strong> <?php echo htmlspecialchars($vehiculo["placa"]); ?></p>
                        <p><strong>Precio día:</strong> $<?php echo number_format((float)$vehiculo["precio_dia"], 0, ',', '.'); ?></p>
                        <p><strong>Reservas del mes:</strong> <?php echo $total_reservas_mes; ?></p>
                    </div>
                </div>
            </div>

            <div class="admin-calendar-nav">
                <a href="disponibilidad.php?id=<?php echo $id_vehiculo; ?>&mes=<?php echo $mes_anterior; ?>&anio=<?php echo $anio_anterior; ?>" class="admin-btn admin-btn-secondary">
                    ← Mes anterior
                </a>

                <div class="admin-calendar-title">
                    <?php echo $nombres_meses[$mes] . " " . $anio; ?>
                </div>

                <a href="disponibilidad.php?id=<?php echo $id_vehiculo; ?>&mes=<?php echo $mes_siguiente; ?>&anio=<?php echo $anio_siguiente; ?>" class="admin-btn admin-btn-secondary">
                    Mes siguiente →
                </a>
            </div>

            <div class="admin-calendar-legend">
                <div>
                    <span class="admin-calendar-dot available"></span>
                    Disponible
                </div>

                <div>
                    <span class="admin-calendar-dot reserved"></span>
                    Reservado
                </div>
            </div>

            <div class="admin-calendar-wrap">
                <table class="admin-calendar">
                    <thead>
                        <tr>
                            <?php foreach ($dias_semana as $dia_semana): ?>
                                <th><?php echo $dia_semana; ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>

                    <tbody>
                        <tr>
                            <?php
                            $columna_actual = 1;

                            for ($i = 1; $i < $dia_semana_inicio; $i++) {
                                echo '<td class="admin-calendar-empty"></td>';
                                $columna_actual++;
                            }

                            for ($dia = 1; $dia <= $numero_dias_mes; $dia++) {
                                $fecha_actual = sprintf('%04d-%02d-%02d', $anio, $mes, $dia);
                                $esta_reservado = isset($detalle_dias[$fecha_actual]);

                                if ($esta_reservado) {
                                    echo '<td class="admin-calendar-day admin-calendar-reserved">';
                                    echo '<div class="admin-calendar-day-number">' . $dia . '</div>';
                                    echo '<div class="admin-calendar-state">Reservado</div>';

                                    foreach ($detalle_dias[$fecha_actual] as $detalle) {
                                        echo '<div class="admin-calendar-reservation">';
                                        echo '<strong>' . htmlspecialchars($detalle["codigo_reserva"]) . '</strong><br>';
                                        echo htmlspecialchars($detalle["texto"]);
                                        echo '</div>';
                                    }

                                    echo '</td>';
                                } else {
                                    echo '<td class="admin-calendar-day admin-calendar-available">';
                                    echo '<div class="admin-calendar-day-number">' . $dia . '</div>';
                                    echo '<div class="admin-calendar-state">Disponible</div>';
                                    echo '</td>';
                                }

                                if ($columna_actual % 7 == 0 && $dia != $numero_dias_mes) {
                                    echo '</tr><tr>';
                                }

                                $columna_actual++;
                            }

                            while (($columna_actual - 1) % 7 != 0) {
                                echo '<td class="admin-calendar-empty"></td>';
                                $columna_actual++;
                            }
                            ?>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="admin-detail-actions">
                <a href="listar.php" class="admin-btn admin-btn-secondary">← Volver a lista</a>
                <a href="../../admin/dashboard.php" class="admin-btn admin-btn-dark">Dashboard</a>
            </div>

        </div>

    </section>
</main>

</body>
</html>