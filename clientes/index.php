<?php
include("../config/conexion.php");

$busqueda = "";

if (isset($_GET['buscar'])) {
    $busqueda = trim($_GET['buscar']);
}

if ($busqueda !== "") {
    $sql = "SELECT * FROM clientes 
            WHERE nombre LIKE ? 
            OR apellido LIKE ? 
            OR numero_documento LIKE ? 
            OR correo LIKE ? 
            OR telefono LIKE ?
            ORDER BY id_cliente DESC";

    $stmt = $conn->prepare($sql);
    $parametro = "%" . $busqueda . "%";
    $stmt->bind_param("sssss", $parametro, $parametro, $parametro, $parametro, $parametro);
    $stmt->execute();
    $resultado = $stmt->get_result();
} else {
    $sql = "SELECT * FROM clientes ORDER BY id_cliente DESC";
    $resultado = $conn->query($sql);
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Módulo de Clientes</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            min-height: 100vh;
            background:
                linear-gradient(135deg, rgba(3, 13, 31, 0.82), rgba(6, 32, 71, 0.78)),
                url("/benedetti-rent-a-car/assets/img/fondo_vehiculos.png") center center / cover no-repeat fixed;
            color: #ffffff;
            padding: 45px 7%;
        }

        .clientes-container {
            max-width: 1250px;
            margin: 0 auto;
        }

        .clientes-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 24px;
            margin-bottom: 28px;
        }

        .clientes-badge {
            display: inline-flex;
            padding: 9px 15px;
            border-radius: 999px;
            background: rgba(34, 197, 94, 0.12);
            border: 1px solid rgba(34, 197, 94, 0.35);
            color: #86efac;
            font-size: 0.9rem;
            font-weight: 700;
            margin-bottom: 14px;
        }

        h1 {
            font-size: clamp(2rem, 4vw, 3rem);
            color: #ffffff;
            margin-bottom: 10px;
        }

        .clientes-header p {
            color: #d8e2f0;
            max-width: 650px;
            line-height: 1.6;
        }

        .clientes-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .admin-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 48px;
            padding: 12px 22px;
            border-radius: 999px;
            border: none;
            font-size: 0.95rem;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.25s ease;
            text-decoration: none;
            white-space: nowrap;
        }

        .admin-btn-primary {
            color: #ffffff;
            background: linear-gradient(180deg, #6eff1f 0%, #38d600 45%, #19a500 100%);
            box-shadow:
                inset 0 2px 0 rgba(255, 255, 255, 0.32),
                0 12px 24px rgba(34, 197, 94, 0.28);
        }

        .admin-btn-primary:hover {
            transform: translateY(-2px);
            box-shadow:
                inset 0 2px 0 rgba(255, 255, 255, 0.35),
                0 16px 32px rgba(34, 197, 94, 0.38);
        }

        .admin-btn-dark {
            color: #ffffff;
            background: rgba(3, 10, 24, 0.65);
            border: 1px solid rgba(255, 255, 255, 0.14);
        }

        .admin-btn-dark:hover {
            transform: translateY(-2px);
            background: rgba(3, 10, 24, 0.85);
        }

        .clientes-card {
            background: rgba(15, 28, 51, 0.88);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 26px;
            padding: 28px;
            box-shadow: 0 24px 70px rgba(0, 0, 0, 0.35);
            backdrop-filter: blur(16px);
        }

        .clientes-card-header {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            align-items: center;
            padding-bottom: 20px;
            margin-bottom: 22px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.10);
        }

        .clientes-card-header h2 {
            color: #ffffff;
            font-size: 1.45rem;
        }

        .clientes-card-header p {
            color: #cfd8e6;
            font-weight: 700;
        }

        .search-box {
            margin-bottom: 22px;
            padding: 18px;
            border-radius: 20px;
            background: rgba(8, 21, 45, 0.72);
            border: 1px solid rgba(255, 255, 255, 0.10);
        }

        .search-form {
            display: grid;
            grid-template-columns: 1fr auto auto;
            gap: 12px;
            align-items: center;
        }

        .search-form input {
            width: 100%;
            min-height: 50px;
            padding: 13px 16px;
            border-radius: 999px;
            border: 1px solid rgba(255, 255, 255, 0.16);
            background: rgba(3, 10, 24, 0.75);
            color: #ffffff;
            font-size: 0.95rem;
            outline: none;
        }

        .search-form input::placeholder {
            color: #94a3b8;
        }

        .search-form input:focus {
            border-color: #22c55e;
            box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.13);
        }

        .table-wrap {
            width: 100%;
            max-height: 65vh;
            overflow-y: auto;
            overflow-x: auto;
            border-radius: 18px;
        }

        table {
            width: 100%;
            min-width: 950px;
            border-collapse: collapse;
            overflow: hidden;
            background: rgba(8, 21, 45, 0.72);
        }

        thead {
            background: rgba(34, 197, 94, 0.14);
        }

        th {
            padding: 16px 14px;
            color: #ffffff;
            font-size: 0.88rem;
            text-align: left;
            white-space: nowrap;
            border-bottom: 1px solid rgba(255, 255, 255, 0.12);
        }

        td {
            padding: 15px 14px;
            color: #d8e2f0;
            font-size: 0.9rem;
            vertical-align: middle;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        tbody tr:hover {
            background: rgba(255, 255, 255, 0.055);
        }

        td strong {
            color: #ffffff;
        }

        .cliente-link {
            color: #ffffff;
            font-weight: 800;
            text-decoration: none;
            transition: color 0.25s ease;
        }

        .cliente-link:hover {
            color: #86efac;
        }

        .acciones {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .accion-ver {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 13px;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 800;
            color: #ffffff;
            text-decoration: none;
            background: rgba(59, 130, 246, 0.18);
            border: 1px solid rgba(59, 130, 246, 0.36);
            transition: all 0.25s ease;
        }

        .accion-ver:hover {
            transform: translateY(-2px);
            filter: brightness(1.15);
        }

        .empty-box {
            text-align: center;
            padding: 45px 20px;
            background: rgba(8, 21, 45, 0.72);
            border-radius: 20px;
            border: 1px dashed rgba(255, 255, 255, 0.16);
        }

        .empty-box h3 {
            color: #ffffff;
            font-size: 1.4rem;
            margin-bottom: 10px;
        }

        .empty-box p {
            color: #cfd8e6;
            margin-bottom: 22px;
        }

        @media (max-width: 900px) {
            body {
                padding: 35px 5%;
            }

            .clientes-header,
            .clientes-card-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .clientes-actions {
                justify-content: flex-start;
            }

            .search-form {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 600px) {
            .clientes-card {
                padding: 20px;
                border-radius: 22px;
            }

            .clientes-actions {
                flex-direction: column;
                width: 100%;
            }

            .admin-btn {
                width: 100%;
            }
        }
    </style>
</head>

<body>

    <div class="clientes-container">

        <div class="clientes-header">
            <div>
                <span class="clientes-badge">Gestión administrativa</span>
                <h1>Módulo de Clientes</h1>
                <p>
                    Consulta, registra y administra la información de los clientes de Benedetti Rent a Car
                    desde un panel limpio, moderno y organizado.
                </p>
            </div>

            <div class="clientes-actions">
                <a href="crear.php" class="admin-btn admin-btn-primary">+ Nuevo Cliente</a>
                <a href="../admin/dashboard.php" class="admin-btn admin-btn-dark">← Volver al Dashboard</a>
            </div>
        </div>

        <div class="clientes-card">

            <div class="clientes-card-header">
                <div>
                    <h2>Listado de clientes</h2>
                </div>

                <p>
                    Total encontrados:
                    <?php echo $resultado ? $resultado->num_rows : 0; ?>
                </p>
            </div>

            <div class="search-box">
                <form method="GET" action="index.php" class="search-form">
                    <input 
                        type="text" 
                        name="buscar" 
                        placeholder="Buscar por nombre, documento, correo o teléfono..."
                        value="<?php echo htmlspecialchars($busqueda); ?>"
                    >

                    <button type="submit" class="admin-btn admin-btn-primary">
                        Buscar
                    </button>

                    <a href="index.php" class="admin-btn admin-btn-dark">
                        Limpiar
                    </a>
                </form>
            </div>

            <?php if ($resultado && $resultado->num_rows > 0) { ?>

                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Documento</th>
                                <th>Cliente</th>
                                <th>Teléfono</th>
                                <th>Correo</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php while ($cliente = $resultado->fetch_assoc()) { ?>

                                <tr>
                                    <td>
                                        <strong>#<?php echo htmlspecialchars($cliente['id_cliente']); ?></strong>
                                    </td>

                                    <td>
                                        <?php echo htmlspecialchars($cliente['numero_documento']); ?>
                                    </td>

                                    <td>
                                        <a class="cliente-link" href="ver.php?id=<?php echo $cliente['id_cliente']; ?>">
                                            <?php echo htmlspecialchars($cliente['nombre'] . ' ' . $cliente['apellido']); ?>
                                        </a>
                                    </td>

                                    <td>
                                        <?php echo htmlspecialchars($cliente['telefono']); ?>
                                    </td>

                                    <td>
                                        <?php echo htmlspecialchars($cliente['correo']); ?>
                                    </td>

                                    <td>
                                        <div class="acciones">
                                            <a class="accion-ver" href="ver.php?id=<?php echo $cliente['id_cliente']; ?>">
                                                Ver
                                            </a>
                                        </div>
                                    </td>
                                </tr>

                            <?php } ?>
                        </tbody>
                    </table>
                </div>

            <?php } else { ?>

                <div class="empty-box">
                    <h3>No se encontraron clientes</h3>

                    <?php if ($busqueda !== "") { ?>
                        <p>No hay resultados para: <strong><?php echo htmlspecialchars($busqueda); ?></strong></p>
                        <a href="index.php" class="admin-btn admin-btn-dark">Ver todos los clientes</a>
                    <?php } else { ?>
                        <p>Cuando registres clientes, aparecerán en este listado.</p>
                        <a href="crear.php" class="admin-btn admin-btn-primary">+ Crear primer cliente</a>
                    <?php } ?>
                </div>

            <?php } ?>

        </div>

    </div>

</body>
</html>