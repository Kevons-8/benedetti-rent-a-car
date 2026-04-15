<?php
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../views/partials/header.php';
require_once __DIR__ . '/../views/partials/navbar.php';

$errores = [];
$exito = false;
$resumen = [];

function formatearEstadoBonito(string $valor = null): string
{
    $valor = trim((string)$valor);

    if ($valor === '') {
        return 'No definido';
    }

    $valor = str_replace('_', ' ', $valor);
    return ucfirst($valor);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $errores[] = 'Acceso no permitido.';
} else {
    $idReserva = isset($_POST['id_reserva']) ? (int)$_POST['id_reserva'] : 0;
    $metodoPago = trim($_POST['metodo_pago'] ?? '');

    if ($idReserva <= 0) {
        $errores[] = 'La reserva no es válida.';
    }

    if ($metodoPago === '') {
        $errores[] = 'Debe seleccionar un método de pago.';
    }

    if (empty($errores)) {
        try {
            $sql = "
                SELECT
                    r.id_reserva,
                    r.codigo_reserva,
                    r.estado_reserva,
                    r.total_final,
                    r.total_estimado,
                    r.anticipo_requerido,
                    r.anticipo_pagado,
                    r.id_cliente,
                    c.nombres,
                    c.apellidos,
                    c.estado_cliente,
                    v.marca,
                    v.modelo,
                    v.imagen,
                    p.id_pago,
                    p.referencia_pago,
                    p.estado_pago
                FROM reservas r
                INNER JOIN clientes c ON c.id_cliente = r.id_cliente
                INNER JOIN vehiculos v ON v.id_vehiculo = r.id_vehiculo
                INNER JOIN pagos p ON p.id_reserva = r.id_reserva
                WHERE r.id_reserva = :id_reserva
                LIMIT 1
            ";

            $stmt = $conexion->prepare($sql);
            $stmt->bindValue(':id_reserva', $idReserva, PDO::PARAM_INT);
            $stmt->execute();
            $reserva = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$reserva) {
                $errores[] = 'No se encontró la reserva.';
            }
        } catch (Throwable $e) {
            $errores[] = 'Ocurrió un error al consultar la reserva: ' . $e->getMessage();
        }
    }

    if (empty($errores)) {
        $tipoCliente = 'nuevo';

        if (($reserva['estado_cliente'] ?? '') === 'activo') {
            $tipoCliente = 'existente';
        }

        if (
            isset($_SESSION['reserva_creada']['id_reserva']) &&
            (int)$_SESSION['reserva_creada']['id_reserva'] === $idReserva
        ) {
            $tipoCliente = $_SESSION['reserva_creada']['tipo_cliente'] ?? $tipoCliente;
        }

        $metodosPermitidos = [];

        if ($tipoCliente === 'nuevo') {
            $metodosPermitidos = ['tarjeta'];
        } else {
            $metodosPermitidos = ['tarjeta', 'pse', 'qr', 'efectivo'];
        }

        if (!in_array($metodoPago, $metodosPermitidos, true)) {
            $errores[] = 'El método de pago seleccionado no está permitido para este cliente.';
        }
    }

    if (empty($errores)) {
        try {
            $conexion->beginTransaction();

            $totalFinal = isset($reserva['total_final']) ? (float)$reserva['total_final'] : 0;
            $totalEstimado = isset($reserva['total_estimado']) ? (float)$reserva['total_estimado'] : 0;
            $anticipoRequerido = isset($reserva['anticipo_requerido']) ? (float)$reserva['anticipo_requerido'] : 0;

            $montoPago = $totalFinal > 0 ? $totalFinal : $totalEstimado;
            $pasarela = 'pendiente_gateway';
            $nuevoEstadoReserva = 'pendiente_pago';
            $nuevoEstadoPago = 'pendiente';

            if ($metodoPago === 'efectivo') {
                $montoPago = $anticipoRequerido > 0 ? $anticipoRequerido : $montoPago;
                $pasarela = 'manual';
                $nuevoEstadoReserva = 'pendiente_anticipo';
                $nuevoEstadoPago = 'pendiente';
            } elseif ($metodoPago === 'tarjeta') {
                $pasarela = 'wompi_simulado';
                $nuevoEstadoReserva = 'reserva_confirmada';
                $nuevoEstadoPago = 'pagado';
            } elseif ($metodoPago === 'pse') {
                $pasarela = 'wompi_pse_simulado';
                $nuevoEstadoReserva = 'pendiente_pago';
                $nuevoEstadoPago = 'pendiente';
            } elseif ($metodoPago === 'qr') {
                $pasarela = 'manual_qr';
                $nuevoEstadoReserva = 'pendiente_pago';
                $nuevoEstadoPago = 'pendiente';
            }

            $sqlUpdatePago = "
                UPDATE pagos
                SET
                    metodo_pago = :metodo_pago,
                    monto = :monto,
                    pasarela = :pasarela,
                    estado_pago = :estado_pago
                WHERE id_pago = :id_pago
            ";

            $stmtUpdatePago = $conexion->prepare($sqlUpdatePago);
            $stmtUpdatePago->bindValue(':metodo_pago', $metodoPago);
            $stmtUpdatePago->bindValue(':monto', $montoPago);
            $stmtUpdatePago->bindValue(':pasarela', $pasarela);
            $stmtUpdatePago->bindValue(':estado_pago', $nuevoEstadoPago);
            $stmtUpdatePago->bindValue(':id_pago', (int)$reserva['id_pago'], PDO::PARAM_INT);
            $stmtUpdatePago->execute();

            $sqlUpdateReserva = "
                UPDATE reservas
                SET
                    modo_pago = :modo_pago,
                    estado_reserva = :estado_reserva
                WHERE id_reserva = :id_reserva
            ";

            $stmtUpdateReserva = $conexion->prepare($sqlUpdateReserva);
            $stmtUpdateReserva->bindValue(':modo_pago', $metodoPago);
            $stmtUpdateReserva->bindValue(':estado_reserva', $nuevoEstadoReserva);
            $stmtUpdateReserva->bindValue(':id_reserva', $idReserva, PDO::PARAM_INT);
            $stmtUpdateReserva->execute();

            $conexion->commit();

            $exito = true;

            $resumen = [
                'id_reserva' => (int)$reserva['id_reserva'],
                'id_pago' => (int)$reserva['id_pago'],
                'codigo_reserva' => $reserva['codigo_reserva'],
                'referencia_pago' => $reserva['referencia_pago'],
                'cliente' => trim(($reserva['nombres'] ?? '') . ' ' . ($reserva['apellidos'] ?? '')),
                'vehiculo' => trim(($reserva['marca'] ?? '') . ' ' . ($reserva['modelo'] ?? '')),
                'tipo_cliente' => $tipoCliente,
                'metodo_pago' => $metodoPago,
                'pasarela' => $pasarela,
                'monto_pago' => $montoPago,
                'estado_reserva' => $nuevoEstadoReserva,
                'estado_pago' => $nuevoEstadoPago
            ];

        } catch (Throwable $e) {
            if ($conexion->inTransaction()) {
                $conexion->rollBack();
            }

            $errores[] = 'Ocurrió un error al iniciar el pago: ' . $e->getMessage();
        }
    }
}
?>

<style>
.iniciar-pago-page {
    padding-top: 20px;
}

.iniciar-pago-title {
    font-size: 2.2rem;
    font-weight: 800;
    margin-bottom: 24px;
}

.resultado-card {
    background: #1a2740;
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 20px;
    padding: 24px;
    box-shadow: 0 18px 35px rgba(0,0,0,0.18);
}

.resultado-card h2 {
    margin-bottom: 16px;
    color: #f7c600;
}

.resultado-card p {
    margin-bottom: 10px;
    color: #d8e2f0;
    line-height: 1.7;
}

.resultado-card strong {
    color: #ffffff;
}

.estado-linea {
    display: inline-block;
    padding: 8px 12px;
    margin-top: 6px;
    margin-right: 8px;
    border-radius: 999px;
    font-size: 0.88rem;
    font-weight: 700;
}

.estado-reserva {
    background: rgba(56, 189, 248, 0.12);
    border: 1px solid rgba(56, 189, 248, 0.22);
    color: #7dd3fc;
}

.estado-pago {
    background: rgba(247, 198, 0, 0.10);
    border: 1px solid rgba(247, 198, 0, 0.18);
    color: #f7c600;
}

.acciones-finales {
    margin-top: 22px;
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
}

.acciones-finales .btn {
    min-width: 220px;
}

.lista-errores {
    padding-left: 20px;
    list-style: disc;
}

.lista-errores li {
    margin-bottom: 8px;
    color: #fecaca;
}

.nota-simulacion {
    margin-top: 18px;
    padding: 14px;
    border-radius: 14px;
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.06);
}

@media (max-width: 768px) {
    .iniciar-pago-title {
        font-size: 1.9rem;
    }

    .resultado-card {
        padding: 18px;
    }

    .acciones-finales .btn {
        width: 100%;
    }
}
</style>

<main class="page-section iniciar-pago-page">
    <div class="container">
        <h1 class="iniciar-pago-title">Resultado del pago simulado</h1>

        <?php if (!empty($errores)): ?>
            <div class="resultado-card">
                <h2>Se encontraron errores</h2>
                <ul class="lista-errores">
                    <?php foreach ($errores as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>

                <div class="acciones-finales">
                    <a href="/benedetti-rent-a-car/public/index.php" class="btn btn-secondary">Volver al inicio</a>
                </div>
            </div>
        <?php elseif ($exito): ?>
            <div class="resultado-card">
                <h2>Método de pago confirmado</h2>

                <p><strong>Reserva:</strong> <?php echo htmlspecialchars($resumen['codigo_reserva']); ?></p>
                <p><strong>Referencia de pago:</strong> <?php echo htmlspecialchars($resumen['referencia_pago']); ?></p>
                <p><strong>Cliente:</strong> <?php echo htmlspecialchars($resumen['cliente']); ?></p>
                <p><strong>Vehículo:</strong> <?php echo htmlspecialchars($resumen['vehiculo']); ?></p>
                <p><strong>Tipo de cliente:</strong> <?php echo htmlspecialchars($resumen['tipo_cliente']); ?></p>
                <p><strong>Método elegido:</strong> <?php echo htmlspecialchars($resumen['metodo_pago']); ?></p>
                <p><strong>Pasarela / canal:</strong> <?php echo htmlspecialchars($resumen['pasarela']); ?></p>
                <p><strong>Monto del movimiento:</strong> $<?php echo number_format((float)$resumen['monto_pago'], 0, ',', '.'); ?></p>

                <div style="margin-top: 10px;">
                    <span class="estado-linea estado-reserva">Reserva <?php echo htmlspecialchars(formatearEstadoBonito($resumen['estado_reserva'])); ?></span>
                    <span class="estado-linea estado-pago">Pago <?php echo htmlspecialchars(formatearEstadoBonito($resumen['estado_pago'])); ?></span>
                </div>

                <hr style="margin: 20px 0; border-color: #334155;">

                <?php if ($resumen['metodo_pago'] === 'tarjeta'): ?>
                    <p><strong>Resultado del flujo:</strong> pago simulado aprobado. La reserva queda confirmada.</p>
                    <p>Este comportamiento te sirve para probar el flujo final antes de conectar Wompi real.</p>
                <?php elseif ($resumen['metodo_pago'] === 'pse'): ?>
                    <p><strong>Resultado del flujo:</strong> el pago queda pendiente de validación bancaria simulada.</p>
                    <p>Más adelante aquí podrás conectar la respuesta real del banco o de la pasarela.</p>
                <?php elseif ($resumen['metodo_pago'] === 'qr'): ?>
                    <p><strong>Resultado del flujo:</strong> el pago queda pendiente hasta validar el comprobante o confirmación interna.</p>
                <?php elseif ($resumen['metodo_pago'] === 'efectivo'): ?>
                    <p><strong>Resultado del flujo:</strong> la reserva queda pendiente de anticipo manual antes de su confirmación.</p>
                <?php endif; ?>

                <div class="nota-simulacion">
                    <p><strong>Nota:</strong> este flujo es simulado y está pensado para completar la lógica del negocio antes de integrar pagos reales.</p>
                </div>

                <div class="acciones-finales">
                    <a href="/benedetti-rent-a-car/public/index.php" class="btn btn-secondary">Volver al inicio</a>
                    <a href="/benedetti-rent-a-car/public/vehiculos.php" class="btn btn-primary">Ver vehículos</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php require_once __DIR__ . '/../views/partials/footer.php'; ?>