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
    $error_permission = true;
}

// Obtener datos de la película para mostrar contexto
$peli = null;
if ($peli_id) {
    $stmt = $conexion->prepare("SELECT nombre FROM pelis WHERE peli_id = ?");
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Opinión - CineVice</title>
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

    <!-- Main Content -->
    <main class="max-w-4xl mx-auto px-4 py-8">
        <?php if (isset($error_permission) && $error_permission): ?>
            <!-- Permission Error -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-12 text-center transition-all duration-300">
                <i data-feather="lock" class="w-24 h-24 mx-auto mb-6 text-red-400"></i>
                <h1 class="text-3xl font-bold text-red-600 dark:text-red-400 mb-4">Acceso Denegado</h1>
                <p class="text-gray-500 dark:text-gray-400 mb-8">No tienes permiso para editar esta opinión.</p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="javascript:history.back()" class="inline-flex items-center space-x-2 px-6 py-3 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors duration-200">
                        <i data-feather="arrow-left" class="w-4 h-4"></i>
                        <span>Volver</span>
                    </a>
                    <a href="index.php" class="inline-flex items-center space-x-2 px-6 py-3 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors duration-200">
                        <i data-feather="home" class="w-4 h-4"></i>
                        <span>Ir al inicio</span>
                    </a>
                </div>
            </div>
        <?php else: ?>
            <!-- Edit Form -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden transition-all duration-300">
                <!-- Header -->
                <div class="bg-gradient-to-r from-pink-500 via-purple-500 to-blue-500 p-6 text-white">
                    <div class="flex items-center space-x-3">
                        <i data-feather="edit-3" class="w-8 h-8"></i>
                        <div>
                            <h1 class="text-2xl font-bold">Editar tu opinión</h1>
                            <?php if ($peli): ?>
                                <p class="text-white/80">Sobre: <?php echo htmlspecialchars($peli['nombre']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Form -->
                <div class="p-8">
                    <form action="actualizar_opinion.php" method="POST" class="space-y-6">
                        <input type="hidden" name="op_id" value="<?php echo $op_id; ?>">
                        <input type="hidden" name="peli_id" value="<?php echo $peli_id; ?>">

                        <!-- Rating Section -->
                        <div>
                            <label for="puntuacion" class="block text-lg font-medium mb-3 flex items-center space-x-2">
                                <i data-feather="star" class="w-5 h-5 text-yellow-500"></i>
                                <span>Puntuación</span>
                            </label>
                            <select name="puntuacion" id="puntuacion" required 
                                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-200 dark:bg-gray-700 dark:text-white text-lg transition-all duration-200">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <option value="<?php echo $i; ?>" <?php if ($opinion['puntuacion'] == $i) echo 'selected'; ?>>
                                        <?php echo $i; ?> estrella<?php echo $i > 1 ? 's' : ''; ?> <?php echo str_repeat('⭐', $i); ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <!-- Opinion Content -->
                        <div>
                            <label for="contenido" class="block text-lg font-medium mb-3 flex items-center space-x-2">
                                <i data-feather="message-square" class="w-5 h-5 text-blue-500"></i>
                                <span>Tu opinión</span>
                            </label>
                            <div class="relative">
                                <textarea name="contenido" id="contenido" rows="6" required
                                          placeholder="Escribe aquí lo que pensás sobre esta película o serie..."
                                          class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-200 dark:bg-gray-700 dark:text-white resize-none transition-all duration-200"><?php echo htmlspecialchars($opinion['contenido']); ?></textarea>
                                <div class="absolute bottom-3 right-3 text-sm text-gray-400" id="charCount">0 caracteres</div>
                            </div>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                                Comparte tu experiencia, lo que más te gustó o lo que no te convenció.
                            </p>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex flex-col sm:flex-row gap-4 pt-4">
                            <button type="submit" 
                                    class="flex-1 px-6 py-3 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors duration-200 flex items-center justify-center space-x-2 font-medium">
                                <i data-feather="save" class="w-4 h-4"></i>
                                <span>Actualizar Opinión</span>
                            </button>
                            <button type="button" onclick="history.back()"
                                    class="flex-1 px-6 py-3 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors duration-200 flex items-center justify-center space-x-2 font-medium">
                                <i data-feather="x" class="w-4 h-4"></i>
                                <span>Cancelar</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tips Section -->
            <div class="mt-8 bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 transition-all duration-300">
                <h3 class="text-lg font-bold mb-4 flex items-center space-x-2">
                    <i data-feather="lightbulb" class="w-5 h-5 text-yellow-500"></i>
                    <span>Tips para una buena opinión</span>
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex items-start space-x-3">
                        <div class="w-2 h-2 bg-blue-500 rounded-full mt-2 flex-shrink-0"></div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Sé específico sobre lo que te gustó o no</p>
                    </div>
                    <div class="flex items-start space-x-3">
                        <div class="w-2 h-2 bg-blue-500 rounded-full mt-2 flex-shrink-0"></div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Evita spoilers importantes</p>
                    </div>
                    <div class="flex items-start space-x-3">
                        <div class="w-2 h-2 bg-blue-500 rounded-full mt-2 flex-shrink-0"></div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Mantén un tono respetuoso</p>
                    </div>
                    <div class="flex items-start space-x-3">
                        <div class="w-2 h-2 bg-blue-500 rounded-full mt-2 flex-shrink-0"></div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Ayuda a otros con tu perspectiva</p>
                    </div>
                </div>
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

        // Character Counter
        const contenido = document.getElementById('contenido');
        const charCount = document.getElementById('charCount');
        
        if (contenido && charCount) {
            function updateCharCount() {
                const count = contenido.value.length;
                charCount.textContent = `${count} caracteres`;
                
                // Visual feedback for length
                if (count > 500) {
                    charCount.className = 'absolute bottom-3 right-3 text-sm text-green-500';
                } else if (count > 50) {
                    charCount.className = 'absolute bottom-3 right-3 text-sm text-blue-500';
                } else {
                    charCount.className = 'absolute bottom-3 right-3 text-sm text-gray-400';
                }
            }

            // Initialize counter
            updateCharCount();
            
            // Update on input
            contenido.addEventListener('input', updateCharCount);
        }

        // Form validation
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                const contenidoValue = contenido.value.trim();
                const puntuacion = document.getElementById('puntuacion').value;
                
                if (!puntuacion) {
                    e.preventDefault();
                    alert('Por favor selecciona una puntuación');
                    return;
                }
                
                if (contenidoValue.length < 10) {
                    e.preventDefault();
                    alert('La opinión debe tener al menos 10 caracteres');
                    return;
                }
                
                // Show loading state
                const submitBtn = form.querySelector('button[type="submit"]');
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i data-feather="loader" class="w-4 h-4 animate-spin"></i><span>Actualizando...</span>';
                feather.replace();
            });
        }

        // Auto-expand textarea
        if (contenido) {
            contenido.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = Math.min(this.scrollHeight, 200) + 'px';
            });
            
            // Initialize height
            contenido.style.height = Math.min(contenido.scrollHeight, 200) + 'px';
        }
    </script>
</body>
</html>