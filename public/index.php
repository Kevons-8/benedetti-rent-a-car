<?php
require_once __DIR__ . '/../views/partials/header.php';
require_once __DIR__ . '/../views/partials/navbar.php';
?>

<main>

    <section class="hero hero-static-banner" style="background-image: url('/benedetti-rent-a-car/assets/img/hero_barranquilla.png');">
        <div class="hero-overlay"></div>

        <div class="container hero-content">
            <div class="hero-text">
                <span class="hero-badge">Movilidad segura en Barranquilla</span>

                <h1>Alquila tu vehículo con confianza</h1>

                <p>
                    Descubre Barranquilla con estilo, comodidad y seguridad.
                </p>

                <div class="hero-buttons">
                    <a href="/benedetti-rent-a-car/public/vehiculos.php" class="btn btn-primary">
                        <span class="btn-icon">🚗</span>
                        <span>Reservar vehículo</span>
                    </a>

                    <!-- BOTÓN WHATSAPP PRINCIPAL -->
                    <a 
                        href="https://wa.me/573153472438?text=Hola,%20estoy%20interesado%20en%20alquilar%20un%20vehículo%20en%20Barranquilla.%20¿Me%20pueden%20ayudar?"
                        target="_blank" 
                        class="btn btn-secondary"
                        onclick="registrarClickWhatsapp()"
                    >
                        <span class="btn-icon">💬</span>
                        <span>WhatsApp</span>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="home-showcase-overlap">
        <div class="container">
            <div class="home-showcase-card animate-on-scroll">
                <div class="section-header section-header-light">
                    <h2>¿Por qué elegir Benedetti Rent a Car?</h2>
                    <p>
                        Te ofrecemos una experiencia confiable, rápida y adaptada a tus necesidades.
                    </p>
                </div>

                <div class="benefits-grid">
                    <article class="card hover-lift">
                        <h3>Atención personalizada</h3>
                        <p>Te acompañamos durante todo el proceso.</p>
                    </article>

                    <article class="card hover-lift">
                        <h3>Vehículos confiables</h3>
                        <p>Autos listos para cualquier tipo de viaje.</p>
                    </article>

                    <article class="card hover-lift">
                        <h3>Proceso sencillo</h3>
                        <p>Reserva fácil y sin complicaciones.</p>
                    </article>

                    <article class="card hover-lift">
                        <h3>Flexibilidad</h3>
                        <p>Nos adaptamos a tus tiempos.</p>
                    </article>
                </div>
            </div>
        </div>
    </section>

    <section class="page-section animate-on-scroll">
        <div class="container">
            <div class="cta-box">
                <h2>Encuentra el vehículo ideal para ti</h2>
                <p>
                    Explora nuestro catálogo y elige el vehículo perfecto para tu viaje.
                </p>

                <a href="/benedetti-rent-a-car/public/vehiculos.php" class="btn btn-primary">
                    <span class="btn-icon">✨</span>
                    <span>Ver vehículos disponibles</span>
                </a>
            </div>
        </div>
    </section>

</main>

<!-- BOTÓN FLOTANTE WHATSAPP PRO -->
<a 
    href="https://wa.me/573153472438?text=Hola,%20estoy%20interesado%20en%20alquilar%20un%20vehículo%20en%20Barranquilla.%20¿Me%20pueden%20ayudar?"
    class="whatsapp-float"
    target="_blank"
    aria-label="Contactar por WhatsApp"
    onclick="registrarClickWhatsapp()"
>
    <span class="whatsapp-icon">💬</span>
    <span class="whatsapp-text">¿Necesitas ayuda?</span>
</a>

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

/* TRACKING DE CLICS */
function registrarClickWhatsapp() {
    let clicks = localStorage.getItem("clicksWhatsappBenedetti") || 0;
    clicks++;
    localStorage.setItem("clicksWhatsappBenedetti", clicks);

    console.log("Clics en WhatsApp Benedetti Rent a Car:", clicks);
}
</script>

<?php
require_once __DIR__ . '/../views/partials/footer.php';
?>