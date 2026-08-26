<?php
require_once "config/database.php";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Benedetti Rent a Car</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>

    <header class="navbar">
        <div class="logo">
            <h2>Benedetti</h2>
            <span>Rent a Car</span>
        </div>

        <nav>
            <ul class="nav-links">
                <li><a href="#inicio">Inicio</a></li>
                <li><a href="#vehiculos">Vehículos</a></li>
                <li><a href="#reservar">Reservar</a></li>
                <li><a href="#contacto">Contacto</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section class="hero" id="inicio">
            <div class="hero-overlay"></div>
            <div class="hero-glow hero-glow-1"></div>
            <div class="hero-glow hero-glow-2"></div>

            <div class="hero-content">
                <div class="hero-text">
                    <span class="hero-badge">Movilidad segura en Barranquilla</span>
                    <h1>Alquila tu vehículo con confianza y estilo</h1>
                    <p>
                        En Benedetti Rent a Car te ofrecemos vehículos cómodos, seguros
                        y listos para acompañarte en cada trayecto.
                    </p>

                    <div class="hero-buttons">
                        <a href="#vehiculos" class="btn btn-primary">Ver vehículos</a>
                        <a href="#reservar" class="btn btn-secondary">Reservar por WhatsApp</a>
                    </div>
                </div>

                <div class="hero-card" id="reservar">
                    <h3>Reserva rápida</h3>
                    <p>
                        Consulta disponibilidad, elige fechas y solicita tu vehículo
                        en pocos pasos.
                    </p>
                </div>
            </div>
        </section>

        <section class="vehicles" id="vehiculos">
            <div class="section-header">
                <h2>Vehículos disponibles para tu viaje</h2>
                <p>Conoce nuestros vehículos disponibles en Barranquilla.</p>
            </div>

            <div class="vehicle-grid">
                <article class="vehicle-card">
                    <img src="assets/img/picanto.jpg" alt="Kia Picanto">
                    <div class="vehicle-info">
                        <h3>Kia Picanto</h3>
                        <p>Económico, cómodo y perfecto para ciudad.</p>
                    </div>
                </article>

                <article class="vehicle-card">
                    <img src="assets/img/kwid.jpg" alt="Renault Kwid">
                    <div class="vehicle-info">
                        <h3>Renault Kwid</h3>
                        <p>Compacto, moderno y eficiente.</p>
                    </div>
                </article>

                <article class="vehicle-card">
                    <img src="assets/img/swift.jpg" alt="Suzuki Swift Dzire">
                    <div class="vehicle-info">
                        <h3>Suzuki Swift Dzire</h3>
                        <p>Ideal para viajes cómodos y seguros.</p>
                    </div>
                </article>
            </div>
        </section>

        <section class="contact" id="contacto">
            <div class="section-header">
                <h2>Contáctanos</h2>
                <p>Estamos listos para ayudarte a encontrar el vehículo ideal.</p>
            </div>

            <div class="contact-box">
                <p><strong>WhatsApp:</strong> +57 300 000 0000</p>
                <p><strong>Ciudad:</strong> Barranquilla, Colombia</p>
                <p><strong>Email:</strong> contacto@benedettirentacar.com</p>
            </div>
        </section>
    </main>

</body>
</html>