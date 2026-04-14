<?php
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../views/partials/header.php';
require_once __DIR__ . '/../views/partials/navbar.php';

$idReserva = isset($_GET['id_reserva']) ? (int)$_GET['id_reserva'] : 0;

if ($idReserva <= 0) {
    echo "<main class='page-section'><div class='container'><div class='placeholder-box'><h2>Reserva no válida</h2></div></div></main>";
    require_once __DIR__ . '/../views/partials/footer.php';
    exit;
}

$sql = "
    SELECT
        r.id_reserva,
        r.codigo_reserva,
        r.estado_reserva,
        r.total_final,
        r.total_estimado,
        r.fecha_inicio,
        r.fecha_fin,
        r.lugar_entrega,
        r.lugar_devolucion,
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
    echo "<main class='page-section'><div class='container'><div class='placeholder-box'><h2>No se encontró la reserva</h2></div></div></main>";
    require_once __DIR__ . '/../views/partials/footer.php';
    exit;
}

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
?>

<style>
.pago-page {
    padding-top: 20px;
}

.pago-title {
    margin-bottom: 24px;
}

.pago-layout {
    display: grid;
    grid-template-columns: 320px 1fr;
    gap: 28px;
    align-items: start;
}

.pago-sidebar {
    position: sticky;
    top: 110px;
}

.pago-card {
    background: #1a2740;
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 20px;
    padding: 24px;
    box-shadow: 0 18px 35px rgba(0,0,0,0.18);
}

.pago-block {
    background: #1e2d47;
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 16px;
    padding: 18px;
    margin-bottom: 18px;
}

.pago-block-title {
    font-size: 1rem;
    font-weight: 700;
    color: #ffffff;
    margin-bottom: 14px;
    padding-bottom: 8px;
    border-bottom: 1px solid rgba(255,255,255,0.08);
}

.pago-resumen p {
    margin-bottom: 8px;
    line-height: 1.6;
    color: #d8e2f0;
}

.pago-resumen strong {
    color: #ffffff;
}

.pago-total {
    font-size: 1.22rem;
    font-weight: 700;
    color: #38bdf8;
}

.pago-note {
    color: #d8e2f0;
    line-height: 1.7;
}

.metodos-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 14px;
}

.metodo-form {
    margin: 0;
}

.metodo-btn {
    width: 100%;
    border: 1px solid rgba(255,255,255,0.08);
    background: #08152d;
    color: #ffffff;
    border-radius: 14px;
    padding: 16px;
    text-align: left;
    cursor: pointer;
    transition: all 0.25s ease;
}

.metodo-btn:hover {
    transform: translateY(-2px);
    border-color: #f7c600;
    box-shadow: 0 10px 24px rgba(0,0,0,0.18);
}

.metodo-btn strong {
    display: block;
    font-size: 1rem;
    margin-bottom: 6px;
    color: #f7c600;
}

.metodo-btn span {
    display: block;
    color: #d8e2f0;
    font-size: 0.92rem;
    line-height: 1.5;
}

.badge-cliente {
    display: inline-block;
    margin-top: 8px;
    padding: 8px 12px;
    border-radius: 999px;
    background: rgba(56, 189, 248, 0.12);
    border: 1px solid rgba(56, 189, 248, 0.22);
    color: #7dd3fc;
    font-size: 0.88rem;
    font-weight: 700;
}

.vehiculo-mini-img {
    width: 100%;
    height: 220px;
    object-fit: cover;
    display: block;
    border-radius: 16px;
    margin-bottom: 14px;
}

@media (max-width: 992px) {
    .pago-layout {
        grid-template-columns: 1fr;
    }

    .pago-sidebar {
        position: static;
    }
}

@media (max-width: 768px) {
    .metodos-grid {
        grid-template-columns: 1fr;
    }

    .pago-card {
        padding: 16px;
    }

    .pago-block {
        padding: 14px;
    }
}
</style>

<main class="page-section pago-page">
    <div class="container">
        <h1 class="pago-title">Selecciona tu método de pago</h1>

        <div class="pago-layout">
            <aside class="pago-sidebar">
                <div class="vehiculo-card vehiculo-resumen">
                    <?php if (!empty($reserva['imagen'])): ?>
                        <img
                            src="/benedetti-rent-a-car/assets/img/<?php echo htmlspecialchars($reserva['imagen']); ?>"
                            alt="<?php echo htmlspecialchars(($reserva['marca'] ?? '') . ' ' . ($reserva['modelo'] ?? '')); ?>"
                            class="vehiculo-mini-img"
                        >
                    <?php endif; ?>

                    <div class="vehiculo-info">
                        <h3><?php echo htmlspecialchars(($reserva['marca'] ?? '') . ' ' . ($reserva['modelo'] ?? '')); ?></h3>
                        <p><strong>Reserva:</strong> <?php echo htmlspecialchars($reserva['codigo_reserva']); ?></p>
                        <p><strong>Referencia:</strong> <?php echo htmlspecialchars($reserva['referencia_pago']); ?></p>
                        <p><strong>Cliente:</strong> <?php echo htmlspecialchars(($reserva['nombres'] ?? '') . ' ' . ($reserva['apellidos'] ?? '')); ?></p>
                        <span class="badge-cliente">Cliente <?php echo htmlspecialchars($tipoCliente); ?></span>
                    </div>
                </div>
            </aside>

            <section class="pago-card">
                <div class="pago-block pago-resumen">
                    <h2 class="pago-block-title">Resumen de la reserva</h2>
                    <p><strong>Fecha inicio:</strong> <?php echo htmlspecialchars($reserva['fecha_inicio']); ?></p>
                    <p><strong>Fecha fin:</strong> <?php echo htmlspecialchars($reserva['fecha_fin']); ?></p>
                    <p><strong>Entrega:</strong> <?php echo htmlspecialchars($reserva['lugar_entrega']); ?></p>
                    <p><strong>Devolución:</strong> <?php echo htmlspecialchars($reserva['lugar_devolucion']); ?></p>
                    <p class="pago-total"><strong>Total a pagar:</strong> $<?php echo number_format((float)$reserva['total_final'], 0, ',', '.'); ?></p>
                </div>

                <div class="pago-block">
                    <h2 class="pago-block-title">Métodos habilitados</h2>

                    <?php if ($tipoCliente === 'nuevo'): ?>
                        <p class="pago-note">
                            Por políticas de seguridad de Benedetti Rent a Car, para clientes nuevos solo está habilitado el pago con tarjeta.
                        </p>
                    <?php else: ?>
                        <p class="pago-note">
                            Como cliente recurrente o referido, tienes habilitados más métodos de pago.
                        </p>
                    <?php endif; ?>

                    <div class="metodos-grid" style="margin-top:16px;">
                        <?php if (in_array('tarjeta', $metodosPermitidos, true)): ?>
                            <form action="/benedetti-rent-a-car/public/iniciar_pago.php" method="post" class="metodo-form">
                                <input type="hidden" name="id_reserva" value="<?php echo (int)$reserva['id_reserva']; ?>">
                                <input type="hidden" name="metodo_pago" value="tarjeta">
                                <button type="submit" class="metodo-btn">
                                    <strong>Pagar con tarjeta</strong>
                                    <span>Pago online para continuar con el proceso de confirmación.</span>
                                </button>
                            </form>
                        <?php endif; ?>

                        <?php if (in_array('pse', $metodosPermitidos, true)): ?>
                            <form action="/benedetti-rent-a-car/public/iniciar_pago.php" method="post" class="metodo-form">
                                <input type="hidden" name="id_reserva" value="<?php echo (int)$reserva['id_reserva']; ?>">
                                <input type="hidden" name="metodo_pago" value="pse">
                                <button type="submit" class="metodo-btn">
                                    <strong>Pagar con PSE</strong>
                                    <span>Pago desde cuenta bancaria con validación posterior.</span>
                                </button>
                            </form>
                        <?php endif; ?>

                        <?php if (in_array('qr', $metodosPermitidos, true)): ?>
                            <form action="/benedetti-rent-a-car/public/iniciar_pago.php" method="post" class="metodo-form">
                                <input type="hidden" name="id_reserva" value="<?php echo (int)$reserva['id_reserva']; ?>">
                                <input type="hidden" name="metodo_pago" value="qr">
                                <button type="submit" class="metodo-btn">
                                    <strong>Pagar con QR</strong>
                                    <span>Transferencia o pago por código QR según tu flujo interno.</span>
                                </button>
                            </form>
                        <?php endif; ?>

                        <?php if (in_array('efectivo', $metodosPermitidos, true)): ?>
                            <form action="/benedetti-rent-a-car/public/iniciar_pago.php" method="post" class="metodo-form">
                                <input type="hidden" name="id_reserva" value="<?php echo (int)$reserva['id_reserva']; ?>">
                                <input type="hidden" name="metodo_pago" value="efectivo">
                                <button type="submit" class="metodo-btn">
                                    <strong>Pago en efectivo con anticipo</strong>
                                    <span>Tu reserva queda sujeta a confirmación del anticipo requerido.</span>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../views/partials/footer.php'; ?>