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
<title>Eliminar Cliente</title>

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
        linear-gradient(135deg, rgba(3,13,31,0.86), rgba(6,32,71,0.82)),
        url("/benedetti-rent-a-car/assets/img/fondo_vehiculos.png") center center / cover no-repeat fixed;
    color:#fff;
    padding:45px 7%;
}

.container{
    max-width:1100px;
    margin:0 auto;
}

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
    background:rgba(239,68,68,0.16);
    border:1px solid rgba(239,68,68,0.35);
    color:#fecaca;
    font-size:0.85rem;
    font-weight:800;
    margin-bottom:10px;
}

h1{
    font-size:clamp(2rem,4vw,3rem);
    margin-bottom:10px;
}

.header p{
    color:#cfd8e6;
    max-width:680px;
}

.actions{
    display:flex;
    gap:10px;
    flex-wrap:wrap;
}

.btn{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    min-height:50px;
    padding:13px 24px;
    border-radius:999px;
    text-decoration:none;
    font-weight:800;
    font-size:0.9rem;
    border:none;
    cursor:pointer;
    transition:all 0.25s ease;
}

.btn-dark{
    background:rgba(3,10,24,0.65);
    border:1px solid rgba(255,255,255,0.15);
    color:#fff;
}

.btn-dark:hover{
    transform:translateY(-2px);
    background:rgba(3,10,24,0.85);
}

.btn-danger{
    color:#ffffff;
    background:linear-gradient(180deg,#ff5f5f 0%,#ef4444 45%,#b91c1c 100%);
    box-shadow:
        inset 0 2px 0 rgba(255,255,255,0.28),
        0 12px 24px rgba(239,68,68,0.28);
}

.btn-danger:hover{
    transform:translateY(-2px);
    box-shadow:
        inset 0 2px 0 rgba(255,255,255,0.34),
        0 16px 32px rgba(239,68,68,0.38);
}

.delete-card{
    display:grid;
    grid-template-columns:320px 1fr;
    gap:28px;
    align-items:stretch;
    background:rgba(15,28,51,0.88);
    border:1px solid rgba(255,255,255,0.12);
    border-radius:26px;
    padding:30px;
    box-shadow:0 24px 70px rgba(0,0,0,0.35);
    backdrop-filter:blur(16px);
}

.warning-panel{
    background:rgba(239,68,68,0.12);
    border:1px solid rgba(239,68,68,0.32);
    border-radius:22px;
    padding:28px;
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:center;
    text-align:center;
}

.warning-icon{
    width:120px;
    height:120px;
    border-radius:50%;
    display:flex;
    align-items:center;
    justify-content:center;
    margin-bottom:18px;
    background:linear-gradient(180deg,#ff5f5f 0%,#ef4444 45%,#b91c1c 100%);
    color:#ffffff;
    font-size:3.4rem;
    font-weight:900;
    box-shadow:
        inset 0 2px 0 rgba(255,255,255,0.28),
        0 16px 34px rgba(239,68,68,0.28);
}

.warning-panel h2{
    color:#ffffff;
    font-size:1.5rem;
    margin-bottom:10px;
}

.warning-panel p{
    color:#fecaca;
    line-height:1.6;
}

.info-panel{
    display:flex;
    flex-direction:column;
    justify-content:center;
}

.info-panel h2{
    color:#ffffff;
    font-size:2rem;
    margin-bottom:12px;
}

.info-panel > p{
    color:#d8e2f0;
    line-height:1.7;
    margin-bottom:22px;
}

.detail-grid{
    display:grid;
    grid-template-columns:repeat(2,1fr);
    gap:14px;
    margin-bottom:26px;
}

.detail-grid p{
    margin:0;
    padding:15px 16px;
    border-radius:16px;
    color:#d8e2f0;
    background:rgba(8,21,45,0.75);
    border:1px solid rgba(255,255,255,0.10);
    word-break:break-word;
}

.detail-grid strong{
    display:block;
    color:#ffffff;
    margin-bottom:6px;
    font-size:0.9rem;
}

.form-actions{
    display:flex;
    gap:14px;
    flex-wrap:wrap;
    padding-top:22px;
    border-top:1px solid rgba(255,255,255,0.10);
}

@media(max-width:900px){
    body{
        padding:35px 5%;
    }

    .header{
        flex-direction:column;
    }

    .delete-card{
        grid-template-columns:1fr;
    }

    .detail-grid{
        grid-template-columns:1fr;
    }
}

@media(max-width:600px){
    .delete-card{
        padding:22px;
        border-radius:22px;
    }

    .actions,
    .form-actions{
        width:100%;
        flex-direction:column;
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
            <span class="badge">Acción irreversible</span>
            <h1>Eliminar Cliente</h1>
            <p>
                Antes de eliminar este cliente, verifica cuidadosamente la información.
                Esta acción no se puede deshacer.
            </p>
        </div>

        <div class="actions">
            <a href="ver.php?id=<?php echo $cliente['id_cliente']; ?>" class="btn btn-dark">← Volver a la ficha</a>
            <a href="index.php" class="btn btn-dark">Listado</a>
        </div>
    </div>

    <div class="delete-card">

        <div class="warning-panel">
            <div class="warning-icon">!</div>
            <h2>Confirmar eliminación</h2>
            <p>
                El cliente será eliminado permanentemente del sistema.
            </p>
        </div>

        <div class="info-panel">
            <h2>
                <?php echo htmlspecialchars($cliente['nombre'] . ' ' . $cliente['apellido']); ?>
            </h2>

            <p>
                Vas a eliminar el registro del cliente seleccionado. Si el cliente tiene reservas asociadas,
                es posible que la base de datos no permita eliminarlo por integridad de la información.
            </p>

            <div class="detail-grid">
                <p>
                    <strong>Código cliente</strong>
                    #<?php echo htmlspecialchars($cliente['id_cliente']); ?>
                </p>

                <p>
                    <strong>Documento</strong>
                    <?php echo htmlspecialchars($cliente['numero_documento']); ?>
                </p>

                <p>
                    <strong>Teléfono</strong>
                    <?php echo htmlspecialchars($cliente['telefono']); ?>
                </p>

                <p>
                    <strong>Correo</strong>
                    <?php echo htmlspecialchars($cliente['correo']); ?>
                </p>
            </div>

            <form action="eliminar_confirmar.php" method="POST">
                <input type="hidden" name="id_cliente" value="<?php echo $cliente['id_cliente']; ?>">

                <div class="form-actions">
                    <button type="submit" class="btn btn-danger">
                        Sí, eliminar cliente
                    </button>

                    <a href="ver.php?id=<?php echo $cliente['id_cliente']; ?>" class="btn btn-dark">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>

    </div>

</div>

</body>
</html>