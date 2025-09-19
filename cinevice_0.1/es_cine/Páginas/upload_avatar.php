<?php
// upload_avatar.php - Script independiente para subir avatares (opcional)
session_start();
require_once("../conexion.php");

if (!isset($_SESSION['usuario']) || !isset($_FILES['avatar'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Error: No autorizado o archivo no válido']);
    exit;
}

$usuario = $_SESSION['usuario'];
$upload_dir = '../uploads/avatars/';

// Crear directorio si no existe
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$file = $_FILES['avatar'];
$allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
$max_size = 5 * 1024 * 1024; // 5MB

// Validaciones mejoradas
if ($file['error'] !== 0) {
    echo json_encode(['success' => false, 'message' => 'Error al subir archivo']);
    exit;
}

if (!in_array($file['type'], $allowed_types)) {
    echo json_encode(['success' => false, 'message' => 'Tipo de archivo no permitido. Use JPG, PNG o GIF']);
    exit;
}

if ($file['size'] > $max_size) {
    echo json_encode(['success' => false, 'message' => 'Archivo muy grande. Máximo 5MB']);
    exit;
}

// Validar que realmente es una imagen
$image_info = getimagesize($file['tmp_name']);
if ($image_info === false) {
    echo json_encode(['success' => false, 'message' => 'El archivo no es una imagen válida']);
    exit;
}

// Eliminar imagen anterior si existe
if (!empty($usuario['imagen'])) {
    $old_image = $upload_dir . $usuario['imagen'];
    if (file_exists($old_image)) {
        unlink($old_image);
    }
}

// Generar nombre único y seguro
$extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$filename = 'user_' . $usuario['id'] . '_' . uniqid() . '.' . $extension;
$upload_path = $upload_dir . $filename;

if (move_uploaded_file($file['tmp_name'], $upload_path)) {
    // Redimensionar imagen si es muy grande (opcional)
    resizeImage($upload_path, 400, 400);
    
    // Actualizar base de datos
    $query = "UPDATE usuarios SET imagen = ? WHERE usu_id = ?";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("si", $filename, $usuario['id']);
    
    if ($stmt->execute()) {
        // Actualizar sesión
        $_SESSION['usuario']['imagen'] = $filename;
        echo json_encode([
            'success' => true, 
            'message' => 'Imagen actualizada correctamente',
            'image_url' => $upload_dir . $filename
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al guardar en base de datos']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Error al guardar archivo']);
}

// Función para redimensionar imagen
function resizeImage($source, $max_width, $max_height) {
    $image_info = getimagesize($source);
    $width = $image_info[0];
    $height = $image_info[1];
    $mime = $image_info['mime'];
    
    // Si la imagen ya es pequeña, no hacer nada
    if ($width <= $max_width && $height <= $max_height) {
        return;
    }
    
    // Calcular nuevas dimensiones manteniendo proporción
    $ratio = min($max_width / $width, $max_height / $height);
    $new_width = intval($width * $ratio);
    $new_height = intval($height * $ratio);
    
    // Crear imagen fuente
    switch ($mime) {
        case 'image/jpeg':
            $source_image = imagecreatefromjpeg($source);
            break;
        case 'image/png':
            $source_image = imagecreatefrompng($source);
            break;
        case 'image/gif':
            $source_image = imagecreatefromgif($source);
            break;
        default:
            return;
    }
    
    // Crear nueva imagen
    $new_image = imagecreatetruecolor($new_width, $new_height);
    
    // Preservar transparencia para PNG y GIF
    if ($mime == 'image/png' || $mime == 'image/gif') {
        imagealphablending($new_image, false);
        imagesavealpha($new_image, true);
        $transparent = imagecolorallocatealpha($new_image, 255, 255, 255, 127);
        imagefilledrectangle($new_image, 0, 0, $new_width, $new_height, $transparent);
    }
    
    // Redimensionar
    imagecopyresampled($new_image, $source_image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
    
    // Guardar imagen redimensionada
    switch ($mime) {
        case 'image/jpeg':
            imagejpeg($new_image, $source, 85);
            break;
        case 'image/png':
            imagepng($new_image, $source, 8);
            break;
        case 'image/gif':
            imagegif($new_image, $source);
            break;
    }
    
    // Limpiar memoria
    imagedestroy($source_image);
    imagedestroy($new_image);
}
?>