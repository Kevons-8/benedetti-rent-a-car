<?php
require_once __DIR__ . '/../views/partials/header.php';
require_once __DIR__ . '/../views/partials/navbar.php';
?>
<main class="home-page">
    <section class="hero hero-static-banner home-hero" style="background-image: url('/assets/img/hero-malecon-rio-v2.png');">
        <div class="hero-overlay"></div>
        <img class="home-hero-sky-logo" src="/assets/img/logo-benedetti-hero-transparent.png" alt="Benedetti Rent a Car">
        <div class="container hero-content">
            <div class="hero-text">
                <span class="hero-badge"><span></span>Barranquilla · Caribe colombiano</span>
                <h1>El Caribe se disfruta mejor a tu ritmo.</h1>
                <p>
                    Tu viaje comienza en Barranquilla. Recorre la ciudad, la costa y cada destino con comodidad, libertad y atención cercana.
                </p>
                <div class="hero-buttons">
                    <a href="/public/vehiculos.php" class="btn btn-primary">
                        <span class="btn-icon">⌁</span>
                        <span>Reservar vehículo</span>
                    </a>
                </div>
            </div>
            <div class="home-trust-bar" aria-label="Beneficios de Benedetti Rent a Car">
                <span><b>✦</b> Atención personalizada</span>
                <span><b>✦</b> Viaja con confianza</span>
                <span><b>✦</b> Entrega flexible</span>
            </div>
        </div>
    </section>
    <section class="home-destination-section">
        <div class="container destination-layout">
            <div class="destination-copy animate-on-scroll">
                <span class="eyebrow eyebrow-dark">DESCUBRE A TU MANERA</span>
                <h2>Más que un destino,<br>una ruta por vivir.</h2>
                <p>Del Malecón del Río a la costa Caribe, cada recorrido tiene una historia. Elige el vehículo que te acompaña a vivirla.</p>
            </div>
            <div class="destination-gallery animate-on-scroll">
                <img class="destination-main" src="/assets/img/malecon_y_buque_gloria.jpg" alt="Malecón del Río, Barranquilla">
                <img class="destination-detail" src="/assets/img/ventana_al_mundo.jpg" alt="Ventana al Mundo en Barranquilla">
            </div>
        </div>
    </section>
</main>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, {
        threshold: 0.15
    });
    document.querySelectorAll('.animate-on-scroll').forEach(el => {
        observer.observe(el);
    });
});
</script>
<?php
require_once __DIR__ . '/../views/partials/footer.php';
?>
