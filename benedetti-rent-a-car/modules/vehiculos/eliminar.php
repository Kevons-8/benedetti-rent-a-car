<?php
require_once "../../includes/auth.php";
require_once "../../config/database.php";

$mensaje = "";
$tipo_mensaje = "";

if (!isset($_GET["id"])) {
    die("ID no especificado.");
}

$id = $_GET["id"];

try {
    $sql = "SELECT * FROM vehiculos WHERE id_vehiculo = :id";
    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $vehiculo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$vehiculo) {
        die("Vehículo no encontrado.");
    }
} catch (PDOException $e) {
    die("Error al obtener vehículo: " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        if (!empty($vehiculo["imagen"])) {
            $ruta_imagen = "../../assets/img/vehiculos/" . $vehiculo["imagen"];

            if (file_exists($ruta_imagen)) {
                unlink($ruta_imagen);
            }
        }

        $sql = "DELETE FROM vehiculos WHERE id_vehiculo = :id";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        header("Location: listar.php");
        exit();

    } catch (PDOException $e) {
        $mensaje = "Error al eliminar vehículo: " . $e->getMessage();
        $tipo_mensaje = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Eliminar Vehículo | Benedetti Rent a Car</title>
    <link rel="stylesheet" href="/benedetti-rent-a-car/assets/css/style.css">
</head>

<body class="admin-vehicle-page">

<main class="admin-vehicle-bg">
    <div class="admin-vehicle-overlay"></div>

    <section class="admin-vehicle-container">

        <div class="admin-vehicle-header">
            <div>
                <span class="admin-vehicle-badge">Zona de eliminación</span>
                <h1>Eliminar vehículo</h1>
                <p>Confirma si deseas retirar este vehículo del inventario administrativo.</p>
            </div>

            <div class="admin-vehicle-actions">
                <a href="listar.php" class="admin-btn admin-btn-secondary">Volver a vehículos</a>
                <a href="../../admin/dashboard.php" class="admin-btn admin-btn-dark">Dashboard</a>
            </div>
        </div>

        <?php if (!empty($mensaje)) : ?>
            <div class="admin-alert admin-alert-<?php echo htmlspecialchars($tipo_mensaje); ?>">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php endif; ?>

        <div class="admin-delete-card">
            <div class="admin-delete-preview">
                <?php if (!empty($vehiculo["imagen"])): ?>
                    <img 
                        src="/benedetti-rent-a-car/assets/img/vehiculos/<?php echo htmlspecialchars($vehiculo["imagen"]); ?>" 
                        alt="<?php echo htmlspecialchars($vehiculo["marca"] . " " . $vehiculo["modelo"]); ?>"
                    >
                <?php else: ?>
                    <div class="admin-no-image">Sin imagen</div>
                <?php endif; ?>
            </div>

            <div class="admin-delete-info">
                <span class="admin-delete-warning">Acción irreversible</span>

                <h2>
                    <?php echo htmlspecialchars($vehiculo["marca"] . " " . $vehiculo["modelo"]); ?>
                </h2>

                <p>
                    Estás a punto de eliminar este vehículo del sistema. También se eliminará su imagen asociada del servidor si existe.
                </p>

                <div class="admin-delete-details">
                    <p><strong>Código:</strong> <?php echo htmlspecialchars($vehiculo["codigo_vehiculo"]); ?></p>
                    <p><strong>Placa:</strong> <?php echo htmlspecialchars($vehiculo["placa"]); ?></p>
                    <p><strong>Estado:</strong> <?php echo htmlspecialchars(ucfirst($vehiculo["estado"])); ?></p>
                    <p><strong>Precio día:</strong> $<?php echo number_format((float)$vehiculo["precio_dia"], 0, ',', '.'); ?></p>
                </div>

                <form method="POST" class="admin-delete-actions">
                    <button type="submit" class="admin-btn admin-btn-danger">
                        Sí, eliminar vehículo
                    </button>

                    <a href="listar.php" class="admin-btn admin-btn-secondary">
                        Cancelar
                    </a>
                </form>
            </div>
        </div>

    </section>
</main>

</body>
</html>