<?php
if (isset($_GET['peli_id'])) {
    $peli_id = intval($_GET['peli_id']);
    header("Location: en_desarrollo.php?peli_id=$peli_id");
    exit;
}
?>

<p>Película no encontrada.</p>
