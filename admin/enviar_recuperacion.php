<?php

require_once "../vendor/autoload.php";
require_once "../config/mail_config.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$config = require "../config/mail_config.php";

$correo = $_GET["correo"] ?? "";
$token = $_GET["token"] ?? "";

if (empty($correo) || empty($token)) {
    die("Datos inválidos.");
}

$link = "http://localhost/benedetti-rent-a-car/admin/reset_password.php?token=" . $token;

$mail = new PHPMailer(true);

try {

    $mail->isSMTP();
    $mail->Host       = $config['host'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $config['username'];
    $mail->Password   = $config['password'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = $config['port'];

    $mail->CharSet = 'UTF-8';

    $mail->setFrom(
        $config['from_email'],
        $config['from_name']
    );

    $mail->addAddress($correo);

    $mail->isHTML(true);

    $mail->Subject = "Recuperación de contraseña - Benedetti Rent a Car";

    $mail->Body = "

    <h2>Recuperación de contraseña</h2>

    <p>Se ha solicitado el cambio de contraseña para el panel administrativo.</p>

    <p>
        Haz clic en el siguiente enlace:
    </p>

    <p>
        <a href='$link'>
            Restablecer contraseña
        </a>
    </p>

    <p>
        Este enlace expirará en 30 minutos.
    </p>

    ";

    $mail->send();

    echo "
    <h2>Correo enviado correctamente</h2>
    <p>Revisa tu bandeja de entrada.</p>
    <a href='login.php'>Volver al Login</a>
    ";

} catch (Exception $e) {

    echo "
    Error al enviar correo:<br>
    " . $mail->ErrorInfo;

}
?>
