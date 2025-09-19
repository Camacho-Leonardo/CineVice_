<?php
session_start();
require_once("conexion.php");

// Verificar que el usuario esté logueado
if (!isset($_SESSION['usuario'])) {
    $_SESSION['error'] = "Debes iniciar sesión para comentar.";
    header("Location: foros.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $foro_id = (int)$_POST['foro_id'];
    $contenido = trim($_POST['contenido']);
    $com_padre_id = isset($_POST['com_padre_id']) ? (int)$_POST['com_padre_id'] : null;
    $usu_id = $_SESSION['usuario']['id'];
    
    // Validaciones
    if (empty($contenido)) {
        $_SESSION['error'] = "El contenido del comentario no puede estar vacío.";
        header("Location: foro_detalle.php?id=" . $foro_id);
        exit;
    }
    
    if (strlen($contenido) > 500) {
        $_SESSION['error'] = "El comentario no puede tener más de 500 caracteres.";
        header("Location: foro_detalle.php?id=" . $foro_id);
        exit;
    }
    
    // Verificar que el foro existe
    $stmt = $conexion->prepare("SELECT foro_id FROM foros WHERE foro_id = ? AND est_id = 1");
    $stmt->bind_param("i", $foro_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $_SESSION['error'] = "El foro no existe.";
        header("Location: foros.php");
        exit;
    }
    $stmt->close();
    
    // Si es una respuesta, verificar que el comentario padre existe
    if ($com_padre_id) {
        $stmt = $conexion->prepare("SELECT com_id FROM comentarios WHERE com_id = ? AND foro_id = ? AND est_id = 1");
        $stmt->bind_param("ii", $com_padre_id, $foro_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $_SESSION['error'] = "El comentario al que intentas responder no existe.";
            header("Location: foro_detalle.php?id=" . $foro_id);
            exit;
        }
        $stmt->close();
    }
    
    // Filtro de palabras prohibidas
    $stmt = $conexion->prepare("SELECT palabra FROM prohibidas WHERE est_id = 1");
    $stmt->execute();
    $palabras_result = $stmt->get_result();
    
    $contenido_lower = strtolower($contenido);
    $palabras_prohibidas = [];
    
    while ($palabra = $palabras_result->fetch_assoc()) {
        if (strpos($contenido_lower, strtolower($palabra['palabra'])) !== false) {
            $palabras_prohibidas[] = $palabra['palabra'];
        }
    }
    $stmt->close();
    
    $estado_comentario = 1; // Activo por defecto
    
    // Si hay palabras prohibidas, marcar como en revisión
    if (!empty($palabras_prohibidas)) {
        $estado_comentario = 3; // En revisión
        
        // Registrar en la tabla usu_pro
        foreach ($palabras_prohibidas as $palabra_prohibida) {
            $stmt_pro = $conexion->prepare("SELECT pro_id FROM prohibidas WHERE palabra = ? AND est_id = 1");
            $stmt_pro->bind_param("s", $palabra_prohibida);
            $stmt_pro->execute();
            $pro_result = $stmt_pro->get_result();
            
            if ($pro_result->num_rows > 0) {
                $pro_row = $pro_result->fetch_assoc();
                $pro_id = $pro_row['pro_id'];
                
                $stmt_usu_pro = $conexion->prepare("
                    INSERT INTO usu_pro (usu_id, pro_id, ori_id, origen, contenido, fecha, est_id) 
                    VALUES (?, ?, 2, ?, ?, NOW(), 3)
                ");
                $origen_id = $com_padre_id ? $com_padre_id : $foro_id;
                $stmt_usu_pro->bind_param("iiis", $usu_id, $pro_id, $origen_id, $contenido);
                $stmt_usu_pro->execute();
                $stmt_usu_pro->close();
            }
            $stmt_pro->close();
        }
    }
    
    // Insertar comentario
    if ($com_padre_id) {
        $stmt = $conexion->prepare("
            INSERT INTO comentarios (usu_id, foro_id, contenido, com_padre_id, fecha, est_id) 
            VALUES (?, ?, ?, ?, NOW(), ?)
        ");
        $stmt->bind_param("iisii", $usu_id, $foro_id, $contenido, $com_padre_id, $estado_comentario);
    } else {
        $stmt = $conexion->prepare("
            INSERT INTO comentarios (usu_id, foro_id, contenido, fecha, est_id) 
            VALUES (?, ?, ?, NOW(), ?)
        ");
        $stmt->bind_param("iisi", $usu_id, $foro_id, $contenido, $estado_comentario);
    }
    
    if ($stmt->execute()) {
        if ($estado_comentario === 3) {
            $_SESSION['success'] = "Tu comentario ha sido enviado y está en revisión debido a contenido que podría ser inapropiado.";
        } else {
            $_SESSION['success'] = "Comentario publicado exitosamente.";
        }
    } else {
        $_SESSION['error'] = "Error al publicar el comentario. Inténtalo de nuevo.";
    }
    
    $stmt->close();
    
} else {
    $_SESSION['error'] = "Método de acceso no válido.";
}

// Redirigir de vuelta al foro
header("Location: foro_detalle.php?id=" . $foro_id);
exit;
?>