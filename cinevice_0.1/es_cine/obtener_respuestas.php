<?php
session_start();
require_once("conexion.php");

if (!isset($_GET['comment_id']) || !is_numeric($_GET['comment_id'])) {
    echo "<p class='text-red-500'>ID de comentario no válido</p>";
    exit;
}

$comment_id = (int)$_GET['comment_id'];

// Obtener respuestas del comentario
$stmt = $conexion->prepare("
    SELECT c.*, u.nombre as usuario_nombre, u.imagen as usuario_imagen
    FROM comentarios c
    LEFT JOIN usuarios u ON c.usu_id = u.usu_id
    WHERE c.com_padre_id = ? AND c.est_id = 1
    ORDER BY c.fecha ASC
");
$stmt->bind_param("i", $comment_id);
$stmt->execute();
$respuestas_result = $stmt->get_result();

if ($respuestas_result->num_rows > 0):
    while ($respuesta = $respuestas_result->fetch_assoc()):
?>
        <div class="bg-gray-50 rounded-lg p-4">
            <div class="flex items-start space-x-3">
                <!-- Avatar del usuario -->
                <div class="flex-shrink-0">
                    <?php if (!empty($respuesta['usuario_imagen'])): ?>
                        <img src="./uploads/usuarios/<?= htmlspecialchars($respuesta['usuario_imagen']) ?>" 
                             alt="Avatar" class="w-8 h-8 rounded-full object-cover">
                    <?php else: ?>
                        <div class="w-8 h-8 bg-cinevice-blue rounded-full flex items-center justify-center">
                            <span class="text-white text-sm font-semibold">
                                <?= strtoupper(substr($respuesta['usuario_nombre'], 0, 1)) ?>
                            </span>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Contenido de la respuesta -->
                <div class="flex-1">
                    <div class="flex items-center space-x-2 mb-1">
                        <h5 class="font-medium text-gray-900 text-sm"><?= htmlspecialchars($respuesta['usuario_nombre']) ?></h5>
                        <span class="text-xs text-gray-500"><?= date('d/m/Y H:i', strtotime($respuesta['fecha'])) ?></span>
                    </div>
                    
                    <p class="text-gray-700 text-sm"><?= nl2br(htmlspecialchars($respuesta['contenido'])) ?></p>
                </div>
            </div>
        </div>
<?php 
    endwhile;
else:
?>
    <p class="text-gray-500 text-sm">No hay respuestas para este comentario.</p>
<?php 
endif;

$stmt->close();
?>