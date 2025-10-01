<?php 
session_start(); 
require_once("conexion.php");
require_once("./Páginas/get_user_avatar.php");

// Obtener películas por género
$proximos_lanzamientos = [];
$pelis_populares = [];
$series_populares = [];

// Próximos lanzamientos (genero_id = 6)
$query_proximos = "SELECT p.peli_id, p.nombre, p.poster FROM pelis p 
                   INNER JOIN pelis_generos pg ON p.peli_id = pg.peli_id 
                   WHERE pg.gen_id = 6 AND p.est_id = 1 LIMIT 5";
$result_proximos = mysqli_query($conexion, $query_proximos);
while ($peli = mysqli_fetch_assoc($result_proximos)) {
    $proximos_lanzamientos[] = $peli;
}

// Películas más populares (genero_id = 7)
$query_populares = "SELECT p.peli_id, p.nombre, p.poster FROM pelis p 
                    INNER JOIN pelis_generos pg ON p.peli_id = pg.peli_id 
                    WHERE pg.gen_id = 7 AND p.est_id = 1 LIMIT 5";
$result_populares = mysqli_query($conexion, $query_populares);
while ($peli = mysqli_fetch_assoc($result_populares)) {
    $pelis_populares[] = $peli;
}

// Series más populares (genero_id = 8)
$query_series = "SELECT p.peli_id, p.nombre, p.poster FROM pelis p 
                 INNER JOIN pelis_generos pg ON p.peli_id = pg.peli_id 
                 WHERE pg.gen_id = 8 AND p.est_id = 1 LIMIT 5";
$result_series = mysqli_query($conexion, $query_series);
while ($peli = mysqli_fetch_assoc($result_series)) {
    $series_populares[] = $peli;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CineVice</title>
    <link href="../../src/output.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="./Imágenes/C-logo.png">
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
</head>

<body class="min-h-screen transition-all duration-300" id="body">
    <!-- Navbar -->
    <header class="sticky top-0 z-50 shadow-lg transition-all duration-300" id="navbar">
        <div class="container mx-auto px-4 py-3">
            <div class="flex items-center justify-between">
                <!-- Logo -->
                <div class="flex items-center space-x-4">
                    <a href="./index.php" class="group">
                        <h1 class="text-3xl font-black bg-gradient-to-r from-pink-500 via-purple-500 to-blue-500 bg-clip-text text-transparent hover:scale-105 transition-transform duration-300">
                            CINE<span class="text-blue-400">VICE</span>
                        </h1>
                    </a>
                    
                    <!-- Navigation Links -->
                    <nav class="hidden md:flex space-x-2">
                        <a href="./peliculas_series.php" class="px-4 py-2 rounded-lg transition-all duration-200 hover:bg-blue-500 hover:text-white" id="navLink1">
                            Películas/Series
                        </a>
                        <a href="./foros.php" class="px-4 py-2 rounded-lg transition-all duration-200 hover:bg-blue-500 hover:text-white" id="navLink2">
                            Foros
                        </a>
                    </nav>
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
                        <?php 
                        $user_avatar = getUserAvatar($_SESSION['usuario']['id'], $conexion);
                        ?>
                        <div class="flex items-center space-x-3">
                            <img src="<?php echo $user_avatar; ?>" alt="Avatar" class="w-8 h-8 rounded-full border-2 border-pink-300 dark:border-purple-400 object-cover">
                            <a href="./Páginas/perfil.php" class="hidden md:block font-medium hover:text-pink-500 transition-colors duration-200">
                                <?php echo htmlspecialchars($_SESSION['usuario']['nombre']); ?>
                            </a>
                            <a href="./Páginas/logout.php" class="px-4 py-2 bg-gradient-to-r from-red-500 to-pink-500 text-white rounded-lg hover:from-red-600 hover:to-pink-600 transition-all duration-200 font-medium">
                                Cerrar sesión
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="flex space-x-3">
                            <a href="./Páginas/formularios.php?inicio" class="px-4 py-2 bg-gradient-to-r from-blue-500 to-purple-500 text-white rounded-lg hover:from-blue-600 hover:to-purple-600 transition-all duration-200 font-medium">
                                Iniciar sesión
                            </a>
                            <a href="./Páginas/formularios.php?registro" class="px-4 py-2 bg-gradient-to-r from-pink-500 to-purple-500 text-white rounded-lg hover:from-pink-600 hover:to-purple-600 transition-all duration-200 font-medium">
                                Registrarse
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1">
        <!-- Carousel Section -->
        <section class="py-8">
            <div class="container mx-auto px-4">
                <div class="max-w-5xl mx-auto relative rounded-3xl overflow-hidden shadow-2xl transition-all duration-300" id="carouselContainer">
                    <div class="carousel" id="carousel">
                        <div class="slide active relative h-96 md:h-[500px]">
                            <div class="absolute inset-0 bg-gradient-to-r from-black/60 to-transparent z-10"></div>
                            <div class="slide-info absolute left-8 top-1/2 transform -translate-y-1/2 z-20 text-white max-w-md">
                                <div class="slide-info-img-container mb-4">
                                    <img src="./Imágenes/Carrousel/stitch_titulo.png" alt="stitch titulo" class="max-w-xs">
                                </div>
                                <p class="text-lg leading-relaxed">Una solitaria niña hawaiana se hace amiga de un extraterrestre fugitivo y ayuda a sanar a su fragmentada familia.</p>
                            </div>
                            <img src="./Imágenes/Carrousel/stitch_carrousel.webp" alt="Pelicula 1" class="w-full h-full object-cover">
                        </div>

                        <div class="slide relative h-96 md:h-[500px]">
                            <div class="absolute inset-0 bg-gradient-to-r from-black/60 to-transparent z-10"></div>
                            <div class="slide-info absolute left-8 top-1/2 transform -translate-y-1/2 z-20 text-white max-w-md">
                                <div class="slide-info-img-container mb-4">
                                    <img src="./Imágenes/Carrousel/minecraft_titulo.png" alt="minecraft titulo" class="max-w-xs">
                                </div>
                                <p class="text-lg leading-relaxed">El malvado dragón de Ender está en su camino a la destrucción, haciendo que una chica joven y su grupo de aventureros amigos intenten salvar Overworld.</p>
                            </div>
                            <img src="./Imágenes/Carrousel/minecraft_carrousel.webp" alt="Pelicula 2" class="w-full h-full object-cover">
                        </div>

                        <div class="slide relative h-96 md:h-[500px]">
                            <div class="absolute inset-0 bg-gradient-to-r from-black/60 to-transparent z-10"></div>
                            <div class="slide-info absolute left-8 top-1/2 transform -translate-y-1/2 z-20 text-white max-w-md">
                                <div class="slide-info-img-container mb-4">
                                    <img src="./Imágenes/Carrousel/eleternauta_titulo.png" alt="eternauta titulo" class="max-w-xs">
                                </div>
                                <p class="text-lg leading-relaxed">Sigue a Juan Salvo junto con un grupo de supervivientes mientras luchan contra una amenaza alienígena.</p>
                            </div>
                            <img src="./Imágenes/Carrousel/eternauta_carrousel.webp" alt="Pelicula 3" class="w-full h-full object-cover">
                        </div>

                        <div class="slide relative h-96 md:h-[500px]">
                            <div class="absolute inset-0 bg-gradient-to-r from-black/60 to-transparent z-10"></div>
                            <div class="slide-info absolute left-8 top-1/2 transform -translate-y-1/2 z-20 text-white max-w-md">
                                <div class="slide-info-img-container mb-4">
                                    <img src="./Imágenes/Carrousel/thelastofus_titulo.png" alt="the last of us titulo" class="max-w-xs">
                                </div>
                                <p class="text-lg leading-relaxed">Joel y Ellie, una pareja conectada a través de la dureza del mundo en el que viven.</p>
                            </div>
                            <img src="./Imágenes/Carrousel/thelasofus_carrousel.webp" alt="Pelicula 4" class="w-full h-full object-cover">
                        </div>

                        <div class="slide relative h-96 md:h-[500px]">
                            <div class="absolute inset-0 bg-gradient-to-r from-black/60 to-transparent z-10"></div>
                            <div class="slide-info absolute left-8 top-1/2 transform -translate-y-1/2 z-20 text-white max-w-md">
                                <div class="slide-info-img-container mb-4">
                                    <img src="./Imágenes/Carrousel/misionimposible_titulo.png" alt="mision imposible titulo" class="max-w-xs">
                                </div>
                                <p class="text-lg leading-relaxed">Ethan y su equipo tienen la misión de encontrar y destruir a una IA conocida como La Entidad.</p>
                            </div>
                            <img src="./Imágenes/Carrousel/misionimposible_carrousel.webp" alt="Pelicula 5" class="w-full h-full object-cover">
                        </div>
                    </div>

                    <!-- Navigation Buttons -->
                    <button class="nav prev absolute left-4 top-1/2 transform -translate-y-1/2 z-30 bg-white/20 hover:bg-white/40 backdrop-blur-sm rounded-full p-3 transition-all duration-200" onclick="prevSlide()">
                        <img src="./Imágenes/flecha-izquierda-carrusel.png" alt="left-arrow" class="w-6 h-6">
                    </button>
                    <button class="nav next absolute right-4 top-1/2 transform -translate-y-1/2 z-30 bg-white/20 hover:bg-white/40 backdrop-blur-sm rounded-full p-3 transition-all duration-200" onclick="nextSlide()">
                        <img src="./Imágenes/flecha-derecha-carrusel.png" alt="right-arrow" class="w-6 h-6">
                    </button>

                    <!-- Indicators -->
                    <div class="indicators absolute bottom-4 left-1/2 transform -translate-x-1/2 z-30 flex space-x-2" id="indicators"></div>
                </div>
            </div>
        </section>

        <!-- Próximos lanzamientos -->
        <section class="py-12 transition-all duration-300" id="section1">
            <div class="container mx-auto px-4">
                <h2 class="text-4xl font-bold text-center mb-12 transition-all duration-300" id="title1">
                    Próximos lanzamientos
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-8">
                    <?php foreach ($proximos_lanzamientos as $peli): ?>
                        <div class="group cursor-pointer">
                            <a href="en_desarrollo.php?peli_id=<?php echo $peli['peli_id']; ?>" class="block">
                                <div class="relative overflow-hidden rounded-2xl shadow-lg group-hover:shadow-2xl transition-all duration-300 transform group-hover:scale-105">
                                    <img src="./Imágenes/Posters/<?php echo $peli['poster']; ?>" alt="<?php echo $peli['nombre']; ?>" class="w-full h-80 object-cover" onerror="this.src='./Imágenes/default-poster.jpg'">
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                    <div class="absolute bottom-4 left-4 right-4 text-white transform translate-y-full group-hover:translate-y-0 transition-transform duration-300">
                                        <h3 class="font-bold text-lg"><?php echo $peli['nombre']; ?></h3>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- Películas más populares -->
        <section class="py-12 transition-all duration-300" id="section2">
            <div class="container mx-auto px-4">
                <h2 class="text-4xl font-bold text-center mb-12 transition-all duration-300" id="title2">
                    Películas más populares
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-8">
                    <?php foreach ($pelis_populares as $peli): ?>
                        <div class="group cursor-pointer">
                            <a href="en_desarrollo.php?peli_id=<?php echo $peli['peli_id']; ?>" class="block">
                                <div class="relative overflow-hidden rounded-2xl shadow-lg group-hover:shadow-2xl transition-all duration-300 transform group-hover:scale-105">
                                    <img src="./Imágenes/Posters/<?php echo $peli['poster']; ?>" alt="<?php echo $peli['nombre']; ?>" class="w-full h-80 object-cover" onerror="this.src='./Imágenes/default-poster.jpg'">
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                    <div class="absolute bottom-4 left-4 right-4 text-white transform translate-y-full group-hover:translate-y-0 transition-transform duration-300">
                                        <h3 class="font-bold text-lg"><?php echo $peli['nombre']; ?></h3>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- Series más populares -->
        <section class="py-12 transition-all duration-300" id="section3">
            <div class="container mx-auto px-4">
                <h2 class="text-4xl font-bold text-center mb-12 transition-all duration-300" id="title3">
                    Series más populares
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-8">
                    <?php foreach ($series_populares as $peli): ?>
                        <div class="group cursor-pointer">
                            <a href="en_desarrollo.php?peli_id=<?php echo $peli['peli_id']; ?>" class="block">
                                <div class="relative overflow-hidden rounded-2xl shadow-lg group-hover:shadow-2xl transition-all duration-300 transform group-hover:scale-105">
                                    <img src="./Imágenes/Posters/<?php echo $peli['poster']; ?>" alt="<?php echo $peli['nombre']; ?>" class="w-full h-80 object-cover" onerror="this.src='./Imágenes/default-poster.jpg'">
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                    <div class="absolute bottom-4 left-4 right-4 text-white transform translate-y-full group-hover:translate-y-0 transition-transform duration-300">
                                        <h3 class="font-bold text-lg"><?php echo $peli['nombre']; ?></h3>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="bg-gradient-to-r from-gray-900 via-purple-900 to-blue-900 text-white py-12 mt-16">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Logo y descripción -->
                <div>
                    <h3 class="text-2xl font-bold bg-gradient-to-r from-pink-400 to-purple-400 bg-clip-text text-transparent mb-4">
                        CINE<span class="text-blue-400">VICE</span>
                    </h3>
                    <p class="text-gray-300">Tu plataforma favorita para descubrir y opinar sobre películas y series.</p>
                </div>

                <!-- Contacto -->
                <div>
                    <h4 class="text-lg font-semibold mb-4 text-pink-400">Contacto</h4>
                    <div class="space-y-3">
                        <a href="mailto:cinevice.suport@gmail.com" class="flex items-center space-x-2 text-gray-300 hover:text-pink-400 transition-colors duration-200">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"></path>
                                <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"></path>
                            </svg>
                            <span>cinevice.suport@gmail.com</span>
                        </a>
                        <a href="https://www.facebook.com/profile.php?id=61581046115329" target="_blank" class="flex items-center space-x-2 text-gray-300 hover:text-blue-400 transition-colors duration-200">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                            <span>Facebook</span>
                        </a>
                    </div>
                </div>

                <!-- Enlaces útiles -->
                <div>
                    <h4 class="text-lg font-semibold mb-4 text-purple-400">Enlaces</h4>
                    <div class="space-y-2">
                        <a href="./peliculas_series.php" class="block text-gray-300 hover:text-purple-400 transition-colors duration-200">Películas/Series</a>
                        <a href="./foros.php" class="block text-gray-300 hover:text-purple-400 transition-colors duration-200">Foros</a>
                        <?php if (isset($_SESSION['usuario'])): ?>
                            <a href="./Páginas/perfil.php" class="block text-gray-300 hover:text-purple-400 transition-colors duration-200">Mi Perfil</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-700 mt-8 pt-8 text-center">
                <p class="text-gray-400">&copy; 2025 CineVice. Todos los derechos reservados.</p>
                <p class="text-gray-500 text-sm mt-2">Versión 0.5 Beta</p>
            </div>
        </div>
    </footer>

    <script>
        // Initialize Feather Icons
        feather.replace();

        document.addEventListener('DOMContentLoaded', function() {
            // Theme Toggle - Sistema igual que perfil.php
            const themeToggle = document.getElementById('themeToggle');
            const body = document.getElementById('body');
            const navbar = document.getElementById('navbar');
            const carouselContainer = document.getElementById('carouselContainer');
            const navLink1 = document.getElementById('navLink1');
            const navLink2 = document.getElementById('navLink2');
            
            // Secciones y títulos
            const section1 = document.getElementById('section1');
            const section2 = document.getElementById('section2');
            const section3 = document.getElementById('section3');
            const title1 = document.getElementById('title1');
            const title2 = document.getElementById('title2');
            const title3 = document.getElementById('title3');

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
                navbar.className = 'sticky top-0 z-50 shadow-lg transition-all duration-300 bg-gray-800 text-white';
                carouselContainer.className = 'max-w-5xl mx-auto relative rounded-3xl overflow-hidden shadow-2xl transition-all duration-300 bg-gray-800/50 backdrop-blur-sm';
                
                // Nav links
                navLink1.className = 'px-4 py-2 rounded-lg transition-all duration-200 hover:bg-blue-500 hover:text-white text-gray-300';
                navLink2.className = 'px-4 py-2 rounded-lg transition-all duration-200 hover:bg-blue-500 hover:text-white text-gray-300';
                
                // Secciones
                section1.className = 'py-12 transition-all duration-300 bg-gray-800';
                section2.className = 'py-12 transition-all duration-300 bg-gray-900';
                section3.className = 'py-12 transition-all duration-300 bg-gray-800';
                
                // Títulos
                title1.className = 'text-4xl font-bold text-center mb-12 transition-all duration-300 text-white';
                title2.className = 'text-4xl font-bold text-center mb-12 transition-all duration-300 text-white';
                title3.className = 'text-4xl font-bold text-center mb-12 transition-all duration-300 text-white';
            }

            function enableLightMode() {
                body.className = 'min-h-screen transition-all duration-300 bg-gradient-to-br from-pink-100 via-purple-50 to-blue-100 text-gray-900';
                navbar.className = 'sticky top-0 z-50 shadow-lg transition-all duration-300 bg-white/80 backdrop-blur-lg border-b border-pink-200';
                carouselContainer.className = 'max-w-5xl mx-auto relative rounded-3xl overflow-hidden shadow-2xl transition-all duration-300 bg-white/20 backdrop-blur-sm';
                
                // Nav links
                navLink1.className = 'px-4 py-2 rounded-lg transition-all duration-200 hover:bg-blue-500 hover:text-white text-gray-700';
                navLink2.className = 'px-4 py-2 rounded-lg transition-all duration-200 hover:bg-blue-500 hover:text-white text-gray-700';
                
                // Secciones
                section1.className = 'py-12 transition-all duration-300 bg-gradient-to-r from-blue-100 to-purple-100';
                section2.className = 'py-12 transition-all duration-300 bg-gradient-to-r from-pink-100 to-red-100';
                section3.className = 'py-12 transition-all duration-300 bg-gradient-to-r from-purple-100 to-indigo-100';
                
                // Títulos
                title1.className = 'text-4xl font-bold text-center mb-12 transition-all duration-300 bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent';
                title2.className = 'text-4xl font-bold text-center mb-12 transition-all duration-300 bg-gradient-to-r from-pink-600 to-red-600 bg-clip-text text-transparent';
                title3.className = 'text-4xl font-bold text-center mb-12 transition-all duration-300 bg-gradient-to-r from-purple-600 to-indigo-600 bg-clip-text text-transparent';
            }

            // Carousel functionality
            let currentSlide = 0;
            const slides = document.querySelectorAll('.slide');
            const indicatorsContainer = document.getElementById('indicators');

            function showSlide(index) {
                slides.forEach((slide, i) => {
                    slide.classList.remove('active');
                    if (indicatorsContainer.children[i]) {
                        indicatorsContainer.children[i].classList.remove('active');
                    }
                });

                if (slides[index]) {
                    slides[index].classList.add('active');
                }
                if (indicatorsContainer.children[index]) {
                    indicatorsContainer.children[index].classList.add('active');
                }
            }

            // Funciones globales para el carousel
            window.nextSlide = function() {
                currentSlide = (currentSlide + 1) % slides.length;
                showSlide(currentSlide);
            }

            window.prevSlide = function() {
                currentSlide = (currentSlide - 1 + slides.length) % slides.length;
                showSlide(currentSlide);
            }

            function createIndicators() {
                indicatorsContainer.innerHTML = '';
                slides.forEach((_, index) => {
                    const dot = document.createElement('button');
                    dot.className = 'w-3 h-3 rounded-full bg-white/50 hover:bg-white/80 transition-all duration-200';
                    dot.addEventListener('click', () => {
                        currentSlide = index;
                        showSlide(currentSlide);
                    });
                    if (index === 0) {
                        dot.classList.add('active');
                    }
                    indicatorsContainer.appendChild(dot);
                });
            }

            // CSS para carousel
            const style = document.createElement('style');
            style.textContent = `
                .indicators button.active {
                    background-color: white !important;
                }
                .slide {
                    display: none;
                }
                .slide.active {
                    display: block;
                }
                .carousel {
                    position: relative;
                }
            `;
            document.head.appendChild(style);

            // Inicializar carousel
            if (slides.length > 0) {
                createIndicators();
                showSlide(0);
                
                // Auto-advance carousel cada 8 segundos
                setInterval(window.nextSlide, 8000);
            }
        });
    </script>
</body>
</html>