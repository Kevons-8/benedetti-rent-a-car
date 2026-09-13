<?php
// El monto y las relaciones siempre se obtienen de la base de datos.
function procesarPagoSimulado(PDO $db, int $idReserva, string $metodo, string $tipoCliente): int
{
    $permitidos = $tipoCliente === 'nuevo' ? ['tarjeta'] : ['tarjeta', 'pse', 'qr', 'efectivo'];
    if (!in_array($metodo, $permitidos, true)) {
        throw new DomainException('El método de pago no está permitido para esta reserva.');
    }
    $db->beginTransaction();
    try {
        $stmt = $db->prepare('SELECT id_vehiculo FROM reservas WHERE id_reserva = ?');
        $stmt->execute([$idReserva]);
        $idVehiculo = $stmt->fetchColumn();
        if (!$idVehiculo) throw new DomainException('No se encontró la reserva.');
        // Todas las confirmaciones de este flujo se serializan por vehículo.
        $stmt = $db->prepare('SELECT estado FROM vehiculos WHERE id_vehiculo = ? FOR UPDATE');
        $stmt->execute([$idVehiculo]);
        $estadoVehiculo = $stmt->fetchColumn();
        $stmt = $db->prepare('SELECT * FROM reservas WHERE id_reserva = ? FOR UPDATE');
        $stmt->execute([$idReserva]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$r || (int)$r['id_vehiculo'] !== (int)$idVehiculo) {
            throw new DomainException('La reserva cambió. Vuelve a consultar el resumen.');
        }
        if (in_array($r['estado_reserva'], ['cancelada', 'finalizada'], true)) {
            throw new DomainException('Esta reserva ya no admite pagos.');
        }
        $stmt = $db->prepare('SELECT * FROM pagos WHERE id_reserva = ? ORDER BY id_pago FOR UPDATE');
        $stmt->execute([$idReserva]);
        $pagos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (count($pagos) !== 1) throw new DomainException('La reserva requiere revisar sus pagos antes de continuar.');
        $p = $pagos[0];
        if ($p['estado_pago'] === 'aprobado') {
            if ($r['estado_reserva'] !== 'confirmada') throw new DomainException('El pago aprobado requiere revisión de la reserva.');
            $db->commit();
            return (int)$p['id_pago'];
        }
        if ($p['estado_pago'] !== 'pendiente' || !in_array($r['estado_reserva'], ['pendiente', ''], true)) {
            throw new DomainException('El estado actual no permite procesar este pago.');
        }
        if (!$estadoVehiculo || in_array($estadoVehiculo, ['mantenimiento', 'inactivo'], true)) {
            throw new DomainException('El vehículo no está disponible.');
        }
        $stmt = $db->prepare("SELECT id_reserva FROM reservas WHERE id_vehiculo = ? AND id_reserva <> ?
            AND (estado_reserva = 'confirmada' OR (bloquea_disponibilidad = 1 AND estado_reserva NOT IN ('cancelada','finalizada')))
            AND fecha_inicio < ? AND fecha_fin > ? LIMIT 1 FOR UPDATE");
        $stmt->execute([$idVehiculo, $idReserva, $r['fecha_fin'], $r['fecha_inicio']]);
        if ($stmt->fetchColumn()) throw new DomainException('El vehículo ya está reservado en esas fechas. El pago no se ha aprobado.');
        $total = (float)$r['total_final'] > 0 ? $r['total_final'] : $r['total_estimado'];
        if ((float)$total <= 0 || abs((float)$p['monto'] - (float)$total) > 0.009) {
            throw new DomainException('El importe del pago requiere revisión.');
        }
        $efectivo = $metodo === 'efectivo';
        $transaccion = $efectivo ? null : 'SIM-' . bin2hex(random_bytes(12));
        $respuesta = json_encode(['simulado' => true, 'resultado' => $efectivo ? 'pendiente_anticipo' : 'aprobado'], JSON_UNESCAPED_UNICODE);
        $stmt = $db->prepare('UPDATE pagos SET metodo_pago = ?, estado_pago = ?, pasarela = ?, transaccion_id = ?, respuesta_pasarela = ?, fecha_pago = NOW() WHERE id_pago = ?');
        $stmt->execute([$metodo, $efectivo ? 'pendiente' : 'aprobado', $efectivo ? 'efectivo_pendiente' : 'wompi_simulado', $transaccion, $respuesta, $p['id_pago']]);
        $stmt = $db->prepare('UPDATE reservas SET estado_reserva = ?, modo_pago = ?, bloquea_disponibilidad = ? WHERE id_reserva = ?');
        $stmt->execute([$efectivo ? 'pendiente' : 'confirmada', $metodo, $efectivo ? 0 : 1, $idReserva]);
        $db->commit();
        return (int)$p['id_pago'];
    } catch (Throwable $e) {
        if ($db->inTransaction()) $db->rollBack();
        throw $e;
    }
}
