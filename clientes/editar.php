<?php
include("../config/conexion.php");

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = intval($_GET['id']);

$sql = "SELECT * FROM clientes WHERE id_cliente = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();

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

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    font-family: Arial, sans-serif;
    min-height:100vh;

    background:
        linear-gradient(135deg, rgba(3,13,31,0.85), rgba(6,32,71,0.80)),
        url("/benedetti-rent-a-car/assets/img/fondo_vehiculos.png") center center / cover no-repeat fixed;

    color:#fff;
    padding:45px 7%;
}

/* CONTENEDOR */
.container{
    max-width:1100px;
    margin:0 auto;
}

/* HEADER */
.header{
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    gap:20px;
    margin-bottom:30px;
}

.badge{
    display:inline-flex;
    padding:8px 14px;
    border-radius:999px;
    background:rgba(34,197,94,0.15);
    border:1px solid rgba(34,197,94,0.35);
    color:#86efac;
    font-size:0.85rem;
    font-weight:700;
    margin-bottom:10px;
}

h1{
    font-size:clamp(2rem,4vw,3rem);
    margin-bottom:10px;
}

.header p{
    color:#cfd8e6;
}

.actions{
    display:flex;
    gap:10px;
    flex-wrap:wrap;
}

/* BOTONES */
.btn{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    padding:12px 22px;
    border-radius:999px;
    text-decoration:none;
    font-weight:800;
    font-size:0.9rem;
    transition:all 0.25s ease;
}

.btn-dark{
    background:rgba(3,10,24,0.65);
    border:1px solid rgba(255,255,255,0.15);
    color:#fff;
}

.btn-dark:hover{
    transform:translateY(-2px);
}

/* CARD */
.card{
    background:rgba(15,28,51,0.88);
    border:1px solid rgba(255,255,255,0.12);
    border-radius:26px;
    padding:30px;
    box-shadow:0 24px 70px rgba(0,0,0,0.35);
    backdrop-filter:blur(16px);
}

/* FORM GRID */
.form-grid{
    display:grid;
    grid-template-columns:repeat(2,1fr);
    gap:18px;
}

.form-group{
    display:flex;
    flex-direction:column;
}

.form-group-full{
    grid-column:1 / -1;
}

label{
    font-size:0.9rem;
    font-weight:700;
    margin-bottom:6px;
}

input, select{
    width:100%;
    padding:12px 14px;
    border-radius:12px;
    border:1px solid rgba(255,255,255,0.15);
    background:rgba(8,21,45,0.95);
    color:#fff;
    font-size:0.9rem;
}

input:focus, select:focus{
    outline:none;
    border-color:#22c55e;
    box-shadow:0 0 0 3px rgba(34,197,94,0.15);
}

/* BOTÓN PRINCIPAL */
.btn-primary{
    margin-top:25px;
    width:100%;
    padding:14px;
    border:none;
    border-radius:14px;
    font-weight:800;
    cursor:pointer;

    background:linear-gradient(180deg,#6eff1f 0%,#38d600 45%,#19a500 100%);
    color:#fff;

    box-shadow:0 10px 22px rgba(34,197,94,0.25);
}

.btn-primary:hover{
    transform:translateY(-2px);
}

/* RESPONSIVE */
@media(max-width:800px){
    .form-grid{
        grid-template-columns:1fr;
    }

    .header{
        flex-direction:column;
    }

    .actions{
        width:100%;
    }

    .btn{
        width:100%;
    }
}

</style>

</head>

<body>

<div class="container">

    <div class="header">
        <div>
            <span class="badge">Edición de cliente</span>
            <h1>Editar Cliente</h1>
            <p>Modifica la información del cliente de forma segura.</p>
        </div>

        <div class="actions">
            <a href="index.php" class="btn btn-dark">← Volver</a>
            <a href="../admin/dashboard.php" class="btn btn-dark">Dashboard</a>
        </div>
    </div>

    <div class="card">

        <form action="actualizar.php" method="POST">

            <input type="hidden" name="id_cliente" value="<?php echo $cliente['id_cliente']; ?>">

            <div class="form-grid">

                <div class="form-group">
                    <label>Tipo de documento</label>
                    <select name="tipo_documento" required>
                        <option value="CC" <?php if($cliente['tipo_documento']=='CC') echo 'selected'; ?>>CC</option>
                        <option value="CE" <?php if($cliente['tipo_documento']=='CE') echo 'selected'; ?>>CE</option>
                        <option value="Pasaporte" <?php if($cliente['tipo_documento']=='Pasaporte') echo 'selected'; ?>>Pasaporte</option>
                        <option value="NIT" <?php if($cliente['tipo_documento']=='NIT') echo 'selected'; ?>>NIT</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Número de documento</label>
                    <input type="text" name="numero_documento" value="<?php echo htmlspecialchars($cliente['numero_documento']); ?>" required>
                </div>

                <div class="form-group">
                    <label>Nombre</label>
                    <input type="text" name="nombre" value="<?php echo htmlspecialchars($cliente['nombre']); ?>" required>
                </div>

                <div class="form-group">
                    <label>Apellido</label>
                    <input type="text" name="apellido" value="<?php echo htmlspecialchars($cliente['apellido']); ?>" required>
                </div>

                <div class="form-group">
                    <label>Teléfono</label>
                    <input type="text" name="telefono" value="<?php echo htmlspecialchars($cliente['telefono']); ?>" required>
                </div>

                <div class="form-group">
                    <label>Correo</label>
                    <input type="email" name="correo" value="<?php echo htmlspecialchars($cliente['correo']); ?>">
                </div>

                <div class="form-group form-group-full">
                    <label>Dirección</label>
                    <input type="text" name="direccion" value="<?php echo htmlspecialchars($cliente['direccion']); ?>">
                </div>

                <div class="form-group form-group-full">
                    <label>Licencia de conducción</label>
                    <input type="text" name="licencia_conduccion" value="<?php echo htmlspecialchars($cliente['licencia_conduccion']); ?>">
                </div>

            </div>

            <button type="submit" class="btn-primary">
                Actualizar Cliente
            </button>

        </form>

    </div>

</div>

</body>
</html>