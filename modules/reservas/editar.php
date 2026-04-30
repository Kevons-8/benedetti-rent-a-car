<?php
require_once "../../includes/auth.php";
require_once "../../config/database.php";

if (!isset($_GET["id"])) {
    die("ID no especificado");
}

$id = (int) $_GET["id"];

/* ======================
OBTENER DATOS
====================== */
$stmt = $conexion->prepare("SELECT * FROM reservas WHERE id_reserva = :id");
$stmt->bindParam(':id', $id);
$stmt->execute();

$reserva = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reserva) {
    die("Reserva no encontrada");
}

/* ======================
ACTUALIZAR
====================== */
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $fecha_inicio = $_POST["fecha_inicio"];
    $fecha_fin = $_POST["fecha_fin"];
    $observaciones = $_POST["observaciones"];

    try {
        $sql = "UPDATE reservas 
                SET fecha_inicio = :inicio,
                    fecha_fin = :fin,
                    observaciones = :obs
                WHERE id_reserva = :id";

        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':inicio', $fecha_inicio);
        $stmt->bindParam(':fin', $fecha_fin);
        $stmt->bindParam(':obs', $observaciones);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        header("Location: ver.php?id=" . $id);
        exit();

    } catch (PDOException $e) {
        die("Error al actualizar: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Editar Reserva</title>

<style>
body{
    font-family: Arial;
    background:#0b172a;
    color:#fff;
    padding:40px;
}

form{
    background:#0f1c33;
    padding:30px;
    border-radius:20px;
    max-width:600px;
}

input, textarea{
    width:100%;
    padding:10px;
    margin-bottom:15px;
    border-radius:10px;
    border:none;
}

button{
    background:linear-gradient(180deg,#6eff1f,#19a500);
    padding:12px 20px;
    border:none;
    border-radius:999px;
    font-weight:bold;
    cursor:pointer;
}
</style>

</head>
<body>

<h1>Editar Reserva</h1>

<form method="POST">

<label>Fecha inicio</label>
<input type="datetime-local" name="fecha_inicio" 
value="<?php echo date('Y-m-d\TH:i', strtotime($reserva["fecha_inicio"])); ?>">

<label>Fecha fin</label>
<input type="datetime-local" name="fecha_fin" 
value="<?php echo date('Y-m-d\TH:i', strtotime($reserva["fecha_fin"])); ?>">

<label>Observaciones</label>
<textarea name="observaciones"><?php echo htmlspecialchars($reserva["observaciones"]); ?></textarea>

<button type="submit">Actualizar reserva</button>

</form>

</body>
</html>