<?php
session_start();
require_once("conexion.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $usu_id = $_POST['usu_id'];
    $peli_id = $_POST['peli_id'];
    $puntuacion = $_POST['puntuacion'];
    $contenido = trim($_POST['contenido']);

    if ($contenido === "") {
        header("Location: en_desarrollo.php?peli_id=$peli_id&error=empty");
        exit;
    }

    $likes = 0;
    $dislikes = 0;
    $est_id = 1;

    $stmt = $conexion->prepare("
        INSERT INTO opiniones (usu_id, peli_id, puntuacion, contenido, op_likes, op_dislikes, fecha, est_id)
        VALUES (?, ?, ?, ?, ?, ?, NOW(), ?)
    ");
    $stmt->bind_param("iiisiii", $usu_id, $peli_id, $puntuacion, $contenido, $likes, $dislikes, $est_id);
    $stmt->execute();
    $stmt->close();

    header("Location: en_desarrollo.php?peli_id=" . $peli_id);
    exit;
}
?>
