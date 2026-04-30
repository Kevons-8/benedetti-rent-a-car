<?php
require_once "../../includes/auth.php";
require_once "../../config/database.php";

$mensaje = "";
$tipo_mensaje = "";

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
    $imagen = null;

    if ($precio_especial_3_dias === "") {
        $precio_especial_3_dias = null;
    }

    if (
        !empty($codigo_vehiculo) &&
        !empty($marca) &&
        !empty($modelo) &&
        !empty($anio) &&
        !empty($placa) &&
        !empty($precio_dia) &&
        !empty($estado)
    ) {
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

                $imagen = "vehiculo_" . time() . "_" . rand(1000, 9999) . "." . $extension;
                $ruta_destino = $carpeta_destino . $imagen;

                if (!move_uploaded_file($_FILES["imagen"]["tmp_name"], $ruta_destino)) {
                    throw new Exception("No se pudo guardar la imagen del vehículo.");
                }
            }

            $sql = "INSERT INTO vehiculos 
                    (codigo_vehiculo, marca, modelo, color, capacidad, transmision, categoria, anio, placa, precio_dia, precio_especial_3_dias, estado, imagen)
                    VALUES
                    (:codigo_vehiculo, :marca, :modelo, :color, :capacidad, :transmision, :categoria, :anio, :placa, :precio_dia, :precio_especial_3_dias, :estado, :imagen)";

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

            $stmt->execute();

            $mensaje = "Vehículo registrado correctamente.";
            $tipo_mensaje = "success";
        } catch (Exception $e) {
            $mensaje = "Error al registrar vehículo: " . $e->getMessage();
            $tipo_mensaje = "error";
        }
    } else {
        $mensaje = "Los campos Código, Marca, Modelo, Año, Placa, Precio por día y Estado son obligatorios.";
        $tipo_mensaje = "warning";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Vehículo | Benedetti Rent a Car</title>
    <link rel="stylesheet" href="/benedetti-rent-a-car/assets/css/style.css">
</head>
<body class="admin-vehicle-page">

    <main class="admin-vehicle-bg">
        <div class="admin-vehicle-overlay"></div>

        <section class="admin-vehicle-container">

            <div class="admin-vehicle-header">
                <div>
                    <span class="admin-vehicle-badge">Panel administrativo</span>
                    <h1>Registrar nuevo vehículo</h1>
                    <p>
                        Agrega una nueva unidad al inventario operativo de Benedetti Rent a Car.
                    </p>
                </div>

                <div class="admin-vehicle-actions">
                    <a href="../../modules/vehiculos/listar.php" class="admin-btn admin-btn-secondary">
                        Ver vehículos
                    </a>
                    <a href="../../admin/dashboard.php" class="admin-btn admin-btn-dark">
                        Dashboard
                    </a>
                </div>
            </div>

            <?php if (!empty($mensaje)) : ?>
                <div class="admin-alert admin-alert-<?php echo htmlspecialchars($tipo_mensaje); ?>">
                    <?php echo htmlspecialchars($mensaje); ?>
                </div>
            <?php endif; ?>

            <div class="admin-form-card">
                <div class="admin-form-card-header">
                    <h2>Información del vehículo</h2>
                    <p>Completa los datos principales para registrar el vehículo en el sistema.</p>
                </div>

                <form action="" method="POST" enctype="multipart/form-data" class="admin-vehicle-form">

                    <div class="admin-form-grid">
                        <div class="admin-form-group">
                            <label for="codigo_vehiculo">Código del vehículo *</label>
                            <input type="text" name="codigo_vehiculo" id="codigo_vehiculo" placeholder="Ej: BRC-001" required>
                        </div>

                        <div class="admin-form-group">
                            <label for="marca">Marca *</label>
                            <input type="text" name="marca" id="marca" placeholder="Ej: Toyota" required>
                        </div>

                        <div class="admin-form-group">
                            <label for="modelo">Modelo *</label>
                            <input type="text" name="modelo" id="modelo" placeholder="Ej: Corolla" required>
                        </div>

                        <div class="admin-form-group">
                            <label for="color">Color</label>
                            <input type="text" name="color" id="color" placeholder="Ej: Blanco">
                        </div>

                        <div class="admin-form-group">
                            <label for="capacidad">Capacidad</label>
                            <input type="number" name="capacidad" id="capacidad" placeholder="Ej: 5">
                        </div>

                        <div class="admin-form-group">
                            <label for="transmision">Transmisión</label>
                            <select name="transmision" id="transmision">
                                <option value="">Seleccione</option>
                                <option value="manual">Manual</option>
                                <option value="automatica">Automática</option>
                            </select>
                        </div>

                        <div class="admin-form-group">
                            <label for="categoria">Categoría</label>
                            <select name="categoria" id="categoria">
                                <option value="">Seleccione</option>
                                <option value="sedan">Sedán</option>
                                <option value="hatchback">Hatchback</option>
                                <option value="suv">SUV</option>
                                <option value="pickup">Pickup</option>
                                <option value="coupe">Coupé</option>
                                <option value="van">Van</option>
                                <option value="camioneta">Camioneta</option>
                            </select>
                        </div>

                        <div class="admin-form-group">
                            <label for="anio">Año *</label>
                            <input type="number" name="anio" id="anio" placeholder="Ej: 2024" required>
                        </div>

                        <div class="admin-form-group">
                            <label for="placa">Placa *</label>
                            <input type="text" name="placa" id="placa" placeholder="Ej: ABC123" required>
                        </div>

                        <div class="admin-form-group">
                            <label for="estado">Estado *</label>
                            <select name="estado" id="estado" required>
                                <option value="disponible">Disponible</option>
                                <option value="reservado">Reservado</option>
                                <option value="mantenimiento">Mantenimiento</option>
                                <option value="inactivo">Inactivo</option>
                            </select>
                        </div>

                        <div class="admin-form-group">
                            <label for="precio_dia">Precio por día *</label>
                            <input type="number" step="0.01" name="precio_dia" id="precio_dia" placeholder="Ej: 180000" required>
                        </div>

                        <div class="admin-form-group">
                            <label for="precio_especial_3_dias">Precio especial desde el día 3</label>
                            <input type="number" step="0.01" name="precio_especial_3_dias" id="precio_especial_3_dias" placeholder="Ej: 150000">
                        </div>

                        <div class="admin-form-group">
                            <label for="imagen">Foto del vehículo</label>
                            <input type="file" name="imagen" id="imagen" accept="image/jpeg,image/png,image/webp">
                        </div>
                    </div>

                    <div class="admin-form-footer">
                        <button type="submit" class="admin-btn admin-btn-primary">
                            Guardar vehículo
                        </button>
                        <a href="../../modules/vehiculos/listar.php" class="admin-btn admin-btn-secondary">
                            Cancelar
                        </a>
                    </div>

                </form>
            </div>

        </section>
    </main>

</body>
</html>