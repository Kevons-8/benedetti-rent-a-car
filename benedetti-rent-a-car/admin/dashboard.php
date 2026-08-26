<?php
require_once "../includes/auth.php";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Benedetti Rent a Car</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, Helvetica, sans-serif;
        }

        :root {
            --yellow: #facc15;
            --dark: #0f172a;
            --card-dark: rgba(15, 23, 42, 0.86);
            --border: rgba(255, 255, 255, 0.15);
        }

        body {
            min-height: 100vh;
            background: url('../assets/img/pumarejo_nuevo.png') no-repeat center center/cover;
            color: #fff;
            position: relative;
            overflow-x: hidden;
        }

        body::before {
            content: "";
            position: fixed;
            inset: 0;
            background: linear-gradient(
                rgba(15, 23, 42, 0.50),
                rgba(15, 23, 42, 0.60)
            );
            backdrop-filter: blur(2px);
            z-index: 0;
        }

        body.dark-mode::before {
            background: linear-gradient(
                rgba(2, 6, 23, 0.68),
                rgba(2, 6, 23, 0.78)
            );
        }

        .admin-layout {
            position: relative;
            z-index: 1;
            display: flex;
            min-height: 100vh;
            animation: dashboardFade 0.8s ease forwards;
        }

        @keyframes dashboardFade {
            from {
                opacity: 0;
                transform: translateY(18px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .sidebar {
            width: 260px;
            background: rgba(15, 23, 42, 0.88);
            backdrop-filter: blur(16px);
            color: #fff;
            padding: 24px 18px;
            transition: 0.3s ease;
            border-right: 1px solid var(--border);
        }

        .sidebar.collapsed {
            width: 86px;
        }

        .brand {
            margin-bottom: 35px;
            text-align: center;
        }

        .brand h2 {
            font-size: 22px;
            color: var(--yellow);
        }

        .brand p {
            font-size: 13px;
            color: #cbd5e1;
            margin-top: 5px;
        }

        .sidebar.collapsed .brand h2 {
            font-size: 18px;
        }

        .sidebar.collapsed .brand p,
        .sidebar.collapsed .menu-text,
        .sidebar.collapsed .logout-text {
            display: none;
        }

        .toggle-btn {
            width: 100%;
            border: none;
            background: rgba(255, 255, 255, 0.12);
            color: #fff;
            padding: 11px;
            border-radius: 12px;
            cursor: pointer;
            font-weight: bold;
            margin-bottom: 22px;
            transition: 0.2s;
        }

        .toggle-btn:hover {
            background: var(--yellow);
            color: #1e293b;
        }

        .menu {
            list-style: none;
        }

        .menu li {
            margin-bottom: 10px;
        }

        .menu a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 13px 14px;
            border-radius: 12px;
            text-decoration: none;
            color: #e2e8f0;
            transition: 0.2s;
            font-weight: bold;
            font-size: 14px;
        }

        .menu a:hover,
        .menu a.active {
            background: var(--yellow);
            color: #1e293b;
        }

        .menu-icon {
            font-size: 18px;
            min-width: 24px;
            text-align: center;
        }

        .logout {
            margin-top: 35px;
        }

        .logout a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 13px 14px;
            border-radius: 12px;
            text-decoration: none;
            background: rgba(239, 68, 68, 0.22);
            color: #fecaca;
            font-weight: bold;
            font-size: 14px;
            transition: 0.2s;
        }

        .logout a:hover {
            background: #ef4444;
            color: #fff;
        }

        .main-content {
            flex: 1;
            padding: 32px;
        }

        .topbar {
            background: var(--card-dark);
            backdrop-filter: blur(14px);
            border-radius: 20px;
            padding: 22px 26px;
            box-shadow: 0 10px 35px rgba(0, 0, 0, 0.22);
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 28px;
            border: 1px solid var(--border);
        }

        .topbar h1 {
            font-size: 28px;
            margin-bottom: 6px;
            color: #fff;
        }

        .topbar p {
            color: #cbd5e1;
            font-size: 14px;
        }

        .top-actions {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .mode-btn {
            border: none;
            background: rgba(2, 6, 23, 0.55);
            color: #fff;
            padding: 11px 14px;
            border-radius: 12px;
            cursor: pointer;
            font-weight: bold;
            transition: 0.2s;
        }

        .mode-btn:hover {
            background: var(--yellow);
            color: #1e293b;
        }

        .admin-user {
            text-align: right;
            font-size: 14px;
        }

        .admin-user strong {
            display: block;
            color: #fff;
        }

        .admin-user span {
            color: #cbd5e1;
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 22px;
            margin-bottom: 28px;
        }

        .card {
            background: var(--card-dark);
            backdrop-filter: blur(14px);
            border-radius: 20px;
            padding: 24px;
            box-shadow: 0 10px 35px rgba(0, 0, 0, 0.22);
            border: 1px solid var(--border);
            transition: 0.25s;
            animation: cardUp 0.8s ease forwards;
            color: #fff;
        }

        .card:nth-child(2) { animation-delay: 0.08s; }
        .card:nth-child(3) { animation-delay: 0.16s; }
        .card:nth-child(4) { animation-delay: 0.24s; }

        @keyframes cardUp {
            from {
                opacity: 0;
                transform: translateY(24px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .card:hover {
            transform: translateY(-6px);
            box-shadow: 0 22px 48px rgba(0, 0, 0, 0.32);
        }

        .card .icon {
            width: 46px;
            height: 46px;
            border-radius: 14px;
            background: var(--yellow);
            color: #1e293b;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            margin-bottom: 16px;
        }

        .card h3 {
            font-size: 14px;
            color: #cbd5e1;
            margin-bottom: 8px;
        }

        .card .number {
            font-size: 30px;
            font-weight: bold;
            color: #fff;
        }

        .panel {
            background: var(--card-dark);
            backdrop-filter: blur(14px);
            border-radius: 20px;
            padding: 24px;
            box-shadow: 0 10px 35px rgba(0, 0, 0, 0.22);
            border: 1px solid var(--border);
            animation: cardUp 0.9s ease forwards;
            color: #fff;
        }

        .panel-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 18px;
        }

        .panel-header h2 {
            font-size: 20px;
        }

        .panel-header a {
            background: rgba(2, 6, 23, 0.55);
            color: #fff;
            text-decoration: none;
            padding: 10px 14px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: bold;
            transition: 0.2s;
        }

        .panel-header a:hover {
            background: var(--yellow);
            color: #1e293b;
        }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 14px;
        }

        .quick-actions a {
            text-decoration: none;
            background: rgba(15, 23, 42, 0.55);
            border: 1px solid var(--border);
            color: #f8fafc;
            padding: 16px;
            border-radius: 14px;
            font-weight: bold;
            transition: 0.2s;
        }

        .quick-actions a:hover {
            background: var(--yellow);
            border-color: var(--yellow);
            color: #1e293b;
            transform: translateY(-2px);
        }

        body.dark-mode .topbar,
        body.dark-mode .card,
        body.dark-mode .panel {
            background: rgba(2, 6, 23, 0.9);
        }

        @media (max-width: 1000px) {
            .cards,
            .quick-actions {
                grid-template-columns: repeat(2, 1fr);
            }

            .sidebar {
                width: 230px;
            }
        }

        @media (max-width: 720px) {
            body {
                overflow: auto;
            }

            .admin-layout {
                flex-direction: column;
            }

            .sidebar,
            .sidebar.collapsed {
                width: 100%;
            }

            .sidebar.collapsed .menu-text,
            .sidebar.collapsed .logout-text,
            .sidebar.collapsed .brand p {
                display: inline;
            }

            .toggle-btn {
                display: none;
            }

            .main-content {
                padding: 20px;
            }

            .topbar {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .top-actions {
                width: 100%;
                justify-content: space-between;
            }

            .admin-user {
                text-align: left;
            }

            .cards,
            .quick-actions {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

<div class="admin-layout">

    <aside class="sidebar" id="sidebar">
        <div class="brand">
            <h2>Benedetti</h2>
            <p>Rent a Car Admin</p>
        </div>

        <button class="toggle-btn" type="button" onclick="toggleSidebar()">☰ Menú</button>

        <ul class="menu">
            <li>
                <a href="dashboard.php" class="active">
                    <span class="menu-icon">📊</span>
                    <span class="menu-text">Dashboard</span>
                </a>
            </li>

            <li>
                <a href="../modules/vehiculos/listar.php">
                    <span class="menu-icon">🚗</span>
                    <span class="menu-text">Vehículos</span>
                </a>
            </li>

            <li>
                <a href="../clientes/index.php">
                    <span class="menu-icon">👤</span>
                    <span class="menu-text">Clientes</span>
                </a>
            </li>

            <li>
                <a href="../modules/reservas/listar.php">
                    <span class="menu-icon">📅</span>
                    <span class="menu-text">Reservas</span>
                </a>
            </li>

            <li>
                <a href="../modules/pagos/listar.php">
                    <span class="menu-icon">💳</span>
                    <span class="menu-text">Pagos</span>
                </a>
            </li>
        </ul>

        <div class="logout">
            <a href="logout.php">
                <span class="menu-icon">🔒</span>
                <span class="logout-text">Cerrar sesión</span>
            </a>
        </div>
    </aside>

    <main class="main-content">

        <section class="topbar">
            <div>
                <h1>Panel de Administración</h1>
                <p>Resumen general de Benedetti Rent a Car</p>
            </div>

            <div class="top-actions">
                <button class="mode-btn" type="button" onclick="toggleDarkMode()">🌙 Modo oscuro</button>

                <div class="admin-user">
                    <strong><?php echo htmlspecialchars($_SESSION["admin_nombre"]); ?></strong>
                    <span><?php echo htmlspecialchars($_SESSION["admin_correo"]); ?></span>
                </div>
            </div>
        </section>

        <section class="cards">
            <div class="card">
                <div class="icon">📅</div>
                <h3>Reservas</h3>
                <div class="number">0</div>
            </div>

            <div class="card">
                <div class="icon">🚗</div>
                <h3>Vehículos</h3>
                <div class="number">0</div>
            </div>

            <div class="card">
                <div class="icon">👤</div>
                <h3>Clientes</h3>
                <div class="number">0</div>
            </div>

            <div class="card">
                <div class="icon">💳</div>
                <h3>Pagos</h3>
                <div class="number">$0</div>
            </div>
        </section>

        <section class="panel">
            <div class="panel-header">
                <h2>Accesos rápidos</h2>
                <a href="../modules/reservas/listar.php">Ver reservas</a>
            </div>

            <div class="quick-actions">
                <a href="../modules/vehiculos/listar.php">🚗 Gestionar vehículos</a>
                <a href="../clientes/index.php">👤 Gestionar clientes</a>
                <a href="../modules/reservas/listar.php">📅 Gestionar reservas</a>
                <a href="../modules/pagos/listar.php">💳 Gestionar pagos</a>
            </div>
        </section>

    </main>

</div>

<script>
    function toggleSidebar() {
        const sidebar = document.getElementById("sidebar");
        sidebar.classList.toggle("collapsed");
    }

    function toggleDarkMode() {
        document.body.classList.toggle("dark-mode");
    }
</script>

</body>
</html>