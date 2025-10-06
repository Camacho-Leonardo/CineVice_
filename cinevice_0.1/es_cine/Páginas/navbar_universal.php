<?php
// navbar_universal.php - Include este archivo en todas tus páginas
// Ejemplo de cómo implementar navbar con avatar en cualquier página

require_once("get_user_avatar.php");

// Obtener datos del usuario con avatar
if (isset($_SESSION['usuario'])) {
    $user_data = getUserWithAvatar($_SESSION['usuario']['id'], $conexion);
    $user_avatar = $user_data ? $user_data['avatar_path'] : '../Imágenes/default-avatar.png';
    $user_name = $user_data ? $user_data['nombre'] : 'Usuario';
} else {
    $user_avatar = '../Imágenes/default-avatar.png';
    $user_name = 'Invitado';
}
?>

<!-- Navbar Universal para todas las páginas -->
<nav class="shadow-lg transition-all duration-300 bg-white text-gray-900" id="navbar">
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
                    <a href="../peliculas_series.php" class="px-4 py-2 rounded-lg transition-all duration-200 hover:bg-blue-500 hover:text-white">
                        Películas/Series
                    </a>
                    <a href="../foros.php" class="px-4 py-2 rounded-lg transition-all duration-200 hover:bg-blue-500 hover:text-white">
                        Foros
                    </a>
                </div>
            </div>

            <!-- Right Section -->
            <div class="flex items-center space-x-4">
                <?php if (isset($_SESSION['usuario'])): ?>
                    <!-- Theme Toggle -->
                    <button id="themeToggle" class="p-2 rounded-lg transition-colors duration-200 hover:bg-gray-200 dark:hover:bg-gray-700">
                        <i data-feather="sun" class="w-5 h-5 hidden dark:block"></i>
                        <i data-feather="moon" class="w-5 h-5 block dark:hidden"></i>
                    </button>
                    
                    <!-- User Info with Profile Picture -->
                    <div class="flex items-center space-x-2">
                        <img src="<?php echo $user_avatar; ?>" alt="Avatar" 
                             class="w-8 h-8 rounded-full object-cover border-2 border-blue-400">
                        <span class="hidden md:block font-medium"><?php echo htmlspecialchars($user_name); ?></span>
                    </div>
                    
                    <!-- Profile Link (solo si NO estás en perfil.php) -->
                    <?php if (basename($_SERVER['PHP_SELF']) !== 'perfil.php'): ?>
                        <a href="./perfil.php" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors duration-200">
                            Perfil
                        </a>
                    <?php endif; ?>
                    
                    <!-- Logout Button -->
                    <a href="./logout.php" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors duration-200">
                        Cerrar Sesión
                    </a>
                <?php else: ?>
                    <!-- Login/Register buttons for guests -->
                    <a href="./formularios.php?inicio" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors duration-200">
                        Iniciar Sesión
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<script>
// Script para tema (incluir en todas las páginas)
document.addEventListener('DOMContentLoaded', function() {
    feather.replace(); // Si usas Feather Icons
    
    const themeToggle = document.getElementById('themeToggle');
    if (themeToggle) {
        // Aplicar tema guardado
        const savedTheme = localStorage.getItem('theme') || 'light';
        if (savedTheme === 'dark') {
            document.body.classList.add('dark');
        }
        
        themeToggle.addEventListener('click', function() {
            document.body.classList.toggle('dark');
            const newTheme = document.body.classList.contains('dark') ? 'dark' : 'light';
            localStorage.setItem('theme', newTheme);
        });
    }
});
</script>

<!-- Estilos CSS adicionales -->
<style>
/* Efectos hover para el avatar */
.navbar img[alt="Avatar"]:hover {
    transform: scale(1.1);
    transition: transform 0.2s ease;
}

/* Responsive avatar */
@media (max-width: 768px) {
    .navbar img[alt="Avatar"] {
        width: 32px;
        height: 32px;
    }
}

/* Tema oscuro para navbar */
body.dark .navbar {
    background-color: #1f2937;
    color: white;
}

body.dark .navbar a {
    color: white;
}

body.dark .navbar button:hover {
    background-color: #374151;
}
</style>

<?php
// Ejemplo de uso en cualquier página:
/*
1. Al inicio de tu página PHP:
   session_start();
   require_once("../conexion.php");
   
2. Incluir la navbar:
   include("navbar_universal.php");
   
3. ¡Y listo! El avatar se mostrará automáticamente desde la BD
*/
?>