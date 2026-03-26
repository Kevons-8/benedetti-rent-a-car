<?php
require_once "../../includes/auth.php";
require_once "../../config/database.php";

$mensaje = "";

/* ==========================
OBTENER EL ID DEL VEHÍCULO
========================== */

if (!isset($_GET["id"])) {
    die("ID de vehículo no especificado.");
}

$id = $_GET["id"];

/* ==========================
BUSCAR EL VEHÍCULO EN MYSQL
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
PROCESAR EL FORMULARIO
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

    if ($precio_especial_3_dias === "") {
        $precio_especial_3_dias = null;
    }

    try {
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
                estado = :estado
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
        $stmt->bindParam(':id', $id);

        $stmt->execute();

        $mensaje = "Vehículo actualizado correctamente.";

        $sql = "SELECT * FROM vehiculos WHERE id_vehiculo = :id";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $vehiculo = $stmt->fetch(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        $mensaje = "Error al actualizar: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Editar Vehículo</title>
</head>

<body>

<h1>Editar Vehículo</h1>

<?php if ($mensaje != "") { ?>
<p><?php echo htmlspecialchars($mensaje); ?></p>
<?php } ?>

<form method="POST">

<label>Código:</label><br>
<input type="text" name="codigo_vehiculo" value="<?php echo htmlspecialchars($vehiculo["codigo_vehiculo"]); ?>" required><br><br>

<label>Marca:</label><br>
<input type="text" name="marca" value="<?php echo htmlspecialchars($vehiculo["marca"]); ?>" required><br><br>

<label>Modelo:</label><br>
<input type="text" name="modelo" value="<?php echo htmlspecialchars($vehiculo["modelo"]); ?>" required><br><br>

<label>Color:</label><br>
<input type="text" name="color" value="<?php echo htmlspecialchars($vehiculo["color"]); ?>"><br><br>

<label>Capacidad:</label><br>
<input type="number" name="capacidad" value="<?php echo htmlspecialchars($vehiculo["capacidad"]); ?>"><br><br>

<label>Transmisión:</label><br>
<select name="transmision">
    <option value="">Seleccione</option>
    <option value="manual" <?php echo ($vehiculo["transmision"] == "manual") ? "selected" : ""; ?>>Manual</option>
    <option value="automatica" <?php echo ($vehiculo["transmision"] == "automatica") ? "selected" : ""; ?>>Automática</option>
</select><br><br>

<label>Categoría:</label><br>
<select name="categoria">
    <option value="">Seleccione</option>
    <option value="sedan" <?php echo ($vehiculo["categoria"] == "sedan") ? "selected" : ""; ?>>Sedán</option>
    <option value="hatchback" <?php echo ($vehiculo["categoria"] == "hatchback") ? "selected" : ""; ?>>Hatchback</option>
    <option value="suv" <?php echo ($vehiculo["categoria"] == "suv") ? "selected" : ""; ?>>SUV</option>
    <option value="pickup" <?php echo ($vehiculo["categoria"] == "pickup") ? "selected" : ""; ?>>Pickup</option>
    <option value="coupe" <?php echo ($vehiculo["categoria"] == "coupe") ? "selected" : ""; ?>>Coupé</option>
    <option value="van" <?php echo ($vehiculo["categoria"] == "van") ? "selected" : ""; ?>>Van</option>
    <option value="camioneta" <?php echo ($vehiculo["categoria"] == "camioneta") ? "selected" : ""; ?>>Camioneta</option>
</select><br><br>

<label>Año:</label><br>
<input type="number" name="anio" value="<?php echo htmlspecialchars($vehiculo["anio"]); ?>" required><br><br>

<label>Placa:</label><br>
<input type="text" name="placa" value="<?php echo htmlspecialchars($vehiculo["placa"]); ?>" required><br><br>

<label>Precio por día:</label><br>
<input type="number" step="0.01" name="precio_dia" value="<?php echo htmlspecialchars($vehiculo["precio_dia"]); ?>" required><br><br>

<label>Precio especial desde el día 3 (COP):</label><br>
<input type="number" step="0.01" name="precio_especial_3_dias" value="<?php echo htmlspecialchars($vehiculo["precio_especial_3_dias"] ?? ""); ?>"><br><br>

<label>Estado:</label><br>
<select name="estado" required>
    <option value="disponible" <?php echo ($vehiculo["estado"] == "disponible") ? "selected" : ""; ?>>Disponible</option>
    <option value="reservado" <?php echo ($vehiculo["estado"] == "reservado") ? "selected" : ""; ?>>Reservado</option>
    <option value="mantenimiento" <?php echo ($vehiculo["estado"] == "mantenimiento") ? "selected" : ""; ?>>Mantenimiento</option>
    <option value="inactivo" <?php echo ($vehiculo["estado"] == "inactivo") ? "selected" : ""; ?>>Inactivo</option>
</select><br><br>

<button type="submit">Actualizar Vehículo</button>

</form>

<br>

<a href="listar.php">Volver a la lista</a><br><br>
<a href="../../admin/dashboard.php">Volver al Dashboard</a>

</body>
</html>