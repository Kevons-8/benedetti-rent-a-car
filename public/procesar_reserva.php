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
    $datos = [
        'id_vehiculo' => trim($_POST['id_vehiculo'] ?? ''),
        'nombres' => trim($_POST['nombres'] ?? ''),
        'apellidos' => trim($_POST['apellidos'] ?? ''),
        'correo' => trim($_POST['correo'] ?? ''),
        'telefono' => trim($_POST['telefono'] ?? ''),
        'tipo_documento' => trim($_POST['tipo_documento'] ?? ''),
        'numero_documento' => trim($_POST['numero_documento'] ?? ''),
        'codigo_referido' => trim($_POST['codigo_referido'] ?? ''),
        'modo_pago' => trim($_POST['modo_pago'] ?? ''),
        'fecha_inicio' => trim($_POST['fecha_inicio'] ?? ''),
        'hora_inicio' => trim($_POST['hora_inicio'] ?? ''),
        'fecha_fin' => trim($_POST['fecha_fin'] ?? ''),
        'hora_fin' => trim($_POST['hora_fin'] ?? ''),
        'lugar_entrega' => trim($_POST['lugar_entrega'] ?? ''),
        'lugar_devolucion' => trim($_POST['lugar_devolucion'] ?? ''),
        'observaciones' => trim($_POST['observaciones'] ?? ''),
        'acepta_datos' => isset($_POST['acepta_datos']) ? 1 : 0
    ];

    if ($datos['id_vehiculo'] === '') $errores[] = 'Falta el vehículo seleccionado.';
    if ($datos['nombres'] === '') $errores[] = 'Los nombres son obligatorios.';
    if ($datos['apellidos'] === '') $errores[] = 'Los apellidos son obligatorios.';
    if ($datos['correo'] === '' || !filter_var($datos['correo'], FILTER_VALIDATE_EMAIL)) $errores[] = 'El correo no es válido.';
    if ($datos['telefono'] === '') $errores[] = 'El teléfono es obligatorio.';
    if ($datos['tipo_documento'] === '') $errores[] = 'El tipo de documento es obligatorio.';
    if ($datos['numero_documento'] === '') $errores[] = 'El número de documento es obligatorio.';
    if ($datos['modo_pago'] === '') $errores[] = 'Debe seleccionar un modo de pago.';
    if ($datos['fecha_inicio'] === '' || $datos['hora_inicio'] === '') $errores[] = 'La fecha y hora de inicio son obligatorias.';
    if ($datos['fecha_fin'] === '' || $datos['hora_fin'] === '') $errores[] = 'La fecha y hora de fin son obligatorias.';
    if ($datos['lugar_entrega'] === '') $errores[] = 'El lugar de entrega es obligatorio.';
    if ($datos['lugar_devolucion'] === '') $errores[] = 'El lugar de devolución es obligatorio.';
    if ($datos['acepta_datos'] !== 1) $errores[] = 'Debe aceptar el tratamiento de datos para continuar.';

    $fecha_hora_inicio = $datos['fecha_inicio'] . ' ' . $datos['hora_inicio'] . ':00';
    $fecha_hora_fin = $datos['fecha_fin'] . ' ' . $datos['hora_fin'] . ':00';

    $ts_inicio = strtotime($fecha_hora_inicio);
    $ts_fin = strtotime($fecha_hora_fin);

    if ($ts_inicio === false || $ts_fin === false) {
        $errores[] = 'Las fechas no tienen un formato válido.';
    } elseif ($ts_fin <= $ts_inicio) {
        $errores[] = 'La fecha y hora de fin debe ser posterior a la de inicio.';
    }

    if (empty($errores)) {
        try {
            $conexion->beginTransaction();

            $sqlVehiculo = "SELECT * FROM vehiculos WHERE id_vehiculo = :id_vehiculo LIMIT 1";
            $stmtVehiculo = $conexion->prepare($sqlVehiculo);
            $stmtVehiculo->bindParam(':id_vehiculo', $datos['id_vehiculo'], PDO::PARAM_INT);
            $stmtVehiculo->execute();
            $vehiculo = $stmtVehiculo->fetch(PDO::FETCH_ASSOC);

            if (!$vehiculo) {
                $errores[] = 'El vehículo seleccionado no existe.';
            }

            if (empty($errores)) {
                $sqlDisponibilidad = "
                    SELECT COUNT(*) 
                    FROM reservas
                    WHERE id_vehiculo = :id_vehiculo
                      AND estado_reserva IN ('confirmada', 'pago_parcial_confirmado')
                      AND (:fecha_inicio < fecha_fin AND :fecha_fin > fecha_inicio)
                ";
                $stmtDisp = $conexion->prepare($sqlDisponibilidad);
                $stmtDisp->bindParam(':id_vehiculo', $datos['id_vehiculo'], PDO::PARAM_INT);
                $stmtDisp->bindParam(':fecha_inicio', $fecha_hora_inicio);
                $stmtDisp->bindParam(':fecha_fin', $fecha_hora_fin);
                $stmtDisp->execute();

                $conflictos = (int)$stmtDisp->fetchColumn();

                if ($conflictos > 0) {
                    $errores[] = 'El vehículo ya está reservado o bloqueado en ese rango de fecha y hora.';
                }
            }

            $cliente = null;

            if (empty($errores)) {
                $sqlCliente = "
                    SELECT * FROM clientes
                    WHERE numero_documento = :numero_documento
                       OR correo = :correo
                       OR telefono = :telefono
                    LIMIT 1
                ";
                $stmtCliente = $conexion->prepare($sqlCliente);
                $stmtCliente->bindParam(':numero_documento', $datos['numero_documento']);
                $stmtCliente->bindParam(':correo', $datos['correo']);
                $stmtCliente->bindParam(':telefono', $datos['telefono']);
                $stmtCliente->execute();
                $cliente = $stmtCliente->fetch(PDO::FETCH_ASSOC);
            }

            $tipoCliente = 'nuevo';
            $clienteReferente = null;
            $estadoClienteEncontrado = null;

            if ($cliente) {
                $estadoClienteEncontrado = $cliente['estado_cliente'] ?? null;

                if ($estadoClienteEncontrado === 'activo') {
                    $tipoCliente = 'existente';
                } elseif ($estadoClienteEncontrado === 'prospecto') {
                    $tipoCliente = 'nuevo';
                } else {
                    $tipoCliente = 'existente';
                }
            } else {
                if ($datos['codigo_referido'] !== '') {
                    $sqlReferido = "SELECT * FROM clientes WHERE codigo_cliente = :codigo_cliente LIMIT 1";
                    $stmtReferido = $conexion->prepare($sqlReferido);
                    $stmtReferido->bindParam(':codigo_cliente', $datos['codigo_referido']);
                    $stmtReferido->execute();
                    $clienteReferente = $stmtReferido->fetch(PDO::FETCH_ASSOC);

                    if ($clienteReferente) {
                        $tipoCliente = 'referido';
                    }
                }
            }

            $metodosPermitidos = [];

            if ($tipoCliente === 'nuevo') {
                $metodosPermitidos = ['credito'];
            } elseif ($tipoCliente === 'referido' || $tipoCliente === 'existente') {
                $metodosPermitidos = ['credito', 'debito', 'qr', 'efectivo'];
            }

            if (!in_array($datos['modo_pago'], $metodosPermitidos, true)) {
                if (!$cliente) {
                    $sqlInsertCliente = "
                        INSERT INTO clientes (
                            nombre,
                            apellido,
                            nombres,
                            apellidos,
                            correo,
                            telefono,
                            tipo_documento,
                            numero_documento,
                            estado_cliente
                        ) VALUES (
                            :nombre,
                            :apellido,
                            :nombres,
                            :apellidos,
                            :correo,
                            :telefono,
                            :tipo_documento,
                            :numero_documento,
                            'prospecto'
                        )
                    ";

                    $stmtInsertCliente = $conexion->prepare($sqlInsertCliente);
                    $stmtInsertCliente->bindParam(':nombre', $datos['nombres']);
                    $stmtInsertCliente->bindParam(':apellido', $datos['apellidos']);
                    $stmtInsertCliente->bindParam(':nombres', $datos['nombres']);
                    $stmtInsertCliente->bindParam(':apellidos', $datos['apellidos']);
                    $stmtInsertCliente->bindParam(':correo', $datos['correo']);
                    $stmtInsertCliente->bindParam(':telefono', $datos['telefono']);
                    $stmtInsertCliente->bindParam(':tipo_documento', $datos['tipo_documento']);
                    $stmtInsertCliente->bindParam(':numero_documento', $datos['numero_documento']);
                    $stmtInsertCliente->execute();
                }

                $conexion->commit();

                $_SESSION['mensaje_reserva'] = 'Por políticas de seguridad de Benedetti Rent a Car, si usted es cliente nuevo solo tiene habilitado el pago con tarjeta de crédito. Para acceder a otros métodos de pago como débito, QR o efectivo con anticipo, debe ser referido por un cliente recurrente o haber alquilado mínimo 3 veces vehículo con nosotros.';
                $_SESSION['mensaje_reserva_tipo'] = 'error';

                header('Location: /benedetti-rent-a-car/public/reserva.php?id_vehiculo=' . urlencode($datos['id_vehiculo']));
                exit;
            }

            if (!$cliente) {
                $sqlInsertCliente = "
                    INSERT INTO clientes (
                        nombre,
                        apellido,
                        nombres,
                        apellidos,
                        correo,
                        telefono,
                        tipo_documento,
                        numero_documento,
                        estado_cliente
                    ) VALUES (
                        :nombre,
                        :apellido,
                        :nombres,
                        :apellidos,
                        :correo,
                        :telefono,
                        :tipo_documento,
                        :numero_documento,
                        'prospecto'
                    )
                ";

                $stmtInsertCliente = $conexion->prepare($sqlInsertCliente);
                $stmtInsertCliente->bindParam(':nombre', $datos['nombres']);
                $stmtInsertCliente->bindParam(':apellido', $datos['apellidos']);
                $stmtInsertCliente->bindParam(':nombres', $datos['nombres']);
                $stmtInsertCliente->bindParam(':apellidos', $datos['apellidos']);
                $stmtInsertCliente->bindParam(':correo', $datos['correo']);
                $stmtInsertCliente->bindParam(':telefono', $datos['telefono']);
                $stmtInsertCliente->bindParam(':tipo_documento', $datos['tipo_documento']);
                $stmtInsertCliente->bindParam(':numero_documento', $datos['numero_documento']);
                $stmtInsertCliente->execute();

                $idCliente = (int)$conexion->lastInsertId();
            } else {
                $idCliente = (int)$cliente['id_cliente'];
            }

            $precioDia = (float)$vehiculo['precio_dia'];
            $precioHoraExtra = isset($vehiculo['precio_hora_extra']) ? (float)$vehiculo['precio_hora_extra'] : 0;

            $diferenciaSegundos = $ts_fin - $ts_inicio;
            $segundosPorDia = 86400;

            $diasBase = intdiv($diferenciaSegundos, $segundosPorDia);
            $sobranteSegundos = $diferenciaSegundos % $segundosPorDia;

            if ($diasBase < 1) {
                $diasBase = 1;
                $sobranteSegundos = 0;
            }

            $horasAdicionales = 0;
            if ($sobranteSegundos > 0) {
                $horasAdicionales = (int)ceil($sobranteSegundos / 3600);
            }

            $recargoHorasExtra = 0;
            $totalEstimado = $diasBase * $precioDia;

            if ($horasAdicionales === 0) {
                $recargoHorasExtra = 0;
            } elseif ($horasAdicionales === 1) {
                $recargoHorasExtra = 0;
            } elseif ($horasAdicionales >= 2 && $horasAdicionales <= 4) {
                $recargoHorasExtra = $horasAdicionales * $precioHoraExtra;
                $totalEstimado += $recargoHorasExtra;
            } elseif ($horasAdicionales >= 5) {
                $totalEstimado += $precioDia;
            }

            $totalFinal = $totalEstimado;
            $horasExtraCobradas = $horasAdicionales;

            $anticipoRequerido = 0;
            $anticipoPagado = 0;
            $estadoReserva = 'pendiente_pago';
            $bloqueaDisponibilidad = 0;

            if ($datos['modo_pago'] === 'efectivo') {
                $anticipoRequerido = $precioDia;
                $estadoReserva = 'pendiente_anticipo';
            }

            $codigoReserva = 'RES-' . time();

            $sqlInsertReserva = "
                INSERT INTO reservas (
                    codigo_reserva,
                    id_cliente,
                    id_vehiculo,
                    fecha_inicio,
                    fecha_fin,
                    lugar_entrega,
                    lugar_devolucion,
                    observaciones,
                    estado_reserva,
                    modo_pago,
                    codigo_referido_usado,
                    anticipo_requerido,
                    anticipo_pagado,
                    total_pago,
                    total_estimado,
                    horas_extra_cobradas,
                    recargo_horas_extra,
                    total_final,
                    bloquea_disponibilidad
                ) VALUES (
                    :codigo_reserva,
                    :id_cliente,
                    :id_vehiculo,
                    :fecha_inicio,
                    :fecha_fin,
                    :lugar_entrega,
                    :lugar_devolucion,
                    :observaciones,
                    :estado_reserva,
                    :modo_pago,
                    :codigo_referido_usado,
                    :anticipo_requerido,
                    :anticipo_pagado,
                    :total_pago,
                    :total_estimado,
                    :horas_extra_cobradas,
                    :recargo_horas_extra,
                    :total_final,
                    :bloquea_disponibilidad
                )
            ";

            $stmtInsertReserva = $conexion->prepare($sqlInsertReserva);
            $stmtInsertReserva->bindParam(':codigo_reserva', $codigoReserva);
            $stmtInsertReserva->bindParam(':id_cliente', $idCliente, PDO::PARAM_INT);
            $stmtInsertReserva->bindParam(':id_vehiculo', $datos['id_vehiculo'], PDO::PARAM_INT);
            $stmtInsertReserva->bindParam(':fecha_inicio', $fecha_hora_inicio);
            $stmtInsertReserva->bindParam(':fecha_fin', $fecha_hora_fin);
            $stmtInsertReserva->bindParam(':lugar_entrega', $datos['lugar_entrega']);
            $stmtInsertReserva->bindParam(':lugar_devolucion', $datos['lugar_devolucion']);
            $stmtInsertReserva->bindParam(':observaciones', $datos['observaciones']);
            $stmtInsertReserva->bindParam(':estado_reserva', $estadoReserva);
            $stmtInsertReserva->bindParam(':modo_pago', $datos['modo_pago']);
            $stmtInsertReserva->bindParam(':codigo_referido_usado', $datos['codigo_referido']);
            $stmtInsertReserva->bindParam(':anticipo_requerido', $anticipoRequerido);
            $stmtInsertReserva->bindParam(':anticipo_pagado', $anticipoPagado);
            $stmtInsertReserva->bindParam(':total_pago', $totalEstimado);
            $stmtInsertReserva->bindParam(':total_estimado', $totalEstimado);
            $stmtInsertReserva->bindParam(':horas_extra_cobradas', $horasExtraCobradas, PDO::PARAM_INT);
            $stmtInsertReserva->bindParam(':recargo_horas_extra', $recargoHorasExtra);
            $stmtInsertReserva->bindParam(':total_final', $totalFinal);
            $stmtInsertReserva->bindParam(':bloquea_disponibilidad', $bloqueaDisponibilidad, PDO::PARAM_INT);
            $stmtInsertReserva->execute();

            $idReserva = (int)$conexion->lastInsertId();

            $conexion->commit();
            $exito = true;

            $resumen = [
                'codigo_reserva' => $codigoReserva,
                'id_reserva' => $idReserva,
                'tipo_cliente' => $tipoCliente,
                'nombres' => $datos['nombres'],
                'apellidos' => $datos['apellidos'],
                'correo' => $datos['correo'],
                'telefono' => $datos['telefono'],
                'fecha_inicio' => $fecha_hora_inicio,
                'fecha_fin' => $fecha_hora_fin,
                'modo_pago' => $datos['modo_pago'],
                'estado_reserva' => $estadoReserva,
                'anticipo_requerido' => $anticipoRequerido,
                'total_estimado' => $totalEstimado,
                'dias_base' => $diasBase,
                'horas_extra' => $horasAdicionales,
                'recargo_horas_extra' => $recargoHorasExtra,
                'vehiculo' => $vehiculo['marca'] . ' ' . $vehiculo['modelo']
            ];
        } catch (Throwable $e) {
            if ($conexion->inTransaction()) {
                $conexion->rollBack();
            }
            $errores[] = 'Ocurrió un error al procesar la reserva: ' . $e->getMessage();
        }
    }
}
?>

<main class="page-section">
    <div class="container">
        <h1>Procesar reserva</h1>

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
                <h2>Solicitud de reserva creada correctamente</h2>
                <p><strong>Código de reserva:</strong> <?php echo htmlspecialchars($resumen['codigo_reserva']); ?></p>
                <p><strong>Vehículo:</strong> <?php echo htmlspecialchars($resumen['vehiculo']); ?></p>
                <p><strong>Cliente:</strong> <?php echo htmlspecialchars($resumen['nombres'] . ' ' . $resumen['apellidos']); ?></p>
                <p><strong>Tipo de cliente detectado:</strong> <?php echo htmlspecialchars($resumen['tipo_cliente']); ?></p>
                <p><strong>Fecha de inicio:</strong> <?php echo htmlspecialchars($resumen['fecha_inicio']); ?></p>
                <p><strong>Fecha de fin:</strong> <?php echo htmlspecialchars($resumen['fecha_fin']); ?></p>
                <p><strong>Modo de pago:</strong> <?php echo htmlspecialchars($resumen['modo_pago']); ?></p>
                <p><strong>Estado de reserva:</strong> <?php echo htmlspecialchars($resumen['estado_reserva']); ?></p>
                <p><strong>Días base:</strong> <?php echo htmlspecialchars((string)$resumen['dias_base']); ?></p>
                <p><strong>Horas adicionales:</strong> <?php echo htmlspecialchars((string)$resumen['horas_extra']); ?></p>
                <p><strong>Recargo por horas extra:</strong> $<?php echo number_format($resumen['recargo_horas_extra'], 0, ',', '.'); ?></p>
                <p><strong>Total estimado:</strong> $<?php echo number_format($resumen['total_estimado'], 0, ',', '.'); ?></p>

                <?php if ($resumen['modo_pago'] === 'efectivo'): ?>
                    <p><strong>Anticipo requerido para bloquear la reserva:</strong> $<?php echo number_format($resumen['anticipo_requerido'], 0, ',', '.'); ?></p>
                    <p>La reserva todavía no bloquea disponibilidad hasta que el anticipo quede confirmado.</p>
                <?php else: ?>
                    <p>La reserva quedó pendiente de pago. El vehículo se bloqueará cuando el pago sea confirmado.</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php require_once __DIR__ . '/../views/partials/footer.php'; ?>