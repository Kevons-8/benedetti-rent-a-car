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

/*
|--------------------------------------------------------------------------
| CONFIGURACION INTERNA
|--------------------------------------------------------------------------
| Este valor NO lo edita el cliente.
| Si la devolucion es "otro lugar", este valor se aplica desde codigo.
*/
$costoDevolucionOtroFijo = 0;
?>

<style>
.reserva-page {
    padding-top: 20px;
}

.reserva-title {
    margin-bottom: 24px;
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

.reserva-form-card {
    background: #1a2740;
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 20px;
    padding: 24px;
    box-shadow: 0 18px 35px rgba(0,0,0,0.18);
}

.reserva-block {
    background: #1e2d47;
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 16px;
    padding: 18px;
    margin-bottom: 18px;
}

.reserva-block-title {
    font-size: 1rem;
    font-weight: 700;
    color: #ffffff;
    margin-bottom: 14px;
    padding-bottom: 8px;
    border-bottom: 1px solid rgba(255,255,255,0.08);
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
    background: #08152d;
    color: #ffffff;
    border: 1px solid #3a4b66;
    border-radius: 10px;
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
    box-shadow: 0 0 0 3px rgba(247, 198, 0, 0.12);
}

.reserva-resumen {
    background: #08152d;
    border: 1px solid #2c3e5d;
    border-radius: 16px;
    padding: 18px;
}

.reserva-resumen h3 {
    margin-bottom: 12px;
}

.reserva-resumen p {
    margin-bottom: 8px;
    line-height: 1.6;
    color: #d8e2f0;
}

.reserva-total {
    font-size: 1.18rem;
    font-weight: 700;
    color: #38bdf8;
}

.reserva-form-card .btn-primary {
    width: 100%;
    margin-top: 8px;
    border: none;
    border-radius: 12px;
    padding: 14px 18px;
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
}

@media (max-width: 768px) {
    .reserva-grid-2 {
        grid-template-columns: 1fr;
    }

    .reserva-form-card {
        padding: 16px;
    }

    .reserva-block {
        padding: 14px;
    }
}
</style>

<main class="page-section reserva-page">
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
                                    Acepto el tratamiento de mis datos personales conforme a la política de privacidad de Benedetti Rent a Car. Autorizo que mis datos sean almacenados para gestionar esta solicitud de reserva y futuras reservas, de acuerdo con la normativa aplicable de protección de datos.
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

                        <hr style="margin: 15px 0; border-color: #334155;">

                        <p><strong>Política de pago</strong></p>
                        <p>• En esta primera pantalla solo registras tu solicitud de reserva.</p>
                        <p>• En la siguiente pantalla el sistema verificará si eres cliente nuevo, referido o recurrente.</p>
                        <p>• Allí se te mostrarán únicamente los métodos de pago que Benedetti Rent a Car tenga habilitados para tu perfil.</p>

                        <hr style="margin: 15px 0; border-color: #334155;">

                        <p><strong>Política de entrega y devolución</strong></p>
                        <p>• Puedes seleccionar aeropuerto, oficina u otro lugar.</p>
                        <p>• Si seleccionas "Otro lugar" en entrega, podrás escribir la dirección y definir un costo manual temporal.</p>
                        <p>• El costo de devolución en "otro lugar" se define internamente por Benedetti Rent a Car.</p>

                        <hr style="margin: 15px 0; border-color: #334155;">

                        <p><strong>Política de devolución tardía / tiempo adicional</strong></p>
                        <p>• Hasta 1 hora de gracia: sin cobro.</p>
                        <p>• Desde 2 hasta 4 horas adicionales: se cobra valor por hora extra.</p>
                        <p>• Desde la 5ta hora adicional: se cobra 1 día completo adicional.</p>
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

        if (horasAdicionales === 0) {
            recargoHoras = 0;
        } else if (horasAdicionales === 1) {
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