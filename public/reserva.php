<?php
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../views/partials/header.php';
require_once __DIR__ . '/../views/partials/navbar.php';

$vehiculo = null;
$mensaje = '';
$mensaje_tipo = 'error';

if (isset($_SESSION['mensaje_reserva'])) {
    $mensaje = $_SESSION['mensaje_reserva'];
    $mensaje_tipo = $_SESSION['mensaje_reserva_tipo'] ?? 'error';

    unset($_SESSION['mensaje_reserva'], $_SESSION['mensaje_reserva_tipo']);
}

if (isset($_GET['id_vehiculo']) && !empty($_GET['id_vehiculo'])) {
    $id_vehiculo = $_GET['id_vehiculo'];

    $sql = "SELECT * FROM vehiculos WHERE id_vehiculo = :id_vehiculo LIMIT 1";
    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(':id_vehiculo', $id_vehiculo, PDO::PARAM_INT);
    $stmt->execute();

    $vehiculo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$vehiculo) {
        $mensaje = 'El vehículo seleccionado no existe.';
        $mensaje_tipo = 'error';
    }
} else {
    $mensaje = 'No se seleccionó ningún vehículo.';
    $mensaje_tipo = 'error';
}

function generarOpcionesHora(): string
{
    $opciones = '';

    for ($hora = 0; $hora < 24; $hora++) {
        foreach (['00', '30'] as $minuto) {
            $hora24 = str_pad((string)$hora, 2, '0', STR_PAD_LEFT);
            $valor = $hora24 . ':' . $minuto;

            $hora12 = $hora % 12;
            if ($hora12 === 0) {
                $hora12 = 12;
            }

            $periodo = $hora < 12 ? 'a. m.' : 'p. m.';
            $texto = str_pad((string)$hora12, 2, '0', STR_PAD_LEFT) . ':' . $minuto . ' ' . $periodo;

            $opciones .= '<option value="' . htmlspecialchars($valor) . '">' . htmlspecialchars($texto) . '</option>';
        }
    }

    return $opciones;
}

$precioDia = $vehiculo ? (float)$vehiculo['precio_dia'] : 0;
$precioHoraExtra = ($vehiculo && !empty($vehiculo['precio_hora_extra'])) ? (float)$vehiculo['precio_hora_extra'] : 0;
?>

<main class="page-section">
    <div class="container">
        <h1>Formulario de reserva</h1>

        <?php if (!empty($mensaje)): ?>
            <div class="placeholder-box" style="<?php echo $mensaje_tipo === 'ok' ? 'border-color:#22c55e;' : 'border-color:#ef4444;'; ?>">
                <p><?php echo htmlspecialchars($mensaje); ?></p>
            </div>
        <?php endif; ?>

        <?php if ($vehiculo): ?>
            <div class="reserva-layout">
                <div class="vehiculo-card vehiculo-resumen">
                    <?php if (!empty($vehiculo['imagen'])): ?>
                        <img
                            src="/benedetti-rent-a-car/assets/img/<?php echo htmlspecialchars($vehiculo['imagen']); ?>"
                            alt="<?php echo htmlspecialchars($vehiculo['marca'] . ' ' . $vehiculo['modelo']); ?>"
                            class="vehiculo-img"
                        >
                    <?php endif; ?>

                    <div class="vehiculo-info">
                        <h3><?php echo htmlspecialchars($vehiculo['marca'] . ' ' . $vehiculo['modelo']); ?></h3>
                        <p><strong>Color:</strong> <?php echo htmlspecialchars($vehiculo['color']); ?></p>
                        <p><strong>Transmisión:</strong> <?php echo htmlspecialchars($vehiculo['transmision']); ?></p>
                        <p><strong>Precio por día:</strong> $<?php echo number_format($vehiculo['precio_dia'], 0, ',', '.'); ?></p>
                        <p><strong>Precio hora extra:</strong>
                            <?php if ($precioHoraExtra > 0): ?>
                                $<?php echo number_format($precioHoraExtra, 0, ',', '.'); ?>
                            <?php else: ?>
                                Por definir
                            <?php endif; ?>
                        </p>
                    </div>
                </div>

                <form action="/benedetti-rent-a-car/public/procesar_reserva.php" method="POST" class="form-reserva">
                    <input type="hidden" name="id_vehiculo" value="<?php echo htmlspecialchars($vehiculo['id_vehiculo']); ?>">

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="nombres">Nombres</label>
                            <input type="text" id="nombres" name="nombres" required>
                        </div>

                        <div class="form-group">
                            <label for="apellidos">Apellidos</label>
                            <input type="text" id="apellidos" name="apellidos" required>
                        </div>

                        <div class="form-group">
                            <label for="correo">Correo electrónico</label>
                            <input type="email" id="correo" name="correo" required>
                        </div>

                        <div class="form-group">
                            <label for="telefono">Teléfono</label>
                            <input type="text" id="telefono" name="telefono" required>
                        </div>

                        <div class="form-group">
                            <label for="tipo_documento">Tipo de documento</label>
                            <select id="tipo_documento" name="tipo_documento" required>
                                <option value="">Seleccione una opción</option>
                                <option value="cedula_ciudadania">Cédula de ciudadanía</option>
                                <option value="cedula_extranjeria">Cédula de extranjería</option>
                                <option value="pasaporte">Pasaporte</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="numero_documento">Número de documento</label>
                            <input type="text" id="numero_documento" name="numero_documento" required>
                        </div>

                        <div class="form-group">
                            <label for="codigo_referido">Código de referido (opcional)</label>
                            <input type="text" id="codigo_referido" name="codigo_referido">
                        </div>

                        <div class="form-group">
                            <label for="modo_pago">Modo de pago</label>
                            <select id="modo_pago" name="modo_pago" required>
                                <option value="">Seleccione una opción</option>
                                <option value="credito">Tarjeta de crédito</option>
                                <option value="debito">Tarjeta débito</option>
                                <option value="qr">Transferencia por código QR</option>
                                <option value="efectivo">Efectivo (con anticipo de 1 día)</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="fecha_inicio">Fecha de inicio</label>
                            <input type="date" id="fecha_inicio" name="fecha_inicio" required>
                        </div>

                        <div class="form-group">
                            <label for="hora_inicio">Hora de inicio</label>
                            <select id="hora_inicio" name="hora_inicio" required>
                                <option value="">Seleccione una hora</option>
                                <?php echo generarOpcionesHora(); ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="fecha_fin">Fecha de fin</label>
                            <input type="date" id="fecha_fin" name="fecha_fin" required>
                        </div>

                        <div class="form-group">
                            <label for="hora_fin">Hora de fin</label>
                            <select id="hora_fin" name="hora_fin" required>
                                <option value="">Seleccione una hora</option>
                                <?php echo generarOpcionesHora(); ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="lugar_entrega">Lugar de entrega</label>
                            <input type="text" id="lugar_entrega" name="lugar_entrega" required>
                        </div>

                        <div class="form-group">
                            <label for="lugar_devolucion">Lugar de devolución</label>
                            <input type="text" id="lugar_devolucion" name="lugar_devolucion" required>
                        </div>

                        <div class="form-group form-group-full">
                            <label for="observaciones">Observaciones</label>
                            <textarea id="observaciones" name="observaciones" rows="4"></textarea>
                        </div>

                        <div class="form-group form-group-full">
                            <label style="display:flex; align-items:flex-start; gap:10px; line-height:1.5;">
                                <input type="checkbox" name="acepta_datos" value="1" required style="margin-top:4px;">
                                <span>
                                    Acepto el tratamiento de mis datos personales conforme a la política de privacidad de Benedetti Rent a Car. Autorizo que mis datos sean almacenados para gestionar esta solicitud de reserva y futuras reservas, de acuerdo con la normativa aplicable de protección de datos.
                                </span>
                            </label>
                        </div>
                    </div>

                    <div class="placeholder-box" style="margin-top:20px;">
                        <h3 style="margin-bottom:10px;">Resumen de la reserva</h3>
                        <p><strong>Precio por día:</strong> $<span id="precio-dia"><?php echo number_format($precioDia, 0, ',', '.'); ?></span></p>
                        <p><strong>Precio por hora extra:</strong> $<span id="precio-hora-extra"><?php echo $precioHoraExtra > 0 ? number_format($precioHoraExtra, 0, ',', '.') : '0'; ?></span></p>
                        <p><strong>Días base:</strong> <span id="dias-base">0</span></p>
                        <p><strong>Horas adicionales programadas:</strong> <span id="horas-extra">0</span></p>
                        <p><strong>Recargo por horas extra:</strong> $<span id="recargo-horas">0</span></p>
                        <p><strong>Total estimado:</strong> $<span id="total-estimado">0</span></p>

                        <hr style="margin: 15px 0; border-color: #334155;">

                        <p><strong>Política de devolución tardía / tiempo adicional</strong></p>
                        <p>• Hasta 1 hora de gracia: sin cobro.</p>
                        <p>• Desde 2 hasta 4 horas adicionales: se cobra valor por hora extra.</p>
                        <p>• Desde la 5ta hora adicional: se cobra 1 día completo adicional.</p>
                    </div>

                    <button type="submit" class="btn btn-primary" style="margin-top:20px;">Continuar reserva</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const precioDia = <?php echo json_encode($precioDia); ?>;
    const precioHoraExtra = <?php echo json_encode($precioHoraExtra); ?>;

    const fechaInicio = document.getElementById('fecha_inicio');
    const horaInicio = document.getElementById('hora_inicio');
    const fechaFin = document.getElementById('fecha_fin');
    const horaFin = document.getElementById('hora_fin');

    const diasBaseEl = document.getElementById('dias-base');
    const horasExtraEl = document.getElementById('horas-extra');
    const recargoHorasEl = document.getElementById('recargo-horas');
    const totalEstimadoEl = document.getElementById('total-estimado');

    function formatearPesos(valor) {
        return new Intl.NumberFormat('es-CO').format(valor);
    }

    function calcularResumen() {
        const fi = fechaInicio.value;
        const hi = horaInicio.value;
        const ff = fechaFin.value;
        const hf = horaFin.value;

        if (!fi || !hi || !ff || !hf) {
            diasBaseEl.textContent = '0';
            horasExtraEl.textContent = '0';
            recargoHorasEl.textContent = '0';
            totalEstimadoEl.textContent = '0';
            return;
        }

        const inicio = new Date(fi + 'T' + hi + ':00');
        const fin = new Date(ff + 'T' + hf + ':00');

        if (isNaN(inicio.getTime()) || isNaN(fin.getTime()) || fin <= inicio) {
            diasBaseEl.textContent = '0';
            horasExtraEl.textContent = '0';
            recargoHorasEl.textContent = '0';
            totalEstimadoEl.textContent = '0';
            return;
        }

        const diferenciaMs = fin - inicio;
        const msDia = 1000 * 60 * 60 * 24;
        const msHora = 1000 * 60 * 60;

        let diasBase = Math.floor(diferenciaMs / msDia);
        let sobranteMs = diferenciaMs % msDia;

        if (diasBase < 1) {
            diasBase = 1;
            sobranteMs = 0;
        }

        let horasAdicionales = Math.ceil(sobranteMs / msHora);
        if (sobranteMs === 0) {
            horasAdicionales = 0;
        }

        let recargoHoras = 0;
        let total = diasBase * precioDia;

        if (horasAdicionales === 0) {
            recargoHoras = 0;
        } else if (horasAdicionales === 1) {
            recargoHoras = 0; // hora de gracia
        } else if (horasAdicionales >= 2 && horasAdicionales <= 4) {
            recargoHoras = horasAdicionales * precioHoraExtra;
            total += recargoHoras;
        } else if (horasAdicionales >= 5) {
            total += precioDia;
        }

        diasBaseEl.textContent = diasBase;
        horasExtraEl.textContent = horasAdicionales;
        recargoHorasEl.textContent = formatearPesos(recargoHoras);
        totalEstimadoEl.textContent = formatearPesos(total);
    }

    fechaInicio.addEventListener('change', calcularResumen);
    horaInicio.addEventListener('change', calcularResumen);
    fechaFin.addEventListener('change', calcularResumen);
    horaFin.addEventListener('change', calcularResumen);
});
</script>

<?php require_once __DIR__ . '/../views/partials/footer.php'; ?>