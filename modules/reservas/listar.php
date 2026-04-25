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
<title>Reservas | Benedetti Rent a Car</title>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: Arial, Helvetica, sans-serif;
}

body {
    min-height: 100vh;
    background: url('../../assets/img/bocas_de_ceniza_.png') no-repeat center center/cover;
    color: #fff;
    position: relative;
}

body::before {
    content: "";
    position: fixed;
    inset: 0;
    background: linear-gradient(
        rgba(15, 23, 42, 0.62),
        rgba(15, 23, 42, 0.78)
    );
    backdrop-filter: blur(2px);
    z-index: 0;
}

.container {
    position: relative;
    z-index: 1;
    padding: 35px;
    animation: fadeIn 0.7s ease;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(18px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.header {
    background: rgba(15, 23, 42, 0.82);
    border: 1px solid rgba(255,255,255,0.14);
    backdrop-filter: blur(14px);
    border-radius: 20px;
    padding: 24px;
    margin-bottom: 24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.header h1 {
    font-size: 28px;
    margin-bottom: 6px;
}

.header p {
    color: #cbd5e1;
}

.header a {
    text-decoration: none;
    background: #facc15;
    color: #1e293b;
    padding: 12px 16px;
    border-radius: 12px;
    font-weight: bold;
    transition: 0.2s;
}

.header a:hover {
    background: #eab308;
    transform: translateY(-2px);
}

.filters {
    background: rgba(15, 23, 42, 0.78);
    border: 1px solid rgba(255,255,255,0.14);
    backdrop-filter: blur(14px);
    border-radius: 18px;
    padding: 18px;
    margin-bottom: 20px;
    display: grid;
    grid-template-columns: 1fr 230px;
    gap: 14px;
}

.filters input,
.filters select {
    width: 100%;
    padding: 13px 14px;
    border-radius: 12px;
    border: 1px solid rgba(255,255,255,0.16);
    background: rgba(255,255,255,0.1);
    color: #fff;
    outline: none;
    font-weight: bold;
}

.filters input::placeholder {
    color: #cbd5e1;
}

.filters select option {
    color: #0f172a;
}

.table-container {
    background: rgba(15, 23, 42, 0.86);
    border-radius: 20px;
    padding: 22px;
    backdrop-filter: blur(14px);
    border: 1px solid rgba(255,255,255,0.14);
    box-shadow: 0 20px 60px rgba(0,0,0,0.35);
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
}

thead {
    background: rgba(255,255,255,0.06);
}

th {
    padding: 15px;
    text-align: left;
    font-size: 13px;
    color: #cbd5e1;
    text-transform: uppercase;
}

td {
    padding: 15px;
    border-top: 1px solid rgba(255,255,255,0.08);
    font-size: 14px;
}

tbody tr {
    transition: 0.2s;
}

tbody tr:hover {
    background: rgba(250, 204, 21, 0.08);
    transform: scale(1.01);
}

.badge {
    padding: 7px 11px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: bold;
    display: inline-block;
}

.pendiente {
    background: #facc15;
    color: #1e293b;
}

.confirmada {
    background: #22c55e;
    color: #052e16;
}

.cancelada {
    background: #ef4444;
    color: #fff;
}

.finalizada {
    background: #38bdf8;
    color: #082f49;
}

.actions a {
    text-decoration: none;
    padding: 8px 11px;
    border-radius: 9px;
    margin-right: 5px;
    font-size: 12px;
    font-weight: bold;
    display: inline-block;
    transition: 0.2s;
}

.actions a:hover {
    transform: translateY(-2px);
}

.btn-view {
    background: #facc15;
    color: #1e293b;
}

.btn-edit {
    background: #3b82f6;
    color: #fff;
}

.btn-delete {
    background: #ef4444;
    color: #fff;
}

.empty {
    text-align: center;
    padding: 30px;
    color: #cbd5e1;
}

.no-results {
    display: none;
    text-align: center;
    padding: 24px;
    color: #cbd5e1;
    font-weight: bold;
}

@media(max-width: 900px) {
    .container {
        padding: 20px;
    }

    .header {
        flex-direction: column;
        align-items: flex-start;
        gap: 14px;
    }

    .filters {
        grid-template-columns: 1fr;
    }

    table, thead, tbody, th, td, tr {
        display: block;
    }

    thead {
        display: none;
    }

    tr {
        margin-bottom: 15px;
        background: rgba(255,255,255,0.05);
        border-radius: 14px;
        padding: 12px;
    }

    td {
        border: none;
        padding: 9px;
    }

    td::before {
        content: attr(data-label);
        display: block;
        color: #facc15;
        font-size: 12px;
        margin-bottom: 4px;
        font-weight: bold;
    }
}
</style>
</head>

<body>

<div class="container">

    <div class="header">
        <div>
            <h1>📅 Reservas</h1>
            <p>Gestión profesional de reservas de Benedetti Rent a Car</p>
        </div>

        <a href="crear.php">+ Nueva reserva</a>
    </div>

    <div class="filters">
        <input 
            type="text" 
            id="searchInput" 
            placeholder="🔍 Buscar por cliente, vehículo, placa, código o total..."
        >

        <select id="filterEstado">
            <option value="">Todos los estados</option>
            <option value="pendiente">Pendiente</option>
            <option value="confirmada">Confirmada</option>
            <option value="cancelada">Cancelada</option>
            <option value="finalizada">Finalizada</option>
        </select>
    </div>

    <div class="table-container">
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
                    <th>Acciones</th>
                </tr>
            </thead>

            <tbody id="reservasTable">
                <?php if (count($reservas) > 0): ?>
                    <?php foreach ($reservas as $r): ?>
                        <tr data-estado="<?php echo htmlspecialchars($r["estado_reserva"]); ?>">
                            <td data-label="Código">
                                <?php echo htmlspecialchars($r["codigo_reserva"] ?? "RSV-" . $r["id_reserva"]); ?>
                            </td>

                            <td data-label="Cliente">
                                <?php echo htmlspecialchars($r["nombre"] . " " . $r["apellido"]); ?>
                            </td>

                            <td data-label="Vehículo">
                                <?php echo htmlspecialchars($r["marca"] . " " . $r["modelo"]); ?>
                            </td>

                            <td data-label="Placa">
                                <?php echo htmlspecialchars($r["placa"]); ?>
                            </td>

                            <td data-label="Inicio">
                                <?php echo date("d/m/Y H:i", strtotime($r["fecha_inicio"])); ?>
                            </td>

                            <td data-label="Fin">
                                <?php echo date("d/m/Y H:i", strtotime($r["fecha_fin"])); ?>
                            </td>

                            <td data-label="Estado">
                                <span class="badge <?php echo htmlspecialchars($r["estado_reserva"]); ?>">
                                    <?php echo ucfirst(htmlspecialchars($r["estado_reserva"])); ?>
                                </span>
                            </td>

                            <td data-label="Total">
                                $<?php echo number_format($r["total_pago"], 0, ",", "."); ?>
                            </td>

                            <td data-label="Acciones" class="actions">
                                <a href="ver.php?id=<?php echo $r["id_reserva"]; ?>" class="btn-view">Ver</a>
                                <a href="editar.php?id=<?php echo $r["id_reserva"]; ?>" class="btn-edit">Editar</a>
                                <a href="eliminar.php?id=<?php echo $r["id_reserva"]; ?>" class="btn-delete">Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" class="empty">No hay reservas registradas.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div id="noResults" class="no-results">
            No se encontraron reservas con esos filtros.
        </div>
    </div>

</div>

<script>
const searchInput = document.getElementById("searchInput");
const filterEstado = document.getElementById("filterEstado");
const rows = document.querySelectorAll("#reservasTable tr");
const noResults = document.getElementById("noResults");

function filtrarReservas() {
    const texto = searchInput.value.toLowerCase();
    const estado = filterEstado.value.toLowerCase();
    let visibles = 0;

    rows.forEach(row => {
        const contenido = row.innerText.toLowerCase();
        const estadoFila = row.getAttribute("data-estado")?.toLowerCase() || "";

        const coincideTexto = contenido.includes(texto);
        const coincideEstado = estado === "" || estadoFila === estado;

        if (coincideTexto && coincideEstado) {
            row.style.display = "";
            visibles++;
        } else {
            row.style.display = "none";
        }
    });

    noResults.style.display = visibles === 0 ? "block" : "none";
}

searchInput.addEventListener("keyup", filtrarReservas);
filterEstado.addEventListener("change", filtrarReservas);
</script>

</body>
</html>