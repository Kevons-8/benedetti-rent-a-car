<?php
session_start();
require_once "../config/database.php";

if (isset($_SESSION["admin_id"])) {
    header("Location: dashboard.php");
    exit();
}

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $correo = trim($_POST["correo"]);
    $password = trim($_POST["password"]);

    if (!empty($correo) && !empty($password)) {
        $sql = "SELECT * FROM administradores WHERE correo = :correo LIMIT 1";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(":correo", $correo);
        $stmt->execute();

        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin && password_verify($password, $admin["password"])) {
            $_SESSION["admin_id"] = $admin["id"];
            $_SESSION["admin_nombre"] = $admin["nombre"];
            $_SESSION["admin_correo"] = $admin["correo"];

            header("Location: dashboard.php");
            exit();
        } else {
            $mensaje = "Correo o contraseña incorrectos.";
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
    <title>Admin | Benedetti Rent a Car</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, Helvetica, sans-serif;
        }

        body {
            min-height: 100vh;
            background: url('../assets/img/barranquilla_completa.png') no-repeat center center/cover;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            padding-top: 170px;
            overflow: hidden;
        }

        body::before {
            content: "";
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.32);
            z-index: 0;
        }

        .login-container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 340px;
            background: rgba(5, 15, 20, 0.52);
            backdrop-filter: blur(10px);
            border-radius: 18px;
            padding: 26px 24px;
            box-shadow: 0 22px 55px rgba(0, 0, 0, 0.55);
            border: 1px solid rgba(255, 255, 255, 0.16);
            color: #fff;

            animation: fadeInFloat 0.9s ease-out forwards, floatingCard 4s ease-in-out infinite;
        }

        @keyframes fadeInFloat {
            from {
                opacity: 0;
                transform: translateY(35px) scale(0.96);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes floatingCard {
            0% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-8px);
            }
            100% {
                transform: translateY(0);
            }
        }

        .brand {
            text-align: center;
            margin-bottom: 20px;
        }

        .brand h1 {
            font-size: 22px;
            margin-bottom: 5px;
        }

        .brand p {
            font-size: 13px;
            opacity: 0.85;
        }

        .mensaje {
            background: rgba(255, 77, 77, 0.9);
            padding: 9px;
            border-radius: 8px;
            margin-bottom: 14px;
            text-align: center;
            font-size: 13px;
        }

        .form-group {
            margin-bottom: 13px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-size: 13px;
            font-weight: bold;
        }

        input {
            width: 100%;
            padding: 10px;
            font-size: 14px;
            border: 1px solid rgba(255,255,255,0.25);
            border-radius: 10px;
            background: rgba(255,255,255,0.16);
            color: #fff;
            transition: 0.2s;
        }

        input::placeholder {
            color: #ddd;
        }

        input:focus {
            outline: none;
            border-color: #facc15;
            box-shadow: 0 0 0 3px rgba(250,204,21,0.3);
            background: rgba(255,255,255,0.22);
        }

        button {
            width: 100%;
            padding: 11px;
            font-size: 14px;
            background: #facc15;
            color: #1e293b;
            border: none;
            border-radius: 10px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.2s;
            margin-top: 10px;
        }

        button:hover {
            background: #eab308;
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.35);
        }

        .footer-text {
            margin-top: 14px;
            text-align: center;
            font-size: 11px;
            opacity: 0.8;
        }

        @media (max-width: 600px) {
            body {
                padding: 20px;
                overflow: auto;
            }

            .login-container {
                max-width: 100%;
                animation: fadeInFloat 0.9s ease-out forwards;
            }
        }
    </style>
</head>

<body>

<div class="login-container">
    <div class="brand">
        <h1>Benedetti Rent a Car</h1>
        <p>Panel administrativo</p>
    </div>

    <?php if (!empty($mensaje)) : ?>
        <div class="mensaje">
            <?php echo htmlspecialchars($mensaje); ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Correo</label>
            <input type="email" name="correo" placeholder="admin@benedetti.com" required>
        </div>

        <div class="form-group">
            <label>Contraseña</label>
            <input type="password" name="password" placeholder="Ingresa tu contraseña" required>
        </div>

        <button type="submit">Iniciar sesión</button>
    </form>

    <div class="footer-text">
        © <?php echo date("Y"); ?> Benedetti Rent a Car
    </div>
</div>

</body>
</html>