<?php
// get_user_avatar.php - Helper function para obtener avatar de usuario
// Úsalo en otras páginas para mostrar la foto de perfil

/**
 * Obtiene la ruta de la imagen de perfil de un usuario
 * @param int $user_id ID del usuario
 * @param mysqli $conexion Conexión a la base de datos
 * @param string $default_path Ruta de imagen por defecto
 * @return string Ruta de la imagen de perfil
 */
function getUserAvatar($user_id, $conexion, $default_path = '../Imágenes/default-avatar.png') {
    $query = "SELECT imagen FROM usuarios WHERE usu_id = ?";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($user = $result->fetch_assoc()) {
        if (!empty($user['imagen']) && file_exists('../uploads/avatars/' . $user['imagen'])) {
            return '../uploads/avatars/' . $user['imagen'];
        }
    }
    
    return $default_path;
}

/**
 * Obtiene la información completa del usuario incluyendo avatar
 * @param int $user_id ID del usuario
 * @param mysqli $conexion Conexión a la base de datos
 * @return array|null Información del usuario o null si no existe
 */
function getUserWithAvatar($user_id, $conexion) {
    $query = "SELECT usu_id, nombre, email, imagen, rol_id FROM usuarios WHERE usu_id = ?";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($user = $result->fetch_assoc()) {
        // Añadir ruta completa de avatar
        $user['avatar_path'] = !empty($user['imagen']) && file_exists('../uploads/avatars/' . $user['imagen']) 
            ? '../uploads/avatars/' . $user['imagen'] 
            : '../Imágenes/default-avatar.png';
        return $user;
    }
    
    return null;
}

// Ejemplo de uso en otras páginas:
/*
// En foros.php, comentarios.php, etc:
require_once("get_user_avatar.php");

// Obtener solo la imagen
$user_avatar = getUserAvatar($_SESSION['usuario']['id'], $conexion);
echo '<img src="' . $user_avatar . '" class="w-8 h-8 rounded-full">';

// Obtener información completa del usuario
$user_data = getUserWithAvatar($_SESSION['usuario']['id'], $conexion);
echo '<img src="' . $user_data['avatar_path'] . '" class="w-8 h-8 rounded-full">';
echo '<span>' . htmlspecialchars($user_data['nombre']) . '</span>';
*/
?>