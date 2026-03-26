<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

function enviarCorreo($destinatario, $nombreDestinatario, $asunto, $contenidoHtml) {

    $config = require __DIR__ . '/../config/mail.php';

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = $config['host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $config['username'];
        $mail->Password   = $config['password'];
        $mail->SMTPSecure = $config['encryption'];
        $mail->Port       = $config['port'];

        $mail->setFrom($config['from_email'], $config['from_name']);
        $mail->addAddress($destinatario, $nombreDestinatario);

        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = $asunto;
        $mail->Body    = $contenidoHtml;

        $mail->send();

        return [
            "ok" => true,
            "mensaje" => "Correo enviado correctamente."
        ];

    } catch (Exception $e) {
        return [
            "ok" => false,
            "mensaje" => "Error al enviar correo: " . $mail->ErrorInfo
        ];
    }
}