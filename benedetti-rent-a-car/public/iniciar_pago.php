<?php
session_start();
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../includes/pago_simulado.php';
$base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
$idReserva = filter_var($_POST['id_reserva'] ?? $_GET['id_reserva'] ?? null, FILTER_VALIDATE_INT);
$autorizacion = $_SESSION['reservas_autorizadas'][$idReserva ?: 0] ?? null;
$error = null;
$resumen = null;
try {
    if (!$idReserva || !$autorizacion) {
        http_response_code(403);
        throw new DomainException('No tienes una reserva autorizada en esta sesión. Inicia nuevamente la reserva.');
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!is_string($_POST['csrf_pago'] ?? null) || !hash_equals($_SESSION['csrf_pago'] ?? '', $_POST['csrf_pago'])) {
            http_response_code(403);
            throw new DomainException('La solicitud no es válida. Vuelve al resumen de la reserva.');
        }
        $metodo = is_string($_POST['metodo_pago'] ?? null) ? $_POST['metodo_pago'] : '';
        $idPago = procesarPagoSimulado($conexion, $idReserva, $metodo, $autorizacion['tipo_cliente']);
        $_SESSION['pagos_procesados'][$idReserva] = $idPago;
        header('Location: ' . $base . '/iniciar_pago.php?id_reserva=' . $idReserva, true, 303);
        exit;
    }
    if ($_SERVER['REQUEST_METHOD'] !== 'GET' || empty($_SESSION['pagos_procesados'][$idReserva])) {
        throw new DomainException('Primero selecciona el método de pago en el resumen de tu reserva.');
    }
    $stmt = $conexion->prepare("SELECT r.codigo_reserva, r.estado_reserva, c.nombres, c.apellidos, c.nombre, c.apellido,
        v.marca, v.modelo, p.referencia_pago, p.metodo_pago, p.pasarela, p.monto, p.estado_pago
        FROM reservas r JOIN clientes c ON c.id_cliente = r.id_cliente
        JOIN vehiculos v ON v.id_vehiculo = r.id_vehiculo JOIN pagos p ON p.id_reserva = r.id_reserva
        WHERE r.id_reserva = ? AND p.id_pago = ?");
    $stmt->execute([$idReserva, $_SESSION['pagos_procesados'][$idReserva]]);
    $resumen = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$resumen) throw new DomainException('No se encontró el resultado del pago.');
} catch (DomainException $e) {
    $error = $e->getMessage();
    if (http_response_code() < 400) http_response_code(409);
} catch (Throwable $e) {
    error_log('Pago simulado: ' . $e->getMessage());
    http_response_code(500);
    $error = 'No se pudo completar el pago. Vuelve al resumen para consultar el estado e intentarlo nuevamente.';
}
function pagoHtml($value): string { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
require_once __DIR__ . '/../views/partials/header.php';
require_once __DIR__ . '/../views/partials/navbar.php';
?>
<main class="page-section"><div class="container"><div class="payment-box">
<?php if ($error): ?>
    <h2>No se completó la solicitud</h2><p><?= pagoHtml($error) ?></p>
<?php else: ?>
    <h2><?= $resumen['estado_pago'] === 'aprobado' ? 'Pago simulado aprobado' : 'Método de pago registrado' ?></h2>
    <p><strong>Reserva:</strong> <?= pagoHtml($resumen['codigo_reserva']) ?></p>
    <p><strong>Referencia de pago:</strong> <?= pagoHtml($resumen['referencia_pago']) ?></p>
    <p><strong>Cliente:</strong> <?= pagoHtml(($resumen['nombres'] ?: $resumen['nombre']) . ' ' . ($resumen['apellidos'] ?: $resumen['apellido'])) ?></p>
    <p><strong>Vehículo:</strong> <?= pagoHtml($resumen['marca'] . ' ' . $resumen['modelo']) ?></p>
    <p><strong>Método elegido:</strong> <?= pagoHtml($resumen['metodo_pago']) ?></p>
    <p><strong>Total de la reserva:</strong> $<?= number_format((float)$resumen['monto'], 0, ',', '.') ?></p>
    <p><strong>Estado de reserva:</strong> <?= pagoHtml($resumen['estado_reserva']) ?></p>
    <p><strong>Estado de pago:</strong> <?= pagoHtml($resumen['estado_pago']) ?></p>
    <p><?= $resumen['estado_pago'] === 'aprobado' ? 'Simulación completada. No se ha realizado ningún cobro real.' : 'Pendiente de recibir y validar el anticipo. La reserva todavía no está confirmada.' ?></p>
<?php endif; ?>
<?php if ($autorizacion): ?><p><a href="<?= pagoHtml($base) ?>/pago.php?id_reserva=<?= (int)$idReserva ?>">Volver al resumen</a></p><?php endif; ?>
</div></div></main>
<?php require_once __DIR__ . '/../views/partials/footer.php'; ?>
