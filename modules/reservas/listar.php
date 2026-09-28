<?php
require_once "../../includes/auth.php";
require_once "../../config/database.php";

$sql = "SELECT 
            r.id_reserva,
            r.codigo_reserva,
            r.fecha_inicio,
            r.fecha_fin,
            r.estado_reserva,
            r.total_pago,
            c.nombre,
            c.apellido,
            v.marca,
            v.modelo,
            v.placa
        FROM reservas r
        INNER JOIN clientes c ON r.id_cliente = c.id_cliente
        INNER JOIN vehiculos v ON r.id_vehiculo = v.id_vehiculo
        ORDER BY r.id_reserva DESC";

$reservas = $conexion->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Reservas</title>

<style>
*{margin:0;padding:0;box-sizing:border-box;}

body{
    font-family: Arial;
    min-height:100vh;

    background:
        linear-gradient(135deg, rgba(3,13,31,0.85), rgba(6,32,71,0.80)),
        url("../../assets/img/fondo_vehiculos.png") center/cover no-repeat fixed;

    color:#fff;
    padding:45px 7%;
}

/* HEADER */
.header-top{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:20px;
}

h1{
    font-size:2.5rem;
}

/* BOTÓN DASHBOARD */
.btn-dashboard{
    color:#fff;
    text-decoration:none;
    padding:10px 18px;
    border-radius:999px;
    font-weight:800;

    background:rgba(3,10,24,0.7);
    border:1px solid rgba(255,255,255,0.15);

    transition:0.2s;
}

.btn-dashboard:hover{
    transform:translateY(-2px);
    background:rgba(255,255,255,0.08);
}

/* CARD */
.card{
    background:rgba(15,28,51,0.88);
    border-radius:26px;
    padding:28px;
    backdrop-filter:blur(16px);
}

/* FILTROS */
.filters{
    display:flex;
    gap:12px;
    margin-bottom:18px;
}

.filters input,
.filters select{
    padding:12px;
    border-radius:999px;
    border:none;
}

/* TABLA */
.table-wrap{overflow:auto;}

table{
    width:100%;
    border-collapse:collapse;
}

th, td{
    padding:14px;
    border-bottom:1px solid rgba(255,255,255,0.1);
}

th{
    color:#86efac;
}

tbody tr:hover{
    background:rgba(255,255,255,0.05);
}

/* BOTÓN VER */
.action-view-pro{
    background:linear-gradient(180deg,#6eff1f,#19a500);
    padding:8px 14px;
    border-radius:999px;
    color:#fff;
    text-decoration:none;
    font-weight:bold;
}

/* ESTADOS */
.status{
    padding:6px 12px;
    border-radius:999px;
    font-size:12px;
}

.status-pendiente{background:#f59e0b33;}
.status-confirmada{background:#22c55e33;}
.status-finalizada{background:#3b82f633;}
.status-cancelada{background:#ef444433;}

</style>
</head>

<body>

<div class="header-top">
    <h1>Listado de Reservas</h1>

    <a href="../../admin/dashboard.php" class="btn-dashboard">
        ← Dashboard
    </a>
</div>

<div class="card">

<div class="filters">
    <input type="text" id="searchInput" placeholder="Buscar...">

    <select id="filterEstado">
        <option value="">Todos</option>
        <option value="pendiente">Pendiente</option>
        <option value="confirmada">Confirmada</option>
        <option value="finalizada">Finalizada</option>
        <option value="cancelada">Cancelada</option>
    </select>
</div>

<div class="table-wrap">
<table>

<thead>
<tr>
    <th>Código</th>
    <th>Cliente</th>
    <th>Vehículo</th>
    <th>Placa</th>
    <th>Inicio</th>
    <th>Fin</th>
    <th>Estado</th>
    <th>Total</th>
    <th>Acción</th>
</tr>
</thead>

<tbody id="tabla">

<?php foreach($reservas as $r): 

$estado = strtolower($r["estado_reserva"]);

if($estado=="confirmada") $clase="status-confirmada";
elseif($estado=="finalizada") $clase="status-finalizada";
elseif($estado=="cancelada") $clase="status-cancelada";
else $clase="status-pendiente";
?>

<tr data-estado="<?php echo $estado; ?>">

<td><?php echo $r["codigo_reserva"]; ?></td>
<td><?php echo $r["nombre"]." ".$r["apellido"]; ?></td>
<td><?php echo $r["marca"]." ".$r["modelo"]; ?></td>
<td><?php echo $r["placa"]; ?></td>
<td><?php echo date("d/m/Y H:i", strtotime($r["fecha_inicio"])); ?></td>
<td><?php echo date("d/m/Y H:i", strtotime($r["fecha_fin"])); ?></td>

<td>
<span class="status <?php echo $clase; ?>">
<?php echo ucfirst($estado); ?>
</span>
</td>

<td>$<?php echo number_format($r["total_pago"]); ?></td>

<td>
<a href="ver.php?id=<?php echo $r["id_reserva"]; ?>" class="action-view-pro">
Ver
</a>
</td>

</tr>

<?php endforeach; ?>

</tbody>
</table>
</div>

</div>

<script>
const search = document.getElementById("searchInput");
const estado = document.getElementById("filterEstado");
const rows = document.querySelectorAll("#tabla tr");

function filtrar(){
    let texto = search.value.toLowerCase();
    let est = estado.value;

    rows.forEach(row=>{
        let contenido = row.innerText.toLowerCase();
        let estadoRow = row.getAttribute("data-estado");

        let mostrar = contenido.includes(texto) &&
                     (est=="" || estadoRow==est);

        row.style.display = mostrar ? "" : "none";
    });
}

search.addEventListener("keyup", filtrar);
estado.addEventListener("change", filtrar);
</script>

</body>
</html>