<?php
session_start();
require_once "../config/database.php";

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $correo = trim($_POST["correo"]);

    $sql = "SELECT * FROM administradores WHERE correo = :correo LIMIT 1";
    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(":correo", $correo);
    $stmt->execute();

    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin) {

        $token = bin2hex(random_bytes(32));

        $sql = "INSERT INTO password_resets
                (correo, token, fecha_creacion)
                VALUES
                (:correo, :token, NOW())";

        $stmt = $conexion->prepare($sql);

        $stmt->execute([
            ":correo" => $correo,
            ":token" => $token
        ]);

        header("Location: enviar_recuperacion.php?correo=" . urlencode($correo) . "&token=" . urlencode($token));
        exit();

    } else {

        $mensaje = "El correo no existe en el sistema.";

    }
}
?>

<!DOCTYPE html>

<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Recuperar contraseña</title>

<style>

body{
    font-family: Arial, Helvetica, sans-serif;
    background:url('../assets/img/barranquilla_completa.png') no-repeat center center/cover;
    min-height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
}

.card{
    width:350px;
    background:rgba(0,0,0,.65);
    padding:25px;
    border-radius:15px;
    color:white;
}

input{
    width:100%;
    padding:10px;
    margin-top:10px;
    border-radius:8px;
    border:none;
}

button{
    width:100%;
    margin-top:15px;
    padding:12px;
    background:#facc15;
    border:none;
    border-radius:8px;
    font-weight:bold;
    cursor:pointer;
}

.mensaje{
    margin-top:10px;
    color:#ffb3b3;
}

a{
    color:#facc15;
    text-decoration:none;
}

</style>

</head>

<body>

<div class="card">

<h2>Recuperar contraseña</h2>

<p>Ingresa tu correo administrativo.</p>

<?php if(!empty($mensaje)): ?>

<div class="mensaje">
    <?= htmlspecialchars($mensaje) ?>
</div>
<?php endif; ?>

<form method="POST">

<input
type="email"
name="correo"
placeholder="Correo electrónico"
required>

<button type="submit">
Enviar enlace de recuperación
</button>

</form>

<br>

<a href="login.php">
Volver al login
</a>

</div>

</body>
</html>
