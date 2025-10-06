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
    <style>
        .bg-cinevice-pink { background-color: #E879A5; }
        .bg-cinevice-blue { background-color: #7DD3FC; }
        .text-cinevice-pink { color: #E879A5; }
        .text-cinevice-blue { color: #7DD3FC; }
        .border-cinevice-pink { border-color: #E879A5; }
        .border-cinevice-blue { border-color: #7DD3FC; }
        .hover\:bg-cinevice-pink:hover { background-color: #E879A5; }
        .hover\:bg-cinevice-blue:hover { background-color: #7DD3FC; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Navbar -->
    <nav class="bg-white shadow-lg border-b-2 border-cinevice-pink">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="../../index.php" class="flex items-center space-x-2">
                        <img src="./Imágenes/cine-vice-navbar.png" alt="CineVice" class="h-10">
                    </a>
                </div>

                <!-- Buscador -->
                <div class="flex-1 max-w-lg mx-8">
                    <div class="relative">
                        <input type="text" 
                               id="searchInput" 
                               placeholder="Buscar foros..." 
                               value="<?= htmlspecialchars($search) ?>"
                               class="w-full px-4 py-2 pl-10 pr-4 border-2 border-cinevice-pink rounded-full focus:outline-none focus:ring-2 focus:ring-cinevice-blue">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <!-- Sugerencias de búsqueda -->
                        <div id="searchSuggestions" class="absolute z-10 w-full bg-white border border-gray-300 rounded-md mt-1 hidden shadow-lg max-h-60 overflow-y-auto"></div>
                    </div>
                </div>

                <!-- Usuario -->
                <div class="flex items-center space-x-4">
                    <?php if (isset($_SESSION['usuario'])): ?>
                        <span class="text-gray-700">Hola, 
                            <a href="perfil.php" class="text-cinevice-pink hover:text-cinevice-blue font-semibold">
                                <?= htmlspecialchars($_SESSION['usuario']['nombre']) ?>
                            </a>
                        </span>
                        <a href="logout.php" class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600 transition">
                            Cerrar Sesión
                        </a>
                    <?php else: ?>
                        <a href="Páginas/formularios.php?inicio" class="text-cinevice-pink hover:text-cinevice-blue">
                            Iniciar Sesión
                        </a>
                        <a href="Páginas/formularios.php?registro" class="bg-cinevice-pink text-white px-4 py-2 rounded-md hover:bg-cinevice-blue transition">
                            Registrarse
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-4xl font-bold text-gray-900 mb-2">
                    Foros de <span class="text-cinevice-pink">Cine</span><span class="text-cinevice-blue">Vice</span>
                </h1>
                <p class="text-gray-600">Discute tus películas y series favoritas con la comunidad</p>
            </div>
            
            <?php if (isset($_SESSION['usuario'])): ?>
                <button onclick="openCreateModal()" class="bg-cinevice-pink text-white px-6 py-3 rounded-lg hover:bg-cinevice-blue transition duration-300 flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Crear Foro</span>
                </button>
            <?php endif; ?>
        </div>

        <!-- Filtros -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Filtrar por género</h3>
            <div class="flex flex-wrap gap-2">
                <a href="?<?= http_build_query(array_filter(['search' => $search])) ?>" 
                   class="<?= $genero_filter == 0 ? 'bg-cinevice-pink text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' ?> px-4 py-2 rounded-full transition">
                    Todos
                </a>
                <?php while ($genero = $generos_result->fetch_assoc()): ?>
                    <a href="?<?= http_build_query(array_filter(['search' => $search, 'genero' => $genero['gen_id']])) ?>" 
                       class="<?= $genero_filter == $genero['gen_id'] ? 'bg-cinevice-blue text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' ?> px-4 py-2 rounded-full transition">
                        <?= htmlspecialchars($genero['nombre']) ?>
                    </a>
                <?php endwhile; ?>
            </div>
        </div>

        <!-- Lista de Foros -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php if ($foros_result->num_rows > 0): ?>
                <?php while ($foro = $foros_result->fetch_assoc()): ?>
                    <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition duration-300 overflow-hidden">
                        <?php if (!empty($foro['imagen'])): ?>
                            <img src="./uploads/foros/<?= htmlspecialchars($foro['imagen']) ?>" 
                                 alt="Imagen del foro" 
                                 class="w-full h-48 object-cover">
                        <?php else: ?>
                            <div class="w-full h-48 bg-gradient-to-br from-cinevice-pink to-cinevice-blue flex items-center justify-center">
                                <svg class="w-16 h-16 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                </svg>
                            </div>
                        <?php endif; ?>
                        
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="text-xl font-semibold text-gray-900 truncate">
                                    <?= htmlspecialchars($foro['nombre']) ?>
                                </h3>
                                <?php if ($foro['genero_nombre']): ?>
                                    <span class="bg-cinevice-blue text-white text-xs px-2 py-1 rounded-full">
                                        <?= htmlspecialchars($foro['genero_nombre']) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <p class="text-gray-600 text-sm mb-4 line-clamp-3">
                                <?= htmlspecialchars($foro['descripcion']) ?>
                            </p>
                            
                            <div class="flex items-center justify-between text-sm text-gray-500 mb-4">
                                <span>Por <?= htmlspecialchars($foro['usuario_nombre']) ?></span>
                                <span><?= date('d/m/Y', strtotime($foro['creacion'])) ?></span>
                            </div>
                            
                            <div class="flex items-center justify-between">
                                <span class="flex items-center text-sm text-gray-500">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                    </svg>
                                    <?= $foro['total_comentarios'] ?> comentarios
                                </span>
                                
                                <a href="foro_detalle.php?id=<?= $foro['foro_id'] ?>" 
                                   class="bg-cinevice-pink text-white px-4 py-2 rounded-md hover:bg-cinevice-blue transition">
                                    Entrar al Foro
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-span-full text-center py-12">
                    <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">No hay foros disponibles</h3>
                    <p class="text-gray-600">
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
        <div class="bg-white rounded-lg max-w-md w-full p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-bold text-gray-900">Crear Nuevo Foro</h2>
                <button onclick="closeCreateModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <form action="crear_foro.php" method="POST" enctype="multipart/form-data">
                <div class="space-y-4">
                    <div>
                        <label for="nombre" class="block text-sm font-medium text-gray-700 mb-1">Nombre del Foro</label>
                        <input type="text" id="nombre" name="nombre" required maxlength="50"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-cinevice-pink">
                    </div>
                    
                    <div>
                        <label for="genero_id" class="block text-sm font-medium text-gray-700 mb-1">Género</label>
                        <select id="genero_id" name="genero_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-cinevice-pink">
                            <option value="">Seleccionar género (opcional)</option>
                            <?php
                            $generos_result->data_seek(0);
                            while ($genero = $generos_result->fetch_assoc()): ?>
                                <option value="<?= $genero['gen_id'] ?>"><?= htmlspecialchars($genero['nombre']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label for="descripcion" class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                        <textarea id="descripcion" name="descripcion" required maxlength="500" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-cinevice-pink"></textarea>
                    </div>
                    
                    <div>
                        <label for="imagen" class="block text-sm font-medium text-gray-700 mb-1">Imagen (opcional)</label>
                        <input type="file" id="imagen" name="imagen" accept="image/*"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-cinevice-pink">
                    </div>
                </div>
                
                <div class="flex space-x-3 mt-6">
                    <button type="button" onclick="closeCreateModal()" 
                            class="flex-1 px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition">
                        Cancelar
                    </button>
                    <button type="submit" 
                            class="flex-1 px-4 py-2 bg-cinevice-pink text-white rounded-md hover:bg-cinevice-blue transition">
                        Crear Foro
                    </button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <script>
        // Funciones del modal
        function openCreateModal() {
            document.getElementById('createModal').classList.remove('hidden');
        }
        
        function closeCreateModal() {
            document.getElementById('createModal').classList.add('hidden');
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
                                    `<div class="px-4 py-2 hover:bg-gray-100 cursor-pointer border-b" onclick="selectSuggestion('${foro.nombre}')">${foro.nombre}</div>`
                                ).join('');
                                searchSuggestions.classList.remove('hidden');
                            } else {
                                searchSuggestions.classList.add('hidden');
                            }
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

        // Ocultar sugerencias al hacer click fuera
        document.addEventListener('click', function(event) {
            if (!searchInput.contains(event.target) && !searchSuggestions.contains(event.target)) {
                searchSuggestions.classList.add('hidden');
            }
        });

        // Enviar búsqueda al presionar Enter
        searchInput.addEventListener('keypress', function(event) {
            if (event.key === 'Enter') {
                window.location.href = `?search=${encodeURIComponent(this.value)}`;
            }
        });
    </script>
</body>
</html>