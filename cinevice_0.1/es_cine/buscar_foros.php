<?php
session_start();
require_once("conexion.php");

header('Content-Type: application/json');

if (!isset($_GET['q']) || empty(trim($_GET['q']))) {
    echo json_encode([]);
    exit;
}

$query = trim($_GET['q']);

$stmt = $conexion->prepare("
    SELECT nombre 
    FROM foros 
    WHERE nombre LIKE ? AND est_id = 1 
    ORDER BY nombre 
    LIMIT 5
");
$search_term = $query . "%";
$stmt->bind_param("s", $search_term);
$stmt->execute();
$result = $stmt->get_result();

$suggestions = [];
while ($row = $result->fetch_assoc()) {
    $suggestions[] = $row;
}

echo json_encode($suggestions);
$stmt->close();
?>