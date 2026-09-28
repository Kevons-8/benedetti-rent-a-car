<?php

// Copia este archivo como conexion.php y completa los valores solo en local o producción.
date_default_timezone_set('America/Bogota');

$host = 'localhost';
$dbname = 'NOMBRE_BASE_DE_DATOS';
$username = 'USUARIO_BASE_DE_DATOS';
$password = 'CONTRASENA_BASE_DE_DATOS';

try {
    $conexion = new PDO("mysql:host={$host};dbname={$dbname};charset=utf8mb4", $username, $password);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Error de conexión a la base de datos.');
}
