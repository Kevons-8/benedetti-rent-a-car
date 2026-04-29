<?php
require_once __DIR__ . '/../views/partials/header.php';
require_once __DIR__ . '/../views/partials/navbar.php';
?>

<main class="pago-bg">

    <div class="overlay"></div>

    <div class="container contenido-pago">

        <div class="payment-box">

            <h2 style="color: #ffd700;">Método de pago confirmado</h2>

            <p><strong>Reserva:</strong> RES-20260429190439-5</p>
            <p><strong>Referencia de pago:</strong> PAY-RES-20260429190439-5081</p>
            <p><strong>Cliente:</strong> Kevin Benedetti Hernández</p>
            <p><strong>Vehículo:</strong> Renault Kwid</p>
            <p><strong>Tipo de cliente:</strong> existente</p>
            <p><strong>Método elegido:</strong> tarjeta</p>
            <p><strong>Pasarela / canal:</strong> wompi_simulado</p>
            <p><strong>Monto del movimiento:</strong> $400.000</p>

            <div style="margin-top: 20px;">
                <span class="badge badge-success">Reserva confirmada</span>
                <span class="badge badge-warning">Pago Pagado</span>
            </div>

            <hr style="margin: 25px 0; border-color: rgba(255,255,255,0.2);">

            <p><strong>Resultado del flujo:</strong> pago simulado aprobado. La reserva queda confirmada.</p>

        </div>

    </div>

</main>

<?php
require_once __DIR__ . '/../views/partials/footer.php';
?>