<?php
require_once __DIR__ . '/../views/partials/header.php';
require_once __DIR__ . '/../views/partials/navbar.php';
?>

<main>
    <section class="hero">
        <div class="hero-overlay"></div>
        <div class="hero-glow hero-glow-1"></div>
        <div class="hero-glow hero-glow-2"></div>

        <div class="container hero-content">
            <div class="hero-text">
                <span class="hero-badge">Movilidad segura en Barranquilla</span>

                <h1>Alquila tu vehículo en Barranquilla con confianza</h1>

                <p>
                    En Benedetti Rent a Car te ofrecemos vehículos cómodos, seguros
                    y listos para que disfrutes tu estadía, tu trabajo o tu movilidad
                    en la ciudad de forma práctica y confiable.
                </p>

                <div class="hero-buttons">
                    <a href="/benedetti-rent-a-car/public/vehiculos.php" class="btn btn-primary">
                        Ver vehículos
                    </a>
                    <a href="https://wa.me/573001234567" target="_blank" class="btn btn-secondary">
                        Reservar por WhatsApp
                    </a>
                </div>
            </div>

            <div class="hero-card">
                <h3>Reserva rápida</h3>
                <p>
                    Consulta disponibilidad, elige fechas y solicita tu vehículo
                    en pocos pasos de manera fácil y segura.
                </p>
            </div>
        </div>
    </section>

    <section class="page-section">
        <div class="container">
            <div class="section-header">
                <h2>¿Por qué elegir Benedetti Rent a Car?</h2>
                <p>
                    Trabajamos para brindarte una experiencia de alquiler clara,
                    confiable y adaptada a tus necesidades de movilidad.
                </p>
            </div>

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
                    <p>Solicita tu reserva de forma rápida, clara y organizada.</p>
                </article>

                <article class="card">
                    <h3>Flexibilidad</h3>
                    <p>Opciones adaptadas a tus necesidades y tiempos de viaje.</p>
                </article>
            </div>
        </div>
    </section>

    <section class="page-section">
        <div class="container">
            <div class="cta-box">
                <h2>Encuentra el vehículo ideal para ti</h2>
                <p>
                    Revisa nuestro catálogo y elige el vehículo que mejor se adapte
                    a tu necesidad.
                </p>
                <a href="/benedetti-rent-a-car/public/vehiculos.php" class="btn btn-primary">
                    Explorar catálogo
                </a>
            </div>
        </div>
    </section>
</main>

<?php
require_once __DIR__ . '/../views/partials/footer.php';
?>