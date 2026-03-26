<?php
require_once "includes/email_helper.php";

$destinatario = "benedettirentacar@gmail.com";
$nombre = "Prueba Benedetti";
$asunto = "Prueba de correo - Benedetti Rent a Car";
$contenidoHtml = "
    <h2>Prueba de correo</h2>
    <p>Este es un correo de prueba desde el sistema Benedetti Rent a Car.</p>
";

$resultado = enviarCorreo($destinatario, $nombre, $asunto, $contenidoHtml);

echo "<pre>";
print_r($resultado);
echo "</pre>";
?>