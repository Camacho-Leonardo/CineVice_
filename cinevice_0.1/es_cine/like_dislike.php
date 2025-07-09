<?php
session_start();
require_once("conexion.php");

if (!isset($_SESSION['usuario'])) {
    header("Location: formularios.php?inicio");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $opinion_id = $_POST['opinion_id'] ?? null;
    $accion = $_POST['accion'] ?? null;

    if ($opinion_id && in_array($accion, ['like', 'dislike'])) {
        $campo = $accion === 'like' ? 'op_likes' : 'op_dislikes';

        $stmt = $conexion->prepare("UPDATE opiniones SET $campo = $campo + 1 WHERE op_id = ?");
        $stmt->bind_param("i", $opinion_id);
        $stmt->execute();
        $stmt->close();
    }

// Redirigir a la página anterior (donde se hizo clic)
if (!empty($_SERVER['HTTP_REFERER'])) {
    header("Location: " . $_SERVER['HTTP_REFERER']);
} else {
    header("Location: index.php");
}
exit;
}
?>
