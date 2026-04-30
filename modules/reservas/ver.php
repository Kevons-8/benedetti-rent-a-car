<?php
require_once "../../includes/auth.php";
require_once "../../config/database.php";

if (!isset($_GET["id"])) {
    die("ID de reserva no especificado.");
}

$id_reserva = (int) $_GET["id"];

try {
    $sql = "SELECT 
                reservas.*,
                clientes.tipo_documento,
                clientes.numero_documento,
                clientes.nombre,
                clientes.apellido,
                clientes.telefono,
                clientes.correo,
                clientes.direccion,
                clientes.licencia_conduccion,
                vehiculos.codigo_vehiculo,
                vehiculos.marca,
                vehiculos.modelo,
                vehiculos.color,
                vehiculos.placa,
                vehiculos.precio_dia,
                vehiculos.precio_especial_3_dias
            FROM reservas
            INNER JOIN clientes ON reservas.id_cliente = clientes.id_cliente
            INNER JOIN vehiculos ON reservas.id_vehiculo = vehiculos.id_vehiculo
            WHERE reservas.id_reserva = :id_reserva";

    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(':id_reserva', $id_reserva, PDO::PARAM_INT);
    $stmt->execute();

    $reserva = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reserva) {
        die("Reserva no encontrada.");
    }

} catch (PDOException $e) {
    die("Error al obtener el detalle de la reserva: " . $e->getMessage());
}

$estado = strtolower($reserva["estado_reserva"] ?? "pendiente");

if ($estado === "confirmada" || $estado === "activa" || $estado === "aprobada") {
    $estado_clase = "status-success";
} elseif ($estado === "finalizada" || $estado === "entregada") {
    $estado_clase = "status-info";
} elseif ($estado === "cancelada") {
    $estado_clase = "status-danger";
} else {
    $estado_clase = "status-warning";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Detalle de Reserva</title>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: Arial, sans-serif;
    min-height: 100vh;
    background:
        linear-gradient(135deg, rgba(3, 13, 31, 0.86), rgba(6, 32, 71, 0.82)),
        url("/benedetti-rent-a-car/assets/img/fondo_vehiculos.png") center center / cover no-repeat fixed;
    color: #ffffff;
    padding: 45px 7%;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
}

.header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 24px;
    margin-bottom: 28px;
}

.badge {
    display: inline-flex;
    padding: 9px 15px;
    border-radius: 999px;
    background: rgba(34, 197, 94, 0.12);
    border: 1px solid rgba(34, 197, 94, 0.35);
    color: #86efac;
    font-size: 0.9rem;
    font-weight: 800;
    margin-bottom: 14px;
}

h1 {
    font-size: clamp(2rem, 4vw, 3rem);
    color: #ffffff;
    margin-bottom: 10px;
}

.header p {
    color: #d8e2f0;
    max-width: 720px;
    line-height: 1.6;
}

.header-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    justify-content: flex-end;
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 48px;
    padding: 12px 22px;
    border-radius: 999px;
    border: none;
    font-size: 0.92rem;
    font-weight: 900;
    cursor: pointer;
    transition: all 0.25s ease;
    text-decoration: none;
    white-space: nowrap;
    color: #ffffff;
}

.btn:hover {
    transform: translateY(-2px);
    filter: brightness(1.08);
}

.btn-edit {
    background: linear-gradient(180deg, #6eff1f 0%, #38d600 45%, #19a500 100%);
    box-shadow:
        inset 0 2px 0 rgba(255, 255, 255, 0.32),
        0 12px 24px rgba(34, 197, 94, 0.28);
}

.btn-finish {
    background: linear-gradient(180deg, #60a5fa 0%, #2563eb 45%, #1d4ed8 100%);
    box-shadow:
        inset 0 2px 0 rgba(255, 255, 255, 0.28),
        0 12px 24px rgba(37, 99, 235, 0.30);
}

.btn-delete {
    background: linear-gradient(180deg, #ff5f5f 0%, #ef4444 45%, #b91c1c 100%);
    box-shadow:
        inset 0 2px 0 rgba(255, 255, 255, 0.28),
        0 12px 24px rgba(239, 68, 68, 0.30);
}

.btn-dark {
    background: rgba(3, 10, 24, 0.65);
    border: 1px solid rgba(255, 255, 255, 0.14);
}

.summary-card,
.section-card {
    background: rgba(15, 28, 51, 0.90);
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 26px;
    padding: 28px;
    box-shadow: 0 24px 70px rgba(0, 0, 0, 0.35);
    backdrop-filter: blur(16px);
}

.summary-card {
    margin-bottom: 24px;
}

.summary-top {
    display: flex;
    justify-content: space-between;
    gap: 22px;
    align-items: center;
    padding-bottom: 22px;
    margin-bottom: 22px;
    border-bottom: 1px solid rgba(255,255,255,0.10);
}

.summary-top h2 {
    font-size: 1.7rem;
    color: #ffffff;
    margin-bottom: 6px;
}

.summary-top p {
    color: #cfd8e6;
}

.status {
    display: inline-flex;
    padding: 9px 15px;
    border-radius: 999px;
    font-size: 0.85rem;
    font-weight: 900;
    white-space: nowrap;
}

.status-success {
    color: #dcfce7;
    background: rgba(34, 197, 94, 0.18);
    border: 1px solid rgba(34, 197, 94, 0.38);
}

.status-warning {
    color: #fef3c7;
    background: rgba(245, 158, 11, 0.18);
    border: 1px solid rgba(245, 158, 11, 0.38);
}

.status-info {
    color: #dbeafe;
    background: rgba(59, 130, 246, 0.18);
    border: 1px solid rgba(59, 130, 246, 0.38);
}

.status-danger {
    color: #fee2e2;
    background: rgba(239, 68, 68, 0.18);
    border: 1px solid rgba(239, 68, 68, 0.38);
}

.quick-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 14px;
}

.quick-item {
    padding: 16px;
    border-radius: 18px;
    background: rgba(8, 21, 45, 0.75);
    border: 1px solid rgba(255,255,255,0.10);
}

.quick-item span {
    display: block;
    color: #94a3b8;
    font-size: 0.82rem;
    font-weight: 800;
    margin-bottom: 7px;
}

.quick-item strong {
    color: #ffffff;
    font-size: 1rem;
    word-break: break-word;
}

.content-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 24px;
}

.section-card.full {
    grid-column: 1 / -1;
}

.section-title {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 14px;
    padding-bottom: 16px;
    margin-bottom: 18px;
    border-bottom: 1px solid rgba(255,255,255,0.10);
}

.section-title h2 {
    color: #ffffff;
    font-size: 1.35rem;
}

.section-number {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: rgba(34, 197, 94, 0.16);
    border: 1px solid rgba(34, 197, 94, 0.35);
    color: #86efac;
    font-weight: 900;
}

.detail-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 14px;
}

.detail-item {
    padding: 15px 16px;
    border-radius: 16px;
    color: #d8e2f0;
    background: rgba(8, 21, 45, 0.75);
    border: 1px solid rgba(255,255,255,0.10);
    word-break: break-word;
}

.detail-item.full {
    grid-column: 1 / -1;
}

.detail-item strong {
    display: block;
    color: #ffffff;
    margin-bottom: 6px;
    font-size: 0.9rem;
}

.total-box {
    padding: 20px;
    border-radius: 20px;
    background: rgba(34, 197, 94, 0.13);
    border: 1px solid rgba(34, 197, 94, 0.35);
}

.total-box strong {
    display: block;
    color: #86efac;
    font-size: 0.95rem;
    margin-bottom: 8px;
}

.total-box span {
    color: #ffffff;
    font-size: 2rem;
    font-weight: 900;
}

.observaciones {
    color: #d8e2f0;
    line-height: 1.7;
    padding: 18px;
    border-radius: 18px;
    background: rgba(8, 21, 45, 0.75);
    border: 1px solid rgba(255,255,255,0.10);
}

@media (max-width: 1000px) {
    .quick-grid,
    .content-grid {
        grid-template-columns: 1fr;
    }

    .section-card.full {
        grid-column: auto;
    }
}

@media (max-width: 700px) {
    body {
        padding: 35px 5%;
    }

    .header,
    .summary-top {
        flex-direction: column;
        align-items: flex-start;
    }

    .header-actions {
        width: 100%;
        flex-direction: column;
    }

    .btn {
        width: 100%;
    }

    .detail-grid {
        grid-template-columns: 1fr;
    }

    .detail-item.full {
        grid-column: auto;
    }

    .summary-card,
    .section-card {
        padding: 22px;
        border-radius: 22px;
    }
}
</style>
</head>

<body>

<div class="container">

    <div class="header">
        <div>
            <span class="badge">Detalle administrativo</span>
            <h1>Detalle de Reserva</h1>
            <p>
                Consulta la información completa de la reserva, cliente, vehículo,
                fechas, liquidación y observaciones.
            </p>
        </div>

        <div class="header-actions">
    <a href="editar.php?id=<?php echo $reserva["id_reserva"]; ?>" class="btn btn-edit">
        Editar reserva
    </a>

    <a href="listar.php" class="btn btn-dark">
        ← Volver
    </a>
</div>
    </div>

    <div class="summary-card">

        <div class="summary-top">
            <div>
                <h2><?php echo htmlspecialchars($reserva["codigo_reserva"]); ?></h2>
                <p>Reserva #<?php echo htmlspecialchars($reserva["id_reserva"]); ?></p>
            </div>

            <span class="status <?php echo $estado_clase; ?>">
                <?php echo htmlspecialchars($reserva["estado_reserva"]); ?>
            </span>
        </div>

        <div class="quick-grid">
            <div class="quick-item">
                <span>Cliente</span>
                <strong><?php echo htmlspecialchars($reserva["nombre"] . " " . $reserva["apellido"]); ?></strong>
            </div>

            <div class="quick-item">
                <span>Vehículo</span>
                <strong><?php echo htmlspecialchars($reserva["marca"] . " " . $reserva["modelo"]); ?></strong>
            </div>

            <div class="quick-item">
                <span>Fecha de inicio</span>
                <strong><?php echo htmlspecialchars($reserva["fecha_inicio"]); ?></strong>
            </div>

            <div class="quick-item">
                <span>Total pagado</span>
                <strong>$<?php echo number_format((float)$reserva["total_pago"], 0, ',', '.'); ?></strong>
            </div>
        </div>

    </div>

    <div class="content-grid">

        <div class="section-card">
            <div class="section-title">
                <h2>Información general</h2>
                <span class="section-number">1</span>
            </div>

            <div class="detail-grid">
                <div class="detail-item">
                    <strong>ID Reserva</strong>
                    <?php echo htmlspecialchars($reserva["id_reserva"]); ?>
                </div>

                <div class="detail-item">
                    <strong>Código de reserva</strong>
                    <?php echo htmlspecialchars($reserva["codigo_reserva"]); ?>
                </div>

                <div class="detail-item">
                    <strong>Fecha de inicio</strong>
                    <?php echo htmlspecialchars($reserva["fecha_inicio"]); ?>
                </div>

                <div class="detail-item">
                    <strong>Fecha de fin</strong>
                    <?php echo htmlspecialchars($reserva["fecha_fin"]); ?>
                </div>

                <div class="detail-item">
                    <strong>Entrega real</strong>
                    <?php echo !empty($reserva["fecha_entrega_real"]) ? htmlspecialchars($reserva["fecha_entrega_real"]) : "No registrada"; ?>
                </div>

                <div class="detail-item">
                    <strong>Fecha de creación</strong>
                    <?php echo htmlspecialchars($reserva["fecha_creacion"]); ?>
                </div>
            </div>
        </div>

        <div class="section-card">
            <div class="section-title">
                <h2>Información del cliente</h2>
                <span class="section-number">2</span>
            </div>

            <div class="detail-grid">
                <div class="detail-item">
                    <strong>Tipo documento</strong>
                    <?php echo htmlspecialchars($reserva["tipo_documento"]); ?>
                </div>

                <div class="detail-item">
                    <strong>Número documento</strong>
                    <?php echo htmlspecialchars($reserva["numero_documento"]); ?>
                </div>

                <div class="detail-item full">
                    <strong>Nombre completo</strong>
                    <?php echo htmlspecialchars($reserva["nombre"] . " " . $reserva["apellido"]); ?>
                </div>

                <div class="detail-item">
                    <strong>Teléfono</strong>
                    <?php echo htmlspecialchars($reserva["telefono"]); ?>
                </div>

                <div class="detail-item">
                    <strong>Correo</strong>
                    <?php echo !empty($reserva["correo"]) ? htmlspecialchars($reserva["correo"]) : "No registrado"; ?>
                </div>

                <div class="detail-item full">
                    <strong>Dirección</strong>
                    <?php echo !empty($reserva["direccion"]) ? htmlspecialchars($reserva["direccion"]) : "No registrada"; ?>
                </div>

                <div class="detail-item full">
                    <strong>Licencia de conducción</strong>
                    <?php echo !empty($reserva["licencia_conduccion"]) ? htmlspecialchars($reserva["licencia_conduccion"]) : "No registrada"; ?>
                </div>
            </div>
        </div>

        <div class="section-card">
            <div class="section-title">
                <h2>Información del vehículo</h2>
                <span class="section-number">3</span>
            </div>

            <div class="detail-grid">
                <div class="detail-item">
                    <strong>Código vehículo</strong>
                    <?php echo htmlspecialchars($reserva["codigo_vehiculo"]); ?>
                </div>

                <div class="detail-item">
                    <strong>Placa</strong>
                    <?php echo htmlspecialchars($reserva["placa"]); ?>
                </div>

                <div class="detail-item full">
                    <strong>Vehículo</strong>
                    <?php echo htmlspecialchars($reserva["marca"] . " " . $reserva["modelo"]); ?>
                </div>

                <div class="detail-item">
                    <strong>Color</strong>
                    <?php echo htmlspecialchars($reserva["color"]); ?>
                </div>

                <div class="detail-item">
                    <strong>Precio por día</strong>
                    $<?php echo number_format((float)$reserva["precio_dia"], 0, ',', '.'); ?>
                </div>

                <div class="detail-item full">
                    <strong>Precio especial desde día 3</strong>
                    <?php
                    if ($reserva["precio_especial_3_dias"] !== null) {
                        echo "$" . number_format((float)$reserva["precio_especial_3_dias"], 0, ',', '.');
                    } else {
                        echo "No aplica";
                    }
                    ?>
                </div>
            </div>
        </div>

        <div class="section-card">
            <div class="section-title">
                <h2>Pago y liquidación</h2>
                <span class="section-number">4</span>
            </div>

            <div class="detail-grid">
                <div class="detail-item">
                    <strong>Horas de gracia</strong>
                    <?php echo htmlspecialchars($reserva["horas_gracia"]); ?>
                </div>

                <div class="detail-item">
                    <strong>Tarifa hora extra</strong>
                    $<?php echo number_format((float)$reserva["tarifa_hora_extra"], 0, ',', '.'); ?>
                </div>

                <div class="detail-item">
                    <strong>Horas extra</strong>
                    <?php echo htmlspecialchars($reserva["horas_extra"]); ?>
                </div>

                <div class="detail-item">
                    <strong>Cargo extra</strong>
                    $<?php echo number_format((float)$reserva["cargo_extra"], 0, ',', '.'); ?>
                </div>

                <div class="total-box detail-item full">
                    <strong>Total pagado</strong>
                    <span>$<?php echo number_format((float)$reserva["total_pago"], 0, ',', '.'); ?></span>
                </div>
            </div>
        </div>

        <div class="section-card full">
            <div class="section-title">
                <h2>Observaciones</h2>
                <span class="section-number">5</span>
            </div>

            <div class="observaciones">
                <?php
                if (!empty($reserva["observaciones"])) {
                    echo nl2br(htmlspecialchars($reserva["observaciones"]));
                } else {
                    echo "Sin observaciones.";
                }
                ?>
            </div>
        </div>

    </div>

</div>

</body>
</html>