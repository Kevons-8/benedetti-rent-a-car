<?php

// Copia este archivo como database.php y completa los valores solo en local o producción.
$host     = 'localhost';
$dbname   = 'NOMBRE_BASE_DE_DATOS';
$username = 'USUARIO_BASE_DE_DATOS';
$password = 'CONTRASENA_BASE_DE_DATOS';

// Conexión MySQLi ($conn).
$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die('Error de conexión a la base de datos.');
}
$conn->set_charset('utf8mb4');

// Conexión PDO ($conexion) para los archivos que la usen.
try {
    $conexion = new PDO(
        "mysql:host={$host};dbname={$dbname};charset=utf8mb4",
        $username,
        $password
    );
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Error de conexión a la base de datos.');
}
