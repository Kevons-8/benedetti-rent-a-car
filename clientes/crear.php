<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar Cliente</title>
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

        .btn-guardar{
            background: #007bff;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn-guardar:hover{
            background: #0056b3;
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

    <h1>Registrar Cliente</h1>

    <form action="guardar.php" method="POST">

        <label for="tipo_documento">Tipo de documento</label>
        <select name="tipo_documento" id="tipo_documento" required>
            <option value="">Seleccione una opción</option>
            <option value="CC">Cédula de ciudadanía</option>
            <option value="CE">Cédula de extranjería</option>
            <option value="Pasaporte">Pasaporte</option>
            <option value="NIT">NIT</option>
        </select>

        <label for="numero_documento">Número de documento</label>
        <input type="text" name="numero_documento" id="numero_documento" required>

        <label for="nombre">Nombre</label>
        <input type="text" name="nombre" id="nombre" required>

        <label for="apellido">Apellido</label>
        <input type="text" name="apellido" id="apellido" required>

        <label for="telefono">Teléfono</label>
        <input type="text" name="telefono" id="telefono" required>

        <label for="correo">Correo electrónico</label>
        <input type="email" name="correo" id="correo">

        <label for="direccion">Dirección</label>
        <input type="text" name="direccion" id="direccion">

        <label for="licencia_conduccion">Licencia de conducción</label>
        <input type="text" name="licencia_conduccion" id="licencia_conduccion" required>

        <button type="submit" class="btn-guardar">Guardar Cliente</button>
        <a href="index.php" class="btn-volver">Volver</a>

    </form>

</body>
</html>
