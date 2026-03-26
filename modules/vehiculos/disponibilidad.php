<?php
require_once "../../includes/auth.php";
require_once "../../config/database.php";

/* ==========================
VALIDAR ID DEL VEHÍCULO
========================== */
if (!isset($_GET["id"])) {
    die("ID de vehículo no especificado.");
}

$id_vehiculo = (int) $_GET["id"];

/* ==========================
OBTENER MES Y AÑO
========================== */
$mes = isset($_GET["mes"]) ? (int) $_GET["mes"] : (int) date("m");
$anio = isset($_GET["anio"]) ? (int) $_GET["anio"] : (int) date("Y");

if ($mes < 1 || $mes > 12) {
    $mes = (int) date("m");
}
if ($anio < 2000 || $anio > 2100) {
    $anio = (int) date("Y");
}

/* ==========================
MES ANTERIOR / SIGUIENTE
========================== */
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

/* ==========================
OBTENER DATOS DEL VEHÍCULO
========================== */
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

/* ==========================
RANGO DEL MES
========================== */
$primer_dia_mes = sprintf('%04d-%02d-01', $anio, $mes);
$ultimo_dia_mes = date('Y-m-t', strtotime($primer_dia_mes));
$primer_dia_mes_inicio = $primer_dia_mes . " 00:00:00";
$ultimo_dia_mes_fin = $ultimo_dia_mes . " 23:59:59";

/* ==========================
OBTENER RESERVAS DEL MES
========================== */
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

/* ==========================
CONSTRUIR DETALLE POR DÍA
========================== */
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

/* ==========================
DATOS DEL CALENDARIO
========================== */
$dias_semana = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];
$nombres_meses = [
    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
];

$timestamp_primer_dia = strtotime($primer_dia_mes);
$numero_dias_mes = (int) date('t', $timestamp_primer_dia);
$dia_semana_inicio = (int) date('N', $timestamp_primer_dia);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Disponibilidad del Vehículo</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        .encabezado {
            margin-bottom: 20px;
        }

        .info-vehiculo {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #ccc;
        }

        .navegacion {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 980px;
            margin-bottom: 20px;
        }

        .navegacion a {
            text-decoration: none;
            padding: 10px 14px;
            border: 1px solid #999;
            color: #000;
        }

        .titulo-mes {
            font-size: 24px;
            font-weight: bold;
        }

        .leyenda {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
            align-items: center;
            flex-wrap: wrap;
        }

        .leyenda-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .caja-leyenda {
            width: 22px;
            height: 22px;
            border: 1px solid #aaa;
        }

        .disponible {
            background-color: #ffffff;
        }

        .reservado {
            background-color: #d9d9d9;
        }

        .calendario {
            border-collapse: collapse;
            width: 100%;
            max-width: 980px;
        }

        .calendario th,
        .calendario td {
            border: 1px solid #999;
            width: 140px;
            height: 120px;
            text-align: left;
            vertical-align: top;
            padding: 8px;
        }

        .calendario th {
            background-color: #f2f2f2;
            text-align: center;
        }

        .dia-numero {
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 6px;
        }

        .celda-vacia {
            background-color: #fafafa;
        }

        .celda-reservada {
            background-color: #d9d9d9;
        }

        .estado-texto {
            font-size: 12px;
            margin-top: 4px;
            line-height: 1.4;
        }

        .detalle-reserva {
            display: block;
            margin-top: 4px;
            padding: 2px 4px;
            background: rgba(255,255,255,0.6);
            border: 1px solid #bbb;
        }

        .acciones {
            margin-top: 25px;
        }

        .acciones a {
            text-decoration: none;
            margin-right: 15px;
        }
    </style>
</head>
<body>

    <div class="encabezado">
        <h1>Disponibilidad del Vehículo</h1>
    </div>

    <div class="info-vehiculo">
        <p><strong>Código:</strong> <?php echo htmlspecialchars($vehiculo["codigo_vehiculo"]); ?></p>
        <p><strong>Vehículo:</strong> <?php echo htmlspecialchars($vehiculo["marca"] . " " . $vehiculo["modelo"]); ?></p>
        <p><strong>Placa:</strong> <?php echo htmlspecialchars($vehiculo["placa"]); ?></p>
        <p><strong>Precio por día:</strong> $<?php echo number_format((float)$vehiculo["precio_dia"], 0, ',', '.'); ?></p>
    </div>

    <div class="navegacion">
        <a href="disponibilidad.php?id=<?php echo $id_vehiculo; ?>&mes=<?php echo $mes_anterior; ?>&anio=<?php echo $anio_anterior; ?>">
            ← Mes anterior
        </a>

        <div class="titulo-mes">
            <?php echo $nombres_meses[$mes] . " " . $anio; ?>
        </div>

        <a href="disponibilidad.php?id=<?php echo $id_vehiculo; ?>&mes=<?php echo $mes_siguiente; ?>&anio=<?php echo $anio_siguiente; ?>">
            Mes siguiente →
        </a>
    </div>

    <div class="leyenda">
        <div class="leyenda-item">
            <div class="caja-leyenda disponible"></div>
            <span>Disponible</span>
        </div>
        <div class="leyenda-item">
            <div class="caja-leyenda reservado"></div>
            <span>Reservado</span>
        </div>
    </div>

    <table class="calendario">
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
                    echo '<td class="celda-vacia"></td>';
                    $columna_actual++;
                }

                for ($dia = 1; $dia <= $numero_dias_mes; $dia++) {
                    $fecha_actual = sprintf('%04d-%02d-%02d', $anio, $mes, $dia);
                    $esta_reservado = isset($detalle_dias[$fecha_actual]);

                    if ($esta_reservado) {
                        echo '<td class="celda-reservada">';
                        echo '<div class="dia-numero">' . $dia . '</div>';
                        echo '<div class="estado-texto"><strong>Reservado</strong></div>';

                        foreach ($detalle_dias[$fecha_actual] as $detalle) {
                            echo '<div class="detalle-reserva">';
                            echo htmlspecialchars($detalle["texto"]);
                            echo '</div>';
                        }

                        echo '</td>';
                    } else {
                        echo '<td>';
                        echo '<div class="dia-numero">' . $dia . '</div>';
                        echo '<div class="estado-texto">Disponible</div>';
                        echo '</td>';
                    }

                    if ($columna_actual % 7 == 0 && $dia != $numero_dias_mes) {
                        echo '</tr><tr>';
                    }

                    $columna_actual++;
                }

                while (($columna_actual - 1) % 7 != 0) {
                    echo '<td class="celda-vacia"></td>';
                    $columna_actual++;
                }
                ?>
            </tr>
        </tbody>
    </table>

    <div class="acciones">
        <a href="listar.php">← Volver a lista de vehículos</a>
        <a href="../../admin/dashboard.php">Volver al Dashboard</a>
    </div>

</body>
</html>