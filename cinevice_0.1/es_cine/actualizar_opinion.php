<?php
session_start();
require_once("conexion.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $op_id = $_POST['op_id'];
    $peli_id = $_POST['peli_id'];
    $contenido = trim($_POST['contenido']);
    $puntuacion = $_POST['puntuacion'];

    $stmt = $conexion->prepare("
        UPDATE opiniones SET contenido = ?, puntuacion = ?, fecha = NOW()
        WHERE op_id = ? AND usu_id = ?
    ");
    $stmt->bind_param("siii", $contenido, $puntuacion, $op_id, $_SESSION['usuario']['id']);
    $stmt->execute();
    $stmt->close();

    header("Location: en_desarrollo.php?peli_id=$peli_id");
    exit;
}
?>
