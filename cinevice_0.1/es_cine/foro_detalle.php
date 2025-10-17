<?php
session_start();
require_once("conexion.php");

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: foros.php");
    exit;
}

$foro_id = (int)$_GET['id'];

// Obtener información del foro
$stmt = $conexion->prepare("
    SELECT f.*, u.nombre as creador_nombre, g.nombre as genero_nombre
    FROM foros f 
    LEFT JOIN usuarios u ON f.usu_id = u.usu_id
    LEFT JOIN generos g ON f.genero_id = g.gen_id
    WHERE f.foro_id = ? AND f.est_id = 1
");
$stmt->bind_param("i", $foro_id);
$stmt->execute();
$foro_result = $stmt->get_result();

if ($foro_result->num_rows === 0) {
    header("Location: foros.php");
    exit;
}

$foro = $foro_result->fetch_assoc();
$stmt->close();

// Obtener comentarios (solo los comentarios principales, sin respuestas)
$comments_query = "
    SELECT c.*, u.nombre as usuario_nombre, u.imagen as usuario_imagen,
           (SELECT COUNT(*) FROM comentarios cr WHERE cr.com_padre_id = c.com_id AND cr.est_id = 1) as respuestas_count
    FROM comentarios c
    LEFT JOIN usuarios u ON c.usu_id = u.usu_id
    WHERE c.foro_id = ? AND c.est_id = 1 AND (c.com_padre_id IS NULL OR c.com_padre_id = 0)
    ORDER BY c.fecha DESC
";

$stmt = $conexion->prepare($comments_query);
$stmt->bind_param("i", $foro_id);
$stmt->execute();
$comentarios_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($foro['nombre']) ?> - CineVice</title>
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
                        <a href="foros.php" class="px-4 py-2 rounded-lg transition-all duration-200 hover:bg-blue-500 hover:text-white">
                            Foros
                        </a>
                    </div>
                </div>

                <!-- Breadcrumb -->
                <div class="flex-1 mx-8">
                    <nav class="flex" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 md:space-x-3">
                            <li class="inline-flex items-center">
                                <a href="foros.php" class="hover:text-blue-500 transition-colors duration-200 flex items-center space-x-1">
                                    <i data-feather="arrow-left" class="w-4 h-4"></i>
                                    <span>Foros</span>
                                </a>
                            </li>
                            <li>
                                <div class="flex items-center">
                                    <i data-feather="chevron-right" class="w-4 h-4 opacity-50"></i>
                                    <span class="ml-1 font-medium text-blue-500"><?= htmlspecialchars($foro['nombre']) ?></span>
                                </div>
                            </li>
                        </ol>
                    </nav>
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

    <div class="max-w-4xl mx-auto px-4 py-8">
        <!-- Header del Foro -->
        <div class="rounded-lg shadow-md mb-8 overflow-hidden transition-all duration-300" id="foroHeader">
            <?php if (!empty($foro['imagen'])): ?>
                <img src="./uploads/foros/<?= htmlspecialchars($foro['imagen']) ?>" 
                     alt="Imagen del foro" 
                     class="w-full h-64 object-cover">
            <?php else: ?>
                <div class="w-full h-64 bg-gradient-to-br from-pink-500 via-purple-500 to-blue-500 flex items-center justify-center">
                    <i data-feather="message-circle" class="w-20 h-20 text-white"></i>
                </div>
            <?php endif; ?>
            
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h1 class="text-3xl font-bold"><?= htmlspecialchars($foro['nombre']) ?></h1>
                    <?php if ($foro['genero_nombre']): ?>
                        <span class="bg-blue-500 text-white px-3 py-1 rounded-full text-sm">
                            <?= htmlspecialchars($foro['genero_nombre']) ?>
                        </span>
                    <?php endif; ?>
                </div>
                
                <p class="text-lg mb-4 opacity-90"><?= nl2br(htmlspecialchars($foro['descripcion'])) ?></p>
                
                <div class="flex items-center text-sm opacity-70">
                    <i data-feather="user" class="w-4 h-4 mr-1"></i>
                    <span>Creado por <strong><?= htmlspecialchars($foro['creador_nombre']) ?></strong></span>
                    <span class="mx-2">•</span>
                    <i data-feather="calendar" class="w-4 h-4 mr-1"></i>
                    <span><?= date('d/m/Y H:i', strtotime($foro['creacion'])) ?></span>
                </div>
            </div>
        </div>

        <!-- Mensajes de alerta -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="p-4 mb-4 rounded-lg flex items-center space-x-2 transition-all duration-300" id="successAlert">
                <i data-feather="check-circle" class="w-5 h-5 text-green-600"></i>
                <span class="text-green-700 dark:text-green-300"><?= htmlspecialchars($_SESSION['success']) ?></span>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="p-4 mb-4 rounded-lg flex items-center space-x-2 transition-all duration-300" id="errorAlert">
                <i data-feather="alert-circle" class="w-5 h-5 text-red-600"></i>
                <span class="text-red-700 dark:text-red-300"><?= htmlspecialchars($_SESSION['error']) ?></span>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- Formulario de nuevo comentario -->
        <?php if (isset($_SESSION['usuario'])): ?>
            <div class="rounded-lg shadow-md p-6 mb-8 transition-all duration-300" id="commentForm">
                <h3 class="text-lg font-semibold mb-4 flex items-center space-x-2">
                    <i data-feather="edit-3" class="w-5 h-5"></i>
                    <span>Escribir un comentario</span>
                </h3>
                <form action="procesar_comentario.php" method="POST">
                    <input type="hidden" name="foro_id" value="<?= $foro_id ?>">
                    <textarea name="contenido" required maxlength="500" rows="4" 
                              placeholder="Comparte tu opinión..."
                              class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-pink-500 resize-none transition-all duration-200 dark:bg-gray-700 dark:border-gray-600"></textarea>
                    <div class="flex justify-between items-center mt-4">
                        <span class="text-sm opacity-70">Máximo 500 caracteres</span>
                        <button type="submit" 
                                class="px-6 py-2 bg-gradient-to-r from-pink-500 to-blue-500 text-white rounded-md hover:opacity-90 transition-opacity duration-200 flex items-center space-x-2">
                            <i data-feather="send" class="w-4 h-4"></i>
                            <span>Publicar</span>
                        </button>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <div class="rounded-lg shadow-md p-6 mb-8 text-center transition-all duration-300" id="loginPrompt">
                <i data-feather="lock" class="w-12 h-12 mx-auto mb-3 opacity-50"></i>
                <p class="mb-4 opacity-80">Debes iniciar sesión para participar en la discusión</p>
                <a href="Páginas/formularios.php?inicio" 
                   class="inline-block px-6 py-2 bg-gradient-to-r from-pink-500 to-blue-500 text-white rounded-md hover:opacity-90 transition-opacity duration-200">
                    Iniciar Sesión
                </a>
            </div>
        <?php endif; ?>

        <!-- Comentarios -->
        <div class="space-y-6">
            <h3 class="text-xl font-semibold mb-4 flex items-center space-x-2">
                <i data-feather="message-square" class="w-5 h-5"></i>
                <span>Comentarios (<?= $comentarios_result->num_rows ?>)</span>
            </h3>

            <?php if ($comentarios_result->num_rows > 0): ?>
                <?php while ($comentario = $comentarios_result->fetch_assoc()): ?>
                    <div class="rounded-lg shadow-md p-6 transition-all duration-300 commentCard" id="comment-<?= $comentario['com_id'] ?>">
                        <div class="flex items-start space-x-4">
                            <!-- Avatar del usuario -->
                            <div class="flex-shrink-0">
                                <?php if (!empty($comentario['usuario_imagen'])): ?>
                                    <img src="./uploads/avatars/<?= htmlspecialchars($comentario['usuario_imagen']) ?>" 
                                         alt="Avatar" class="w-10 h-10 rounded-full object-cover border-2 border-blue-400">
                                <?php else: ?>
                                    <div class="w-10 h-10 bg-gradient-to-r from-pink-500 to-blue-500 rounded-full flex items-center justify-center">
                                        <span class="text-white font-semibold">
                                            <?= strtoupper(substr($comentario['usuario_nombre'], 0, 1)) ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Contenido del comentario -->
                            <div class="flex-1">
                                <div class="flex items-center space-x-2 mb-2">
                                    <h4 class="font-semibold"><?= htmlspecialchars($comentario['usuario_nombre']) ?></h4>
                                    <span class="text-sm opacity-70 flex items-center space-x-1">
                                        <i data-feather="clock" class="w-3 h-3"></i>
                                        <span><?= date('d/m/Y H:i', strtotime($comentario['fecha'])) ?></span>
                                    </span>
                                </div>
                                
                                <p class="mb-4 opacity-90"><?= nl2br(htmlspecialchars($comentario['contenido'])) ?></p>
                                
                                <!-- Acciones del comentario -->
                                <div class="flex items-center space-x-4">
                                    <?php if (isset($_SESSION['usuario'])): ?>
                                        <button onclick="toggleReplyForm(<?= $comentario['com_id'] ?>)" 
                                                class="text-blue-500 hover:text-pink-500 text-sm font-medium flex items-center space-x-1 transition-colors duration-200">
                                            <i data-feather="corner-down-right" class="w-4 h-4"></i>
                                            <span>Responder</span>
                                        </button>
                                    <?php endif; ?>
                                    
                                    <?php if ($comentario['respuestas_count'] > 0): ?>
                                        <button onclick="toggleReplies(<?= $comentario['com_id'] ?>)" 
                                                class="text-pink-500 hover:text-blue-500 text-sm font-medium flex items-center space-x-1 transition-colors duration-200"
                                                id="toggle-btn-<?= $comentario['com_id'] ?>">
                                            <i data-feather="message-circle" class="w-4 h-4"></i>
                                            <span>Ver <?= $comentario['respuestas_count'] ?> respuestas</span>
                                        </button>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Formulario de respuesta (oculto inicialmente) -->
                                <?php if (isset($_SESSION['usuario'])): ?>
                                    <div id="reply-form-<?= $comentario['com_id'] ?>" class="hidden mt-4">
                                        <form action="procesar_comentario.php" method="POST">
                                            <input type="hidden" name="foro_id" value="<?= $foro_id ?>">
                                            <input type="hidden" name="com_padre_id" value="<?= $comentario['com_id'] ?>">
                                            <textarea name="contenido" required maxlength="500" rows="3" 
                                                      placeholder="Escribe tu respuesta..."
                                                      class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-pink-500 resize-none transition-all duration-200 dark:bg-gray-700 dark:border-gray-600"></textarea>
                                            <div class="flex justify-end mt-2 space-x-2">
                                                <button type="button" onclick="toggleReplyForm(<?= $comentario['com_id'] ?>)"
                                                        class="px-4 py-2 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200">
                                                    Cancelar
                                                </button>
                                                <button type="submit" 
                                                        class="px-4 py-2 bg-gradient-to-r from-pink-500 to-blue-500 text-white rounded-md hover:opacity-90 transition-opacity duration-200 flex items-center space-x-1">
                                                    <i data-feather="send" class="w-4 h-4"></i>
                                                    <span>Responder</span>
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Respuestas (cargadas dinámicamente) -->
                                <div id="replies-<?= $comentario['com_id'] ?>" class="hidden mt-4 ml-6 space-y-4 border-l-2 border-blue-300 dark:border-blue-700 pl-4">
                                    <!-- Las respuestas se cargarán aquí -->
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="text-center py-12">
                    <i data-feather="message-circle" class="w-16 h-16 mx-auto mb-4 opacity-50"></i>
                    <h3 class="text-lg font-medium mb-2">No hay comentarios aún</h3>
                    <p class="opacity-70">Sé el primero en comentar en este foro</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Initialize Feather Icons
        feather.replace();

        // Theme Toggle
        const themeToggle = document.getElementById('themeToggle');
        const body = document.getElementById('body');
        const navbar = document.getElementById('navbar');
        const foroHeader = document.getElementById('foroHeader');
        const commentForm = document.getElementById('commentForm');
        const loginPrompt = document.getElementById('loginPrompt');
        const successAlert = document.getElementById('successAlert');
        const errorAlert = document.getElementById('errorAlert');

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
            
            if (foroHeader) foroHeader.className = 'rounded-lg shadow-md mb-8 overflow-hidden transition-all duration-300 bg-gray-800 text-white';
            if (commentForm) commentForm.className = 'rounded-lg shadow-md p-6 mb-8 transition-all duration-300 bg-gray-800 text-white';
            if (loginPrompt) loginPrompt.className = 'rounded-lg shadow-md p-6 mb-8 text-center transition-all duration-300 bg-gray-800 text-white';
            if (successAlert) successAlert.className = 'p-4 mb-4 rounded-lg flex items-center space-x-2 transition-all duration-300 bg-green-900 border border-green-600';
            if (errorAlert) errorAlert.className = 'p-4 mb-4 rounded-lg flex items-center space-x-2 transition-all duration-300 bg-red-900 border border-red-600';
            
            document.querySelectorAll('.commentCard').forEach(card => {
                card.className = 'rounded-lg shadow-md p-6 transition-all duration-300 commentCard bg-gray-800 text-white';
            });
        }

        function enableLightMode() {
            body.className = 'min-h-screen transition-all duration-300 bg-gradient-to-br from-pink-100 to-blue-100 text-gray-900';
            navbar.className = 'shadow-lg transition-all duration-300 border-b-2 bg-white text-gray-900 border-pink-300';
            
            if (foroHeader) foroHeader.className = 'rounded-lg shadow-md mb-8 overflow-hidden transition-all duration-300 bg-white text-gray-900';
            if (commentForm) commentForm.className = 'rounded-lg shadow-md p-6 mb-8 transition-all duration-300 bg-white text-gray-900';
            if (loginPrompt) loginPrompt.className = 'rounded-lg shadow-md p-6 mb-8 text-center transition-all duration-300 bg-white text-gray-900';
            if (successAlert) successAlert.className = 'p-4 mb-4 rounded-lg flex items-center space-x-2 transition-all duration-300 bg-green-100 border border-green-400';
            if (errorAlert) errorAlert.className = 'p-4 mb-4 rounded-lg flex items-center space-x-2 transition-all duration-300 bg-red-100 border border-red-400';
            
            document.querySelectorAll('.commentCard').forEach(card => {
                card.className = 'rounded-lg shadow-md p-6 transition-all duration-300 commentCard bg-white text-gray-900';
            });
        }

        function toggleReplyForm(commentId) {
            const replyForm = document.getElementById(`reply-form-${commentId}`);
            replyForm.classList.toggle('hidden');
            
            if (!replyForm.classList.contains('hidden')) {
                const textarea = replyForm.querySelector('textarea');
                textarea.focus();
            }
            
            feather.replace();
        }

        function toggleReplies(commentId) {
            const repliesDiv = document.getElementById(`replies-${commentId}`);
            const toggleBtn = document.getElementById(`toggle-btn-${commentId}`);
            
            if (repliesDiv.classList.contains('hidden')) {
                if (repliesDiv.innerHTML.trim() === '<!-- Las respuestas se cargarán aquí -->') {
                    fetch(`obtener_respuestas.php?comment_id=${commentId}`)
                        .then(response => response.text())
                        .then(data => {
                            repliesDiv.innerHTML = data;
                            repliesDiv.classList.remove('hidden');
                            toggleBtn.innerHTML = '<i data-feather="message-circle" class="w-4 h-4"></i><span>Ocultar respuestas</span>';
                            feather.replace();
                        });
                } else {
                    repliesDiv.classList.remove('hidden');
                    toggleBtn.innerHTML = '<i data-feather="message-circle" class="w-4 h-4"></i><span>Ocultar respuestas</span>';
                    feather.replace();
                }
            } else {
                repliesDiv.classList.add('hidden');
                const respuestasCount = toggleBtn.textContent.match(/\d+/)[0];
                toggleBtn.innerHTML = `<i data-feather="message-circle" class="w-4 h-4"></i><span>Ver ${respuestasCount} respuestas</span>`;
                feather.replace();
            }
        }

        // Auto-resize textarea
        document.addEventListener('input', function(e) {
            if (e.target.tagName.toLowerCase() === 'textarea') {
                e.target.style.height = 'auto';
                e.target.style.height = (e.target.scrollHeight) + 'px';
            }
        });

        // Auto-cerrar alertas después de 5 segundos
        setTimeout(() => {
            const alerts = [successAlert, errorAlert].filter(a => a);
            alerts.forEach(alert => {
                if (alert) {
                    alert.style.transition = 'opacity 0.5s';
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 500);
                }
            });
        }, 5000);

        // Refresh icons
        feather.replace();
    </script>
</body>
</html>