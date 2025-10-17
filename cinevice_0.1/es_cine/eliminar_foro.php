<?php
session_start();
require_once("conexion.php");

// Verificar que el usuario esté logueado
if (!isset($_SESSION['usuario'])) {
    header("Location: Páginas/formularios.php?inicio");
    exit;
}

// Verificar que se haya enviado el ID del foro
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: foros.php");
    exit;
}

$foro_id = (int)$_GET['id'];
$usuario_id = $_SESSION['usuario']['id'];

// Verificar que el foro existe y pertenece al usuario
$query_verificar = "SELECT usu_id, imagen FROM foros WHERE foro_id = ? AND est_id = 1";
$stmt = $conexion->prepare($query_verificar);
$stmt->bind_param("i", $foro_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // El foro no existe o ya fue eliminado
    header("Location: foros.php");
    exit;
}

$foro = $result->fetch_assoc();

// Verificar que el usuario sea el propietario del foro
if ($foro['usu_id'] != $usuario_id) {
    // El usuario no es el propietario, no puede eliminar
    header("Location: foros.php");
    exit;
}

// Eliminar la imagen del foro si existe
if (!empty($foro['imagen'])) {
    $image_path = './uploads/foros/' . $foro['imagen'];
    if (file_exists($image_path)) {
        unlink($image_path);
    }
}

// Cambiar el estado del foro a inactivo (est_id = 2) en lugar de eliminarlo físicamente
$query_eliminar = "UPDATE foros SET est_id = 2 WHERE foro_id = ?";
$stmt_eliminar = $conexion->prepare($query_eliminar);
$stmt_eliminar->bind_param("i", $foro_id);

if ($stmt_eliminar->execute()) {
    // También desactivar todos los comentarios asociados al foro
    $query_comentarios = "UPDATE comentarios SET est_id = 2 WHERE foro_id = ?";
    $stmt_comentarios = $conexion->prepare($query_comentarios);
    $stmt_comentarios->bind_param("i", $foro_id);
    $stmt_comentarios->execute();
    
    // Redirigir con mensaje de éxito
    header("Location: foros.php?deleted=success");
} else {
    // Redirigir con mensaje de error
    header("Location: foros.php?deleted=error");
}

exit;
?>