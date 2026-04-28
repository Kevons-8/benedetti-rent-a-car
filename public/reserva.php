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
    $id_vehiculo = (int)$_GET['id_vehiculo'];

    $sql = "SELECT * FROM vehiculos WHERE id_vehiculo = :id_vehiculo LIMIT 1";
    $stmt = $conexion->prepare($sql);
    $stmt->bindValue(':id_vehiculo', $id_vehiculo, PDO::PARAM_INT);
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
$costoDevolucionOtroFijo = 0;
?>

<style>
.reserva-hero {
    position: relative;
    min-height: 100vh;
    display: flex;
    align-items: center;
    padding: 150px 7% 150px;
    background:
        linear-gradient(
            135deg,
            rgba(4, 18, 38, 0.78) 0%,
            rgba(4, 18, 38, 0.58) 42%,
            rgba(4, 18, 38, 0.28) 100%
        ),
        url('/benedetti-rent-a-car/assets/img/reservas_marimondas.jpg') center center / cover no-repeat;
    overflow: hidden;
}

.reserva-hero::after {
    content: "";
    position: absolute;
    left: 0;
    bottom: 0;
    width: 100%;
    height: 220px;
    background: linear-gradient(
        to bottom,
        transparent,
        rgba(6, 18, 38, 0.96)
    );
    z-index: 1;
}

.reserva-hero-content {
    position: relative;
    z-index: 2;
    max-width: 780px;
}

.reserva-badge {
    display: inline-block;
    padding: 10px 16px;
    border-radius: 999px;
    border: 1px solid rgba(255,255,255,0.16);
    background: rgba(255,255,255,0.10);
    backdrop-filter: blur(10px);
    color: #ffffff;
    font-size: 0.92rem;
    margin-bottom: 16px;
}

.reserva-hero-content h1 {
    font-size: clamp(2.3rem, 4vw, 4.2rem);
    line-height: 1.08;
    margin-bottom: 18px;
    color: #ffffff;
    text-shadow: 0 10px 26px rgba(0,0,0,0.30);
}

.reserva-hero-content p {
    font-size: 1.1rem;
    line-height: 1.8;
    color: #e5edf7;
    max-width: 680px;
    text-shadow: 0 4px 14px rgba(0,0,0,0.22);
}

.reserva-page {
    position: relative;
    margin-top: -130px;
    padding: 0 7% 80px;
    background:
        linear-gradient(
            180deg,
            rgba(6, 18, 38, 0.88) 0%,
            rgba(6, 18, 38, 0.76) 45%,
            rgba(6, 18, 38, 0.92) 100%
        ),
        url('/benedetti-rent-a-car/assets/img/reservas_marimondas.jpg') center center / cover no-repeat fixed;
    z-index: 3;
}

.reserva-page::before {
    content: "";
    position: absolute;
    inset: 0;
    background:
        radial-gradient(circle at top left, rgba(56, 189, 248, 0.13), transparent 34%),
        radial-gradient(circle at bottom right, rgba(247, 198, 0, 0.08), transparent 36%);
    pointer-events: none;
    z-index: 1;
}

.reserva-page .container {
    position: relative;
    z-index: 2;
}

.reserva-title {
    margin-bottom: 24px;
    color: #ffffff;
    font-size: 2.4rem;
}

.reserva-layout-pro {
    display: grid;
    grid-template-columns: 320px 1fr;
    gap: 28px;
    align-items: start;
}

.reserva-sidebar {
    position: sticky;
    top: 110px;
}

.reserva-vehiculo-card {
    background: rgba(19, 35, 63, 0.78);
    border-radius: 22px;
    overflow: hidden;
    border: 1px solid rgba(255,255,255,0.10);
    box-shadow: 0 22px 48px rgba(0,0,0,0.34);
    backdrop-filter: blur(14px);
}

.reserva-vehiculo-card .vehiculo-img {
    width: 100%;
    height: 240px;
    object-fit: cover;
}

.reserva-vehiculo-card .vehiculo-info {
    padding: 20px;
}

.reserva-vehiculo-card .vehiculo-info h3 {
    color: #ffffff;
    font-size: 1.4rem;
    margin-bottom: 10px;
}

.reserva-vehiculo-card .vehiculo-info p {
    color: #d8e2f0;
    margin-bottom: 8px;
}

.reserva-form-card {
    background: rgba(26, 39, 64, 0.76);
    border: 1px solid rgba(255,255,255,0.10);
    border-radius: 24px;
    padding: 26px;
    box-shadow: 0 26px 70px rgba(0,0,0,0.36);
    backdrop-filter: blur(16px);
}

.reserva-block {
    background: rgba(30, 45, 71, 0.72);
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: 18px;
    padding: 18px;
    margin-bottom: 18px;
    backdrop-filter: blur(10px);
}

.reserva-block-title {
    font-size: 1rem;
    font-weight: 700;
    color: #ffffff;
    margin-bottom: 14px;
    padding-bottom: 8px;
    border-bottom: 1px solid rgba(255,255,255,0.10);
}

.reserva-grid-2 {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 14px 16px;
}

.reserva-grid-1 {
    display: grid;
    grid-template-columns: 1fr;
    gap: 14px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.form-group label {
    font-size: 0.92rem;
    font-weight: 600;
    color: #ffffff;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    background: rgba(8, 21, 45, 0.92);
    color: #ffffff;
    border: 1px solid rgba(255,255,255,0.12);
    border-radius: 12px;
    padding: 12px 14px;
    font-size: 0.95rem;
    transition: all 0.25s ease;
}

.form-group textarea {
    resize: vertical;
    min-height: 110px;
}

.form-group input::placeholder,
.form-group textarea::placeholder {
    color: #8ea3bf;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #f7c600;
    box-shadow: 0 0 0 3px rgba(247, 198, 0, 0.14);
}

.reserva-resumen {
    background: rgba(8, 21, 45, 0.92);
    border: 1px solid rgba(255,255,255,0.10);
    border-radius: 18px;
    padding: 20px;
    backdrop-filter: blur(10px);
}

.reserva-resumen h3 {
    margin-bottom: 12px;
    color: #ffffff;
}

.reserva-resumen p {
    margin-bottom: 8px;
    line-height: 1.6;
    color: #d8e2f0;
}

.reserva-total {
    font-size: 1.22rem;
    font-weight: 700;
    color: #38bdf8;
}

.reserva-form-card .btn-primary {
    width: 100%;
    margin-top: 12px;
    border: none;
    border-radius: 16px;
    padding: 15px 18px;
    font-size: 1rem;
    font-weight: 700;
    cursor: pointer;
}

.checkbox-line {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    line-height: 1.5;
    color: #d8e2f0;
}

.checkbox-line input {
    margin-top: 4px;
    width: auto;
}

@media (max-width: 992px) {
    .reserva-layout-pro {
        grid-template-columns: 1fr;
    }

    .reserva-sidebar {
        position: static;
    }

    .reserva-hero {
        min-height: 78vh;
        padding: 130px 7% 140px;
    }

    .reserva-page {
        margin-top: -90px;
    }
}

@media (max-width: 768px) {
    .reserva-grid-2 {
        grid-template-columns: 1fr;
    }

    .reserva-form-card {
        padding: 18px;
    }

    .reserva-block {
        padding: 15px;
    }

    .reserva-hero {
        min-height: 70vh;
        padding: 120px 5% 130px;
        background-position: center center;
    }

    .reserva-hero-content h1 {
        font-size: 2rem;
    }

    .reserva-hero-content p {
        font-size: 1rem;
    }

    .reserva-page {
        margin-top: -80px;
        padding: 0 5% 60px;
    }

    .reserva-title {
        font-size: 2rem;
    }
}
</style>

<section class="reserva-hero">
    <div class="container reserva-hero-content">
        <span class="reserva-badge">Reserva tu experiencia en Barranquilla</span>
        <h1>Reserva tu vehículo de forma fácil y segura</h1>
        <p>
            Completa tu solicitud en pocos pasos y prepárate para recorrer Barranquilla con comodidad,
            respaldo y el espíritu alegre de la ciudad.
        </p>
    </div>
</section>

<main class="reserva-page">
    <div class="container">
        <h1 class="reserva-title">Formulario de reserva</h1>

        <?php if (!empty($mensaje)): ?>
            <div class="placeholder-box" style="<?php echo $mensaje_tipo === 'ok' ? 'border-color:#22c55e;' : 'border-color:#ef4444;'; ?>">
                <p><?php echo htmlspecialchars($mensaje); ?></p>
            </div>
        <?php endif; ?>

        <?php if ($vehiculo): ?>
            <div class="reserva-layout-pro">

                <aside class="reserva-sidebar">
                    <div class="reserva-vehiculo-card">
                        <?php if (!empty($vehiculo['imagen'])): ?>
                            <img
                                src="/benedetti-rent-a-car/assets/img/<?php echo htmlspecialchars($vehiculo['imagen']); ?>"
                                alt="<?php echo htmlspecialchars($vehiculo['marca'] . ' ' . $vehiculo['modelo']); ?>"
                                class="vehiculo-img"
                            >
                        <?php endif; ?>

                        <div class="vehiculo-info">
                            <h3><?php echo htmlspecialchars($vehiculo['marca'] . ' ' . $vehiculo['modelo']); ?></h3>
                            <p><strong>Transmisión:</strong> <?php echo htmlspecialchars($vehiculo['transmision']); ?></p>
                            <p><strong>Precio por día:</strong> $<?php echo number_format((float)$vehiculo['precio_dia'], 0, ',', '.'); ?></p>
                        </div>
                    </div>
                </aside>

                <form action="/benedetti-rent-a-car/public/procesar_reserva.php" method="POST" class="reserva-form-card">
                    <input type="hidden" name="id_vehiculo" value="<?php echo htmlspecialchars((string)$vehiculo['id_vehiculo']); ?>">

                    <input type="hidden" name="lat_entrega" id="lat_entrega">
                    <input type="hidden" name="lng_entrega" id="lng_entrega">
                    <input type="hidden" name="lat_devolucion" id="lat_devolucion">
                    <input type="hidden" name="lng_devolucion" id="lng_devolucion">

                    <section class="reserva-block">
                        <h2 class="reserva-block-title">Datos personales</h2>
                        <div class="reserva-grid-2">
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
                        </div>
                    </section>

                    <section class="reserva-block">
                        <h2 class="reserva-block-title">Datos de la reserva</h2>
                        <div class="reserva-grid-2">
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
                        </div>
                    </section>

                    <section class="reserva-block">
                        <h2 class="reserva-block-title">Entrega y devolución</h2>
                        <div class="reserva-grid-2">
                            <div class="form-group">
                                <label for="lugar_entrega_opcion">Lugar de entrega</label>
                                <select id="lugar_entrega_opcion" name="lugar_entrega_opcion" required>
                                    <option value="">Seleccione una opción</option>
                                    <option value="aeropuerto">Aeropuerto Ernesto Cortissoz</option>
                                    <option value="oficina">Oficina Benedetti Rent a Car</option>
                                    <option value="otro">Otro lugar</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="lugar_devolucion_opcion">Lugar de devolución</label>
                                <select id="lugar_devolucion_opcion" name="lugar_devolucion_opcion" required>
                                    <option value="">Seleccione una opción</option>
                                    <option value="aeropuerto">Aeropuerto Ernesto Cortissoz</option>
                                    <option value="oficina">Oficina Benedetti Rent a Car</option>
                                    <option value="otro">Otro lugar</option>
                                </select>
                            </div>

                            <div class="form-group" id="grupo_otro_entrega" style="display:none;">
                                <label for="otro_lugar_entrega">Escriba el otro lugar de entrega</label>
                                <input type="text" id="otro_lugar_entrega" name="otro_lugar_entrega">
                            </div>

                            <div class="form-group" id="grupo_costo_entrega_manual" style="display:none;">
                                <label for="costo_entrega_manual">Costo manual de entrega</label>
                                <input type="number" id="costo_entrega_manual" name="costo_entrega_manual" min="0" step="1000" value="0">
                            </div>

                            <div class="form-group" id="grupo_otro_devolucion" style="display:none;">
                                <label for="otro_lugar_devolucion">Escriba el otro lugar de devolución</label>
                                <input type="text" id="otro_lugar_devolucion" name="otro_lugar_devolucion">
                            </div>
                        </div>

                        <input type="hidden" id="costo_devolucion_manual" name="costo_devolucion_manual" value="<?php echo htmlspecialchars((string)$costoDevolucionOtroFijo); ?>">
                    </section>

                    <section class="reserva-block">
                        <h2 class="reserva-block-title">Información adicional</h2>
                        <div class="reserva-grid-1">
                            <div class="form-group">
                                <label for="observaciones">Observaciones</label>
                                <textarea id="observaciones" name="observaciones" rows="4"></textarea>
                            </div>

                            <label class="checkbox-line">
                                <input type="checkbox" name="acepta_datos" value="1" required>
                                <span>
                                    Acepto el tratamiento de mis datos personales conforme a la política de privacidad de Benedetti Rent a Car.
                                    Autorizo que mis datos sean almacenados para gestionar esta solicitud de reserva y futuras reservas.
                                </span>
                            </label>
                        </div>
                    </section>

                    <section class="reserva-resumen">
                        <h3>Resumen de la reserva</h3>
                        <p><strong>Precio por día:</strong> $<span id="precio-dia"><?php echo number_format($precioDia, 0, ',', '.'); ?></span></p>
                        <p><strong>Días base:</strong> <span id="dias-base">0</span></p>
                        <p><strong>Horas adicionales programadas:</strong> <span id="horas-extra">0</span></p>
                        <p><strong>Recargo por horas extra:</strong> $<span id="recargo-horas">0</span></p>
                        <p><strong>Costo de entrega:</strong> $<span id="costo-entrega">0</span></p>
                        <p><strong>Costo de devolución:</strong> $<span id="costo-devolucion">0</span></p>
                        <p><strong>Costo por distancia:</strong> $<span id="costo-km">0</span></p>
                        <p class="reserva-total"><strong>Total estimado:</strong> $<span id="total-estimado">0</span></p>
                    </section>

                    <button type="submit" class="btn btn-primary">Continuar reserva</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const precioDia = <?php echo json_encode($precioDia); ?>;
    const precioHoraExtra = <?php echo json_encode($precioHoraExtra); ?>;
    const costoDevolucionOtroFijo = <?php echo json_encode($costoDevolucionOtroFijo); ?>;

    const fechaInicio = document.getElementById('fecha_inicio');
    const horaInicio = document.getElementById('hora_inicio');
    const fechaFin = document.getElementById('fecha_fin');
    const horaFin = document.getElementById('hora_fin');

    const lugarEntrega = document.getElementById('lugar_entrega_opcion');
    const lugarDevolucion = document.getElementById('lugar_devolucion_opcion');
    const grupoOtroEntrega = document.getElementById('grupo_otro_entrega');
    const grupoOtroDevolucion = document.getElementById('grupo_otro_devolucion');
    const grupoCostoEntregaManual = document.getElementById('grupo_costo_entrega_manual');

    const otroEntrega = document.getElementById('otro_lugar_entrega');
    const otroDevolucion = document.getElementById('otro_lugar_devolucion');
    const costoEntregaManual = document.getElementById('costo_entrega_manual');

    const diasBaseEl = document.getElementById('dias-base');
    const horasExtraEl = document.getElementById('horas-extra');
    const recargoHorasEl = document.getElementById('recargo-horas');
    const costoEntregaEl = document.getElementById('costo-entrega');
    const costoDevolucionEl = document.getElementById('costo-devolucion');
    const costoKmEl = document.getElementById('costo-km');
    const totalEstimadoEl = document.getElementById('total-estimado');

    function formatearPesos(valor) {
        return new Intl.NumberFormat('es-CO').format(valor);
    }

    function obtenerCostoEntrega(valorSeleccionado, costoManual) {
        if (valorSeleccionado === 'aeropuerto') return 0;
        if (valorSeleccionado === 'oficina') return 0;
        if (valorSeleccionado === 'otro') return Number(costoManual || 0);
        return 0;
    }

    function obtenerCostoDevolucion(valorSeleccionado) {
        if (valorSeleccionado === 'aeropuerto') return 0;
        if (valorSeleccionado === 'oficina') return 0;
        if (valorSeleccionado === 'otro') return Number(costoDevolucionOtroFijo || 0);
        return 0;
    }

    function controlarCamposOtroLugar() {
        if (lugarEntrega.value === 'otro') {
            grupoOtroEntrega.style.display = 'block';
            grupoCostoEntregaManual.style.display = 'block';
            otroEntrega.required = true;
            costoEntregaManual.required = true;
        } else {
            grupoOtroEntrega.style.display = 'none';
            grupoCostoEntregaManual.style.display = 'none';
            otroEntrega.required = false;
            costoEntregaManual.required = false;
            otroEntrega.value = '';
            costoEntregaManual.value = 0;
        }

        if (lugarDevolucion.value === 'otro') {
            grupoOtroDevolucion.style.display = 'block';
            otroDevolucion.required = true;
        } else {
            grupoOtroDevolucion.style.display = 'none';
            otroDevolucion.required = false;
            otroDevolucion.value = '';
        }

        calcularResumen();
    }

    function calcularResumen() {
        const fi = fechaInicio.value;
        const hi = horaInicio.value;
        const ff = fechaFin.value;
        const hf = horaFin.value;

        const costoEntrega = obtenerCostoEntrega(lugarEntrega.value, costoEntregaManual.value);
        const costoDevolucion = obtenerCostoDevolucion(lugarDevolucion.value);
        const costoKm = 0;

        if (!fi || !hi || !ff || !hf) {
            diasBaseEl.textContent = '0';
            horasExtraEl.textContent = '0';
            recargoHorasEl.textContent = '0';
            costoEntregaEl.textContent = formatearPesos(costoEntrega);
            costoDevolucionEl.textContent = formatearPesos(costoDevolucion);
            costoKmEl.textContent = formatearPesos(costoKm);
            totalEstimadoEl.textContent = '0';
            return;
        }

        const inicio = new Date(fi + 'T' + hi + ':00');
        const fin = new Date(ff + 'T' + hf + ':00');

        if (isNaN(inicio.getTime()) || isNaN(fin.getTime()) || fin <= inicio) {
            diasBaseEl.textContent = '0';
            horasExtraEl.textContent = '0';
            recargoHorasEl.textContent = '0';
            costoEntregaEl.textContent = formatearPesos(costoEntrega);
            costoDevolucionEl.textContent = formatearPesos(costoDevolucion);
            costoKmEl.textContent = formatearPesos(costoKm);
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

        let horasAdicionales = 0;
        if (sobranteMs > 0) {
            horasAdicionales = Math.ceil(sobranteMs / msHora);
        }

        let recargoHoras = 0;
        let total = diasBase * precioDia;

        if (horasAdicionales === 1) {
            recargoHoras = 0;
        } else if (horasAdicionales >= 2 && horasAdicionales <= 4) {
            recargoHoras = horasAdicionales * precioHoraExtra;
            total += recargoHoras;
        } else if (horasAdicionales >= 5) {
            total += precioDia;
        }

        total += costoEntrega + costoDevolucion + costoKm;

        diasBaseEl.textContent = diasBase;
        horasExtraEl.textContent = horasAdicionales;
        recargoHorasEl.textContent = formatearPesos(recargoHoras);
        costoEntregaEl.textContent = formatearPesos(costoEntrega);
        costoDevolucionEl.textContent = formatearPesos(costoDevolucion);
        costoKmEl.textContent = formatearPesos(costoKm);
        totalEstimadoEl.textContent = formatearPesos(total);
    }

    lugarEntrega.addEventListener('change', controlarCamposOtroLugar);
    lugarDevolucion.addEventListener('change', controlarCamposOtroLugar);
    costoEntregaManual.addEventListener('input', calcularResumen);

    fechaInicio.addEventListener('change', calcularResumen);
    horaInicio.addEventListener('change', calcularResumen);
    fechaFin.addEventListener('change', calcularResumen);
    horaFin.addEventListener('change', calcularResumen);

    controlarCamposOtroLugar();
});
</script>

<?php require_once __DIR__ . '/../views/partials/footer.php'; ?>