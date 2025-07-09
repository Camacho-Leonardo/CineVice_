<?php session_start(); ?>

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

// Obtener géneros de la película
$generos = [];
if ($peli) {
    $stmt = $conexion->prepare("
        SELECT g.nombre 
        FROM pelis_generos pg 
        JOIN generos g ON pg.gen_id = g.gen_id 
        WHERE pg.peli_id = ?
    ");
    $stmt->bind_param("i", $peli_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($gen = $result->fetch_assoc()) {
        $generos[] = ucfirst($gen['nombre']);
    }
    $stmt->close();
}

// Obtener opiniones activas (est_id = 1)
$opiniones = [];
$promedio = 0;
$orden = $_GET['orden'] ?? 'fecha';
$puntuacion_filtro = $_GET['puntuacion'] ?? '';

$orderBy = "o.fecha DESC"; // default
if ($orden === 'likes') $orderBy = "o.op_likes DESC";
if ($orden === 'dislikes') $orderBy = "o.op_dislikes DESC";

$sql = "
    SELECT o.*, u.nombre 
    FROM opiniones o
    JOIN usuarios u ON o.usu_id = u.usu_id
    WHERE o.peli_id = ? AND o.est_id = 1
";

if ($puntuacion_filtro !== '') {
    $sql .= " AND o.puntuacion = ?";
}

$sql .= " ORDER BY $orderBy";

if ($puntuacion_filtro !== '') {
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ii", $peli_id, $puntuacion_filtro);
} else {
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $peli_id);
}
$stmt->bind_param("i", $peli_id);
$stmt->execute();
$result = $stmt->get_result();
$total_puntos = 0;
$total_op = 0;
while ($op = $result->fetch_assoc()) {
    $opiniones[] = $op;
    $total_puntos += $op['puntuacion'];
    $total_op++;
}
$promedio = $total_op > 0 ? round($total_puntos / $total_op, 1) : 0;
$stmt->close();

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>En desarrollo</title>
    <link rel="stylesheet" href="style.css">
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



<div class="detalle-container">
    <?php if ($peli): ?>
        <div class="detalle-peli">
            <div class="poster-container">
                <img src="Imágenes/<?php echo $peli["poster"]; ?>" alt="<?php echo $peli["nombre"]; ?>">
            </div>
            <div class="info-container">
                <h1 class="titulo-peli"><?php echo $peli["nombre"]; ?></h1>
                <p class="descripcion"><?php echo $peli["descripcion"]; ?></p>
                <ul class="detalles">
                    <li><strong>Emisión:</strong> <?php echo $peli["emision"]; ?></li>
                    <li><strong>Duración:</strong> <?php echo $peli["duracion"]; ?></li>
                    <li><strong>País:</strong> <?php echo $peli["pais"]; ?></li>
                    <li><strong>Idioma:</strong> <?php echo $peli["idioma"]; ?></li>
                    <li><strong>Episodios:</strong> <?php echo $peli["episodios"]; ?></li>
                </ul>
                <?php if (!empty($generos)): ?>
    <div class="generos">
        <strong>Géneros:</strong>
        <?php foreach ($generos as $g): ?>
            <span class="genero-tag"><?php echo $g; ?></span>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

                <div class="rating">
                    <strong>Calificación según CineVice:</strong>
                    <div class="stars">
                        <span>★</span><span>★</span><span>★</span><span>★</span><span>★</span>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <p class="no-encontrada">Película no encontrada.</p>
    <?php endif; ?>
</div>

<form method="GET" action="en_desarrollo.php" class="opinion-filters">
    <input type="hidden" name="peli_id" value="<?php echo $peli_id; ?>">

    <label for="orden">Ordenar por:</label>
    <select name="orden" id="orden">
        <option value="fecha" <?php if ($_GET['orden'] ?? '' === 'fecha') echo 'selected'; ?>>Más recientes</option>
        <option value="likes" <?php if ($_GET['orden'] ?? '' === 'likes') echo 'selected'; ?>>Más likes</option>
        <option value="dislikes" <?php if ($_GET['orden'] ?? '' === 'dislikes') echo 'selected'; ?>>Más dislikes</option>
    </select>

    <label for="puntuacion">Filtrar por estrellas:</label>
    <select name="puntuacion" id="puntuacion">
        <option value="">Todas</option>
        <?php for ($i = 1; $i <= 5; $i++): ?>
            <option value="<?php echo $i; ?>" <?php if (($_GET['puntuacion'] ?? '') == $i) echo 'selected'; ?>>
                <?php echo $i; ?> estrella<?php echo $i > 1 ? 's' : ''; ?>
            </option>
        <?php endfor; ?>
    </select>

    <button type="submit">Aplicar</button>
</form>


<div class="social-section">
    <h2>Opiniones de la comunidad</h2>
    <div class="promedio-puntuacion">
        <span>⭐ Puntuación promedio: <?php echo $promedio; ?>/5</span>
    </div>
    <?php if (!empty($opiniones)): ?>
        <?php foreach ($opiniones as $op): ?>
            <div class="opinion">
                <div class="op-header">
                    <strong><?php echo htmlspecialchars($op['nombre']); ?></strong>
                    <span class="fecha"><?php echo date("d/m/Y", strtotime($op['fecha'])); ?></span>
                </div>
                <div class="op-stars">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <span class="estrella <?php echo $i <= $op['puntuacion'] ? 'activa' : ''; ?>">★</span>
                    <?php endfor; ?>
                </div>
                <p class="op-texto"><?php echo htmlspecialchars($op['contenido']); ?></p>
<div class="op-reacciones">
    <form action="like_dislike.php" method="POST" style="display:inline;">
<input type="hidden" name="opinion_id" value="<?php echo $op['op_id']; ?>">
        <input type="hidden" name="accion" value="like">
        <button type="submit">👍 <?php echo $op['op_likes']; ?></button>
    </form>

    <form action="like_dislike.php" method="POST" style="display:inline;">
        <input type="hidden" name="opinion_id" value="<?php echo $op['op_id']; ?>">
        <input type="hidden" name="accion" value="dislike">
        <button type="submit">👎 <?php echo $op['op_dislikes']; ?></button>
    </form>
</div>

<?php if (isset($_SESSION['usuario']) && $_SESSION['usuario']['id'] == $op['usu_id']): ?>
    <div class="op-mis-controles">
        <form action="editar_opinion.php" method="GET" style="display:inline;">
            <input type="hidden" name="op_id" value="<?php echo $op['op_id']; ?>">
            <input type="hidden" name="peli_id" value="<?php echo $peli_id; ?>">
            <button type="submit">✏️ Editar</button>
        </form>
        <form action="eliminar_opinion.php" method="POST" style="display:inline;" onsubmit="return confirm('¿Estás seguro de que querés eliminar esta opinión?');">
            <input type="hidden" name="op_id" value="<?php echo $op['op_id']; ?>">
            <input type="hidden" name="peli_id" value="<?php echo $peli_id; ?>">
            <button type="submit" style="color: red;">🗑️ Eliminar</button>
        </form>
    </div>
<?php endif; ?>


            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No hay opiniones aún.</p>
    <?php endif; ?>
</div>

<?php if (isset($_SESSION['usuario'])): ?>
    <div class="opinion-form">
        <h3>Dejar una opinión</h3>
        <form action="guardar_opinion.php" method="POST">
            <input type="hidden" name="peli_id" value="<?php echo $peli_id; ?>">
            <input type="hidden" name="usu_id" value="<?php echo $_SESSION['usuario']['id']; ?>">

            <label for="puntuacion">Puntuación:</label>
            <select name="puntuacion" id="puntuacion" required>
                <option value="">Seleccionar</option>
                <option value="1">1 estrella</option>
                <option value="2">2 estrellas</option>
                <option value="3">3 estrellas</option>
                <option value="4">4 estrellas</option>
                <option value="5">5 estrellas</option>
            </select>

            <label for="contenido">Tu opinión:</label>
            <textarea name="contenido" id="contenido" rows="4" placeholder="Escribe lo que pensás..." required></textarea>

            <button type="submit">Enviar</button>
        </form>
    </div>
<?php else: ?>
    <p style="text-align:center; margin-top: 30px;">
        <a href="Páginas/formularios.php?inicio">Inicia sesión</a> para dejar tu opinión.
    </p>
<?php endif; ?>




    
</body>
</html>
