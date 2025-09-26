<?php session_start(); ?>

<?php require_once("conexion.php"); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Películas y Series - CineVice</title>
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
                        <a href="peliculas_series.php" class="px-4 py-2 rounded-lg bg-blue-500 text-white transition-all duration-200">
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
                        <option value="" class="bg-white text-gray-900 dark:bg-gray-700 dark:text-gray-100">-- Ver por género --</option>
                        <?php
                        $genDropdown = mysqli_query($conexion, "SELECT * FROM generos");
                        while ($g = mysqli_fetch_assoc($genDropdown)) {
                            echo '<option value="' . strtolower($g['nombre']) . '" class="bg-white text-gray-900 dark:bg-gray-700 dark:text-gray-100">' . ucfirst($g['nombre']) . '</option>';
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

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 py-8" id="contenido">
        <!-- Page Header -->
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold bg-gradient-to-r from-pink-500 via-purple-500 to-blue-500 bg-clip-text text-transparent mb-4">
                Películas y Series
            </h1>
            <p class="text-lg opacity-80 max-w-2xl mx-auto">
                Descubre una amplia colección de películas y series organizadas por géneros. 
                Encuentra tu próxima obsesión cinematográfica.
            </p>
        </div>

        <!-- Movies and Series by Genre -->
        <?php
        $generos = mysqli_query($conexion, "SELECT * FROM generos");
        while ($gen = mysqli_fetch_assoc($generos)) {
            echo "<section class='mb-12' data-genre='" . strtolower($gen['nombre']) . "'>";
            echo "<div class='flex items-center space-x-3 mb-6'>";
            echo "<div class='w-1 h-8 bg-gradient-to-b from-pink-500 to-blue-500 rounded-full'></div>";
            echo "<h2 class='text-2xl font-bold bg-gradient-to-r from-pink-500 via-purple-500 to-blue-500 bg-clip-text text-transparent'>" . ucfirst($gen['nombre']) . "</h2>";
            echo "</div>";
            
            $gen_id = $gen['gen_id'];
            $pelis = mysqli_query($conexion, "
                SELECT p.peli_id, p.nombre, p.poster FROM pelis p 
                INNER JOIN pelis_generos pg ON p.peli_id = pg.peli_id 
                WHERE pg.gen_id = $gen_id
            ");
            
            echo "<div class='grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-6'>";
            while ($p = mysqli_fetch_assoc($pelis)) {
                echo "<div class='group cursor-pointer transform transition-all duration-300 hover:scale-105' data-movie='" . strtolower($p['nombre']) . "'>";
                echo "<a href='detalle.php?peli_id=" . $p['peli_id'] . "' class='block'>";
                echo "<div class='relative overflow-hidden rounded-xl shadow-lg group-hover:shadow-2xl transition-shadow duration-300'>";
                echo "<img src='Imágenes/" . $p['poster'] . "' alt='" . htmlspecialchars($p['nombre']) . "' class='w-full h-80 object-cover group-hover:scale-110 transition-transform duration-300'>";
                echo "<div class='absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300'></div>";
                echo "<div class='absolute bottom-0 left-0 right-0 p-4 text-white transform translate-y-full group-hover:translate-y-0 transition-transform duration-300'>";
                echo "<h3 class='font-bold text-sm mb-1'>" . htmlspecialchars($p['nombre']) . "</h3>";
                echo "<p class='text-xs opacity-80'>Ver detalles</p>";
                echo "</div>";
                echo "</div>";
                echo "</a>";
                echo "</div>";
            }
            echo "</div>";
            echo "</section>";
        }
        ?>

        <!-- Empty State (if no movies found after search) -->
        <div id="empty-state" class="text-center py-16 hidden">
            <i data-feather="film" class="w-16 h-16 mx-auto mb-4 text-gray-400"></i>
            <h3 class="text-2xl font-bold text-gray-600 dark:text-gray-300 mb-2">No se encontraron resultados</h3>
            <p class="text-gray-500 dark:text-gray-400 mb-6">Intenta con otros términos de búsqueda o explora por género</p>
            <button id="clear-search" class="px-6 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors duration-200">
                Ver todas las películas
            </button>
        </div>
    </main>

    <!-- Loading Indicator -->
    <div id="loading" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 flex items-center space-x-3">
            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-500"></div>
            <span class="text-gray-700 dark:text-gray-300">Buscando...</span>
        </div>
    </div>

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

        // Search Functionality
        const searchInput = document.getElementById('searchInput');
        const generoDropdown = document.getElementById('generoDropdown');
        const contenido = document.getElementById('contenido');
        const mensajeError = document.getElementById('mensaje-error');
        const emptyState = document.getElementById('empty-state');
        const clearSearchBtn = document.getElementById('clear-search');
        const loading = document.getElementById('loading');

        let searchTimeout;

        // Search input handler
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                performSearch();
            }, 300);
        });

        // Genre dropdown handler
        generoDropdown.addEventListener('change', function() {
            performSearch();
        });

        // Clear search button
        clearSearchBtn.addEventListener('click', function() {
            searchInput.value = '';
            generoDropdown.value = '';
            performSearch();
        });

        function performSearch() {
            const searchTerm = searchInput.value.toLowerCase().trim();
            const selectedGenre = generoDropdown.value.toLowerCase();
            
            const sections = document.querySelectorAll('section[data-genre]');
            let hasResults = false;

            sections.forEach(section => {
                const genreName = section.getAttribute('data-genre');
                const movies = section.querySelectorAll('[data-movie]');
                let sectionHasResults = false;

                // Check if genre matches filter
                const genreMatches = !selectedGenre || genreName.includes(selectedGenre);

                if (genreMatches) {
                    movies.forEach(movie => {
                        const movieName = movie.getAttribute('data-movie');
                        const movieMatches = !searchTerm || movieName.includes(searchTerm);

                        if (movieMatches) {
                            movie.style.display = 'block';
                            sectionHasResults = true;
                            hasResults = true;
                        } else {
                            movie.style.display = 'none';
                        }
                    });

                    section.style.display = sectionHasResults ? 'block' : 'none';
                } else {
                    section.style.display = 'none';
                }
            });

            // Show/hide empty state
            if (!hasResults && (searchTerm || selectedGenre)) {
                emptyState.classList.remove('hidden');
                showErrorMessage();
            } else {
                emptyState.classList.add('hidden');
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

        // Smooth scroll for better UX
        document.addEventListener('DOMContentLoaded', function() {
            const links = document.querySelectorAll('a[href^="#"]');
            links.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });
        });

        // Enhanced search suggestions (optional enhancement)
        searchInput.addEventListener('focus', function() {
            // Could implement autocomplete suggestions here
        });

        searchInput.addEventListener('blur', function() {
            // Hide suggestions after a delay to allow clicks
            setTimeout(() => {
                document.getElementById('suggestions').classList.add('hidden');
            }, 200);
        });
    </script>
</body>
</html>