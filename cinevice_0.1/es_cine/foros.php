<?php
session_start();
require_once("conexion.php");

// Obtener géneros para el filtro
$generos_query = "SELECT * FROM generos ORDER BY nombre";
$generos_result = $conexion->query($generos_query);

// Búsqueda y filtros
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$genero_filter = isset($_GET['genero']) ? (int)$_GET['genero'] : 0;

// Construir consulta de foros con filtros
$foros_query = "SELECT f.*, u.nombre as usuario_nombre, g.nombre as genero_nombre,
                       (SELECT COUNT(*) FROM comentarios c WHERE c.foro_id = f.foro_id AND c.est_id = 1) as total_comentarios
                FROM foros f 
                LEFT JOIN usuarios u ON f.usu_id = u.usu_id
                LEFT JOIN generos g ON f.genero_id = g.gen_id
                WHERE f.est_id = 1";

$params = [];
$types = "";

if (!empty($search)) {
    $foros_query .= " AND f.nombre LIKE ?";
    $params[] = "%" . $search . "%";
    $types .= "s";
}

if ($genero_filter > 0) {
    $foros_query .= " AND f.genero_id = ?";
    $params[] = $genero_filter;
    $types .= "i";
}

$foros_query .= " ORDER BY f.creacion DESC";

$stmt = $conexion->prepare($foros_query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$foros_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Foros - CineVice</title>
    <link href="../../src/output.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="./Imágenes/c-logo.png">
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
</head>
<body class="min-h-screen transition-all duration-300" id="body">
    <!-- Navbar -->
    <nav class="shadow-lg transition-all duration-300 border-b-2" id="navbar">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <div class="flex items-center space-x-4">
                    <a href="../../index.php" class="group">
                        <h1 class="text-3xl font-black bg-gradient-to-r from-pink-500 via-purple-500 to-blue-500 bg-clip-text text-transparent hover:scale-105 transition-transform duration-300">
                            CINE<span class="text-blue-400">VICE</span>
                        </h1>
                    </a>
                    <div class="hidden md:flex space-x-2 ml-8">
                        <a href="peliculas_series.php" class="px-4 py-2 rounded-lg transition-all duration-200 hover:bg-blue-500 hover:text-white">
                            Películas/Series
                        </a>
                        <a href="foros.php" class="px-4 py-2 rounded-lg transition-all duration-200 bg-blue-500 text-white">
                            Foros
                        </a>
                    </div>
                </div>

                <!-- Buscador -->
                <div class="flex-1 max-w-lg mx-8">
                    <div class="relative">
                        <input type="text" 
                               id="searchInput" 
                               placeholder="Buscar foros..." 
                               value="<?= htmlspecialchars($search) ?>"
                               class="w-full px-4 py-2 pl-10 pr-4 border-2 rounded-full focus:outline-none focus:ring-2 transition-colors duration-200" style="border-color: #E879A5;">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center">
                            <i data-feather="search" class="w-5 h-5 text-gray-400"></i>
                        </div>
                        <!-- Sugerencias de búsqueda -->
                        <div id="searchSuggestions" class="absolute z-10 w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md mt-1 hidden shadow-lg max-h-60 overflow-y-auto"></div>
                    </div>
                </div>

                <!-- Usuario -->
                <div class="flex items-center space-x-4">
                    <!-- Theme Toggle -->
                    <button id="themeToggle" class="p-2 rounded-lg transition-colors duration-200 hover:bg-gray-200 dark:hover:bg-gray-700">
                        <i data-feather="sun" class="w-5 h-5 hidden dark:block"></i>
                        <i data-feather="moon" class="w-5 h-5 block dark:hidden"></i>
                    </button>

                    <?php if (isset($_SESSION['usuario'])): ?>
                        <a href="Páginas/perfil.php" class="font-semibold hover:text-blue-500 transition-colors duration-200">
                            Hola, <?= htmlspecialchars($_SESSION['usuario']['nombre']) ?>
                        </a>
                        <a href="logout.php" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors duration-200">
                            Cerrar Sesión
                        </a>
                    <?php else: ?>
                        <a href="Páginas/formularios.php?inicio" class="hover:text-blue-500 transition-colors duration-200">
                            Iniciar Sesión
                        </a>
                        <a href="Páginas/formularios.php?registro" class="px-4 py-2 bg-gradient-to-r from-pink-500 to-blue-500 text-white rounded-lg hover:opacity-90 transition-opacity duration-200">
                            Registrarse
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Mensajes de éxito/error -->
        <?php if (isset($_GET['deleted'])): ?>
            <?php if ($_GET['deleted'] == 'success'): ?>
                <div class="mb-6 p-4 bg-green-100 dark:bg-green-900 border border-green-400 dark:border-green-600 text-green-700 dark:text-green-200 rounded-lg flex items-center space-x-2">
                    <i data-feather="check-circle" class="w-5 h-5"></i>
                    <span>Foro eliminado correctamente</span>
                </div>
            <?php else: ?>
                <div class="mb-6 p-4 bg-red-100 dark:bg-red-900 border border-red-400 dark:border-red-600 text-red-700 dark:text-red-200 rounded-lg flex items-center space-x-2">
                    <i data-feather="alert-circle" class="w-5 h-5"></i>
                    <span>Error al eliminar el foro</span>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-4xl font-bold mb-2">
                    Foros de <span class="bg-gradient-to-r from-pink-500 to-blue-500 bg-clip-text text-transparent">CineVice</span>
                </h1>
                <p class="opacity-70">Discute tus películas y series favoritas con la comunidad</p>
            </div>
            
            <?php if (isset($_SESSION['usuario'])): ?>
                <button onclick="openCreateModal()" class="px-6 py-3 bg-gradient-to-r from-pink-500 to-blue-500 text-white rounded-lg hover:opacity-90 transition-opacity duration-300 flex items-center space-x-2 shadow-lg hover:shadow-xl">
                    <i data-feather="plus" class="w-5 h-5"></i>
                    <span>Crear Foro</span>
                </button>
            <?php endif; ?>
        </div>

        <!-- Filtros -->
        <div class="rounded-lg shadow-md p-6 mb-8 transition-all duration-300" id="filtersCard">
            <h3 class="text-lg font-semibold mb-4">Filtrar por género</h3>
            <div class="flex flex-wrap gap-2">
                <a href="?<?= http_build_query(array_filter(['search' => $search])) ?>" 
                   class="<?= $genero_filter == 0 ? 'bg-gradient-to-r from-pink-500 to-blue-500 text-white' : 'bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600' ?> px-4 py-2 rounded-full transition-all duration-200">
                    Todos
                </a>
                <?php while ($genero = $generos_result->fetch_assoc()): ?>
                    <a href="?<?= http_build_query(array_filter(['search' => $search, 'genero' => $genero['gen_id']])) ?>" 
                       class="<?= $genero_filter == $genero['gen_id'] ? 'bg-gradient-to-r from-pink-500 to-blue-500 text-white' : 'bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600' ?> px-4 py-2 rounded-full transition-all duration-200">
                        <?= htmlspecialchars($genero['nombre']) ?>
                    </a>
                <?php endwhile; ?>
            </div>
        </div>

        <!-- Lista de Foros -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php if ($foros_result->num_rows > 0): ?>
                <?php while ($foro = $foros_result->fetch_assoc()): ?>
                    <div class="rounded-lg shadow-md hover:shadow-lg transition-all duration-300 overflow-hidden" id="foroCard">
                        <?php if (!empty($foro['imagen'])): ?>
                            <img src="./uploads/foros/<?= htmlspecialchars($foro['imagen']) ?>" 
                                 alt="Imagen del foro" 
                                 class="w-full h-48 object-cover">
                        <?php else: ?>
                            <div class="w-full h-48 bg-gradient-to-br from-pink-500 via-purple-500 to-blue-500 flex items-center justify-center">
                                <i data-feather="message-circle" class="w-16 h-16 text-white"></i>
                            </div>
                        <?php endif; ?>
                        
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="text-xl font-semibold truncate flex-1">
                                    <?= htmlspecialchars($foro['nombre']) ?>
                                </h3>
                                <?php if ($foro['genero_nombre']): ?>
                                    <span class="bg-blue-500 text-white text-xs px-2 py-1 rounded-full ml-2">
                                        <?= htmlspecialchars($foro['genero_nombre']) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <p class="text-sm mb-4 line-clamp-3 opacity-80">
                                <?= htmlspecialchars($foro['descripcion']) ?>
                            </p>
                            
                            <div class="flex items-center justify-between text-sm opacity-70 mb-4">
                                <span>Por <?= htmlspecialchars($foro['usuario_nombre']) ?></span>
                                <span><?= date('d/m/Y', strtotime($foro['creacion'])) ?></span>
                            </div>
                            
                            <div class="flex items-center justify-between">
                                <span class="flex items-center text-sm opacity-70">
                                    <i data-feather="message-square" class="w-4 h-4 mr-1"></i>
                                    <?= $foro['total_comentarios'] ?> comentarios
                                </span>
                                
                                <div class="flex space-x-2">
                                    <a href="foro_detalle.php?id=<?= $foro['foro_id'] ?>" 
                                       class="px-4 py-2 bg-gradient-to-r from-pink-500 to-blue-500 text-white rounded-md hover:opacity-90 transition-opacity duration-200 flex items-center space-x-1">
                                        <span>Entrar</span>
                                        <i data-feather="arrow-right" class="w-4 h-4"></i>
                                    </a>
                                    
                                    <?php if (isset($_SESSION['usuario']) && $_SESSION['usuario']['id'] == $foro['usu_id']): ?>
                                        <button onclick="confirmDelete(<?= $foro['foro_id'] ?>, '<?= htmlspecialchars(addslashes($foro['nombre'])) ?>')" 
                                                class="px-3 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition-colors duration-200 flex items-center justify-center"
                                                title="Eliminar foro">
                                            <i data-feather="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-span-full text-center py-12">
                    <i data-feather="message-circle" class="w-16 h-16 mx-auto mb-4 opacity-50"></i>
                    <h3 class="text-xl font-semibold mb-2">No hay foros disponibles</h3>
                    <p class="opacity-70">
                        <?php if (empty($search) && $genero_filter == 0): ?>
                            Sé el primero en crear un foro para discutir películas y series.
                        <?php else: ?>
                            No se encontraron foros que coincidan con tu búsqueda.
                        <?php endif; ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal Crear Foro -->
    <?php if (isset($_SESSION['usuario'])): ?>
    <div id="createModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
        <div class="rounded-lg max-w-md w-full p-6 transition-all duration-300" id="modalCard">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-bold">Crear Nuevo Foro</h2>
                <button onclick="closeCreateModal()" class="hover:opacity-70 transition-opacity duration-200">
                    <i data-feather="x" class="w-6 h-6"></i>
                </button>
            </div>
            
            <form action="crear_foro.php" method="POST" enctype="multipart/form-data">
                <div class="space-y-4">
                    <div>
                        <label for="nombre" class="block text-sm font-medium mb-1">Nombre del Foro</label>
                        <input type="text" id="nombre" name="nombre" required maxlength="50"
                               class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-pink-500 transition-all duration-200 dark:bg-gray-700 dark:border-gray-600">
                    </div>
                    
                    <div>
                        <label for="genero_id" class="block text-sm font-medium mb-1">Género</label>
                        <select id="genero_id" name="genero_id" class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-pink-500 transition-all duration-200 dark:bg-gray-700 dark:border-gray-600">
                            <option value="">Seleccionar género (opcional)</option>
                            <?php
                            $generos_result->data_seek(0);
                            while ($genero = $generos_result->fetch_assoc()): ?>
                                <option value="<?= $genero['gen_id'] ?>"><?= htmlspecialchars($genero['nombre']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label for="descripcion" class="block text-sm font-medium mb-1">Descripción</label>
                        <textarea id="descripcion" name="descripcion" required maxlength="500" rows="3"
                                  class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-pink-500 transition-all duration-200 dark:bg-gray-700 dark:border-gray-600"></textarea>
                    </div>
                    
                    <div>
                        <label for="imagen" class="block text-sm font-medium mb-1">Imagen (opcional)</label>
                        <input type="file" id="imagen" name="imagen" accept="image/*"
                               class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-pink-500 transition-all duration-200 dark:bg-gray-700 dark:border-gray-600">
                    </div>
                </div>
                
                <div class="flex space-x-3 mt-6">
                    <button type="button" onclick="closeCreateModal()" 
                            class="flex-1 px-4 py-2 border rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200">
                        Cancelar
                    </button>
                    <button type="submit" 
                            class="flex-1 px-4 py-2 bg-gradient-to-r from-pink-500 to-blue-500 text-white rounded-md hover:opacity-90 transition-opacity duration-200">
                        Crear Foro
                    </button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <!-- Modal Confirmar Eliminación -->
    <div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
        <div class="rounded-lg max-w-md w-full p-6 transition-all duration-300" id="deleteModalCard">
            <div class="flex items-center space-x-3 mb-4">
                <i data-feather="alert-triangle" class="w-6 h-6 text-red-500"></i>
                <h2 class="text-2xl font-bold">Confirmar Eliminación</h2>
            </div>
            
            <p class="mb-6 opacity-80">¿Estás seguro de que deseas eliminar el foro "<span id="foroNombre" class="font-semibold"></span>"? Esta acción no se puede deshacer.</p>
            
            <div class="flex space-x-3">
                <button onclick="closeDeleteModal()" 
                        class="flex-1 px-4 py-2 border rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200">
                    Cancelar
                </button>
                <button onclick="deleteForo()" 
                        class="flex-1 px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition-colors duration-200">
                    Eliminar
                </button>
            </div>
        </div>
    </div>

    <script>
        // Initialize Feather Icons
        feather.replace();

        // Theme Toggle
        const themeToggle = document.getElementById('themeToggle');
        const body = document.getElementById('body');
        const navbar = document.getElementById('navbar');
        const filtersCard = document.getElementById('filtersCard');
        const modalCard = document.getElementById('modalCard');
        const deleteModalCard = document.getElementById('deleteModalCard');

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
            navbar.className = 'shadow-lg transition-all duration-300 border-b-2 bg-gray-800 text-white border-gray-700';
            filtersCard.className = 'rounded-lg shadow-md p-6 mb-8 transition-all duration-300 bg-gray-800 text-white';
            modalCard.className = 'rounded-lg max-w-md w-full p-6 transition-all duration-300 bg-gray-800 text-white';
            deleteModalCard.className = 'rounded-lg max-w-md w-full p-6 transition-all duration-300 bg-gray-800 text-white';
            
            document.querySelectorAll('#foroCard').forEach(card => {
                card.className = 'rounded-lg shadow-md hover:shadow-lg transition-all duration-300 overflow-hidden bg-gray-800 text-white';
            });
        }

        function enableLightMode() {
            body.className = 'min-h-screen transition-all duration-300 bg-gradient-to-br from-pink-100 to-blue-100 text-gray-900';
            navbar.className = 'shadow-lg transition-all duration-300 border-b-2 bg-white text-gray-900 border-pink-300';
            filtersCard.className = 'rounded-lg shadow-md p-6 mb-8 transition-all duration-300 bg-white text-gray-900';
            modalCard.className = 'rounded-lg max-w-md w-full p-6 transition-all duration-300 bg-white text-gray-900';
            deleteModalCard.className = 'rounded-lg max-w-md w-full p-6 transition-all duration-300 bg-white text-gray-900';
            
            document.querySelectorAll('#foroCard').forEach(card => {
                card.className = 'rounded-lg shadow-md hover:shadow-lg transition-all duration-300 overflow-hidden bg-white text-gray-900';
            });
        }

        // Funciones del modal crear
        function openCreateModal() {
            document.getElementById('createModal').classList.remove('hidden');
        }
        
        function closeCreateModal() {
            document.getElementById('createModal').classList.add('hidden');
        }

        // Funciones del modal eliminar
        let foroToDelete = null;

        function confirmDelete(foroId, foroNombre) {
            foroToDelete = foroId;
            document.getElementById('foroNombre').textContent = foroNombre;
            document.getElementById('deleteModal').classList.remove('hidden');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
            foroToDelete = null;
        }

        function deleteForo() {
            if (foroToDelete) {
                window.location.href = `eliminar_foro.php?id=${foroToDelete}`;
            }
        }

        // Búsqueda con sugerencias
        const searchInput = document.getElementById('searchInput');
        const searchSuggestions = document.getElementById('searchSuggestions');
        let searchTimeout;

        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            
            if (query.length > 0) {
                searchTimeout = setTimeout(() => {
                    fetch(`buscar_foros.php?q=${encodeURIComponent(query)}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.length > 0) {
                                searchSuggestions.innerHTML = data.map(foro => 
                                    `<div class="px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer border-b dark:border-gray-600" onclick="selectSuggestion('${foro.nombre.replace(/'/g, "\\'")}')">${foro.nombre}</div>`
                                ).join('');
                                searchSuggestions.classList.remove('hidden');
                            } else {
                                searchSuggestions.classList.add('hidden');
                            }
                        })
                        .catch(error => {
                            console.error('Error al buscar foros:', error);
                            searchSuggestions.classList.add('hidden');
                        });
                }, 300);
            } else {
                searchSuggestions.classList.add('hidden');
            }
        });

        function selectSuggestion(name) {
            searchInput.value = name;
            searchSuggestions.classList.add('hidden');
            window.location.href = `?search=${encodeURIComponent(name)}`;
        }

        document.addEventListener('click', function(event) {
            if (!searchInput.contains(event.target) && !searchSuggestions.contains(event.target)) {
                searchSuggestions.classList.add('hidden');
            }
        });

        searchInput.addEventListener('keypress', function(event) {
            if (event.key === 'Enter') {
                window.location.href = `?search=${encodeURIComponent(this.value)}`;
            }
        });

        // Cerrar modales con ESC
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeCreateModal();
                closeDeleteModal();
            }
        });

        // Auto-cerrar mensajes de éxito/error después de 5 segundos
        setTimeout(() => {
            const alerts = document.querySelectorAll('.bg-green-100, .bg-red-100');
            alerts.forEach(alert => {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);

        // Refresh icons after theme change and page load
        feather.replace();
    </script>
</body>
</html>