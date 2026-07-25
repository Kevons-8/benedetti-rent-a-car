<?php
session_start();
require_once "../config/database.php";

$mensaje = "";
$token = $_GET["token"] ?? "";

if (empty($token)) {
    die("Token inválido.");
}

$sql = "SELECT * FROM password_resets
        WHERE token = :token
        AND usado = 0
        LIMIT 1";

$stmt = $conexion->prepare($sql);
$stmt->bindParam(":token", $token);
$stmt->execute();

$registro = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$registro) {
    die("El enlace ya fue utilizado o es inválido.");
}

$fechaCreacion = strtotime($registro["fecha_creacion"]);
$ahora = time();

if (($ahora - $fechaCreacion) > 1800) {
    die("El enlace ha expirado.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $password1 = trim($_POST["password"]);
    $password2 = trim($_POST["confirmar_password"]);

    if ($password1 !== $password2) {

        $mensaje = "Las contraseñas no coinciden.";

    } else {

        $hash = password_hash($password1, PASSWORD_DEFAULT);

        $sql = "UPDATE administradores
                SET password = :password
                WHERE correo = :correo";

        $stmt = $conexion->prepare($sql);

        $stmt->execute([
            ":password" => $hash,
            ":correo" => $registro["correo"]
        ]);

        $sql = "UPDATE password_resets
                SET usado = 1
                WHERE id = :id";

        $stmt = $conexion->prepare($sql);

        $stmt->execute([
            ":id" => $registro["id"]
        ]);

        echo "
        <!DOCTYPE html>
        <html lang='es'>
        <head>
            <meta charset='UTF-8'>
            <title>Contraseña actualizada</title>
            <style>
                body{
                    font-family:Arial, Helvetica, sans-serif;
                    background:#f5f5f5;
                    display:flex;
                    justify-content:center;
                    align-items:center;
                    height:100vh;
                }

                .card{
                    width:400px;
                    background:white;
                    padding:30px;
                    border-radius:12px;
                    text-align:center;
                    box-shadow:0 0 15px rgba(0,0,0,.15);
                }

                a{
                    display:inline-block;
                    margin-top:20px;
                    padding:12px 20px;
                    background:#facc15;
                    color:#000;
                    text-decoration:none;
                    border-radius:8px;
                    font-weight:bold;
                }
            </style>
        </head>
        <body>

        <div class='card'>
            <h2>✅ Contraseña actualizada correctamente</h2>
            <p>Ya puedes iniciar sesión con tu nueva contraseña.</p>

            <a href='login.php'>
                Ir al Login
            </a>
        </div>

        </body>
        </html>
        ";

        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Nueva contraseña</title>

<style>

body{
    font-family:Arial, Helvetica, sans-serif;
    background:url('../assets/img/barranquilla_completa.png') no-repeat center center/cover;
    min-height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
}

.card{
    width:380px;
    background:rgba(0,0,0,.70);
    color:white;
    padding:25px;
    border-radius:15px;
}

h2{
    text-align:center;
}

input{
    width:100%;
    padding:12px;
    margin-top:12px;
    border:none;
    border-radius:8px;
    box-sizing:border-box;
}

button{
    width:100%;
    margin-top:18px;
    padding:12px;
    border:none;
    border-radius:8px;
    background:#facc15;
    font-weight:bold;
    cursor:pointer;
    font-size:16px;
}

button:hover{
    background:#eab308;
}

.mensaje{
    color:#ffb3b3;
    margin-bottom:10px;
    text-align:center;
}

</style>

</head>

<body>

<div class="card">

<h2>Nueva contraseña</h2>

<?php if(!empty($mensaje)): ?>

<div class="mensaje">
    <?= htmlspecialchars($mensaje) ?>
</div>

<?php endif; ?>

<form method="POST">

<input
type="password"
name="password"
placeholder="Nueva contraseña"
required>

<input
type="password"
name="confirmar_password"
placeholder="Confirmar contraseña"
required>

<button type="submit">
Actualizar contraseña
</button>

</form>

</div>

</body>

</html>