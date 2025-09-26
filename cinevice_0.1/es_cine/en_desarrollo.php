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

$params = [$peli_id];
$types = "i";

if ($puntuacion_filtro !== '' && is_numeric($puntuacion_filtro)) {
    $sql .= " AND o.puntuacion = ?";
    $params[] = intval($puntuacion_filtro);
    $types .= "i";
}

$sql .= " ORDER BY $orderBy";

$stmt = $conexion->prepare($sql);
if (count($params) > 1) {
    $stmt->bind_param($types, ...$params);
} else {
    $stmt->bind_param("i", $params[0]);
}
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $peli ? htmlspecialchars($peli["nombre"]) . ' - CineVice' : 'Película no encontrada - CineVice'; ?></title>
    <link href="../../src/output.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="Imágenes/C-logo.png">
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
</head>
<body class="min-h-screen transition-all duration-300 bg-gradient-to-br from-pink-100 to-blue-100 text-gray-900" id="body">
    <!-- Navigation Bar -->
    <nav class="shadow-lg transition-all duration-300 bg-white text-gray-900 sticky top-0 z-50" id="navbar">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <!-- Logo and Navigation -->
                <div class="flex items-center space-x-4">
                    <a href="./index.php" class="group">
                        <h1 class="text-3xl font-black bg-gradient-to-r from-pink-500 via-purple-500 to-blue-500 bg-clip-text text-transparent hover:scale-105 transition-transform duration-300">
                            CINE<span class="text-blue-400">VICE</span>
                        </h1>
                    </a>
                    <div class="hidden md:flex space-x-2 ml-8">
                        <a href="peliculas_series.php" class="px-4 py-2 rounded-lg transition-all duration-200 hover:bg-blue-500 hover:text-white">
                            Películas/Series
                        </a>
                        <a href="foros.php" class="px-4 py-2 rounded-lg transition-all duration-200 hover:bg-blue-500 hover:text-white">
                            Foros
                        </a>
                    </div>
                </div>

                <!-- Search and Genre Filter -->
                <div class="flex items-center space-x-4 flex-1 max-w-2xl mx-8">
                    <!-- Search Bar -->
                    <div class="relative flex-1">
                        <input 
                            type="text" 
                            id="searchInput" 
                            placeholder="Buscar películas o géneros..." 
                            class="w-full px-4 py-2 pl-10 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-200 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:placeholder-gray-400"
                        >
                        <i data-feather="search" class="w-5 h-5 absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <div id="suggestions" class="absolute top-full left-0 right-0 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg mt-1 hidden z-50"></div>
                    </div>

                    <!-- Genre Filter -->
                    <select 
                        id="generoDropdown" 
                        class="px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-200 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100 dark:focus:ring-blue-400"
                    >
                        <option value="">-- Ver por género --</option>
                        <?php
                        $genDropdown = mysqli_query($conexion, "SELECT * FROM generos");
                        while ($g = mysqli_fetch_assoc($genDropdown)) {
                            echo '<option value="' . strtolower($g['nombre']) . '">' . ucfirst($g['nombre']) . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <!-- Right Section -->
                <div class="flex items-center space-x-4">
                    <!-- Theme Toggle -->
                    <button id="themeToggle" class="p-2 rounded-lg transition-colors duration-200 hover:bg-gray-200 dark:hover:bg-gray-700">
                        <i data-feather="sun" class="w-5 h-5 hidden dark:block"></i>
                        <i data-feather="moon" class="w-5 h-5 block dark:hidden"></i>
                    </button>

                    <!-- User Section -->
                    <?php if (isset($_SESSION['usuario'])): ?>
                        <a href="./Páginas/perfil.php" class="flex items-center space-x-2 px-4 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200">
                            <div class="w-8 h-8 bg-gradient-to-r from-pink-500 to-blue-500 rounded-full flex items-center justify-center">
                                <span class="text-white font-bold text-sm"><?php echo strtoupper(substr($_SESSION['usuario']['nombre'], 0, 1)); ?></span>
                            </div>
                            <span class="hidden md:block font-medium"><?php echo htmlspecialchars($_SESSION['usuario']['nombre']); ?></span>
                        </a>
                        <a href="./Páginas/logout.php" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors duration-200">
                            Cerrar Sesión
                        </a>
                    <?php else: ?>
                        <a href="./Páginas/formularios.php?inicio" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors duration-200">
                            Iniciar Sesión
                        </a>
                        <a href="./Páginas/formularios.php?registro" class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors duration-200">
                            Registrarse
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Error Message -->
    <div id="mensaje-error" class="fixed top-20 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg transform translate-x-full transition-transform duration-300 z-50 opacity-0">
        <div class="flex items-center space-x-2">
            <i data-feather="alert-circle" class="w-5 h-5"></i>
            <span>Búsqueda no encontrada</span>
        </div>
    </div>

    <!-- Success Message -->
    <div id="mensaje-exito" class="fixed top-20 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg transform translate-x-full transition-transform duration-300 z-50 opacity-0">
        <div class="flex items-center space-x-2">
            <i data-feather="check-circle" class="w-5 h-5"></i>
            <span>Acción realizada con éxito</span>
        </div>
    </div>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 py-8">
        <?php if ($peli): ?>
            <!-- Movie Details -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl overflow-hidden mb-8 transition-all duration-300">
                <div class="md:flex">
                    <!-- Poster -->
                    <div class="md:w-1/3 lg:w-1/4">
                        <img src="Imágenes/<?php echo $peli["poster"]; ?>" 
                             alt="<?php echo htmlspecialchars($peli["nombre"]); ?>" 
                             class="w-full h-96 md:h-full object-cover">
                    </div>
                    
                    <!-- Info -->
                    <div class="md:w-2/3 lg:w-3/4 p-8">
                        <h1 class="text-4xl font-bold bg-gradient-to-r from-pink-500 via-purple-500 to-blue-500 bg-clip-text text-transparent mb-4">
                            <?php echo htmlspecialchars($peli["nombre"]); ?>
                        </h1>
                        
                        <p class="text-lg opacity-80 mb-6 leading-relaxed">
                            <?php echo htmlspecialchars($peli["descripcion"]); ?>
                        </p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div class="flex items-center space-x-2">
                                <i data-feather="calendar" class="w-5 h-5 text-blue-500"></i>
                                <span><strong>Emisión:</strong> <?php echo htmlspecialchars($peli["emision"]); ?></span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <i data-feather="clock" class="w-5 h-5 text-blue-500"></i>
                                <span><strong>Duración:</strong> <?php echo htmlspecialchars($peli["duracion"]); ?></span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <i data-feather="globe" class="w-5 h-5 text-blue-500"></i>
                                <span><strong>País:</strong> <?php echo htmlspecialchars($peli["pais"]); ?></span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <i data-feather="volume-2" class="w-5 h-5 text-blue-500"></i>
                                <span><strong>Idioma:</strong> <?php echo htmlspecialchars($peli["idioma"]); ?></span>
                            </div>
                            <?php if ($peli["episodios"]): ?>
                            <div class="flex items-center space-x-2">
                                <i data-feather="list" class="w-5 h-5 text-blue-500"></i>
                                <span><strong>Episodios:</strong> <?php echo htmlspecialchars($peli["episodios"]); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($generos)): ?>
                            <div class="mb-6">
                                <strong class="text-lg mb-3 block">Géneros:</strong>
                                <div class="flex flex-wrap gap-2">
                                    <?php foreach ($generos as $g): ?>
                                        <span class="px-3 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded-full text-sm font-medium">
                                            <?php echo htmlspecialchars($g); ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="flex items-center space-x-4">
                            <div class="flex items-center space-x-2">
                                <i data-feather="star" class="w-6 h-6 text-yellow-500 fill-current"></i>
                                <span class="text-2xl font-bold text-yellow-600"><?php echo $promedio; ?>/5</span>
                            </div>
                            <span class="text-sm opacity-70">(<?php echo $total_op; ?> opiniones)</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter Controls -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 mb-8 transition-all duration-300">
                <h2 class="text-xl font-bold mb-4 flex items-center space-x-2">
                    <i data-feather="filter" class="w-5 h-5 text-blue-500"></i>
                    <span>Filtrar Opiniones</span>
                </h2>
                
                <form method="GET" action="en_desarrollo.php" class="flex flex-wrap items-end gap-4">
                    <input type="hidden" name="peli_id" value="<?php echo $peli_id; ?>">
                    
                    <div>
                        <label for="orden" class="block text-sm font-medium mb-1">Ordenar por:</label>
                        <select name="orden" id="orden" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-200 dark:bg-gray-700 dark:text-white">
                            <option value="fecha" <?php if (($_GET['orden'] ?? '') === 'fecha') echo 'selected'; ?>>Más recientes</option>
                            <option value="likes" <?php if (($_GET['orden'] ?? '') === 'likes') echo 'selected'; ?>>Más likes</option>
                            <option value="dislikes" <?php if (($_GET['orden'] ?? '') === 'dislikes') echo 'selected'; ?>>Más dislikes</option>
                        </select>
                    </div>

                    <div>
                        <label for="puntuacion" class="block text-sm font-medium mb-1">Filtrar por estrellas:</label>
                        <select name="puntuacion" id="puntuacion" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-200 dark:bg-gray-700 dark:text-white">
                            <option value="">Todas</option>
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php if (($_GET['puntuacion'] ?? '') == $i) echo 'selected'; ?>>
                                    <?php echo $i; ?> estrella<?php echo $i > 1 ? 's' : ''; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <button type="submit" class="px-6 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors duration-200 flex items-center space-x-2">
                        <i data-feather="search" class="w-4 h-4"></i>
                        <span>Aplicar</span>
                    </button>
                </form>
            </div>

            <!-- Opinions Section -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 mb-8 transition-all duration-300">
                <h2 class="text-2xl font-bold mb-6 flex items-center space-x-2">
                    <i data-feather="message-circle" class="w-6 h-6 text-blue-500"></i>
                    <span>Opiniones de la comunidad</span>
                </h2>

                <?php if (!empty($opiniones)): ?>
                    <div class="space-y-6">
                        <?php foreach ($opiniones as $op): ?>
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-6 hover:shadow-md transition-shadow duration-300" data-opinion-id="<?php echo $op['op_id']; ?>">
                                <div class="flex justify-between items-start mb-4">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-gradient-to-r from-pink-500 to-blue-500 rounded-full flex items-center justify-center">
                                            <span class="text-white font-bold"><?php echo strtoupper(substr($op['nombre'], 0, 1)); ?></span>
                                        </div>
                                        <div>
                                            <strong class="text-lg"><?php echo htmlspecialchars($op['nombre']); ?></strong>
                                            <div class="flex items-center space-x-1 mt-1">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i data-feather="star" class="w-4 h-4 <?php echo $i <= $op['puntuacion'] ? 'text-yellow-400 fill-current' : 'text-gray-300'; ?>"></i>
                                                <?php endfor; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <span class="text-sm opacity-70"><?php echo date("d/m/Y H:i", strtotime($op['fecha'])); ?></span>
                                </div>

                                <p class="text-gray-700 dark:text-gray-300 mb-4 leading-relaxed">
                                    <?php echo nl2br(htmlspecialchars($op['contenido'])); ?>
                                </p>

                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-4">
                                        <?php if (isset($_SESSION['usuario'])): ?>
                                            <button onclick="toggleReaction(<?php echo $op['op_id']; ?>, 'like')" 
                                                    class="flex items-center space-x-2 px-3 py-2 rounded-lg hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors duration-200 like-btn"
                                                    id="like-btn-<?php echo $op['op_id']; ?>">
                                                <i data-feather="thumbs-up" class="w-4 h-4 text-green-600"></i>
                                                <span class="like-count"><?php echo $op['op_likes']; ?></span>
                                            </button>

                                            <button onclick="toggleReaction(<?php echo $op['op_id']; ?>, 'dislike')" 
                                                    class="flex items-center space-x-2 px-3 py-2 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors duration-200 dislike-btn"
                                                    id="dislike-btn-<?php echo $op['op_id']; ?>">
                                                <i data-feather="thumbs-down" class="w-4 h-4 text-red-600"></i>
                                                <span class="dislike-count"><?php echo $op['op_dislikes']; ?></span>
                                            </button>
                                        <?php else: ?>
                                            <div class="flex items-center space-x-4 opacity-50">
                                                <div class="flex items-center space-x-2">
                                                    <i data-feather="thumbs-up" class="w-4 h-4 text-green-600"></i>
                                                    <span><?php echo $op['op_likes']; ?></span>
                                                </div>
                                                <div class="flex items-center space-x-2">
                                                    <i data-feather="thumbs-down" class="w-4 h-4 text-red-600"></i>
                                                    <span><?php echo $op['op_dislikes']; ?></span>
                                                </div>
                                                <span class="text-sm text-gray-500">Inicia sesión para reaccionar</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <?php if (isset($_SESSION['usuario']) && $_SESSION['usuario']['id'] == $op['usu_id']): ?>
                                        <div class="flex items-center space-x-2">
                                            <a href="editar_opinion.php?op_id=<?php echo $op['op_id']; ?>&peli_id=<?php echo $peli_id; ?>" 
                                               class="flex items-center space-x-1 px-3 py-2 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors duration-200">
                                                <i data-feather="edit-2" class="w-4 h-4"></i>
                                                <span>Editar</span>
                                            </a>
                                            <form action="eliminar_opinion.php" method="POST" style="display:inline;" 
                                                  onsubmit="return confirm('¿Estás seguro de que querés eliminar esta opinión?');">
                                                <input type="hidden" name="op_id" value="<?php echo $op['op_id']; ?>">
                                                <input type="hidden" name="peli_id" value="<?php echo $peli_id; ?>">
                                                <button type="submit" class="flex items-center space-x-1 px-3 py-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors duration-200">
                                                    <i data-feather="trash-2" class="w-4 h-4"></i>
                                                    <span>Eliminar</span>
                                                </button>
                                            </form>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-12">
                        <i data-feather="message-square" class="w-16 h-16 mx-auto mb-4 text-gray-400"></i>
                        <h3 class="text-xl font-semibold text-gray-600 dark:text-gray-300 mb-2">No hay opiniones aún</h3>
                        <p class="text-gray-500 dark:text-gray-400">Sé el primero en compartir tu opinión sobre esta película</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Opinion Form -->
            <?php if (isset($_SESSION['usuario'])): ?>
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 transition-all duration-300">
                    <h3 class="text-xl font-bold mb-6 flex items-center space-x-2">
                        <i data-feather="edit-3" class="w-5 h-5 text-blue-500"></i>
                        <span>Dejar una opinión</span>
                    </h3>
                    
                    <form action="guardar_opinion.php" method="POST" class="space-y-4">
                        <input type="hidden" name="peli_id" value="<?php echo $peli_id; ?>">
                        <input type="hidden" name="usu_id" value="<?php echo $_SESSION['usuario']['id']; ?>">

                        <div>
                            <label for="puntuacion_form" class="block text-sm font-medium mb-2">Puntuación:</label>
                            <select name="puntuacion" id="puntuacion_form" required 
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-200 dark:bg-gray-700 dark:text-white">
                                <option value="">Seleccionar</option>
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <option value="<?php echo $i; ?>"><?php echo $i; ?> estrella<?php echo $i > 1 ? 's' : ''; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <div>
                            <label for="contenido" class="block text-sm font-medium mb-2">Tu opinión:</label>
                            <textarea name="contenido" id="contenido" rows="4" required
                                      placeholder="Escribe lo que pensás..." 
                                      class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-200 dark:bg-gray-700 dark:text-white resize-none"></textarea>
                        </div>

                        <button type="submit" class="w-full px-6 py-3 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors duration-200 flex items-center justify-center space-x-2">
                            <i data-feather="send" class="w-4 h-4"></i>
                            <span>Enviar opinión</span>
                        </button>
                    </form>
                </div>
            <?php else: ?>
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-8 text-center transition-all duration-300">
                    <i data-feather="user" class="w-16 h-16 mx-auto mb-4 text-gray-400"></i>
                    <h3 class="text-xl font-semibold mb-2">¿Querés dejar tu opinión?</h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">Inicia sesión para compartir tu experiencia con esta película</p>
                    <a href="Páginas/formularios.php?inicio" class="inline-flex items-center space-x-2 px-6 py-3 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors duration-200">
                        <i data-feather="log-in" class="w-4 h-4"></i>
                        <span>Iniciar Sesión</span>
                    </a>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-12 text-center transition-all duration-300">
                <i data-feather="film" class="w-24 h-24 mx-auto mb-6 text-gray-400"></i>
                <h1 class="text-3xl font-bold text-gray-600 dark:text-gray-300 mb-4">Película no encontrada</h1>
                <p class="text-gray-500 dark:text-gray-400 mb-8">Lo sentimos, la película que buscás no existe o ha sido eliminada.</p>
                <a href="peliculas_series.php" class="inline-flex items-center space-x-2 px-6 py-3 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors duration-200">
                    <i data-feather="arrow-left" class="w-4 h-4"></i>
                    <span>Volver a películas</span>
                </a>
            </div>
        <?php endif; ?>
    </main>

    <script>
        // Initialize Feather Icons
        feather.replace();

        // Theme Toggle Functionality
        const themeToggle = document.getElementById('themeToggle');
        const body = document.getElementById('body');
        const navbar = document.getElementById('navbar');

        // Check for saved theme preference
        const savedTheme = localStorage.getItem('theme') || 'light';
        if (savedTheme === 'dark') {
            enableDarkMode();
        } else {
            enableLightMode();
        }

        themeToggle.addEventListener('click', () => {
            if (body.classList.contains('dark')) {
                enableLightMode();
                localStorage.setItem('theme', 'light');
            } else {
                enableDarkMode();
                localStorage.setItem('theme', 'dark');
            }
            feather.replace();
        });

        function enableDarkMode() {
            body.className = 'min-h-screen transition-all duration-300 dark bg-gray-900 text-white';
            navbar.className = 'shadow-lg transition-all duration-300 bg-gray-800 text-white sticky top-0 z-50';
        }

        function enableLightMode() {
            body.className = 'min-h-screen transition-all duration-300 bg-gradient-to-br from-pink-100 to-blue-100 text-gray-900';
            navbar.className = 'shadow-lg transition-all duration-300 bg-white text-gray-900 sticky top-0 z-50';
        }

        // Search Functionality (similar to peliculas_series.php)
        const searchInput = document.getElementById('searchInput');
        const generoDropdown = document.getElementById('generoDropdown');
        const mensajeError = document.getElementById('mensaje-error');

        let searchTimeout;

        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                performSearch();
            }, 300);
        });

        generoDropdown.addEventListener('change', function() {
            performSearch();
        });

        function performSearch() {
            const searchTerm = searchInput.value.toLowerCase().trim();
            const selectedGenre = generoDropdown.value.toLowerCase();
            
            if (searchTerm || selectedGenre) {
                // Redirect to peliculas_series.php with search parameters
                let url = 'peliculas_series.php?';
                const params = new URLSearchParams();
                
                if (searchTerm) params.append('search', searchTerm);
                if (selectedGenre) params.append('genre', selectedGenre);
                
                window.location.href = url + params.toString();
            }
        }

        function showErrorMessage() {
            mensajeError.classList.remove('translate-x-full');
            mensajeError.classList.remove('opacity-0');
            setTimeout(() => {
                mensajeError.classList.add('translate-x-full');
                mensajeError.classList.add('opacity-0');
            }, 3000);
        }

        // Like/Dislike System - Client-side only with localStorage
        const userReactions = JSON.parse(localStorage.getItem('userReactions') || '{}');
        const userId = <?php echo isset($_SESSION['usuario']) ? $_SESSION['usuario']['id'] : 'null'; ?>;

        function toggleReaction(opinionId, action) {
            if (!userId) {
                alert('Debes iniciar sesión para reaccionar');
                return;
            }

            const reactionKey = `${userId}-${opinionId}`;
            const currentReaction = userReactions[reactionKey];
            
            const likeBtn = document.getElementById(`like-btn-${opinionId}`);
            const dislikeBtn = document.getElementById(`dislike-btn-${opinionId}`);
            const likeCount = likeBtn.querySelector('.like-count');
            const dislikeCount = dislikeBtn.querySelector('.dislike-count');
            
            let likeValue = parseInt(likeCount.textContent);
            let dislikeValue = parseInt(dislikeCount.textContent);

            // Remove previous reaction if exists
            if (currentReaction === 'like') {
                likeValue--;
                likeBtn.classList.remove('bg-green-100', 'dark:bg-green-900/50');
            } else if (currentReaction === 'dislike') {
                dislikeValue--;
                dislikeBtn.classList.remove('bg-red-100', 'dark:bg-red-900/50');
            }

            // Apply new reaction or remove if same
            if (currentReaction !== action) {
                userReactions[reactionKey] = action;
                
                if (action === 'like') {
                    likeValue++;
                    likeBtn.classList.add('bg-green-100', 'dark:bg-green-900/50');
                } else {
                    dislikeValue++;
                    dislikeBtn.classList.add('bg-red-100', 'dark:bg-red-900/50');
                }
            } else {
                delete userReactions[reactionKey];
            }

            // Update counts
            likeCount.textContent = likeValue;
            dislikeCount.textContent = dislikeValue;

            // Save to localStorage
            localStorage.setItem('userReactions', JSON.stringify(userReactions));

            // Show success message
            showSuccessMessage();
        }

        function showSuccessMessage() {
            const mensajeExito = document.getElementById('mensaje-exito');
            mensajeExito.classList.remove('translate-x-full');
            mensajeExito.classList.remove('opacity-0');
            setTimeout(() => {
                mensajeExito.classList.add('translate-x-full');
                mensajeExito.classList.add('opacity-0');
            }, 2000);
        }

        // Initialize reactions on page load
        document.addEventListener('DOMContentLoaded', function() {
            if (userId) {
                Object.keys(userReactions).forEach(key => {
                    const [uId, opId] = key.split('-');
                    if (parseInt(uId) === userId) {
                        const reaction = userReactions[key];
                        const btn = document.getElementById(`${reaction}-btn-${opId}`);
                        if (btn) {
                            if (reaction === 'like') {
                                btn.classList.add('bg-green-100', 'dark:bg-green-900/50');
                            } else {
                                btn.classList.add('bg-red-100', 'dark:bg-red-900/50');
                            }
                        }
                    }
                });
            }
        });
    </script>
</body>
</html>