<?php
session_start();
require_once "../config/database.php";

if (isset($_SESSION["admin_id"])) {
    header("Location: dashboard.php");
    exit();
}


$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = trim($_POST["correo"]);
    $password = trim($_POST["password"]);

    if (!empty($correo) && !empty($password)) {
        $sql = "SELECT * FROM administradores WHERE correo = :correo LIMIT 1";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':correo', $correo);
        $stmt->execute();

        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin) {
            if (password_verify($password, $admin["password"])) {
                $_SESSION["admin_id"] = $admin["id"];
                $_SESSION["admin_nombre"] = $admin["nombre"];
                $_SESSION["admin_correo"] = $admin["correo"];

                header("Location: dashboard.php");
                exit();
            } else {
                $mensaje = "Contraseña incorrecta.";
            }
        } else {
            $mensaje = "El correo no existe.";
        }
    } else {
        $mensaje = "Todos los campos son obligatorios.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Administrador - Benedetti Rent a Car</title>
</head>
<body>
    <h2>Login Administrador</h2>

    <?php if (!empty($mensaje)) : ?>
        <p><?php echo $mensaje; ?></p>
    <?php endif; ?>

    <form action="" method="POST">
        <label for="correo">Correo:</label><br>
        <input type="email" name="correo" id="correo" required><br><br>

        <label for="password">Contraseña:</label><br>
        <input type="password" name="password" id="password" required><br><br>

        <button type="submit">Iniciar sesión</button>
    </form>
</body>
</html>