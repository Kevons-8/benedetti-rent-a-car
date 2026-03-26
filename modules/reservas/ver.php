<?php
require_once "../../includes/auth.php";
require_once "../../config/database.php";

/* ==========================
VALIDAR ID DE LA RESERVA
========================== */
if (!isset($_GET["id"])) {
    die("ID de reserva no especificado.");
}

$id_reserva = (int) $_GET["id"];

/* ==========================
OBTENER DETALLE COMPLETO DE LA RESERVA
========================== */
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
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de Reserva</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        .bloque {
            border: 1px solid #ccc;
            padding: 15px;
            margin-bottom: 20px;
        }

        h1, h2 {
            margin-bottom: 10px;
        }

        p {
            margin: 6px 0;
        }

        .acciones {
            margin-top: 20px;
        }

        .acciones a {
            text-decoration: none;
            margin-right: 15px;
        }
    </style>
</head>
<body>

    <h1>Detalle de Reserva</h1>

    <div class="bloque">
        <h2>1. Información General</h2>
        <p><strong>ID Reserva:</strong> <?php echo htmlspecialchars($reserva["id_reserva"]); ?></p>
        <p><strong>Código de Reserva:</strong> <?php echo htmlspecialchars($reserva["codigo_reserva"]); ?></p>
        <p><strong>Estado de la Reserva:</strong> <?php echo htmlspecialchars($reserva["estado_reserva"]); ?></p>
        <p><strong>Fecha de Inicio:</strong> <?php echo htmlspecialchars($reserva["fecha_inicio"]); ?></p>
        <p><strong>Fecha de Fin:</strong> <?php echo htmlspecialchars($reserva["fecha_fin"]); ?></p>
        <p><strong>Fecha Real de Entrega:</strong>
            <?php echo !empty($reserva["fecha_entrega_real"]) ? htmlspecialchars($reserva["fecha_entrega_real"]) : "No registrada"; ?>
        </p>
        <p><strong>Fecha de Creación:</strong> <?php echo htmlspecialchars($reserva["fecha_creacion"]); ?></p>
    </div>

    <div class="bloque">
        <h2>2. Información del Cliente</h2>
        <p><strong>Tipo de Documento:</strong> <?php echo htmlspecialchars($reserva["tipo_documento"]); ?></p>
        <p><strong>Número de Documento:</strong> <?php echo htmlspecialchars($reserva["numero_documento"]); ?></p>
        <p><strong>Nombre Completo:</strong> <?php echo htmlspecialchars($reserva["nombre"] . " " . $reserva["apellido"]); ?></p>
        <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($reserva["telefono"]); ?></p>
        <p><strong>Correo:</strong> <?php echo !empty($reserva["correo"]) ? htmlspecialchars($reserva["correo"]) : "No registrado"; ?></p>
        <p><strong>Dirección:</strong> <?php echo !empty($reserva["direccion"]) ? htmlspecialchars($reserva["direccion"]) : "No registrada"; ?></p>
        <p><strong>Licencia de Conducción:</strong> <?php echo htmlspecialchars($reserva["licencia_conduccion"]); ?></p>
    </div>

    <div class="bloque">
        <h2>3. Información del Vehículo</h2>
        <p><strong>Código del Vehículo:</strong> <?php echo htmlspecialchars($reserva["codigo_vehiculo"]); ?></p>
        <p><strong>Vehículo:</strong> <?php echo htmlspecialchars($reserva["marca"] . " " . $reserva["modelo"]); ?></p>
        <p><strong>Color:</strong> <?php echo htmlspecialchars($reserva["color"]); ?></p>
        <p><strong>Placa:</strong> <?php echo htmlspecialchars($reserva["placa"]); ?></p>
        <p><strong>Precio por Día:</strong> $<?php echo number_format((float)$reserva["precio_dia"], 0, ',', '.'); ?></p>
        <p><strong>Precio Especial desde Día 3:</strong>
            <?php
            if ($reserva["precio_especial_3_dias"] !== null) {
                echo "$" . number_format((float)$reserva["precio_especial_3_dias"], 0, ',', '.');
            } else {
                echo "No aplica";
            }
            ?>
        </p>
    </div>

    <div class="bloque">
        <h2>4. Información de Pago y Liquidación</h2>
        <p><strong>Horas de Gracia:</strong> <?php echo htmlspecialchars($reserva["horas_gracia"]); ?></p>
        <p><strong>Tarifa por Hora Extra:</strong> $<?php echo number_format((float)$reserva["tarifa_hora_extra"], 0, ',', '.'); ?></p>
        <p><strong>Horas Extra:</strong> <?php echo htmlspecialchars($reserva["horas_extra"]); ?></p>
        <p><strong>Cargo Extra:</strong> $<?php echo number_format((float)$reserva["cargo_extra"], 0, ',', '.'); ?></p>
        <p><strong>Total Pagado:</strong> $<?php echo number_format((float)$reserva["total_pago"], 0, ',', '.'); ?></p>
    </div>

    <div class="bloque">
        <h2>5. Observaciones</h2>
        <p>
            <?php
            if (!empty($reserva["observaciones"])) {
                echo nl2br(htmlspecialchars($reserva["observaciones"]));
            } else {
                echo "Sin observaciones.";
            }
            ?>
        </p>
    </div>

    <div class="acciones">
        <a href="listar.php">← Volver a la lista de reservas</a>
        <a href="../../admin/dashboard.php">Volver al Dashboard</a>
    </div>

</body>
</html>