<?php
require_once("conexion.php");

if (!isset($conexion) || $conexion->connect_error) {
    die("Error de conexión con la base de datos.");
}

$peli_id = $_GET['peli_id'] ?? null;
$peli = null;

if ($peli_id && is_numeric($peli_id)) {
    $stmt = $conexion->prepare("SELECT * FROM pelis WHERE peli_id = ?");
    $stmt->bind_param("i", $peli_id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $peli = $resultado->fetch_assoc();
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>En desarrollo</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="contenido">
        <h1>En desarrollo aún sin terminar</h1>

        <?php if ($peli): ?>
            <div class="pelis">
                <img src="Imágenes/<?php echo $peli["poster"]; ?>" alt="<?php echo $peli["nombre"]; ?>">
                <h2><?php echo $peli["nombre"]; ?></h2>
                <p><strong>Descripción:</strong> <?php echo $peli["descripcion"]; ?></p>
                <p><strong>Emisión:</strong> <?php echo $peli["emision"]; ?></p>
                <p><strong>Duración:</strong> <?php echo $peli["duracion"]; ?></p>
                <p><strong>País:</strong> <?php echo $peli["pais"]; ?></p>
                <p><strong>Idioma:</strong> <?php echo $peli["idioma"]; ?></p>
                <p><strong>Episodios:</strong> <?php echo $peli["episodios"]; ?></p>
            </div>
        <?php else: ?>
            <p>Película no encontrada.</p>
        <?php endif; ?>
    </div>
</body>
</html>
