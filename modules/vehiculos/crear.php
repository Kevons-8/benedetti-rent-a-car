<?php
require_once "../../includes/auth.php";
require_once "../../config/database.php";

$mensaje = "";

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
            $sql = "INSERT INTO vehiculos 
                    (codigo_vehiculo, marca, modelo, color, capacidad, transmision, categoria, anio, placa, precio_dia, precio_especial_3_dias, estado)
                    VALUES
                    (:codigo_vehiculo, :marca, :modelo, :color, :capacidad, :transmision, :categoria, :anio, :placa, :precio_dia, :precio_especial_3_dias, :estado)";

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

            $stmt->execute();

            $mensaje = "Vehículo registrado correctamente.";
        } catch (PDOException $e) {
            $mensaje = "Error al registrar vehículo: " . $e->getMessage();
        }
    } else {
        $mensaje = "Los campos Código, Marca, Modelo, Año, Placa, Precio por día y Estado son obligatorios.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Vehículo</title>
</head>
<body>
    <h1>Registrar Vehículo</h1>

    <?php if (!empty($mensaje)) : ?>
        <p><?php echo htmlspecialchars($mensaje); ?></p>
    <?php endif; ?>

    <form action="" method="POST">
        <label for="codigo_vehiculo">Código del vehículo:</label><br>
        <input type="text" name="codigo_vehiculo" id="codigo_vehiculo" required><br><br>

        <label for="marca">Marca:</label><br>
        <input type="text" name="marca" id="marca" required><br><br>

        <label for="modelo">Modelo:</label><br>
        <input type="text" name="modelo" id="modelo" required><br><br>

        <label for="color">Color:</label><br>
        <input type="text" name="color" id="color"><br><br>

        <label for="capacidad">Capacidad:</label><br>
        <input type="number" name="capacidad" id="capacidad"><br><br>

        <label for="transmision">Transmisión:</label><br>
        <select name="transmision" id="transmision">
            <option value="">Seleccione</option>
            <option value="manual">Manual</option>
            <option value="automatica">Automática</option>
        </select><br><br>

        <label for="categoria">Categoría:</label><br>
        <select name="categoria" id="categoria">
            <option value="">Seleccione</option>
            <option value="sedan">Sedán</option>
            <option value="hatchback">Hatchback</option>
            <option value="suv">SUV</option>
            <option value="pickup">Pickup</option>
            <option value="coupe">Coupé</option>
            <option value="van">Van</option>
            <option value="camioneta">Camioneta</option>
        </select><br><br>

        <label for="anio">Año:</label><br>
        <input type="number" name="anio" id="anio" required><br><br>

        <label for="placa">Placa:</label><br>
        <input type="text" name="placa" id="placa" required><br><br>

        <label for="precio_dia">Precio por día:</label><br>
        <input type="number" step="0.01" name="precio_dia" id="precio_dia" required><br><br>

        <label for="precio_especial_3_dias">Precio especial desde el día 3 (COP):</label><br>
        <input type="number" step="0.01" name="precio_especial_3_dias" id="precio_especial_3_dias"><br><br>

        <label for="estado">Estado:</label><br>
        <select name="estado" id="estado" required>
            <option value="disponible">Disponible</option>
            <option value="reservado">Reservado</option>
            <option value="mantenimiento">Mantenimiento</option>
            <option value="inactivo">Inactivo</option>
        </select><br><br>

        <button type="submit">Guardar Vehículo</button>
    </form>

    <br>
    <a href="../../modules/vehiculos/listar.php">Volver a lista de vehículos</a><br><br>
    <a href="../../admin/dashboard.php">Volver al Dashboard</a>
</body>
</html>