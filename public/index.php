<?php
require_once __DIR__ . '/../views/partials/header.php';
require_once __DIR__ . '/../views/partials/navbar.php';
?>

<main>
    <section class="hero">
        <div class="container hero-content">
            <div class="hero-text">
                <h1>Alquila tu vehículo en Barranquilla con confianza</h1>
                <p>
                    En Benedetti Rent a Car te ofrecemos vehículos cómodos, seguros
                    y listos para que disfrutes tu estadía, trabajo o movilidad en la ciudad.
                </p>

                <div class="hero-buttons">
                    <a href="/benedetti-rent-a-car/public/vehiculos.php" class="btn btn-primary">Ver vehículos</a>
                    <a href="https://wa.me/573001234567" target="_blank" class="btn btn-secondary">Reservar por WhatsApp</a>
                </div>
            </div>

            <div class="hero-card">
                <h3>Reserva rápida</h3>
                <p>
                    Consulta disponibilidad, elige fechas y solicita tu vehículo en pocos pasos.
                </p>
            </div>
        </div>
    </section>

    <section class="benefits container">
        <h2>¿Por qué elegir Benedetti Rent a Car?</h2>

        <div class="benefits-grid">
            <article class="card">
                <h3>Atención personalizada</h3>
                <p>Te acompañamos durante todo el proceso de reserva.</p>
            </article>

            <article class="card">
                <h3>Vehículos confiables</h3>
                <p>Autos listos para uso urbano, viajes y movilidad segura.</p>
            </article>

            <article class="card">
                <h3>Proceso sencillo</h3>
                <p>Solicita tu reserva de forma rápida y clara.</p>
            </article>

            <article class="card">
                <h3>Flexibilidad</h3>
                <p>Opciones adaptadas a tus necesidades y tiempos de viaje.</p>
            </article>
        </div>
    </section>

    <section class="cta container">
        <div class="cta-box">
            <h2>Encuentra el vehículo ideal para ti</h2>
            <p>
                Revisa nuestro catálogo y elige el vehículo que mejor se adapte a tu necesidad.
            </p>
            <a href="/benedetti-rent-a-car/public/vehiculos.php" class="btn btn-primary">Explorar catálogo</a>
        </div>
    </section>
</main>

<?php
require_once __DIR__ . '/../views/partials/footer.php';
?>