<?php
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../views/partials/header.php';
require_once __DIR__ . '/../views/partials/navbar.php';

$errores = [];
$exito = false;
$resumen = [];

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

        if (isset($_SESSION['reserva_creada']['id_reserva']) && (int)$_SESSION['reserva_creada']['id_reserva'] === $idReserva) {
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

            $montoPago = (float)$reserva['total_final'];
            $pasarela = 'pendiente_gateway';
            $nuevoEstadoReserva = 'pendiente_pago';

            if ($metodoPago === 'efectivo') {
                $montoPago = (float)$reserva['anticipo_requerido'];

                // Si por alguna razón el anticipo requerido está vacío o en 0,
                // usamos el total como respaldo para no romper el flujo.
                if ($montoPago <= 0) {
                    $montoPago = (float)$reserva['total_final'];
                }

                $pasarela = 'manual';
                $nuevoEstadoReserva = 'pendiente_anticipo';
            } elseif ($metodoPago === 'tarjeta') {
                $pasarela = 'wompi';
                $nuevoEstadoReserva = 'pendiente_pago';
            } elseif ($metodoPago === 'pse') {
                $pasarela = 'wompi';
                $nuevoEstadoReserva = 'pendiente_pago';
            } elseif ($metodoPago === 'qr') {
                $pasarela = 'manual_qr';
                $nuevoEstadoReserva = 'pendiente_pago';
            }

            $sqlUpdatePago = "
                UPDATE pagos
                SET
                    metodo_pago = :metodo_pago,
                    monto = :monto,
                    pasarela = :pasarela,
                    estado_pago = 'pendiente'
                WHERE id_pago = :id_pago
            ";

            $stmtUpdatePago = $conexion->prepare($sqlUpdatePago);
            $stmtUpdatePago->bindValue(':metodo_pago', $metodoPago);
            $stmtUpdatePago->bindValue(':monto', $montoPago);
            $stmtUpdatePago->bindValue(':pasarela', $pasarela);
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
                'estado_pago' => 'pendiente'
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

<main class="page-section">
    <div class="container">
        <h1>Iniciar pago</h1>

        <?php if (!empty($errores)): ?>
            <div class="placeholder-box">
                <h2>Se encontraron errores</h2>
                <ul>
                    <?php foreach ($errores as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php elseif ($exito): ?>
            <div class="placeholder-box">
                <h2>Método de pago confirmado</h2>

                <p><strong>Reserva:</strong> <?php echo htmlspecialchars($resumen['codigo_reserva']); ?></p>
                <p><strong>Referencia de pago:</strong> <?php echo htmlspecialchars($resumen['referencia_pago']); ?></p>
                <p><strong>Cliente:</strong> <?php echo htmlspecialchars($resumen['cliente']); ?></p>
                <p><strong>Vehículo:</strong> <?php echo htmlspecialchars($resumen['vehiculo']); ?></p>
                <p><strong>Tipo de cliente:</strong> <?php echo htmlspecialchars($resumen['tipo_cliente']); ?></p>
                <p><strong>Método elegido:</strong> <?php echo htmlspecialchars($resumen['metodo_pago']); ?></p>
                <p><strong>Pasarela / canal:</strong> <?php echo htmlspecialchars($resumen['pasarela']); ?></p>
                <p><strong>Estado de la reserva:</strong> <?php echo htmlspecialchars($resumen['estado_reserva']); ?></p>
                <p><strong>Estado del pago:</strong> <?php echo htmlspecialchars($resumen['estado_pago']); ?></p>
                <p><strong>Monto a pagar:</strong> $<?php echo number_format((float)$resumen['monto_pago'], 0, ',', '.'); ?></p>

                <hr style="margin: 20px 0; border-color: #334155;">

                <?php if ($resumen['metodo_pago'] === 'tarjeta'): ?>
                    <p><strong>Siguiente paso:</strong> esta reserva ya quedó lista para enviarse al checkout de Wompi con pago por tarjeta.</p>
                    <p>En el próximo paso conectaremos esta referencia de pago con Wompi.</p>
                <?php elseif ($resumen['metodo_pago'] === 'pse'): ?>
                    <p><strong>Siguiente paso:</strong> esta reserva quedará lista para integrarse con el flujo de PSE.</p>
                <?php elseif ($resumen['metodo_pago'] === 'qr'): ?>
                    <p><strong>Siguiente paso:</strong> aquí después podrás mostrar instrucciones o comprobante para pago por QR.</p>
                <?php elseif ($resumen['metodo_pago'] === 'efectivo'): ?>
                    <p><strong>Siguiente paso:</strong> esta reserva queda pendiente de anticipo manual. El vehículo no se bloqueará hasta que confirmes ese anticipo.</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php require_once __DIR__ . '/../views/partials/footer.php'; ?>