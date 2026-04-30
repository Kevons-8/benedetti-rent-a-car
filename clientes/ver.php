<?php
include("../config/conexion.php");

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id_cliente = intval($_GET['id']);

$sql = "SELECT * FROM clientes WHERE id_cliente = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_cliente);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    header("Location: index.php");
    exit();
}

$cliente = $resultado->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Ficha del Cliente</title>

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
                linear-gradient(135deg, rgba(3, 13, 31, 0.84), rgba(6, 32, 71, 0.78)),
                url("/benedetti-rent-a-car/assets/img/fondo_vehiculos.png") center center / cover no-repeat fixed;
            color: #ffffff;
            padding: 45px 7%;
        }

        .cliente-container {
            max-width: 1180px;
            margin: 0 auto;
        }

        .cliente-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 24px;
            margin-bottom: 28px;
        }

        .cliente-badge {
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

        .cliente-header p {
            color: #d8e2f0;
            max-width: 680px;
            line-height: 1.6;
        }

        .cliente-actions {
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

        .admin-btn-danger {
            color: #ffffff;
            background: linear-gradient(180deg, #ff5f5f 0%, #ef4444 45%, #b91c1c 100%);
            box-shadow:
                inset 0 2px 0 rgba(255, 255, 255, 0.28),
                0 12px 24px rgba(239, 68, 68, 0.28);
        }

        .admin-btn-danger:hover {
            transform: translateY(-2px);
            box-shadow:
                inset 0 2px 0 rgba(255, 255, 255, 0.34),
                0 16px 32px rgba(239, 68, 68, 0.38);
        }

        .cliente-card {
            display: grid;
            grid-template-columns: 340px 1fr;
            gap: 28px;
            align-items: stretch;
            background: rgba(15, 28, 51, 0.88);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 26px;
            padding: 30px;
            box-shadow: 0 24px 70px rgba(0, 0, 0, 0.35);
            backdrop-filter: blur(16px);
        }

        .cliente-profile {
            background: rgba(8, 21, 45, 0.78);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 22px;
            padding: 28px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .cliente-avatar {
            width: 130px;
            height: 130px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 18px;
            background: linear-gradient(180deg, #6eff1f 0%, #38d600 45%, #19a500 100%);
            color: #ffffff;
            font-size: 3.2rem;
            font-weight: 900;
            box-shadow:
                inset 0 2px 0 rgba(255, 255, 255, 0.32),
                0 16px 34px rgba(34, 197, 94, 0.28);
        }

        .cliente-profile h2 {
            color: #ffffff;
            font-size: 1.7rem;
            text-align: center;
            margin-bottom: 8px;
        }

        .cliente-profile p {
            color: #cfd8e6;
            text-align: center;
            line-height: 1.5;
        }

        .cliente-id {
            margin-top: 16px;
            display: inline-flex;
            padding: 8px 14px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.12);
            color: #d8e2f0;
            font-size: 0.85rem;
            font-weight: 800;
        }

        .cliente-info {
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .cliente-info h2 {
            color: #ffffff;
            font-size: 1.8rem;
            margin-bottom: 18px;
        }

        .cliente-detail-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 14px;
            margin-bottom: 26px;
        }

        .cliente-detail-grid p {
            margin: 0;
            padding: 15px 16px;
            border-radius: 16px;
            color: #d8e2f0;
            background: rgba(8, 21, 45, 0.75);
            border: 1px solid rgba(255, 255, 255, 0.10);
            word-break: break-word;
        }

        .cliente-detail-grid strong {
            display: block;
            color: #ffffff;
            margin-bottom: 6px;
            font-size: 0.9rem;
        }

        .licencia-activa {
            color: #86efac;
            font-weight: 800;
        }

        .licencia-vacia {
            color: #fca5a5;
            font-weight: 800;
        }

        .cliente-footer-actions {
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
            padding-top: 22px;
            border-top: 1px solid rgba(255, 255, 255, 0.10);
        }

        @media (max-width: 900px) {
            body {
                padding: 35px 5%;
            }

            .cliente-header {
                flex-direction: column;
            }

            .cliente-actions {
                justify-content: flex-start;
            }

            .cliente-card {
                grid-template-columns: 1fr;
            }

            .cliente-detail-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 600px) {
            .cliente-card {
                padding: 22px;
                border-radius: 22px;
            }

            .cliente-actions,
            .cliente-footer-actions {
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

    <div class="cliente-container">

        <div class="cliente-header">
            <div>
                <span class="cliente-badge">Ficha del cliente</span>
                <h1>Detalle del Cliente</h1>
                <p>
                    Consulta la información completa del cliente y administra sus datos desde una vista más organizada.
                </p>
            </div>

            <div class="cliente-actions">
                <a href="index.php" class="admin-btn admin-btn-dark">← Volver al listado</a>
                <a href="../admin/dashboard.php" class="admin-btn admin-btn-dark">Dashboard</a>
            </div>
        </div>

        <div class="cliente-card">

            <div class="cliente-profile">
                <div class="cliente-avatar">
                    <?php echo strtoupper(substr($cliente['nombre'], 0, 1)); ?>
                </div>

                <h2>
                    <?php echo htmlspecialchars($cliente['nombre'] . ' ' . $cliente['apellido']); ?>
                </h2>

                <p>
                    Cliente registrado en Benedetti Rent a Car.
                </p>

                <span class="cliente-id">
                    ID Cliente: #<?php echo htmlspecialchars($cliente['id_cliente']); ?>
                </span>
            </div>

            <div class="cliente-info">
                <h2>Información registrada</h2>

                <div class="cliente-detail-grid">

                    <p>
                        <strong>Tipo de documento</strong>
                        <?php echo htmlspecialchars($cliente['tipo_documento']); ?>
                    </p>

                    <p>
                        <strong>Número de documento</strong>
                        <?php echo htmlspecialchars($cliente['numero_documento']); ?>
                    </p>

                    <p>
                        <strong>Nombre</strong>
                        <?php echo htmlspecialchars($cliente['nombre']); ?>
                    </p>

                    <p>
                        <strong>Apellido</strong>
                        <?php echo htmlspecialchars($cliente['apellido']); ?>
                    </p>

                    <p>
                        <strong>Teléfono</strong>
                        <?php echo htmlspecialchars($cliente['telefono']); ?>
                    </p>

                    <p>
                        <strong>Correo electrónico</strong>
                        <?php echo htmlspecialchars($cliente['correo']); ?>
                    </p>

                    <p>
                        <strong>Licencia de conducción</strong>
                        <?php if (!empty($cliente['licencia_conduccion'])) { ?>
                            <span class="licencia-activa">
                                <?php echo htmlspecialchars($cliente['licencia_conduccion']); ?>
                            </span>
                        <?php } else { ?>
                            <span class="licencia-vacia">Sin licencia registrada</span>
                        <?php } ?>
                    </p>

                </div>

                <div class="cliente-footer-actions">
                    <a href="editar.php?id=<?php echo $cliente['id_cliente']; ?>" class="admin-btn admin-btn-primary">
                        Editar cliente
                    </a>

                    <a href="eliminar.php?id=<?php echo $cliente['id_cliente']; ?>" 
   class="admin-btn admin-btn-danger">
   Eliminar cliente
</a>

                    <a href="index.php" class="admin-btn admin-btn-dark">
                        Volver
                    </a>
                </div>
            </div>

        </div>

    </div>

</body>
</html>