<?php
include("../config/conexion.php");

$id = $_GET['id'];

$sql = "SELECT * FROM clientes WHERE id_cliente = $id";
$resultado = $conn->query($sql);

$cliente = $resultado->fetch_assoc();

if (!$cliente) {
    echo "Cliente no encontrado";
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Cliente</title>
    <style>
        body{
            font-family: Arial;
            background-color: #f4f6f9;
            padding: 30px;
        }

        h1{
            color: #333;
        }

        form{
            background: white;
            padding: 20px;
            border-radius: 8px;
            max-width: 700px;
        }

        label{
            font-weight: bold;
            display: block;
            margin-top: 10px;
        }

        input, select{
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .btn-actualizar{
            background: #28a745;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn-actualizar:hover{
            background: #218838;
        }

        .btn-volver{
            display: inline-block;
            margin-left: 10px;
            text-decoration: none;
            color: #333;
        }
    </style>
</head>
<body>

    <h1>Editar Cliente</h1>

    <form action="actualizar.php" method="POST">

        <input type="hidden" name="id_cliente" value="<?php echo $cliente['id_cliente']; ?>">

        <label for="tipo_documento">Tipo de documento</label>
        <select name="tipo_documento" id="tipo_documento" required>
            <option value="CC" <?php if($cliente['tipo_documento'] == 'CC') echo 'selected'; ?>>Cédula de ciudadanía</option>
            <option value="CE" <?php if($cliente['tipo_documento'] == 'CE') echo 'selected'; ?>>Cédula de extranjería</option>
            <option value="Pasaporte" <?php if($cliente['tipo_documento'] == 'Pasaporte') echo 'selected'; ?>>Pasaporte</option>
            <option value="NIT" <?php if($cliente['tipo_documento'] == 'NIT') echo 'selected'; ?>>NIT</option>
        </select>

        <label for="numero_documento">Número de documento</label>
        <input type="text" name="numero_documento" id="numero_documento" value="<?php echo htmlspecialchars($cliente['numero_documento']); ?>" required>

        <label for="nombre">Nombre</label>
        <input type="text" name="nombre" id="nombre" value="<?php echo htmlspecialchars($cliente['nombre']); ?>" required>

        <label for="apellido">Apellido</label>
        <input type="text" name="apellido" id="apellido" value="<?php echo htmlspecialchars($cliente['apellido']); ?>" required>

        <label for="telefono">Teléfono</label>
        <input type="text" name="telefono" id="telefono" value="<?php echo htmlspecialchars($cliente['telefono']); ?>" required>

        <label for="correo">Correo electrónico</label>
        <input type="email" name="correo" id="correo" value="<?php echo htmlspecialchars($cliente['correo']); ?>">

        <label for="direccion">Dirección</label>
        <input type="text" name="direccion" id="direccion" value="<?php echo htmlspecialchars($cliente['direccion']); ?>">

        <label for="licencia_conduccion">Licencia de conducción</label>
        <input type="text" name="licencia_conduccion" id="licencia_conduccion" value="<?php echo htmlspecialchars($cliente['licencia_conduccion']); ?>" required>

        <button type="submit" class="btn-actualizar">Actualizar Cliente</button>
        <a href="index.php" class="btn-volver">Volver</a>

    </form>

</body>
</html>
