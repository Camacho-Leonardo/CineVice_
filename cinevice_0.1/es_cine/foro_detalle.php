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
                    <a href="index.php" class="flex items-center space-x-2">
                        <img src="./Imágenes/cine-vice-navbar.png" alt="CineVice" class="h-10">
                    </a>
                </div>

                <!-- Breadcrumb -->
                <div class="flex-1 mx-8">
                    <nav class="flex" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 md:space-x-3">
                            <li class="inline-flex items-center">
                                <a href="foros.php" class="text-gray-700 hover:text-cinevice-pink">Foros</a>
                            </li>
                            <li>
                                <div class="flex items-center">
                                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span class="ml-1 text-cinevice-pink font-medium"><?= htmlspecialchars($foro['nombre']) ?></span>
                                </div>
                            </li>
                        </ol>
                    </nav>
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

    <div class="max-w-4xl mx-auto px-4 py-8">
        <!-- Header del Foro -->
        <div class="bg-white rounded-lg shadow-md mb-8 overflow-hidden">
            <?php if (!empty($foro['imagen'])): ?>
                <img src="./uploads/foros/<?= htmlspecialchars($foro['imagen']) ?>" 
                     alt="Imagen del foro" 
                     class="w-full h-64 object-cover">
            <?php else: ?>
                <div class="w-full h-64 bg-gradient-to-br from-cinevice-pink to-cinevice-blue flex items-center justify-center">
                    <svg class="w-20 h-20 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                </div>
            <?php endif; ?>
            
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h1 class="text-3xl font-bold text-gray-900"><?= htmlspecialchars($foro['nombre']) ?></h1>
                    <?php if ($foro['genero_nombre']): ?>
                        <span class="bg-cinevice-blue text-white px-3 py-1 rounded-full text-sm">
                            <?= htmlspecialchars($foro['genero_nombre']) ?>
                        </span>
                    <?php endif; ?>
                </div>
                
                <p class="text-gray-700 text-lg mb-4"><?= nl2br(htmlspecialchars($foro['descripcion'])) ?></p>
                
                <div class="flex items-center text-sm text-gray-500">
                    <span>Creado por <strong><?= htmlspecialchars($foro['creador_nombre']) ?></strong></span>
                    <span class="mx-2">•</span>
                    <span><?= date('d/m/Y H:i', strtotime($foro['creacion'])) ?></span>
                </div>
            </div>
        </div>

        <!-- Mensajes de alerta -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?= htmlspecialchars($_SESSION['success']) ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?= htmlspecialchars($_SESSION['error']) ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- Formulario de nuevo comentario -->
        <?php if (isset($_SESSION['usuario'])): ?>
            <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Escribir un comentario</h3>
                <form action="procesar_comentario.php" method="POST">
                    <input type="hidden" name="foro_id" value="<?= $foro_id ?>">
                    <textarea name="contenido" required maxlength="500" rows="4" 
                              placeholder="Comparte tu opinión..."
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-cinevice-pink resize-none"></textarea>
                    <div class="flex justify-between items-center mt-4">
                        <span class="text-sm text-gray-500">Máximo 500 caracteres</span>
                        <button type="submit" 
                                class="bg-cinevice-pink text-white px-6 py-2 rounded-md hover:bg-cinevice-blue transition">
                            Publicar Comentario
                        </button>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-lg shadow-md p-6 mb-8 text-center">
                <p class="text-gray-600 mb-4">Debes iniciar sesión para participar en la discusión</p>
                <a href="Páginas/formularios.php?inicio" 
                   class="bg-cinevice-pink text-white px-6 py-2 rounded-md hover:bg-cinevice-blue transition">
                    Iniciar Sesión
                </a>
            </div>
        <?php endif; ?>

        <!-- Comentarios -->
        <div class="space-y-6">
            <h3 class="text-xl font-semibold text-gray-900 mb-4">
                Comentarios (<?= $comentarios_result->num_rows ?>)
            </h3>

            <?php if ($comentarios_result->num_rows > 0): ?>
                <?php while ($comentario = $comentarios_result->fetch_assoc()): ?>
                    <div class="bg-white rounded-lg shadow-md p-6" id="comment-<?= $comentario['com_id'] ?>">
                        <div class="flex items-start space-x-4">
                            <!-- Avatar del usuario -->
                            <div class="flex-shrink-0">
                                <?php if (!empty($comentario['usuario_imagen'])): ?>
                                    <img src="./uploads/usuarios/<?= htmlspecialchars($comentario['usuario_imagen']) ?>" 
                                         alt="Avatar" class="w-10 h-10 rounded-full object-cover">
                                <?php else: ?>
                                    <div class="w-10 h-10 bg-cinevice-pink rounded-full flex items-center justify-center">
                                        <span class="text-white font-semibold">
                                            <?= strtoupper(substr($comentario['usuario_nombre'], 0, 1)) ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Contenido del comentario -->
                            <div class="flex-1">
                                <div class="flex items-center space-x-2 mb-2">
                                    <h4 class="font-semibold text-gray-900"><?= htmlspecialchars($comentario['usuario_nombre']) ?></h4>
                                    <span class="text-sm text-gray-500"><?= date('d/m/Y H:i', strtotime($comentario['fecha'])) ?></span>
                                </div>
                                
                                <p class="text-gray-700 mb-4"><?= nl2br(htmlspecialchars($comentario['contenido'])) ?></p>
                                
                                <!-- Acciones del comentario -->
                                <div class="flex items-center space-x-4">
                                    <?php if (isset($_SESSION['usuario'])): ?>
                                        <button onclick="toggleReplyForm(<?= $comentario['com_id'] ?>)" 
                                                class="text-cinevice-pink hover:text-cinevice-blue text-sm font-medium">
                                            Responder
                                        </button>
                                    <?php endif; ?>
                                    
                                    <?php if ($comentario['respuestas_count'] > 0): ?>
                                        <button onclick="toggleReplies(<?= $comentario['com_id'] ?>)" 
                                                class="text-cinevice-blue hover:text-cinevice-pink text-sm font-medium"
                                                id="toggle-btn-<?= $comentario['com_id'] ?>">
                                            Ver <?= $comentario['respuestas_count'] ?> respuestas
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
                                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-cinevice-pink resize-none"></textarea>
                                            <div class="flex justify-end mt-2 space-x-2">
                                                <button type="button" onclick="toggleReplyForm(<?= $comentario['com_id'] ?>)"
                                                        class="px-4 py-2 text-gray-600 hover:text-gray-800">
                                                    Cancelar
                                                </button>
                                                <button type="submit" 
                                                        class="bg-cinevice-pink text-white px-4 py-2 rounded-md hover:bg-cinevice-blue transition">
                                                    Responder
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Respuestas (cargadas dinámicamente) -->
                                <div id="replies-<?= $comentario['com_id'] ?>" class="hidden mt-4 ml-6 space-y-4 border-l-2 border-gray-200 pl-4">
                                    <!-- Las respuestas se cargarán aquí -->
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="text-center py-12">
                    <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No hay comentarios aún</h3>
                    <p class="text-gray-500">Sé el primero en comentar en este foro</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function toggleReplyForm(commentId) {
            const replyForm = document.getElementById(`reply-form-${commentId}`);
            replyForm.classList.toggle('hidden');
            
            // Enfocar el textarea si se abre el formulario
            if (!replyForm.classList.contains('hidden')) {
                const textarea = replyForm.querySelector('textarea');
                textarea.focus();
            }
        }

        function toggleReplies(commentId) {
            const repliesDiv = document.getElementById(`replies-${commentId}`);
            const toggleBtn = document.getElementById(`toggle-btn-${commentId}`);
            
            if (repliesDiv.classList.contains('hidden')) {
                // Cargar respuestas si no están cargadas
                if (repliesDiv.innerHTML.trim() === '<!-- Las respuestas se cargarán aquí -->') {
                    fetch(`obtener_respuestas.php?comment_id=${commentId}`)
                        .then(response => response.text())
                        .then(data => {
                            repliesDiv.innerHTML = data;
                            repliesDiv.classList.remove('hidden');
                            toggleBtn.textContent = 'Ocultar respuestas';
                        });
                } else {
                    repliesDiv.classList.remove('hidden');
                    toggleBtn.textContent = 'Ocultar respuestas';
                }
            } else {
                repliesDiv.classList.add('hidden');
                const respuestasCount = toggleBtn.textContent.match(/\d+/)[0];
                toggleBtn.textContent = `Ver ${respuestasCount} respuestas`;
            }
        }

        // Auto-resize textarea
        document.addEventListener('input', function(e) {
            if (e.target.tagName.toLowerCase() === 'textarea') {
                e.target.style.height = 'auto';
                e.target.style.height = (e.target.scrollHeight) + 'px';
            }
        });
    </script>
</body>
</html>