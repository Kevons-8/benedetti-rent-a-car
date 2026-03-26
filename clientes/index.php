<?php
include("../config/conexion.php");

$sql = "SELECT * FROM clientes ORDER BY id_cliente DESC";
$resultado = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">

<head>
<meta charset="UTF-8">
<title>Módulo de Clientes</title>

<style>

body{
    font-family: Arial;
    background-color:#f4f6f9;
    padding:30px;
}

h1{
    color:#333;
}

.btn{
    background:#007bff;
    color:white;
    padding:10px 15px;
    text-decoration:none;
    border-radius:5px;
}

.btn:hover{
    background:#0056b3;
}

.btn-volver{
    background:#6c757d;
    color:white;
    padding:10px 15px;
    text-decoration:none;
    border-radius:5px;
    margin-left:10px;
}

.btn-volver:hover{
    background:#545b62;
}

table{
    width:100%;
    border-collapse:collapse;
    margin-top:20px;
    background:white;
}

th, td{
    border:1px solid #ccc;
    padding:10px;
    text-align:center;
}

th{
    background:#343a40;
    color:white;
}

.editar{
    background:#28a745;
    color:white;
    padding:5px 10px;
    text-decoration:none;
    border-radius:4px;
}

.editar:hover{
    background:#218838;
}

.eliminar{
    background:#dc3545;
    color:white;
    padding:5px 10px;
    text-decoration:none;
    border-radius:4px;
}

.eliminar:hover{
    background:#c82333;
}

</style>

</head>

<body>

<h1>Módulo de Clientes</h1>

<br>

<a href="crear.php" class="btn">+ Nuevo Cliente</a>
<a href="../admin/dashboard.php" class="btn-volver">← Volver al Dashboard</a>

<table>

<tr>
<th>ID</th>
<th>Tipo Documento</th>
<th>Documento</th>
<th>Nombre</th>
<th>Apellido</th>
<th>Teléfono</th>
<th>Correo</th>
<th>Licencia</th>
<th>Acciones</th>
</tr>

<?php while($cliente = $resultado->fetch_assoc()){ ?>

<tr>

<td><?php echo htmlspecialchars($cliente['id_cliente']); ?></td>

<td><?php echo htmlspecialchars($cliente['tipo_documento']); ?></td>

<td><?php echo htmlspecialchars($cliente['numero_documento']); ?></td>

<td><?php echo htmlspecialchars($cliente['nombre']); ?></td>

<td><?php echo htmlspecialchars($cliente['apellido']); ?></td>

<td><?php echo htmlspecialchars($cliente['telefono']); ?></td>

<td><?php echo htmlspecialchars($cliente['correo']); ?></td>

<td><?php echo htmlspecialchars($cliente['licencia_conduccion']); ?></td>

<td>

<a class="editar" href="editar.php?id=<?php echo $cliente['id_cliente']; ?>">
Editar
</a>

<a class="eliminar"
href="eliminar.php?id=<?php echo $cliente['id_cliente']; ?>"
onclick="return confirm('¿Seguro que deseas eliminar este cliente?');">

Eliminar

</a>

</td>

</tr>

<?php } ?>

</table>

</body>
</html>
