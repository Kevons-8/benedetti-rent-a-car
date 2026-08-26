<?php
require_once "../../includes/auth.php";
require_once "../../config/database.php";
require_once "../../includes/email_helper.php";

$mensaje = "";
$cliente_encontrado = false;
$cliente_existente = null;

/* ==========================
OBTENER VEHICULOS DISPONIBLES
========================== */
try {
    $sqlVehiculos = "SELECT id_vehiculo, marca, modelo, precio_dia, precio_especial_3_dias 
                     FROM vehiculos 
                     WHERE estado = 'disponible'";
    $stmtVehiculos = $conexion->prepare($sqlVehiculos);
    $stmtVehiculos->execute();
    $vehiculos = $stmtVehiculos->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al obtener vehículos: " . $e->getMessage());
}

/* ==========================
FUNCION PARA HORAS MILITARES
========================== */
function generarHorasMilitares() {
    $horas = [];
    for ($i = 0; $i <= 23; $i++) {
        $hora = str_pad($i, 2, "0", STR_PAD_LEFT) . ":00";
        $horas[] = $hora;
    }
    return $horas;
}

$horas_militares = generarHorasMilitares();

/* ==========================
FUNCION CALCULO RESERVA
========================== */
function calcularDiasYTotal($fecha_inicio_completa, $fecha_fin_completa, $precio_dia, $precio_especial_3_dias = null) {
    $inicio = strtotime($fecha_inicio_completa);
    $fin = strtotime($fecha_fin_completa);

    if ($inicio === false || $fin === false || $fin <= $inicio) {
        return [
            "dias" => 0,
            "total" => 0
        ];
    }

    $segundos = $fin - $inicio;
    $dias = ceil($segundos / 86400);

    if ($dias < 1) {
        $dias = 1;
    }

    $total = 0;

    if (!empty($precio_especial_3_dias) && $precio_especial_3_dias > 0 && $dias >= 3) {
        $total = ($precio_dia * 2) + (($dias - 2) * $precio_especial_3_dias);
    } else {
        $total = $dias * $precio_dia;
    }

    return [
        "dias" => $dias,
        "total" => $total
    ];
}

/* ==========================
VALORES POR DEFECTO
========================== */
$tipo_cliente = $_POST["tipo_cliente"] ?? "";

$numero_documento_busqueda = $_POST["numero_documento_busqueda"] ?? "";
$tipo_documento_busqueda = $_POST["tipo_documento_busqueda"] ?? "";

$nombre = $_POST["nombre"] ?? "";
$apellido = $_POST["apellido"] ?? "";
$telefono = $_POST["telefono"] ?? "";
$correo = $_POST["correo"] ?? "";
$direccion = $_POST["direccion"] ?? "";
$licencia_conduccion = $_POST["licencia_conduccion"] ?? "";

$tipo_documento_nuevo = $_POST["tipo_documento_nuevo"] ?? "";
$numero_documento_nuevo = $_POST["numero_documento_nuevo"] ?? "";
$nombre_nuevo = $_POST["nombre_nuevo"] ?? "";
$apellido_nuevo = $_POST["apellido_nuevo"] ?? "";
$telefono_nuevo = $_POST["telefono_nuevo"] ?? "";
$correo_nuevo = $_POST["correo_nuevo"] ?? "";
$direccion_nuevo = $_POST["direccion_nuevo"] ?? "";
$licencia_conduccion_nuevo = $_POST["licencia_conduccion_nuevo"] ?? "";

$id_vehiculo = $_POST["id_vehiculo"] ?? "";
$fecha_inicio = $_POST["fecha_inicio"] ?? "";
$hora_inicio = $_POST["hora_inicio"] ?? "";
$fecha_fin = $_POST["fecha_fin"] ?? "";
$hora_fin = $_POST["hora_fin"] ?? "";
$observaciones = $_POST["observaciones"] ?? "";

$dias_calculados = "";
$total_calculado = "";

/* ==========================
BUSCAR CLIENTE EXISTENTE
========================== */
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["buscar_cliente"])) {
    try {
        $sqlBuscarCliente = "SELECT * FROM clientes WHERE numero_documento = :numero_documento";
        $stmtBuscarCliente = $conexion->prepare($sqlBuscarCliente);
        $stmtBuscarCliente->bindParam(':numero_documento', $numero_documento_busqueda);
        $stmtBuscarCliente->execute();

        $cliente_existente = $stmtBuscarCliente->fetch(PDO::FETCH_ASSOC);

        if ($cliente_existente) {
            $cliente_encontrado = true;
            $tipo_documento_busqueda = $cliente_existente["tipo_documento"];
            $numero_documento_busqueda = $cliente_existente["numero_documento"];
            $nombre = $cliente_existente["nombre"];
            $apellido = $cliente_existente["apellido"];
            $telefono = $cliente_existente["telefono"];
            $correo = $cliente_existente["correo"];
            $direccion = $cliente_existente["direccion"];
            $licencia_conduccion = $cliente_existente["licencia_conduccion"];
            $mensaje = "Cliente encontrado. Puede actualizar sus datos si lo desea.";
        } else {
            $mensaje = "Cliente no encontrado con ese número de documento.";
        }
    } catch (PDOException $e) {
        $mensaje = "Error al buscar cliente: " . $e->getMessage();
    }
}

/* ==========================
CREAR RESERVA
========================== */
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["crear_reserva"])) {

    $tipo_cliente = trim($_POST["tipo_cliente"]);
    $id_cliente = null;
    $id_usuario = null;

    $id_vehiculo = trim($_POST["id_vehiculo"]);
    $fecha_inicio = trim($_POST["fecha_inicio"]);
    $hora_inicio = trim($_POST["hora_inicio"]);
    $fecha_fin = trim($_POST["fecha_fin"]);
    $hora_fin = trim($_POST["hora_fin"]);
    $observaciones = trim($_POST["observaciones"]);

    if (empty($id_vehiculo) || empty($fecha_inicio) || empty($hora_inicio) || empty($fecha_fin) || empty($hora_fin)) {
        $mensaje = "Debe completar vehículo, fecha y hora de inicio, y fecha y hora de fin.";
    }

    $fecha_inicio_completa = $fecha_inicio . " " . $hora_inicio . ":00";
    $fecha_fin_completa = $fecha_fin . " " . $hora_fin . ":00";

    if (empty($mensaje) && strtotime($fecha_fin_completa) <= strtotime($fecha_inicio_completa)) {
        $mensaje = "La fecha y hora de fin deben ser mayores a la fecha y hora de inicio.";
    }

    /* ==========================
    VALIDAR RESERVA TRASLAPADA
    ========================== */
    if (empty($mensaje)) {
        try {
            $sqlTraslape = "SELECT COUNT(*) AS total
                            FROM reservas
                            WHERE id_vehiculo = :id_vehiculo
                              AND estado_reserva IN ('pendiente', 'confirmada')
                              AND :fecha_inicio_nueva < fecha_fin
                              AND :fecha_fin_nueva > fecha_inicio";

            $stmtTraslape = $conexion->prepare($sqlTraslape);
            $stmtTraslape->bindParam(':id_vehiculo', $id_vehiculo);
            $stmtTraslape->bindParam(':fecha_inicio_nueva', $fecha_inicio_completa);
            $stmtTraslape->bindParam(':fecha_fin_nueva', $fecha_fin_completa);
            $stmtTraslape->execute();

            $reservaTraslapada = $stmtTraslape->fetch(PDO::FETCH_ASSOC);

            if ($reservaTraslapada && $reservaTraslapada["total"] > 0) {
                $mensaje = "No se puede crear la reserva porque el vehículo ya está reservado en un rango de fechas que se traslapa.";
            }

        } catch (PDOException $e) {
            $mensaje = "Error al validar disponibilidad del vehículo: " . $e->getMessage();
        }
    }

    /* ==========================
    OBTENER PRECIO DEL VEHICULO
    ========================== */
    $precio_dia = 0;
    $precio_especial_3_dias = null;

    if (empty($mensaje)) {
        try {
            $sqlPrecioVehiculo = "SELECT precio_dia, precio_especial_3_dias 
                                  FROM vehiculos 
                                  WHERE id_vehiculo = :id_vehiculo";
            $stmtPrecioVehiculo = $conexion->prepare($sqlPrecioVehiculo);
            $stmtPrecioVehiculo->bindParam(':id_vehiculo', $id_vehiculo);
            $stmtPrecioVehiculo->execute();

            $vehiculoPrecio = $stmtPrecioVehiculo->fetch(PDO::FETCH_ASSOC);

            if (!$vehiculoPrecio) {
                $mensaje = "No se encontró el vehículo seleccionado.";
            } else {
                $precio_dia = (float)$vehiculoPrecio["precio_dia"];
                $precio_especial_3_dias = $vehiculoPrecio["precio_especial_3_dias"] !== null
                    ? (float)$vehiculoPrecio["precio_especial_3_dias"]
                    : null;

                $resultado_calculo = calcularDiasYTotal(
                    $fecha_inicio_completa,
                    $fecha_fin_completa,
                    $precio_dia,
                    $precio_especial_3_dias
                );

                $dias_calculados = $resultado_calculo["dias"];
                $total_calculado = $resultado_calculo["total"];
            }

        } catch (PDOException $e) {
            $mensaje = "Error al calcular el valor de la reserva: " . $e->getMessage();
        }
    }

    if ($tipo_cliente == "existente" && empty($mensaje)) {

        $numero_documento_busqueda = trim($_POST["numero_documento_busqueda"]);
        $nombre = trim($_POST["nombre"]);
        $apellido = trim($_POST["apellido"]);
        $telefono = trim($_POST["telefono"]);
        $correo = trim($_POST["correo"]);
        $direccion = trim($_POST["direccion"]);
        $licencia_conduccion = trim($_POST["licencia_conduccion"]);

        try {
            $sqlBuscarCliente = "SELECT * FROM clientes WHERE numero_documento = :numero_documento";
            $stmtBuscarCliente = $conexion->prepare($sqlBuscarCliente);
            $stmtBuscarCliente->bindParam(':numero_documento', $numero_documento_busqueda);
            $stmtBuscarCliente->execute();

            $cliente_existente = $stmtBuscarCliente->fetch(PDO::FETCH_ASSOC);

            if (!$cliente_existente) {
                $mensaje = "No se encontró un cliente existente con ese número de documento.";
            } else {
                $id_cliente = $cliente_existente["id_cliente"];

                $sqlActualizarCliente = "UPDATE clientes SET
                                         nombre = :nombre,
                                         apellido = :apellido,
                                         telefono = :telefono,
                                         correo = :correo,
                                         direccion = :direccion,
                                         licencia_conduccion = :licencia_conduccion
                                         WHERE id_cliente = :id_cliente";

                $stmtActualizarCliente = $conexion->prepare($sqlActualizarCliente);
                $stmtActualizarCliente->bindParam(':nombre', $nombre);
                $stmtActualizarCliente->bindParam(':apellido', $apellido);
                $stmtActualizarCliente->bindParam(':telefono', $telefono);
                $stmtActualizarCliente->bindParam(':correo', $correo);
                $stmtActualizarCliente->bindParam(':direccion', $direccion);
                $stmtActualizarCliente->bindParam(':licencia_conduccion', $licencia_conduccion);
                $stmtActualizarCliente->bindParam(':id_cliente', $id_cliente);
                $stmtActualizarCliente->execute();
            }

        } catch (PDOException $e) {
            $mensaje = "Error con cliente existente: " . $e->getMessage();
        }

    } elseif ($tipo_cliente == "nuevo" && empty($mensaje)) {

        $tipo_documento_nuevo = trim($_POST["tipo_documento_nuevo"]);
        $numero_documento_nuevo = trim($_POST["numero_documento_nuevo"]);
        $nombre_nuevo = trim($_POST["nombre_nuevo"]);
        $apellido_nuevo = trim($_POST["apellido_nuevo"]);
        $telefono_nuevo = trim($_POST["telefono_nuevo"]);
        $correo_nuevo = trim($_POST["correo_nuevo"]);
        $direccion_nuevo = trim($_POST["direccion_nuevo"]);
        $licencia_conduccion_nuevo = trim($_POST["licencia_conduccion_nuevo"]);

        if (
            empty($tipo_documento_nuevo) ||
            empty($numero_documento_nuevo) ||
            empty($nombre_nuevo) ||
            empty($apellido_nuevo) ||
            empty($telefono_nuevo) ||
            empty($licencia_conduccion_nuevo)
        ) {
            $mensaje = "Para cliente nuevo debe completar los datos obligatorios.";
        } else {
            try {
                $sqlValidarCliente = "SELECT id_cliente FROM clientes WHERE numero_documento = :numero_documento";
                $stmtValidarCliente = $conexion->prepare($sqlValidarCliente);
                $stmtValidarCliente->bindParam(':numero_documento', $numero_documento_nuevo);
                $stmtValidarCliente->execute();

                $clienteYaExiste = $stmtValidarCliente->fetch(PDO::FETCH_ASSOC);

                if ($clienteYaExiste) {
                    $mensaje = "Ya existe un cliente con ese documento. Debe usar la opción de cliente existente.";
                } else {
                    $sqlCliente = "INSERT INTO clientes
                    (tipo_documento, numero_documento, nombre, apellido, telefono, correo, direccion, licencia_conduccion)
                    VALUES
                    (:tipo_documento, :numero_documento, :nombre, :apellido, :telefono, :correo, :direccion, :licencia_conduccion)";

                    $stmtCliente = $conexion->prepare($sqlCliente);
                    $stmtCliente->bindParam(':tipo_documento', $tipo_documento_nuevo);
                    $stmtCliente->bindParam(':numero_documento', $numero_documento_nuevo);
                    $stmtCliente->bindParam(':nombre', $nombre_nuevo);
                    $stmtCliente->bindParam(':apellido', $apellido_nuevo);
                    $stmtCliente->bindParam(':telefono', $telefono_nuevo);
                    $stmtCliente->bindParam(':correo', $correo_nuevo);
                    $stmtCliente->bindParam(':direccion', $direccion_nuevo);
                    $stmtCliente->bindParam(':licencia_conduccion', $licencia_conduccion_nuevo);
                    $stmtCliente->execute();

                    $id_cliente = $conexion->lastInsertId();
                }

            } catch (PDOException $e) {
                $mensaje = "Error al crear cliente nuevo: " . $e->getMessage();
            }
        }
    }

    if (empty($mensaje)) {
        try {
            $conexion->beginTransaction();

            $codigo_reserva = "RES-" . time();

            $sqlReserva = "INSERT INTO reservas
            (codigo_reserva, id_cliente, id_usuario, id_vehiculo, fecha_inicio, fecha_fin, total_pago, observaciones)
            VALUES
            (:codigo_reserva, :id_cliente, :id_usuario, :id_vehiculo, :fecha_inicio, :fecha_fin, :total_pago, :observaciones)";

            $stmtReserva = $conexion->prepare($sqlReserva);
            $stmtReserva->bindParam(':codigo_reserva', $codigo_reserva);
            $stmtReserva->bindParam(':id_cliente', $id_cliente);
            $stmtReserva->bindParam(':id_usuario', $id_usuario);
            $stmtReserva->bindParam(':id_vehiculo', $id_vehiculo);
            $stmtReserva->bindParam(':fecha_inicio', $fecha_inicio_completa);
            $stmtReserva->bindParam(':fecha_fin', $fecha_fin_completa);
            $stmtReserva->bindParam(':total_pago', $total_calculado);
            $stmtReserva->bindParam(':observaciones', $observaciones);
            $stmtReserva->execute();

            $sqlActualizarVehiculo = "UPDATE vehiculos 
                                      SET estado = 'reservado' 
                                      WHERE id_vehiculo = :id_vehiculo";
            $stmtActualizarVehiculo = $conexion->prepare($sqlActualizarVehiculo);
            $stmtActualizarVehiculo->bindParam(':id_vehiculo', $id_vehiculo);
            $stmtActualizarVehiculo->execute();

            $conexion->commit();

            $mensaje = "Reserva creada correctamente. Días calculados: " . $dias_calculados . " | Total: $" . number_format($total_calculado, 0, ',', '.');
            require_once "../../includes/auth.php";
require_once "../../config/database.php";
require_once "../../includes/email_helper.php";

            /* ==========================
ENVIAR CORREO DE CONFIRMACIÓN
========================== */

if (!empty($correo)) {

    $asunto = "Confirmación de Reserva - Benedetti Rent a Car";

    $contenidoHtml = "
        <h2>Reserva confirmada</h2>
        <p>Hola <strong>" . htmlspecialchars($nombre . " " . $apellido) . "</strong>,</p>
        <p>Su reserva fue registrada correctamente.</p>
        <p><strong>Código:</strong> " . htmlspecialchars($codigo_reserva) . "</p>
        <p><strong>Inicio:</strong> " . htmlspecialchars($fecha_inicio) . "</p>
        <p><strong>Fin:</strong> " . htmlspecialchars($fecha_fin) . "</p>
        <p><strong>Total estimado:</strong> $" . number_format((float)$total_calculado, 0, ',', '.') . "</p>
        <br>
        <p>Gracias por confiar en Benedetti Rent a Car.</p>
    ";

    enviarCorreo(
        $correo,
        $nombre . " " . $apellido,
        $asunto,
        $contenidoHtml
    );
}

            $cliente_encontrado = false;
            $cliente_existente = null;

            $tipo_cliente = "";
            $numero_documento_busqueda = "";
            $tipo_documento_busqueda = "";
            $nombre = "";
            $apellido = "";
            $telefono = "";
            $correo = "";
            $direccion = "";
            $licencia_conduccion = "";

            $tipo_documento_nuevo = "";
            $numero_documento_nuevo = "";
            $nombre_nuevo = "";
            $apellido_nuevo = "";
            $telefono_nuevo = "";
            $correo_nuevo = "";
            $direccion_nuevo = "";
            $licencia_conduccion_nuevo = "";

            $id_vehiculo = "";
            $fecha_inicio = "";
            $hora_inicio = "";
            $fecha_fin = "";
            $hora_fin = "";
            $observaciones = "";
            $dias_calculados = "";
            $total_calculado = "";

            $sqlVehiculos = "SELECT id_vehiculo, marca, modelo, precio_dia, precio_especial_3_dias 
                             FROM vehiculos 
                             WHERE estado = 'disponible'";
            $stmtVehiculos = $conexion->prepare($sqlVehiculos);
            $stmtVehiculos->execute();
            $vehiculos = $stmtVehiculos->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            if ($conexion->inTransaction()) {
                $conexion->rollBack();
            }
            $mensaje = "Error al crear reserva: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Crear Reserva</title>

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
        linear-gradient(135deg, rgba(3,13,31,0.86), rgba(6,32,71,0.82)),
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
    background: rgba(34,197,94,0.12);
    border: 1px solid rgba(34,197,94,0.35);
    color: #86efac;
    font-size: 0.9rem;
    font-weight: 800;
    margin-bottom: 14px;
}

h1 {
    font-size: clamp(2rem, 4vw, 3rem);
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
}

.btn,
button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 48px;
    padding: 12px 22px;
    border-radius: 999px;
    border: none;
    font-size: 0.92rem;
    font-weight: 800;
    cursor: pointer;
    transition: all 0.25s ease;
    text-decoration: none;
    white-space: nowrap;
}

.btn-primary,
button[name="crear_reserva"],
button[name="buscar_cliente"] {
    color: #ffffff;
    background: linear-gradient(180deg, #6eff1f 0%, #38d600 45%, #19a500 100%);
    box-shadow:
        inset 0 2px 0 rgba(255,255,255,0.32),
        0 12px 24px rgba(34,197,94,0.28);
}

.btn-dark {
    color: #ffffff;
    background: rgba(3,10,24,0.65);
    border: 1px solid rgba(255,255,255,0.14);
}

.btn:hover,
button:hover {
    transform: translateY(-2px);
    filter: brightness(1.08);
}

.alert {
    margin-bottom: 24px;
    padding: 16px 18px;
    border-radius: 18px;
    font-weight: 800;
    color: #d8e2f0;
    background: rgba(8,21,45,0.78);
    border: 1px solid rgba(255,255,255,0.12);
}

form {
    display: grid;
    gap: 24px;
}

.bloque {
    background: rgba(15,28,51,0.88);
    border: 1px solid rgba(255,255,255,0.12);
    border-radius: 26px;
    padding: 28px;
    box-shadow: 0 24px 70px rgba(0,0,0,0.35);
    backdrop-filter: blur(16px);
}

.bloque h3 {
    color: #ffffff;
    font-size: 1.35rem;
    margin-bottom: 22px;
    padding-bottom: 16px;
    border-bottom: 1px solid rgba(255,255,255,0.10);
}

.oculto {
    display: none;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 18px;
}

.fila {
    display: flex;
    flex-direction: column;
    gap: 7px;
}

.fila-full {
    grid-column: 1 / -1;
}

.fila label {
    color: #ffffff;
    font-size: 0.93rem;
    font-weight: 800;
}

.fila input,
.fila select,
.fila textarea {
    width: 100%;
    min-height: 50px;
    padding: 13px 15px;
    border-radius: 15px;
    background: rgba(8,21,45,0.95);
    color: #ffffff;
    border: 1px solid rgba(148,163,184,0.35);
    font-size: 0.95rem;
    outline: none;
}

.fila textarea {
    resize: vertical;
    min-height: 115px;
}

.fila input:focus,
.fila select:focus,
.fila textarea:focus {
    border-color: #22c55e;
    box-shadow:
        0 0 0 4px rgba(34,197,94,0.13),
        0 0 22px rgba(34,197,94,0.12);
}

.resultado {
    margin-top: 22px;
    padding: 22px;
    border-radius: 22px;
    background: rgba(34,197,94,0.13);
    border: 1px solid rgba(34,197,94,0.35);
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
}

.resultado div {
    padding: 16px;
    border-radius: 18px;
    background: rgba(8,21,45,0.72);
    border: 1px solid rgba(255,255,255,0.10);
}

.resultado strong {
    display: block;
    color: #86efac;
    margin-bottom: 7px;
}

.resultado span {
    color: #ffffff;
    font-size: 1.3rem;
    font-weight: 900;
}

.form-actions {
    display: flex;
    gap: 14px;
    flex-wrap: wrap;
    padding-top: 22px;
    border-top: 1px solid rgba(255,255,255,0.10);
}

.cliente-note {
    margin-top: 18px;
    padding: 15px 18px;
    border-radius: 16px;
    background: rgba(34,197,94,0.13);
    border: 1px solid rgba(34,197,94,0.35);
    color: #dcfce7;
    font-weight: 800;
}

@media (max-width: 850px) {
    body {
        padding: 35px 5%;
    }

    .header {
        flex-direction: column;
    }

    .header-actions,
    .form-actions {
        width: 100%;
        flex-direction: column;
    }

    .btn,
    button {
        width: 100%;
    }

    .form-grid,
    .resultado {
        grid-template-columns: 1fr;
    }

    .fila-full {
        grid-column: auto;
    }

    .bloque {
        padding: 22px;
        border-radius: 22px;
    }
}
</style>

<script>
const vehiculosData = <?php echo json_encode($vehiculos, JSON_UNESCAPED_UNICODE); ?>;

function mostrarFormulario() {
    const tipoCliente = document.getElementById("tipo_cliente").value;
    const bloqueExistente = document.getElementById("bloque_existente");
    const bloqueNuevo = document.getElementById("bloque_nuevo");

    if (tipoCliente === "existente") {
        bloqueExistente.style.display = "block";
        bloqueNuevo.style.display = "none";
    } else if (tipoCliente === "nuevo") {
        bloqueExistente.style.display = "none";
        bloqueNuevo.style.display = "block";
    } else {
        bloqueExistente.style.display = "none";
        bloqueNuevo.style.display = "none";
    }
}

function calcularTotalEstimado() {
    const idVehiculo = document.getElementById("id_vehiculo").value;
    const fechaInicio = document.getElementById("fecha_inicio").value;
    const horaInicio = document.getElementById("hora_inicio").value;
    const fechaFin = document.getElementById("fecha_fin").value;
    const horaFin = document.getElementById("hora_fin").value;

    const diasSpan = document.getElementById("dias_estimados");
    const totalSpan = document.getElementById("total_estimado");

    diasSpan.textContent = "-";
    totalSpan.textContent = "-";

    if (!idVehiculo || !fechaInicio || !horaInicio || !fechaFin || !horaFin) {
        return;
    }

    const inicio = new Date(fechaInicio + "T" + horaInicio + ":00");
    const fin = new Date(fechaFin + "T" + horaFin + ":00");

    if (fin <= inicio) {
        return;
    }

    const vehiculo = vehiculosData.find(v => v.id_vehiculo == idVehiculo);
    if (!vehiculo) {
        return;
    }

    const diferenciaMs = fin - inicio;
    let dias = Math.ceil(diferenciaMs / (1000 * 60 * 60 * 24));
    if (dias < 1) dias = 1;

    const precioDia = parseFloat(vehiculo.precio_dia);
    const precioEspecial = vehiculo.precio_especial_3_dias !== null ? parseFloat(vehiculo.precio_especial_3_dias) : null;

    let total = 0;

    if (precioEspecial && dias >= 3) {
        total = (precioDia * 2) + ((dias - 2) * precioEspecial);
    } else {
        total = dias * precioDia;
    }

    diasSpan.textContent = dias;
    totalSpan.textContent = total.toLocaleString("es-CO");
}

window.onload = function() {
    mostrarFormulario();
    calcularTotalEstimado();
};
</script>
</head>

<body>

<div class="container">

    <div class="header">
        <div>
            <span class="badge">Gestión de reservas</span>
            <h1>Crear Reserva</h1>
            <p>Registra una nueva reserva seleccionando cliente, vehículo, fechas, horas y observaciones.</p>
        </div>

        <div class="header-actions">
            <a href="listar.php" class="btn btn-dark">← Volver a reservas</a>
            <a href="../../admin/dashboard.php" class="btn btn-dark">Dashboard</a>
        </div>
    </div>

    <?php if ($mensaje != "") { ?>
        <div class="alert">
            <?php echo htmlspecialchars($mensaje); ?>
        </div>
    <?php } ?>

    <form method="POST">

        <div class="bloque">
            <h3>1. Seleccione el tipo de cliente</h3>

            <div class="form-grid">
                <div class="fila">
                    <label for="tipo_cliente">Tipo de cliente</label>
                    <select name="tipo_cliente" id="tipo_cliente" onchange="mostrarFormulario()">
                        <option value="">Seleccione una opción</option>
                        <option value="existente" <?php echo ($tipo_cliente == "existente") ? "selected" : ""; ?>>Cliente existente</option>
                        <option value="nuevo" <?php echo ($tipo_cliente == "nuevo") ? "selected" : ""; ?>>Cliente nuevo</option>
                    </select>
                </div>
            </div>
        </div>

        <div id="bloque_existente" class="bloque oculto">
            <h3>2. Datos del cliente existente</h3>

            <div class="form-grid">
                <div class="fila">
                    <label>Tipo documento</label>
                    <select name="tipo_documento_busqueda">
                        <option value="">Seleccione tipo de documento</option>
                        <option value="CC" <?php echo ($tipo_documento_busqueda == "CC") ? "selected" : ""; ?>>Cédula de ciudadanía</option>
                        <option value="CE" <?php echo ($tipo_documento_busqueda == "CE") ? "selected" : ""; ?>>Cédula de extranjería</option>
                        <option value="PASAPORTE" <?php echo ($tipo_documento_busqueda == "PASAPORTE") ? "selected" : ""; ?>>Pasaporte</option>
                        <option value="DNI" <?php echo ($tipo_documento_busqueda == "DNI") ? "selected" : ""; ?>>DNI</option>
                        <option value="TI" <?php echo ($tipo_documento_busqueda == "TI") ? "selected" : ""; ?>>Tarjeta de identidad</option>
                        <option value="NIT" <?php echo ($tipo_documento_busqueda == "NIT") ? "selected" : ""; ?>>NIT</option>
                        <option value="OTRO" <?php echo ($tipo_documento_busqueda == "OTRO") ? "selected" : ""; ?>>Otro</option>
                    </select>
                </div>

                <div class="fila">
                    <label>Número documento</label>
                    <input type="text" name="numero_documento_busqueda" value="<?php echo htmlspecialchars($numero_documento_busqueda); ?>">
                </div>

                <div class="fila fila-full">
                    <button type="submit" name="buscar_cliente">Buscar cliente existente</button>
                </div>

                <div class="fila">
                    <label>Nombre</label>
                    <input type="text" name="nombre" value="<?php echo htmlspecialchars($nombre); ?>">
                </div>

                <div class="fila">
                    <label>Apellido</label>
                    <input type="text" name="apellido" value="<?php echo htmlspecialchars($apellido); ?>">
                </div>

                <div class="fila">
                    <label>Teléfono</label>
                    <input type="text" name="telefono" value="<?php echo htmlspecialchars($telefono); ?>">
                </div>

                <div class="fila">
                    <label>Correo</label>
                    <input type="email" name="correo" value="<?php echo htmlspecialchars($correo); ?>">
                </div>

                <div class="fila">
                    <label>Dirección</label>
                    <input type="text" name="direccion" value="<?php echo htmlspecialchars($direccion); ?>">
                </div>

                <div class="fila">
                    <label>Licencia conducción</label>
                    <input type="text" name="licencia_conduccion" value="<?php echo htmlspecialchars($licencia_conduccion); ?>">
                </div>
            </div>

            <?php if ($cliente_encontrado) { ?>
                <div class="cliente-note">
                    Cliente encontrado. Puede actualizar los datos si lo desea. El número de documento no se modifica.
                </div>
            <?php } ?>
        </div>

        <div id="bloque_nuevo" class="bloque oculto">
            <h3>2. Datos del cliente nuevo</h3>

            <div class="form-grid">
                <div class="fila">
                    <label>Tipo documento</label>
                    <select name="tipo_documento_nuevo">
                        <option value="">Seleccione tipo de documento</option>
                        <option value="CC" <?php echo ($tipo_documento_nuevo == "CC") ? "selected" : ""; ?>>Cédula de ciudadanía</option>
                        <option value="CE" <?php echo ($tipo_documento_nuevo == "CE") ? "selected" : ""; ?>>Cédula de extranjería</option>
                        <option value="PASAPORTE" <?php echo ($tipo_documento_nuevo == "PASAPORTE") ? "selected" : ""; ?>>Pasaporte</option>
                        <option value="DNI" <?php echo ($tipo_documento_nuevo == "DNI") ? "selected" : ""; ?>>DNI</option>
                        <option value="TI" <?php echo ($tipo_documento_nuevo == "TI") ? "selected" : ""; ?>>Tarjeta de identidad</option>
                        <option value="NIT" <?php echo ($tipo_documento_nuevo == "NIT") ? "selected" : ""; ?>>NIT</option>
                        <option value="OTRO" <?php echo ($tipo_documento_nuevo == "OTRO") ? "selected" : ""; ?>>Otro</option>
                    </select>
                </div>

                <div class="fila">
                    <label>Número documento</label>
                    <input type="text" name="numero_documento_nuevo" value="<?php echo htmlspecialchars($numero_documento_nuevo); ?>">
                </div>

                <div class="fila">
                    <label>Nombre</label>
                    <input type="text" name="nombre_nuevo" value="<?php echo htmlspecialchars($nombre_nuevo); ?>">
                </div>

                <div class="fila">
                    <label>Apellido</label>
                    <input type="text" name="apellido_nuevo" value="<?php echo htmlspecialchars($apellido_nuevo); ?>">
                </div>

                <div class="fila">
                    <label>Teléfono</label>
                    <input type="text" name="telefono_nuevo" value="<?php echo htmlspecialchars($telefono_nuevo); ?>">
                </div>

                <div class="fila">
                    <label>Correo</label>
                    <input type="email" name="correo_nuevo" value="<?php echo htmlspecialchars($correo_nuevo); ?>">
                </div>

                <div class="fila">
                    <label>Dirección</label>
                    <input type="text" name="direccion_nuevo" value="<?php echo htmlspecialchars($direccion_nuevo); ?>">
                </div>

                <div class="fila">
                    <label>Licencia conducción</label>
                    <input type="text" name="licencia_conduccion_nuevo" value="<?php echo htmlspecialchars($licencia_conduccion_nuevo); ?>">
                </div>
            </div>
        </div>

        <div class="bloque">
            <h3>3. Datos de la reserva</h3>

            <div class="form-grid">
                <div class="fila fila-full">
                    <label>Vehículo</label>
                    <select name="id_vehiculo" id="id_vehiculo" onchange="calcularTotalEstimado()">
                        <option value="">Seleccione vehículo</option>
                        <?php foreach ($vehiculos as $vehiculo) { ?>
                            <option value="<?php echo $vehiculo["id_vehiculo"]; ?>" <?php echo ($id_vehiculo == $vehiculo["id_vehiculo"]) ? "selected" : ""; ?>>
                                <?php echo htmlspecialchars($vehiculo["marca"] . " " . $vehiculo["modelo"]); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="fila">
                    <label>Fecha inicio</label>
                    <input type="date" id="fecha_inicio" name="fecha_inicio" value="<?php echo htmlspecialchars($fecha_inicio); ?>" onchange="calcularTotalEstimado()">
                </div>

                <div class="fila">
                    <label>Hora inicio</label>
                    <select id="hora_inicio" name="hora_inicio" onchange="calcularTotalEstimado()">
                        <option value="">Seleccione hora</option>
                        <?php foreach ($horas_militares as $hora) { ?>
                            <option value="<?php echo $hora; ?>" <?php echo ($hora_inicio == $hora) ? "selected" : ""; ?>>
                                <?php echo $hora; ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="fila">
                    <label>Fecha fin</label>
                    <input type="date" id="fecha_fin" name="fecha_fin" value="<?php echo htmlspecialchars($fecha_fin); ?>" onchange="calcularTotalEstimado()">
                </div>

                <div class="fila">
                    <label>Hora fin</label>
                    <select id="hora_fin" name="hora_fin" onchange="calcularTotalEstimado()">
                        <option value="">Seleccione hora</option>
                        <?php foreach ($horas_militares as $hora) { ?>
                            <option value="<?php echo $hora; ?>" <?php echo ($hora_fin == $hora) ? "selected" : ""; ?>>
                                <?php echo $hora; ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="fila fila-full">
                    <label>Observaciones</label>
                    <textarea name="observaciones"><?php echo htmlspecialchars($observaciones); ?></textarea>
                </div>
            </div>

            <div class="resultado">
                <div>
                    <strong>Días estimados</strong>
                    <span id="dias_estimados"><?php echo ($dias_calculados !== "") ? htmlspecialchars($dias_calculados) : "-"; ?></span>
                </div>

                <div>
                    <strong>Total estimado</strong>
                    $<span id="total_estimado"><?php echo ($total_calculado !== "") ? number_format((float)$total_calculado, 0, ',', '.') : "-"; ?></span>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" name="crear_reserva">Crear Reserva</button>
                <a href="listar.php" class="btn btn-dark">Cancelar</a>
            </div>
        </div>

    </form>

</div>

</body>
</html>