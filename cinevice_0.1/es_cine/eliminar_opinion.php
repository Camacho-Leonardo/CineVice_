<?php
session_start();
require_once("conexion.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $op_id = $_POST['op_id'];
    $peli_id = $_POST['peli_id'];

    $stmt = $conexion->prepare("UPDATE opiniones SET est_id = 2 WHERE op_id = ? AND usu_id = ?");
    $stmt->bind_param("ii", $op_id, $_SESSION['usuario']['id']);
    $stmt->execute();
    $stmt->close();

    header("Location: en_desarrollo.php?peli_id=$peli_id");
    exit;
}
?>
