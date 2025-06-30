<?php
require_once("conexion.php");

$q = strtolower(trim($_GET['q'] ?? ''));

if (!$q) exit;

// Verificar si el texto coincide con un género
$generoStmt = $conexion->prepare("SELECT gen_id FROM generos WHERE LOWER(nombre) = ?");
$generoStmt->bind_param("s", $q);
$generoStmt->execute();
$generoResult = $generoStmt->get_result();

if ($generoResult->num_rows > 0) {
    $gen = $generoResult->fetch_assoc();
    $gen_id = $gen['gen_id'];

    $pelisStmt = $conexion->prepare("SELECT p.peli_id, p.nombre, p.poster FROM pelis p 
        INNER JOIN pelis_generos pg ON p.peli_id = pg.peli_id WHERE pg.gen_id = ?");
    $pelisStmt->bind_param("i", $gen_id);
    $pelisStmt->execute();
    $pelisRes = $pelisStmt->get_result();

    ob_start();
    echo "<h2 class='titulo-genero'>" . ucfirst($q) . "</h2><div class='pelis-container'>";
    while ($row = $pelisRes->fetch_assoc()) {
        echo '<div class="pelis">';
        echo '<a href="detalle.php?peli_id=' . $row['peli_id'] . '">';
        echo '<img src="Imágenes/' . $row['poster'] . '" alt="' . $row['nombre'] . '">';
        echo '</a>';
        echo '<p>' . $row['nombre'] . '</p>';
        echo '</div>';
    }
    echo "</div>";
    echo "FILTRAR:" . ob_get_clean(); // Indicar que se debe filtrar
    exit;
}

// Buscar por nombre
$stmt = $conexion->prepare("SELECT peli_id, nombre, poster FROM pelis WHERE LOWER(nombre) LIKE CONCAT('%', ?, '%')");
$stmt->bind_param("s", $q);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        echo '<div class="suggestion" data-id="' . $row['peli_id'] . '">';
        echo '<img src="Imágenes/' . $row['poster'] . '" alt="' . $row['nombre'] . '">';
        echo '<span>' . $row['nombre'] . '</span>';
        echo '</div>';
    }
}
$stmt->close();
?>
