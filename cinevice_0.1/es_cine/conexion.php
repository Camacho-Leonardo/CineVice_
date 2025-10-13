<?php
// Cargar .env
$envFile = __DIR__ . '/../../.env';
$lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lines as $line) {
    if (strpos(trim($line), '#') !== 0 && strpos($line, '=') !== false) {
        list($key, $value) = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
    }
}

// Credenciales desde .env
$host = $_ENV['DB_HOST'];
$usuario = $_ENV['DB_USER'];
$contrasena = ($_ENV['DB_PASS'] == '') ? '' : $_ENV['DB_PASS'];
$base_datos = $_ENV['DB_NAME'];

// Conexión
$conexion = new mysqli($host, $usuario, $contrasena, $base_datos);

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}
?>