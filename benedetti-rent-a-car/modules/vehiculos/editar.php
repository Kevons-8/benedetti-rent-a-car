<?php
require_once "../../includes/auth.php";
require_once "../../config/database.php";

$mensaje = "";
$tipo_mensaje = "";

/* ==========================
OBTENER EL ID DEL VEHÍCULO
========================== */

if (!isset($_GET["id"])) {
    die("ID de vehículo no especificado.");
}

$id = $_GET["id"];

/* ==========================
BUSCAR EL VEHÍCULO
========================== */

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

/* ==========================
PROCESAR ACTUALIZACIÓN
========================== */

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $codigo_vehiculo = trim($_POST["codigo_vehiculo"]);
    $marca = trim($_POST["marca"]);
    $modelo = trim($_POST["modelo"]);
    $color = trim($_POST["color"]);
    $capacidad = trim($_POST["capacidad"]);
    $transmision = trim($_POST["transmision"]);
    $categoria = trim($_POST["categoria"]);
    $anio = trim($_POST["anio"]);
    $placa = trim($_POST["placa"]);
    $precio_dia = trim($_POST["precio_dia"]);
    $precio_especial_3_dias = trim($_POST["precio_especial_3_dias"]);
    $estado = trim($_POST["estado"]);
    $imagen = $vehiculo["imagen"];

    if ($precio_especial_3_dias === "") {
        $precio_especial_3_dias = null;
    }

    try {
        if (isset($_FILES["imagen"]) && $_FILES["imagen"]["error"] === UPLOAD_ERR_OK) {
            $carpeta_destino = "../../assets/img/vehiculos/";

            if (!is_dir($carpeta_destino)) {
                mkdir($carpeta_destino, 0777, true);
            }

            $nombre_original = $_FILES["imagen"]["name"];
            $extension = strtolower(pathinfo($nombre_original, PATHINFO_EXTENSION));
            $extensiones_permitidas = ["jpg", "jpeg", "png", "webp"];

            if (!in_array($extension, $extensiones_permitidas)) {
                throw new Exception("Formato de imagen no permitido. Usa JPG, JPEG, PNG o WEBP.");
            }

            $nueva_imagen = "vehiculo_" . time() . "_" . rand(1000, 9999) . "." . $extension;
            $ruta_destino = $carpeta_destino . $nueva_imagen;

            if (!move_uploaded_file($_FILES["imagen"]["tmp_name"], $ruta_destino)) {
                throw new Exception("No se pudo guardar la nueva imagen.");
            }

            if (!empty($imagen)) {
                $ruta_anterior = $carpeta_destino . $imagen;

                if (file_exists($ruta_anterior)) {
                    unlink($ruta_anterior);
                }
            }

            $imagen = $nueva_imagen;
        }

        $sql = "UPDATE vehiculos SET
                codigo_vehiculo = :codigo_vehiculo,
                marca = :marca,
                modelo = :modelo,
                color = :color,
                capacidad = :capacidad,
                transmision = :transmision,
                categoria = :categoria,
                anio = :anio,
                placa = :placa,
                precio_dia = :precio_dia,
                precio_especial_3_dias = :precio_especial_3_dias,
                estado = :estado,
                imagen = :imagen
                WHERE id_vehiculo = :id";

        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':codigo_vehiculo', $codigo_vehiculo);
        $stmt->bindParam(':marca', $marca);
        $stmt->bindParam(':modelo', $modelo);
        $stmt->bindParam(':color', $color);
        $stmt->bindParam(':capacidad', $capacidad);
        $stmt->bindParam(':transmision', $transmision);
        $stmt->bindParam(':categoria', $categoria);
        $stmt->bindParam(':anio', $anio);
        $stmt->bindParam(':placa', $placa);
        $stmt->bindParam(':precio_dia', $precio_dia);
        $stmt->bindParam(':precio_especial_3_dias', $precio_especial_3_dias);
        $stmt->bindParam(':estado', $estado);
        $stmt->bindParam(':imagen', $imagen);
        $stmt->bindParam(':id', $id);

        $stmt->execute();

        $mensaje = "Vehículo actualizado correctamente.";
        $tipo_mensaje = "success";

        $sql = "SELECT * FROM vehiculos WHERE id_vehiculo = :id";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $vehiculo = $stmt->fetch(PDO::FETCH_ASSOC);

    } catch (Exception $e) {
        $mensaje = "Error al actualizar: " . $e->getMessage();
        $tipo_mensaje = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Vehículo | Benedetti Rent a Car</title>
    <link rel="stylesheet" href="/benedetti-rent-a-car/assets/css/style.css">
</head>

<body class="admin-vehicle-page">

<main class="admin-vehicle-bg">
    <div class="admin-vehicle-overlay"></div>

    <section class="admin-vehicle-container">

        <div class="admin-vehicle-header">
            <div>
                <span class="admin-vehicle-badge">Edición de inventario</span>
                <h1>Editar vehículo</h1>
                <p>Actualiza la información, estado, precios e imagen del vehículo seleccionado.</p>
            </div>

            <div class="admin-vehicle-actions">
                <a href="listar.php" class="admin-btn admin-btn-secondary">Ver vehículos</a>
                <a href="../../admin/dashboard.php" class="admin-btn admin-btn-dark">Dashboard</a>
            </div>
        </div>

        <?php if (!empty($mensaje)) : ?>
            <div class="admin-alert admin-alert-<?php echo htmlspecialchars($tipo_mensaje); ?>">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php endif; ?>

        <div class="admin-form-card">
            <div class="admin-form-card-header">
                <h2><?php echo htmlspecialchars($vehiculo["marca"] . " " . $vehiculo["modelo"]); ?></h2>
                <p>Modifica los datos necesarios y guarda los cambios.</p>
            </div>

            <form method="POST" enctype="multipart/form-data" class="admin-vehicle-form">
                <div class="admin-form-grid">

                    <div class="admin-form-group">
                        <label>Código *</label>
                        <input type="text" name="codigo_vehiculo" value="<?php echo htmlspecialchars($vehiculo["codigo_vehiculo"]); ?>" required>
                    </div>

                    <div class="admin-form-group">
                        <label>Marca *</label>
                        <input type="text" name="marca" value="<?php echo htmlspecialchars($vehiculo["marca"]); ?>" required>
                    </div>

                    <div class="admin-form-group">
                        <label>Modelo *</label>
                        <input type="text" name="modelo" value="<?php echo htmlspecialchars($vehiculo["modelo"]); ?>" required>
                    </div>

                    <div class="admin-form-group">
                        <label>Color</label>
                        <input type="text" name="color" value="<?php echo htmlspecialchars($vehiculo["color"]); ?>">
                    </div>

                    <div class="admin-form-group">
                        <label>Capacidad</label>
                        <input type="number" name="capacidad" value="<?php echo htmlspecialchars($vehiculo["capacidad"]); ?>">
                    </div>

                    <div class="admin-form-group">
                        <label>Transmisión</label>
                        <select name="transmision">
                            <option value="">Seleccione</option>
                            <option value="manual" <?php echo ($vehiculo["transmision"] == "manual") ? "selected" : ""; ?>>Manual</option>
                            <option value="automatica" <?php echo ($vehiculo["transmision"] == "automatica") ? "selected" : ""; ?>>Automática</option>
                        </select>
                    </div>

                    <div class="admin-form-group">
                        <label>Categoría</label>
                        <select name="categoria">
                            <option value="">Seleccione</option>
                            <option value="sedan" <?php echo ($vehiculo["categoria"] == "sedan") ? "selected" : ""; ?>>Sedán</option>
                            <option value="hatchback" <?php echo ($vehiculo["categoria"] == "hatchback") ? "selected" : ""; ?>>Hatchback</option>
                            <option value="suv" <?php echo ($vehiculo["categoria"] == "suv") ? "selected" : ""; ?>>SUV</option>
                            <option value="pickup" <?php echo ($vehiculo["categoria"] == "pickup") ? "selected" : ""; ?>>Pickup</option>
                            <option value="coupe" <?php echo ($vehiculo["categoria"] == "coupe") ? "selected" : ""; ?>>Coupé</option>
                            <option value="van" <?php echo ($vehiculo["categoria"] == "van") ? "selected" : ""; ?>>Van</option>
                            <option value="camioneta" <?php echo ($vehiculo["categoria"] == "camioneta") ? "selected" : ""; ?>>Camioneta</option>
                        </select>
                    </div>

                    <div class="admin-form-group">
                        <label>Año *</label>
                        <input type="number" name="anio" value="<?php echo htmlspecialchars($vehiculo["anio"]); ?>" required>
                    </div>

                    <div class="admin-form-group">
                        <label>Placa *</label>
                        <input type="text" name="placa" value="<?php echo htmlspecialchars($vehiculo["placa"]); ?>" required>
                    </div>

                    <div class="admin-form-group">
                        <label>Estado *</label>
                        <select name="estado" required>
                            <option value="disponible" <?php echo ($vehiculo["estado"] == "disponible") ? "selected" : ""; ?>>Disponible</option>
                            <option value="reservado" <?php echo ($vehiculo["estado"] == "reservado") ? "selected" : ""; ?>>Reservado</option>
                            <option value="mantenimiento" <?php echo ($vehiculo["estado"] == "mantenimiento") ? "selected" : ""; ?>>Mantenimiento</option>
                            <option value="inactivo" <?php echo ($vehiculo["estado"] == "inactivo") ? "selected" : ""; ?>>Inactivo</option>
                        </select>
                    </div>

                    <div class="admin-form-group">
                        <label>Precio por día *</label>
                        <input type="number" step="0.01" name="precio_dia" value="<?php echo htmlspecialchars($vehiculo["precio_dia"]); ?>" required>
                    </div>

                    <div class="admin-form-group">
                        <label>Precio especial desde el día 3</label>
                        <input type="number" step="0.01" name="precio_especial_3_dias" value="<?php echo htmlspecialchars($vehiculo["precio_especial_3_dias"] ?? ""); ?>">
                    </div>

                    <div class="admin-form-group">
                        <label>Imagen actual</label>

                        <?php if (!empty($vehiculo["imagen"])): ?>
                            <img
                                src="/benedetti-rent-a-car/assets/img/vehiculos/<?php echo htmlspecialchars($vehiculo["imagen"]); ?>"
                                alt="Imagen actual"
                                class="admin-edit-preview"
                            >
                        <?php else: ?>
                            <div class="admin-no-image">Sin imagen</div>
                        <?php endif; ?>
                    </div>

                    <div class="admin-form-group">
                        <label>Cambiar imagen</label>
                        <input type="file" name="imagen" accept="image/jpeg,image/png,image/webp">
                    </div>

                </div>

                <div class="admin-form-footer">
                    <button type="submit" class="admin-btn admin-btn-primary">Actualizar vehículo</button>
                    <a href="listar.php" class="admin-btn admin-btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>

    </section>
</main>

</body>
</html>

/* =========================
   ADMIN VEHÍCULOS - EDITAR
========================= */

.admin-edit-preview {
    width: 180px;
    height: 115px;
    object-fit: cover;
    border-radius: 18px;
    border: 1px solid rgba(255, 255, 255, 0.16);
    box-shadow: 0 12px 28px rgba(0, 0, 0, 0.28);
    background: rgba(8, 21, 45, 0.75);
}