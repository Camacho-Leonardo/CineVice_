<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: formularios.php?inicio");
    exit;
}

require_once("../conexion.php");
$usuario = $_SESSION['usuario'];

// Si entró con contraseña temporal, lo redirigimos a cambio de contraseña
if (isset($_SESSION['temporal']) && $_SESSION['temporal'] === true) {
    header("Location: cambiar_clave.php");
    exit;
}

// Procesar subida de imagen de perfil
if (isset($_POST['upload_avatar']) && isset($_FILES['avatar'])) {
    $upload_dir = '../uploads/avatars/';
    
    // Crear directorio si no existe
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $file = $_FILES['avatar'];
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    // Validaciones
    if ($file['error'] === 0 && in_array($file['type'], $allowed_types) && $file['size'] <= $max_size) {
        // Generar nombre único
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = $usuario['id'] . '_' . time() . '.' . $extension;
        $upload_path = $upload_dir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $upload_path)) {
            // Actualizar base de datos
            $query = "UPDATE usuarios SET imagen = ? WHERE usu_id = ?";
            $stmt = $conexion->prepare($query);
            $stmt->bind_param("si", $filename, $usuario['id']);
            
            if ($stmt->execute()) {
                // Actualizar sesión
                $_SESSION['usuario']['imagen'] = $filename;
                $usuario['imagen'] = $filename;
                $success_message = "Imagen de perfil actualizada correctamente";
            } else {
                $error_message = "Error al guardar en la base de datos";
            }
        } else {
            $error_message = "Error al subir el archivo";
        }
    } else {
        $error_message = "Archivo no válido. Use JPG, PNG o GIF, máximo 5MB";
    }
}

// Obtener comentarios recientes del usuario
$comentarios_query = "SELECT c.contenido, c.fecha, f.nombre as foro_nombre 
                     FROM comentarios c 
                     JOIN foros f ON c.foro_id = f.foro_id 
                     WHERE c.usu_id = ? 
                     ORDER BY c.fecha DESC 
                     LIMIT 5";
$stmt = $conexion->prepare($comentarios_query);
$stmt->bind_param("i", $usuario['id']);
$stmt->execute();
$comentarios_recientes = $stmt->get_result();

// Obtener foros propietarios del usuario
$foros_query = "SELECT nombre, descripcion, creacion 
               FROM foros 
               WHERE usu_id = ? 
               ORDER BY creacion DESC";
$stmt2 = $conexion->prepare($foros_query);
$stmt2->bind_param("i", $usuario['id']);
$stmt2->execute();
$foros_propietarios = $stmt2->get_result();

// Obtener opiniones recientes del usuario
$opiniones_query = "SELECT o.contenido, o.puntuacion, o.fecha, p.nombre as pelicula_nombre 
                   FROM opiniones o 
                   JOIN pelis p ON o.peli_id = p.peli_id 
                   WHERE o.usu_id = ? 
                   ORDER BY o.fecha DESC 
                   LIMIT 5";
$stmt3 = $conexion->prepare($opiniones_query);
$stmt3->bind_param("i", $usuario['id']);
$stmt3->execute();
$opiniones_recientes = $stmt3->get_result();

// Ruta de imagen de perfil
$profile_image_path = !empty($usuario['imagen']) ? '../uploads/avatars/' . $usuario['imagen'] : '../Imágenes/default-avatar.png';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - CineVice</title>
    <link href="../../../src/output.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="../Imágenes/c-logo.png">
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
</head>
<body class="min-h-screen transition-all duration-300" id="body">
    <!-- Navigation Bar -->
    <nav class="shadow-lg transition-all duration-300" id="navbar">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <div class="flex items-center space-x-4">
                    <a href="../../../index.php" class="group">
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
                        <?php if ($usuario['rol_id'] == 1): ?>
                        <a href="admin_panel.php" class="px-4 py-2 rounded-lg bg-gradient-to-r from-purple-500 to-pink-500 text-white font-medium transition-all duration-200 hover:scale-105 hover:shadow-lg">
                            👑 Panel Admin
                        </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Right Section -->
                <div class="flex items-center space-x-4">
                    <!-- Theme Toggle -->
                    <button id="themeToggle" class="p-2 rounded-lg transition-colors duration-200 hover:bg-gray-200 dark:hover:bg-gray-700">
                        <i data-feather="sun" class="w-5 h-5 hidden dark:block"></i>
                        <i data-feather="moon" class="w-5 h-5 block dark:hidden"></i>
                    </button>
                    
                    <!-- User Info with Profile Picture -->
                    <div class="flex items-center space-x-2">
                        <img id="navProfileImage" src="<?php echo $profile_image_path; ?>" alt="Avatar" 
                             class="w-8 h-8 rounded-full object-cover border-2 border-blue-400">
                        <span class="hidden md:block font-medium">
                            <?php if ($usuario['rol_id'] == 1): ?>
                                👑 <?php echo htmlspecialchars($usuario['nombre']); ?>
                            <?php else: ?>
                                <?php echo htmlspecialchars($usuario['nombre']); ?>
                            <?php endif; ?>
                        </span>
                    </div>
                    
                    <!-- Logout Button -->
                    <a href="./logout.php" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors duration-200">
                        Cerrar Sesión
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-6xl mx-auto px-4 py-8">
        <?php if (isset($success_message)): ?>
            <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Profile Section -->
            <div class="lg:col-span-1">
                <div class="rounded-2xl shadow-xl p-6 transition-all duration-300" id="profileCard">
                    <!-- Profile Picture -->
                    <div class="text-center mb-6">
                        <form method="POST" enctype="multipart/form-data" id="avatarForm">
                            <div class="relative inline-block">
                                <img id="profileImage" src="<?php echo $profile_image_path; ?>" alt="Foto de perfil" 
                                     class="w-32 h-32 rounded-full object-cover border-4 border-blue-400 mx-auto shadow-lg">
                                <label for="profilePictureInput" class="absolute bottom-2 right-2 bg-blue-500 text-white p-2 rounded-full cursor-pointer hover:bg-blue-600 transition-colors duration-200">
                                    <i data-feather="camera" class="w-4 h-4"></i>
                                </label>
                                <input type="file" id="profilePictureInput" name="avatar" accept="image/*" class="hidden">
                                <input type="hidden" name="upload_avatar" value="1">
                            </div>
                        </form>
                    </div>

                    <!-- User Info -->
                    <div class="text-center">
                        <h2 class="text-2xl font-bold mb-4">
                            <?php if ($usuario['rol_id'] == 1): ?>
                                👑 Bienvenido Admin, <?php echo htmlspecialchars($usuario['nombre']); ?>
                            <?php else: ?>
                                👋 Bienvenido, <?php echo htmlspecialchars($usuario['nombre']); ?>
                            <?php endif; ?>
                        </h2>
                        
                        <!-- Email with privacy toggle -->
                        <div class="mb-4">
                            <div class="flex items-center justify-between p-3 rounded-lg transition-colors duration-200" id="emailSection">
                                <span class="font-medium">Email:</span>
                                <div class="flex items-center space-x-2">
                                    <span id="emailText" class="blur-sm select-none"><?php echo htmlspecialchars($usuario['email']); ?></span>
                                    <button id="toggleEmail" class="p-1 rounded hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors duration-200">
                                        <i data-feather="eye" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <?php if ($usuario['rol_id'] == 1): ?>
                        <!-- Admin Quick Access Button -->
                        <div class="mt-6">
                            <a href="admin_panel.php" class="block w-full px-6 py-3 bg-gradient-to-r from-purple-500 to-pink-500 text-white rounded-xl font-bold hover:scale-105 transition-all duration-200 shadow-lg hover:shadow-xl">
                                <i data-feather="settings" class="w-5 h-5 inline mr-2"></i>
                                Acceder al Panel de Administración
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Content Section -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Recent Opinions -->
                <div class="rounded-2xl shadow-xl p-6 transition-all duration-300" id="opinionsCard">
                    <div class="flex items-center space-x-3 mb-6">
                        <i data-feather="star" class="w-6 h-6 text-yellow-500"></i>
                        <h3 class="text-xl font-bold">Mis Opiniones Recientes</h3>
                    </div>
                    
                    <?php if ($opiniones_recientes->num_rows > 0): ?>
                        <div class="space-y-4">
                            <?php while ($opinion = $opiniones_recientes->fetch_assoc()): ?>
                                <div class="p-4 rounded-lg border transition-all duration-200 hover:shadow-md" id="opinionItem">
                                    <div class="flex justify-between items-start mb-2">
                                        <div class="flex items-center space-x-2">
                                            <span class="font-medium text-yellow-600"><?php echo htmlspecialchars($opinion['pelicula_nombre']); ?></span>
                                            <div class="flex">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i data-feather="star" class="w-4 h-4 <?php echo $i <= $opinion['puntuacion'] ? 'text-yellow-400 fill-current' : 'text-gray-300'; ?>"></i>
                                                <?php endfor; ?>
                                            </div>
                                        </div>
                                        <span class="text-sm opacity-70 flex-shrink-0"><?php echo date('d/m/Y H:i', strtotime($opinion['fecha'])); ?></span>
                                    </div>
                                    <p class="leading-relaxed break-words"><?php echo htmlspecialchars($opinion['contenido']); ?></p>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-8 opacity-70">
                            <i data-feather="star" class="w-12 h-12 mx-auto mb-3 opacity-50"></i>
                            <p>Aún no has opinado sobre películas</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Recent Comments -->
                <div class="rounded-2xl shadow-xl p-6 transition-all duration-300" id="commentsCard">
                    <div class="flex items-center space-x-3 mb-6">
                        <i data-feather="message-circle" class="w-6 h-6 text-blue-500"></i>
                        <h3 class="text-xl font-bold">Comentarios Recientes</h3>
                    </div>
                    
                    <?php if ($comentarios_recientes->num_rows > 0): ?>
                        <div class="space-y-4">
                            <?php while ($comentario = $comentarios_recientes->fetch_assoc()): ?>
                                <div class="p-4 rounded-lg border transition-all duration-200 hover:shadow-md" id="commentItem">
                                    <div class="flex justify-between items-start mb-2">
                                        <span class="font-medium text-blue-600 break-words flex-1 mr-2"><?php echo htmlspecialchars($comentario['foro_nombre']); ?></span>
                                        <span class="text-sm opacity-70 flex-shrink-0"><?php echo date('d/m/Y H:i', strtotime($comentario['fecha'])); ?></span>
                                    </div>
                                    <p class="leading-relaxed break-words"><?php echo htmlspecialchars($comentario['contenido']); ?></p>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-8 opacity-70">
                            <i data-feather="message-square" class="w-12 h-12 mx-auto mb-3 opacity-50"></i>
                            <p>Aún no has hecho comentarios</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Own Forums -->
                <div class="rounded-2xl shadow-xl p-6 transition-all duration-300" id="forumsCard">
                    <div class="flex items-center space-x-3 mb-6">
                        <i data-feather="users" class="w-6 h-6 text-blue-500"></i>
                        <h3 class="text-xl font-bold">Mis Foros</h3>
                    </div>
                    
                    <?php if ($foros_propietarios->num_rows > 0): ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <?php while ($foro = $foros_propietarios->fetch_assoc()): ?>
                                <div class="p-4 rounded-lg border transition-all duration-200 hover:shadow-md hover:scale-105" id="forumItem">
                                    <h4 class="font-bold text-lg mb-2 break-words"><?php echo htmlspecialchars($foro['nombre']); ?></h4>
                                    <p class="text-sm opacity-80 mb-3 break-words line-clamp-3"><?php echo htmlspecialchars($foro['descripcion']); ?></p>
                                    <span class="text-xs opacity-60">Creado: <?php echo date('d/m/Y', strtotime($foro['creacion'])); ?></span>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-8 opacity-70">
                            <i data-feather="plus-circle" class="w-12 h-12 mx-auto mb-3 opacity-50"></i>
                            <p>Aún no has creado foros</p>
                            <a href="../foros.php" class="inline-block mt-3 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors duration-200">
                                Crear mi primer foro
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Initialize Feather Icons
        feather.replace();

        // Theme Toggle Functionality
        const themeToggle = document.getElementById('themeToggle');
        const body = document.getElementById('body');
        const navbar = document.getElementById('navbar');
        const profileCard = document.getElementById('profileCard');
        const commentsCard = document.getElementById('commentsCard');
        const forumsCard = document.getElementById('forumsCard');
        const opinionsCard = document.getElementById('opinionsCard');
        const emailSection = document.getElementById('emailSection');

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
            navbar.className = 'shadow-lg transition-all duration-300 bg-gray-800 text-white';
            profileCard.className = 'rounded-2xl shadow-xl p-6 transition-all duration-300 bg-gray-800 text-white';
            commentsCard.className = 'rounded-2xl shadow-xl p-6 transition-all duration-300 bg-gray-800 text-white';
            forumsCard.className = 'rounded-2xl shadow-xl p-6 transition-all duration-300 bg-gray-800 text-white';
            opinionsCard.className = 'rounded-2xl shadow-xl p-6 transition-all duration-300 bg-gray-800 text-white';
            emailSection.className = 'flex items-center justify-between p-3 rounded-lg transition-colors duration-200 bg-gray-700';
            
            // Update comment, forum and opinion items
            const commentItems = document.querySelectorAll('#commentItem');
            const forumItems = document.querySelectorAll('#forumItem');
            const opinionItems = document.querySelectorAll('#opinionItem');
            
            commentItems.forEach(item => {
                item.className = 'p-4 rounded-lg border transition-all duration-200 hover:shadow-md bg-gray-700 border-gray-600';
            });
            
            forumItems.forEach(item => {
                item.className = 'p-4 rounded-lg border transition-all duration-200 hover:shadow-md hover:scale-105 bg-gray-700 border-gray-600';
            });

            opinionItems.forEach(item => {
                item.className = 'p-4 rounded-lg border transition-all duration-200 hover:shadow-md bg-gray-700 border-gray-600';
            });
        }

        function enableLightMode() {
            body.className = 'min-h-screen transition-all duration-300 bg-gradient-to-br from-pink-100 to-blue-100 text-gray-900';
            navbar.className = 'shadow-lg transition-all duration-300 bg-white text-gray-900';
            profileCard.className = 'rounded-2xl shadow-xl p-6 transition-all duration-300 bg-white text-gray-900';
            commentsCard.className = 'rounded-2xl shadow-xl p-6 transition-all duration-300 bg-white text-gray-900';
            forumsCard.className = 'rounded-2xl shadow-xl p-6 transition-all duration-300 bg-white text-gray-900';
            opinionsCard.className = 'rounded-2xl shadow-xl p-6 transition-all duration-300 bg-white text-gray-900';
            emailSection.className = 'flex items-center justify-between p-3 rounded-lg transition-colors duration-200 bg-gray-50';
            
            // Update comment, forum and opinion items
            const commentItems = document.querySelectorAll('#commentItem');
            const forumItems = document.querySelectorAll('#forumItem');
            const opinionItems = document.querySelectorAll('#opinionItem');
            
            commentItems.forEach(item => {
                item.className = 'p-4 rounded-lg border transition-all duration-200 hover:shadow-md bg-gray-50 border-gray-200';
            });
            
            forumItems.forEach(item => {
                item.className = 'p-4 rounded-lg border transition-all duration-200 hover:shadow-md hover:scale-105 bg-gray-50 border-gray-200';
            });

            opinionItems.forEach(item => {
                item.className = 'p-4 rounded-lg border transition-all duration-200 hover:shadow-md bg-gray-50 border-gray-200';
            });
        }

        // Email Privacy Toggle
        const toggleEmail = document.getElementById('toggleEmail');
        const emailText = document.getElementById('emailText');
        let emailVisible = false;

        toggleEmail.addEventListener('click', () => {
            emailVisible = !emailVisible;
            if (emailVisible) {
                emailText.classList.remove('blur-sm');
                emailText.classList.add('select-text');
                toggleEmail.innerHTML = '<i data-feather="eye-off" class="w-4 h-4"></i>';
            } else {
                emailText.classList.add('blur-sm');
                emailText.classList.remove('select-text');
                toggleEmail.innerHTML = '<i data-feather="eye" class="w-4 h-4"></i>';
            }
            feather.replace();
        });

        // Profile Picture Upload with Form Submission
        const profilePictureInput = document.getElementById('profilePictureInput');
        const avatarForm = document.getElementById('avatarForm');
        const profileImage = document.getElementById('profileImage');
        const navProfileImage = document.getElementById('navProfileImage');

        profilePictureInput.addEventListener('change', function(e) {
            if (e.target.files[0]) {
                // Show preview immediately
                const file = e.target.files[0];
                const reader = new FileReader();
                reader.onload = function(e) {
                    profileImage.src = e.target.result;
                    navProfileImage.src = e.target.result;
                };
                reader.readAsDataURL(file);
                
                // Submit form to upload to server
                avatarForm.submit();
            }
        });
    </script>
</body>
</html>