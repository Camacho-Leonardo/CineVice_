<?php session_start(); ?>

<?php require_once("conexion.php"); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>CineVice</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/x-icon" href="Imágenes/favicon.png">
</head>
<body>
<header class="navbar">
    <div class="nav-left">
        <a href="./index.php" id="home-link">
                    <h1>Cine<strong id="colored-h1">Vice</strong></h1>
                </a>
        <div class="extra-buttons">
            <a href="peliculas_series.php"><button>Películas/Series</button></a>
            <a href="foros.php"><button>Foros</button></a>
        </div>
    </div>
    <div class="nav-center">
        <div class="search-wrapper">
            <input type="text" id="searchInput" placeholder="Buscar películas o géneros...">
            <img src="Imágenes/lupa-busqueda.png" alt="Buscar" class="search-icon">
            <div id="suggestions"></div>
        </div>
        <select id="generoDropdown">
            <option value="">-- Ver por género --</option>
            <?php
            $genDropdown = mysqli_query($conexion, "SELECT * FROM generos");
            while ($g = mysqli_fetch_assoc($genDropdown)) {
                echo '<option value="' . strtolower($g['nombre']) . '">' . ucfirst($g['nombre']) . '</option>';
            }
            ?>
        </select>
    </div>
<div class="nav-right">
    <?php if (isset($_SESSION['usuario'])): ?>
        <span class="nav-username">👤 <?php echo htmlspecialchars($_SESSION['usuario']['nombre']); ?></span>
        <a href="./Páginas/perfil.php"><button>Perfil</button></a>
        <a href="./Páginas/logout.php"><button class="log-out">Cerrar sesión</button></a>
    <?php else: ?>
        <a href="./Páginas/formularios.php?inicio"><button class="log-in">Iniciar sesión</button></a>
        <a href="./Páginas/formularios.php?registro"><button class="sing-in">Registrarse</button></a>
    <?php endif; ?>
</div>
</header>

<div id="mensaje-error" class="mensaje-flotante">Búsqueda no encontrada</div>

<main id="contenido">
<?php
$generos = mysqli_query($conexion, "SELECT * FROM generos");
while ($gen = mysqli_fetch_assoc($generos)) {
    echo "<h2 class='titulo-genero'>" . ucfirst($gen['nombre']) . "</h2>";
    $gen_id = $gen['gen_id'];
    $pelis = mysqli_query($conexion, "
        SELECT p.peli_id, p.nombre, p.poster FROM pelis p 
        INNER JOIN pelis_generos pg ON p.peli_id = pg.peli_id 
        WHERE pg.gen_id = $gen_id
    ");
    echo "<div class='pelis-container'>";
    while ($p = mysqli_fetch_assoc($pelis)) {
        echo '<div class="pelis">';
        echo '<a href="detalle.php?peli_id=' . $p['peli_id'] . '">';
        echo '<img src="Imágenes/' . $p['poster'] . '" alt="' . $p['nombre'] . '">';
        echo '</a>';
        echo '<p>' . $p['nombre'] . '</p>';
        echo '</div>';
    }
    echo "</div>";
}
?>
</main>

<script src="script.js"></script>
</body>
</html>
