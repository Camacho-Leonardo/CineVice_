<?php
session_start();
require_once("conexion.php");

$op_id = $_GET['op_id'] ?? null;
$peli_id = $_GET['peli_id'] ?? null;

if (!$op_id || !$peli_id) {
    header("Location: index.php");
    exit;
}

// Obtener la opinión
$stmt = $conexion->prepare("SELECT * FROM opiniones WHERE op_id = ?");
$stmt->bind_param("i", $op_id);
$stmt->execute();
$res = $stmt->get_result();
$opinion = $res->fetch_assoc();
$stmt->close();

// Validar que la opinión sea del usuario
if (!$opinion || $_SESSION['usuario']['id'] != $opinion['usu_id']) {
    echo "<p>No tienes permiso para editar esta opinión.</p>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar opinión</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <main class="opinion-form">
        <h2>Editar tu opinión</h2>
        <form action="actualizar_opinion.php" method="POST">
            <input type="hidden" name="op_id" value="<?php echo $op_id; ?>">
            <input type="hidden" name="peli_id" value="<?php echo $peli_id; ?>">

            <label for="puntuacion">Puntuación:</label>
            <select name="puntuacion" id="puntuacion" required>
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <option value="<?php echo $i; ?>" <?php if ($opinion['puntuacion'] == $i) echo 'selected'; ?>>
                        <?php echo $i; ?> estrella<?php echo $i > 1 ? 's' : ''; ?>
                    </option>
                <?php endfor; ?>
            </select>

            <label for="contenido">Tu opinión:</label>
            <textarea name="contenido" rows="4" required><?php echo htmlspecialchars($opinion['contenido']); ?></textarea>

            <button type="submit">Actualizar</button>
        </form>
    </main>
</body>
</html>
