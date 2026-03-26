<?php
require_once "../../includes/auth.php";
require_once "../../config/database.php";
require_once "../../includes/email_helper.php";

$mensaje = "";
$resultado_calculo = null;

/* ==========================
VALIDAR ID DE RESERVA
========================== */
if (!isset($_GET["id"])) {
    die("ID de reserva no especificado.");
}

$id_reserva = $_GET["id"];

/* ==========================
FUNCION HORAS MILITARES
========================== */
function generarHorasMilitares() {
    $horas = [];
    for ($i = 0; $i <= 23; $i++) {
        $horas[] = str_pad($i, 2, "0", STR_PAD_LEFT) . ":00";
    }
    return $horas;
}

$horas_militares = generarHorasMilitares();

/* ==========================
FUNCION CALCULAR TOTAL BASE
========================== */
function calcularDiasYTotalBase($fecha_inicio, $fecha_fin, $precio_dia, $precio_especial_3_dias = null) {
    $inicio = strtotime($fecha_inicio);
    $fin = strtotime($fecha_fin);

    if ($inicio === false || $fin === false || $fin <= $inicio) {
        return [
            "dias" => 0,
            "total_base" => 0
        ];
    }

    $segundos = $fin - $inicio;
    $dias = ceil($segundos / 86400);

    if ($dias < 1) {
        $dias = 1;
    }

    if (!empty($precio_especial_3_dias) && $precio_especial_3_dias > 0 && $dias >= 3) {
        $total_base = ($precio_dia * 2) + (($dias - 2) * $precio_especial_3_dias);
    } else {
        $total_base = $dias * $precio_dia;
    }

    return [
        "dias" => $dias,
        "total_base" => $total_base
    ];
}

/* ==========================
FUNCION CALCULO COBRO FINAL
========================== */
function calcularCobroFinal($fecha_fin_pactada, $fecha_entrega_real, $precio_dia, $tarifa_hora_extra) {
    $pactada = strtotime($fecha_fin_pactada);
    $real = strtotime($fecha_entrega_real);

    if ($pactada === false || $real === false) {
        return null;
    }

    $retraso_segundos = $real - $pactada;

    $horas_gracia = 1;
    $cargo_extra = 0;
    $horas_extra = 0;
    $tipo_cobro = "Sin recargo";

    if ($retraso_segundos <= 0) {
        return [
            "horas_gracia" => $horas_gracia,
            "retraso_segundos" => 0,
            "horas_extra" => 0,
            "cargo_extra" => 0,
            "tipo_cobro" => "Sin recargo"
        ];
    }

    if ($retraso_segundos <= 3600) {
        return [
            "horas_gracia" => $horas_gracia,
            "retraso_segundos" => $retraso_segundos,
            "horas_extra" => 0,
            "cargo_extra" => 0,
            "tipo_cobro" => "Dentro de hora de gracia"
        ];
    }

    $retraso_horas_totales = $retraso_segundos / 3600;

    if ($retraso_horas_totales >= 5) {
        $cargo_extra = $precio_dia;
        $horas_extra = ceil($retraso_horas_totales);
        $tipo_cobro = "Cobro de día completo";
    } else {
        $segundos_cobrables = $retraso_segundos - 3600;
        $horas_extra = ceil($segundos_cobrables / 3600);
        $cargo_extra = $horas_extra * $tarifa_hora_extra;
        $tipo_cobro = "Cobro por horas extra";
    }

    return [
        "horas_gracia" => $horas_gracia,
        "retraso_segundos" => $retraso_segundos,
        "horas_extra" => $horas_extra,
        "cargo_extra" => $cargo_extra,
        "tipo_cobro" => $tipo_cobro
    ];
}

/* ==========================
OBTENER DATOS DE LA RESERVA
========================== */
try {
    $sql = "SELECT 
                reservas.*,
                clientes.nombre,
                clientes.apellido,
                vehiculos.marca,
                vehiculos.modelo,
                vehiculos.precio_dia,
                vehiculos.precio_especial_3_dias,
                vehiculos.id_vehiculo
            FROM reservas
            INNER JOIN clientes ON reservas.id_cliente = clientes.id_cliente
            INNER JOIN vehiculos ON reservas.id_vehiculo = vehiculos.id_vehiculo
            WHERE reservas.id_reserva = :id_reserva";

    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(':id_reserva', $id_reserva);
    $stmt->execute();

    $reserva = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reserva) {
        die("Reserva no encontrada.");
    }

} catch (PDOException $e) {
    die("Error al obtener la reserva: " . $e->getMessage());
}

/* ==========================
CALCULAR TOTAL BASE REAL
========================== */
$calculo_base = calcularDiasYTotalBase(
    $reserva["fecha_inicio"],
    $reserva["fecha_fin"],
    (float)$reserva["precio_dia"],
    $reserva["precio_especial_3_dias"] !== null ? (float)$reserva["precio_especial_3_dias"] : null
);

$dias_base = $calculo_base["dias"];
$total_base_real = $calculo_base["total_base"];

/* ==========================
VALORES INICIALES DEL FORMULARIO
========================== */
if (!empty($reserva["fecha_entrega_real"])) {
    $timestamp_entrega = strtotime($reserva["fecha_entrega_real"]);
    $fecha_entrega_real = date("Y-m-d", $timestamp_entrega);
    $hora_entrega_real = date("H:i", $timestamp_entrega);
} else {
    $fecha_entrega_real = $_POST["fecha_entrega_real"] ?? "";
    $hora_entrega_real = $_POST["hora_entrega_real"] ?? "";
}

$tarifa_hora_extra = $_POST["tarifa_hora_extra"] ?? $reserva["tarifa_hora_extra"] ?? "";

/* ==========================
CALCULAR PAGO
========================== */
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["calcular_pago"])) {

    $fecha_entrega_real = trim($_POST["fecha_entrega_real"]);
    $hora_entrega_real = trim($_POST["hora_entrega_real"]);
    $tarifa_hora_extra = trim($_POST["tarifa_hora_extra"]);

    if (empty($fecha_entrega_real) || empty($hora_entrega_real)) {
        $mensaje = "Debe ingresar fecha y hora real de entrega.";
    } else {
        $fecha_entrega_real_completa = $fecha_entrega_real . " " . $hora_entrega_real . ":00";

        $retraso_segundos = strtotime($fecha_entrega_real_completa) - strtotime($reserva["fecha_fin"]);

        if ($retraso_segundos > 3600 && $retraso_segundos < (5 * 3600) && $tarifa_hora_extra === "") {
            $mensaje = "Debe ingresar la tarifa por hora extra para calcular el recargo.";
        } else {
            $tarifa_hora_extra_valor = ($tarifa_hora_extra === "") ? 0 : (float)$tarifa_hora_extra;

            $resultado_calculo = calcularCobroFinal(
                $reserva["fecha_fin"],
                $fecha_entrega_real_completa,
                (float)$reserva["precio_dia"],
                $tarifa_hora_extra_valor
            );
        }
    }
}

/* ==========================
GUARDAR LIQUIDACION / FINALIZAR / AJUSTAR
========================== */
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["guardar_liquidacion"])) {

    $fecha_entrega_real = trim($_POST["fecha_entrega_real"]);
    $hora_entrega_real = trim($_POST["hora_entrega_real"]);
    $tarifa_hora_extra = trim($_POST["tarifa_hora_extra"]);

    if (empty($fecha_entrega_real) || empty($hora_entrega_real)) {
        $mensaje = "Debe ingresar fecha y hora real de entrega.";
    } else {
        $fecha_entrega_real_completa = $fecha_entrega_real . " " . $hora_entrega_real . ":00";

        $retraso_segundos = strtotime($fecha_entrega_real_completa) - strtotime($reserva["fecha_fin"]);

        if ($retraso_segundos > 3600 && $retraso_segundos < (5 * 3600) && $tarifa_hora_extra === "") {
            $mensaje = "Debe ingresar la tarifa por hora extra para guardar la liquidación.";
        } else {
            $tarifa_hora_extra_valor = ($tarifa_hora_extra === "") ? 0 : (float)$tarifa_hora_extra;

            $resultado_calculo = calcularCobroFinal(
                $reserva["fecha_fin"],
                $fecha_entrega_real_completa,
                (float)$reserva["precio_dia"],
                $tarifa_hora_extra_valor
            );

            $total_final = $total_base_real + $resultado_calculo["cargo_extra"];

            try {
                $conexion->beginTransaction();

                $sqlUpdate = "UPDATE reservas SET
                                fecha_entrega_real = :fecha_entrega_real,
                                horas_gracia = :horas_gracia,
                                tarifa_hora_extra = :tarifa_hora_extra,
                                horas_extra = :horas_extra,
                                cargo_extra = :cargo_extra,
                                total_pago = :total_pago,
                                estado_reserva = 'finalizada'
                              WHERE id_reserva = :id_reserva";

                $stmtUpdate = $conexion->prepare($sqlUpdate);
                $stmtUpdate->bindParam(':fecha_entrega_real', $fecha_entrega_real_completa);
                $stmtUpdate->bindParam(':horas_gracia', $resultado_calculo["horas_gracia"]);
                $stmtUpdate->bindParam(':tarifa_hora_extra', $tarifa_hora_extra_valor);
                $stmtUpdate->bindParam(':horas_extra', $resultado_calculo["horas_extra"]);
                $stmtUpdate->bindParam(':cargo_extra', $resultado_calculo["cargo_extra"]);
                $stmtUpdate->bindParam(':total_pago', $total_final);
                $stmtUpdate->bindParam(':id_reserva', $id_reserva);
                $stmtUpdate->execute();

                $sqlVehiculoDisponible = "UPDATE vehiculos 
                                          SET estado = 'disponible' 
                                          WHERE id_vehiculo = :id_vehiculo";
                $stmtVehiculoDisponible = $conexion->prepare($sqlVehiculoDisponible);
                $stmtVehiculoDisponible->bindParam(':id_vehiculo', $reserva["id_vehiculo"]);
                $stmtVehiculoDisponible->execute();

                $conexion->commit();

                $mensaje = "Liquidación guardada correctamente. Total final: $" . number_format($total_final, 0, ',', '.');
                require_once "../../includes/auth.php";
                /* ==========================
ENVIAR CORREO FINAL
========================== */

if (!empty($reserva["correo"])) {

    $asunto = "Resumen Final de Reserva - Benedetti Rent a Car";

    $contenidoHtml = "
        <h2>Reserva finalizada</h2>
        <p>Hola <strong>" . htmlspecialchars($reserva["nombre"] . " " . $reserva["apellido"]) . "</strong>,</p>
        <p>Su reserva ha sido finalizada.</p>
        <p><strong>Código:</strong> " . htmlspecialchars($reserva["codigo_reserva"]) . "</p>
        <p><strong>Total final:</strong> $" . number_format((float)$total_final, 0, ',', '.') . "</p>
        <p><strong>Horas extra:</strong> " . htmlspecialchars($resultado_calculo["horas_extra"]) . "</p>
        <br>
        <p>Gracias por elegir Benedetti Rent a Car.</p>
    ";

    enviarCorreo(
        $reserva["correo"],
        $reserva["nombre"] . " " . $reserva["apellido"],
        $asunto,
        $contenidoHtml
    );
}
require_once "../../config/database.php";
require_once "../../includes/email_helper.php";
                $sql = "SELECT 
                            reservas.*,
                            clientes.nombre,
                            clientes.apellido,
                            vehiculos.marca,
                            vehiculos.modelo,
                            vehiculos.precio_dia,
                            vehiculos.precio_especial_3_dias,
                            vehiculos.id_vehiculo
                        FROM reservas
                        INNER JOIN clientes ON reservas.id_cliente = clientes.id_cliente
                        INNER JOIN vehiculos ON reservas.id_vehiculo = vehiculos.id_vehiculo
                        WHERE reservas.id_reserva = :id_reserva";

                $stmt = $conexion->prepare($sql);
                $stmt->bindParam(':id_reserva', $id_reserva);
                $stmt->execute();
                $reserva = $stmt->fetch(PDO::FETCH_ASSOC);

                $calculo_base = calcularDiasYTotalBase(
                    $reserva["fecha_inicio"],
                    $reserva["fecha_fin"],
                    (float)$reserva["precio_dia"],
                    $reserva["precio_especial_3_dias"] !== null ? (float)$reserva["precio_especial_3_dias"] : null
                );

                $dias_base = $calculo_base["dias"];
                $total_base_real = $calculo_base["total_base"];

            } catch (PDOException $e) {
                if ($conexion->inTransaction()) {
                    $conexion->rollBack();
                }
                $mensaje = "Error al guardar la liquidación: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liquidar Reserva</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .bloque { border: 1px solid #ccc; padding: 15px; margin-bottom: 20px; }
        .fila { margin-bottom: 12px; }
        label { font-weight: bold; display: block; margin-bottom: 5px; }
        input, select { width: 320px; padding: 8px; }
        button { padding: 10px 15px; margin-right: 10px; cursor: pointer; }
        .resultado { background: #f7f7f7; border: 1px solid #ddd; padding: 15px; margin-top: 15px; }
    </style>
</head>
<body>

<h1>Liquidar Reserva / Ajustar Cobro</h1>

<?php if ($mensaje != "") { ?>
    <p><?php echo htmlspecialchars($mensaje); ?></p>
<?php } ?>

<div class="bloque">
    <h3>Datos de la Reserva</h3>
    <p><strong>Código:</strong> <?php echo htmlspecialchars($reserva["codigo_reserva"]); ?></p>
    <p><strong>Cliente:</strong> <?php echo htmlspecialchars($reserva["nombre"] . " " . $reserva["apellido"]); ?></p>
    <p><strong>Vehículo:</strong> <?php echo htmlspecialchars($reserva["marca"] . " " . $reserva["modelo"]); ?></p>
    <p><strong>Fecha/Hora pactada de devolución:</strong> <?php echo htmlspecialchars($reserva["fecha_fin"]); ?></p>
    <p><strong>Días base calculados:</strong> <?php echo htmlspecialchars($dias_base); ?></p>
    <p><strong>Precio por día:</strong> $<?php echo number_format((float)$reserva["precio_dia"], 0, ',', '.'); ?></p>
    <p><strong>Total base real de la reserva:</strong> $<?php echo number_format($total_base_real, 0, ',', '.'); ?></p>
    <p><strong>Estado actual:</strong> <?php echo htmlspecialchars($reserva["estado_reserva"]); ?></p>
    <p><strong>Total actualmente guardado:</strong> $<?php echo number_format((float)$reserva["total_pago"], 0, ',', '.'); ?></p>
</div>

<form method="POST">

<div class="bloque">
    <h3>Entrega Real y Liquidación</h3>

    <div class="fila">
        <label>Fecha real de entrega</label>
        <input type="date" name="fecha_entrega_real" value="<?php echo htmlspecialchars($fecha_entrega_real); ?>">
    </div>

    <div class="fila">
        <label>Hora real de entrega</label>
        <select name="hora_entrega_real">
            <option value="">Seleccione hora</option>
            <?php foreach ($horas_militares as $hora) { ?>
                <option value="<?php echo $hora; ?>" <?php echo ($hora_entrega_real == $hora) ? "selected" : ""; ?>>
                    <?php echo $hora; ?>
                </option>
            <?php } ?>
        </select>
    </div>

    <div class="fila">
        <label>Tarifa por hora extra (solo si aplica entre 1:01 y 4:59 horas de retraso)</label>
        <input type="number" step="0.01" name="tarifa_hora_extra" value="<?php echo htmlspecialchars($tarifa_hora_extra); ?>">
    </div>

    <button type="submit" name="calcular_pago">Calcular Pago Final</button>
    <button type="submit" name="guardar_liquidacion">Guardar Liquidación</button>
</div>

</form>

<?php if ($resultado_calculo !== null) { ?>
    <?php $total_final_estimado = $total_base_real + $resultado_calculo["cargo_extra"]; ?>
    <div class="resultado">
        <h3>Resultado del Cálculo</h3>
        <p><strong>Horas de gracia:</strong> <?php echo htmlspecialchars($resultado_calculo["horas_gracia"]); ?></p>
        <p><strong>Horas extra:</strong> <?php echo htmlspecialchars($resultado_calculo["horas_extra"]); ?></p>
        <p><strong>Tipo de cobro:</strong> <?php echo htmlspecialchars($resultado_calculo["tipo_cobro"]); ?></p>
        <p><strong>Cargo extra:</strong> $<?php echo number_format((float)$resultado_calculo["cargo_extra"], 0, ',', '.'); ?></p>
        <p><strong>Total final a pagar:</strong> $<?php echo number_format($total_final_estimado, 0, ',', '.'); ?></p>
    </div>
<?php } ?>

<br>
<a href="listar.php">Volver a la lista de reservas</a><br><br>
<a href="../../admin/dashboard.php">Volver al Dashboard</a>

</body>
</html>