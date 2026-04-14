<?php
session_start();

require_once __DIR__ . '/../config/database.php';

$errores = [];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['mensaje_reserva'] = 'Acceso no permitido.';
    $_SESSION['mensaje_reserva_tipo'] = 'error';
    header('Location: /benedetti-rent-a-car/public/vehiculos.php');
    exit;
}

$datos = [
    'id_vehiculo' => trim($_POST['id_vehiculo'] ?? ''),
    'nombres' => trim($_POST['nombres'] ?? ''),
    'apellidos' => trim($_POST['apellidos'] ?? ''),
    'correo' => trim($_POST['correo'] ?? ''),
    'telefono' => trim($_POST['telefono'] ?? ''),
    'tipo_documento' => trim($_POST['tipo_documento'] ?? ''),
    'numero_documento' => trim($_POST['numero_documento'] ?? ''),
    'codigo_referido' => trim($_POST['codigo_referido'] ?? ''),
    'fecha_inicio' => trim($_POST['fecha_inicio'] ?? ''),
    'hora_inicio' => trim($_POST['hora_inicio'] ?? ''),
    'fecha_fin' => trim($_POST['fecha_fin'] ?? ''),
    'hora_fin' => trim($_POST['hora_fin'] ?? ''),
    'lugar_entrega_opcion' => trim($_POST['lugar_entrega_opcion'] ?? ''),
    'lugar_devolucion_opcion' => trim($_POST['lugar_devolucion_opcion'] ?? ''),
    'otro_lugar_entrega' => trim($_POST['otro_lugar_entrega'] ?? ''),
    'otro_lugar_devolucion' => trim($_POST['otro_lugar_devolucion'] ?? ''),
    'costo_entrega_manual' => trim($_POST['costo_entrega_manual'] ?? '0'),
    'costo_devolucion_manual' => trim($_POST['costo_devolucion_manual'] ?? '0'),
    'lat_entrega' => trim($_POST['lat_entrega'] ?? ''),
    'lng_entrega' => trim($_POST['lng_entrega'] ?? ''),
    'lat_devolucion' => trim($_POST['lat_devolucion'] ?? ''),
    'lng_devolucion' => trim($_POST['lng_devolucion'] ?? ''),
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
if ($datos['fecha_inicio'] === '' || $datos['hora_inicio'] === '') $errores[] = 'La fecha y hora de inicio son obligatorias.';
if ($datos['fecha_fin'] === '' || $datos['hora_fin'] === '') $errores[] = 'La fecha y hora de fin son obligatorias.';
if ($datos['lugar_entrega_opcion'] === '') $errores[] = 'Debe seleccionar el lugar de entrega.';
if ($datos['lugar_devolucion_opcion'] === '') $errores[] = 'Debe seleccionar el lugar de devolución.';
if ($datos['acepta_datos'] !== 1) $errores[] = 'Debe aceptar el tratamiento de datos para continuar.';

if ($datos['lugar_entrega_opcion'] === 'otro' && $datos['otro_lugar_entrega'] === '') {
    $errores[] = 'Debe escribir el otro lugar de entrega.';
}

if ($datos['lugar_devolucion_opcion'] === 'otro' && $datos['otro_lugar_devolucion'] === '') {
    $errores[] = 'Debe escribir el otro lugar de devolución.';
}

$fecha_hora_inicio = $datos['fecha_inicio'] . ' ' . $datos['hora_inicio'] . ':00';
$fecha_hora_fin = $datos['fecha_fin'] . ' ' . $datos['hora_fin'] . ':00';

$ts_inicio = strtotime($fecha_hora_inicio);
$ts_fin = strtotime($fecha_hora_fin);

if ($ts_inicio === false || $ts_fin === false) {
    $errores[] = 'Las fechas no tienen un formato válido.';
} elseif ($ts_fin <= $ts_inicio) {
    $errores[] = 'La fecha y hora de fin debe ser posterior a la de inicio.';
}

$lugaresPredefinidos = [
    'aeropuerto' => 'Aeropuerto Ernesto Cortissoz',
    'oficina' => 'Oficina Benedetti Rent a Car'
];

$lugarEntregaFinal = $datos['lugar_entrega_opcion'] === 'otro'
    ? $datos['otro_lugar_entrega']
    : ($lugaresPredefinidos[$datos['lugar_entrega_opcion']] ?? '');

$lugarDevolucionFinal = $datos['lugar_devolucion_opcion'] === 'otro'
    ? $datos['otro_lugar_devolucion']
    : ($lugaresPredefinidos[$datos['lugar_devolucion_opcion']] ?? '');

$costoEntrega = $datos['lugar_entrega_opcion'] === 'otro' ? (float)$datos['costo_entrega_manual'] : 0;
$costoDevolucion = $datos['lugar_devolucion_opcion'] === 'otro' ? (float)$datos['costo_devolucion_manual'] : 0;

$distanciaKm = 0;
$costoKm = 0;

if (!empty($errores)) {
    $_SESSION['mensaje_reserva'] = implode(' ', $errores);
    $_SESSION['mensaje_reserva_tipo'] = 'error';
    header('Location: /benedetti-rent-a-car/public/reserva.php?id_vehiculo=' . urlencode($datos['id_vehiculo']));
    exit;
}

try {
    $conexion->beginTransaction();

    $sqlVehiculo = "SELECT * FROM vehiculos WHERE id_vehiculo = :id_vehiculo LIMIT 1";
    $stmtVehiculo = $conexion->prepare($sqlVehiculo);
    $stmtVehiculo->bindValue(':id_vehiculo', (int)$datos['id_vehiculo'], PDO::PARAM_INT);
    $stmtVehiculo->execute();
    $vehiculo = $stmtVehiculo->fetch(PDO::FETCH_ASSOC);

    if (!$vehiculo) {
        throw new Exception('El vehículo seleccionado no existe.');
    }

    $sqlDisponibilidad = "
        SELECT COUNT(*)
        FROM reservas
        WHERE id_vehiculo = :id_vehiculo
          AND estado_reserva IN ('confirmada', 'pago_parcial_confirmado')
          AND (:fecha_inicio < fecha_fin AND :fecha_fin > fecha_inicio)
    ";
    $stmtDisp = $conexion->prepare($sqlDisponibilidad);
    $stmtDisp->bindValue(':id_vehiculo', (int)$datos['id_vehiculo'], PDO::PARAM_INT);
    $stmtDisp->bindValue(':fecha_inicio', $fecha_hora_inicio);
    $stmtDisp->bindValue(':fecha_fin', $fecha_hora_fin);
    $stmtDisp->execute();

    if ((int)$stmtDisp->fetchColumn() > 0) {
        throw new Exception('El vehículo ya está reservado o bloqueado en ese rango de fecha y hora.');
    }

    $sqlCliente = "
        SELECT * FROM clientes
        WHERE numero_documento = :numero_documento
           OR correo = :correo
           OR telefono = :telefono
        LIMIT 1
    ";
    $stmtCliente = $conexion->prepare($sqlCliente);
    $stmtCliente->bindValue(':numero_documento', $datos['numero_documento']);
    $stmtCliente->bindValue(':correo', $datos['correo']);
    $stmtCliente->bindValue(':telefono', $datos['telefono']);
    $stmtCliente->execute();
    $cliente = $stmtCliente->fetch(PDO::FETCH_ASSOC);

    $tipoCliente = 'nuevo';
    $clienteReferente = null;

    if ($cliente) {
        $estadoCliente = $cliente['estado_cliente'] ?? null;
        $tipoCliente = ($estadoCliente === 'prospecto') ? 'nuevo' : 'existente';
    } elseif ($datos['codigo_referido'] !== '') {
        $sqlReferido = "SELECT * FROM clientes WHERE codigo_cliente = :codigo_cliente LIMIT 1";
        $stmtReferido = $conexion->prepare($sqlReferido);
        $stmtReferido->bindValue(':codigo_cliente', $datos['codigo_referido']);
        $stmtReferido->execute();
        $clienteReferente = $stmtReferido->fetch(PDO::FETCH_ASSOC);

        if ($clienteReferente) {
            $tipoCliente = 'referido';
        }
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
        $stmtInsertCliente->bindValue(':nombre', $datos['nombres']);
        $stmtInsertCliente->bindValue(':apellido', $datos['apellidos']);
        $stmtInsertCliente->bindValue(':nombres', $datos['nombres']);
        $stmtInsertCliente->bindValue(':apellidos', $datos['apellidos']);
        $stmtInsertCliente->bindValue(':correo', $datos['correo']);
        $stmtInsertCliente->bindValue(':telefono', $datos['telefono']);
        $stmtInsertCliente->bindValue(':tipo_documento', $datos['tipo_documento']);
        $stmtInsertCliente->bindValue(':numero_documento', $datos['numero_documento']);
        $stmtInsertCliente->execute();

        $idCliente = (int)$conexion->lastInsertId();
    } else {
        $idCliente = (int)$cliente['id_cliente'];
    }

    $precioDia = (float)($vehiculo['precio_dia'] ?? 0);
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

    if ($horasAdicionales === 1) {
        $recargoHorasExtra = 0;
    } elseif ($horasAdicionales >= 2 && $horasAdicionales <= 4) {
        $recargoHorasExtra = $horasAdicionales * $precioHoraExtra;
        $totalEstimado += $recargoHorasExtra;
    } elseif ($horasAdicionales >= 5) {
        $totalEstimado += $precioDia;
    }

    $totalEstimado += $costoEntrega + $costoDevolucion + $costoKm;
    $totalFinal = $totalEstimado;

    $codigoReserva = 'RES-' . date('YmdHis') . '-' . random_int(1000, 9999);

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
            bloquea_disponibilidad,
            costo_entrega,
            costo_devolucion,
            lat_entrega,
            lng_entrega,
            lat_devolucion,
            lng_devolucion,
            distancia_km,
            costo_km
        ) VALUES (
            :codigo_reserva,
            :id_cliente,
            :id_vehiculo,
            :fecha_inicio,
            :fecha_fin,
            :lugar_entrega,
            :lugar_devolucion,
            :observaciones,
            'pendiente_pago',
            'pendiente',
            :codigo_referido_usado,
            0,
            0,
            :total_pago,
            :total_estimado,
            :horas_extra_cobradas,
            :recargo_horas_extra,
            :total_final,
            0,
            :costo_entrega,
            :costo_devolucion,
            :lat_entrega,
            :lng_entrega,
            :lat_devolucion,
            :lng_devolucion,
            :distancia_km,
            :costo_km
        )
    ";

    $stmtInsertReserva = $conexion->prepare($sqlInsertReserva);
    $stmtInsertReserva->bindValue(':codigo_reserva', $codigoReserva);
    $stmtInsertReserva->bindValue(':id_cliente', $idCliente, PDO::PARAM_INT);
    $stmtInsertReserva->bindValue(':id_vehiculo', (int)$datos['id_vehiculo'], PDO::PARAM_INT);
    $stmtInsertReserva->bindValue(':fecha_inicio', $fecha_hora_inicio);
    $stmtInsertReserva->bindValue(':fecha_fin', $fecha_hora_fin);
    $stmtInsertReserva->bindValue(':lugar_entrega', $lugarEntregaFinal);
    $stmtInsertReserva->bindValue(':lugar_devolucion', $lugarDevolucionFinal);
    $stmtInsertReserva->bindValue(':observaciones', $datos['observaciones']);
    $stmtInsertReserva->bindValue(':codigo_referido_usado', $datos['codigo_referido'] !== '' ? $datos['codigo_referido'] : null);
    $stmtInsertReserva->bindValue(':total_pago', $totalEstimado);
    $stmtInsertReserva->bindValue(':total_estimado', $totalEstimado);
    $stmtInsertReserva->bindValue(':horas_extra_cobradas', $horasAdicionales, PDO::PARAM_INT);
    $stmtInsertReserva->bindValue(':recargo_horas_extra', $recargoHorasExtra);
    $stmtInsertReserva->bindValue(':total_final', $totalFinal);
    $stmtInsertReserva->bindValue(':costo_entrega', $costoEntrega);
    $stmtInsertReserva->bindValue(':costo_devolucion', $costoDevolucion);
    $stmtInsertReserva->bindValue(':lat_entrega', $datos['lat_entrega'] !== '' ? $datos['lat_entrega'] : null);
    $stmtInsertReserva->bindValue(':lng_entrega', $datos['lng_entrega'] !== '' ? $datos['lng_entrega'] : null);
    $stmtInsertReserva->bindValue(':lat_devolucion', $datos['lat_devolucion'] !== '' ? $datos['lat_devolucion'] : null);
    $stmtInsertReserva->bindValue(':lng_devolucion', $datos['lng_devolucion'] !== '' ? $datos['lng_devolucion'] : null);
    $stmtInsertReserva->bindValue(':distancia_km', $distanciaKm);
    $stmtInsertReserva->bindValue(':costo_km', $costoKm);
    $stmtInsertReserva->execute();

    $idReserva = (int)$conexion->lastInsertId();

    $referenciaPago = 'PAY-' . $codigoReserva;

    $sqlInsertPago = "
        INSERT INTO pagos (
            id_reserva,
            metodo_pago,
            monto,
            estado_pago,
            fecha_pago,
            referencia_pago,
            pasarela,
            comprobante_url,
            transaccion_id,
            respuesta_pasarela
        ) VALUES (
            :id_reserva,
            'pendiente',
            :monto,
            'pendiente',
            NOW(),
            :referencia_pago,
            'pendiente_gateway',
            NULL,
            NULL,
            NULL
        )
    ";

    $stmtInsertPago = $conexion->prepare($sqlInsertPago);
    $stmtInsertPago->bindValue(':id_reserva', $idReserva, PDO::PARAM_INT);
    $stmtInsertPago->bindValue(':monto', $totalFinal);
    $stmtInsertPago->bindValue(':referencia_pago', $referenciaPago);
    $stmtInsertPago->execute();

    $conexion->commit();

    $_SESSION['reserva_creada'] = [
        'id_reserva' => $idReserva,
        'codigo_reserva' => $codigoReserva,
        'referencia_pago' => $referenciaPago,
        'tipo_cliente' => $tipoCliente
    ];

    header('Location: /benedetti-rent-a-car/public/pago.php?id_reserva=' . urlencode((string)$idReserva));
    exit;

} catch (Throwable $e) {
    if ($conexion->inTransaction()) {
        $conexion->rollBack();
    }

    $_SESSION['mensaje_reserva'] = 'Ocurrió un error al procesar la reserva: ' . $e->getMessage();
    $_SESSION['mensaje_reserva_tipo'] = 'error';
    header('Location: /benedetti-rent-a-car/public/reserva.php?id_vehiculo=' . urlencode($datos['id_vehiculo']));
    exit;
}