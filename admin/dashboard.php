<?php
require_once "../includes/auth.php";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Benedetti Rent a Car</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 30px;
        }

        h1 {
            margin-bottom: 10px;
        }

        .info {
            margin-bottom: 20px;
        }

        .menu {
            list-style: none;
            padding: 0;
            max-width: 400px;
        }

        .menu li {
            margin-bottom: 10px;
        }

        .menu a {
            display: block;
            padding: 12px;
            text-decoration: none;
            border: 1px solid #ccc;
            color: #000;
            background-color: #f9f9f9;
        }

        .menu a:hover {
            background-color: #e6e6e6;
        }

        .logout {
            margin-top: 20px;
        }
    </style>
</head>
<body>

    <h1>Panel de Administración</h1>

    <div class="info">
        <p><strong>Bienvenido:</strong> <?php echo htmlspecialchars($_SESSION["admin_nombre"]); ?></p>
        <p><strong>Correo:</strong> <?php echo htmlspecialchars($_SESSION["admin_correo"]); ?></p>
    </div>

    <ul class="menu">
        <li>
            <a href="../modules/vehiculos/listar.php">
                🚗 Gestionar Vehículos
            </a>
        </li>

        <li>
            <a href="../clientes/index.php">
                👤 Gestionar Clientes
            </a>
        </li>

        <li>
            <a href="../modules/reservas/listar.php">
                📅 Gestionar Reservas
            </a>
        </li>

        <li>
            <a href="../modules/pagos/listar.php">
                💳 Gestionar Pagos
            </a>
        </li>
    </ul>

    <div class="logout">
        <a href="logout.php">🔒 Cerrar sesión</a>
    </div>

</body>
</html>