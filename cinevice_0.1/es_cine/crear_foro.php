<?php
session_start();
require_once("conexion.php");

// Verificar que el usuario esté logueado
if (!isset($_SESSION['usuario'])) {
    header("Location: Páginas/formularios.php?inicio");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $genero_id = !empty($_POST['genero_id']) ? (int)$_POST['genero_id'] : null;
    $usu_id = $_SESSION['usuario']['id'];
    
    // Validaciones
    if (empty($nombre) || empty($descripcion)) {
        $_SESSION['error'] = "Nombre y descripción son obligatorios.";
        header("Location: foros.php");
        exit;
    }
    
    if (strlen($nombre) > 50) {
        $_SESSION['error'] = "El nombre no puede tener más de 50 caracteres.";
        header("Location: foros.php");
        exit;
    }
    
    if (strlen($descripcion) > 500) {
        $_SESSION['error'] = "La descripción no puede tener más de 500 caracteres.";
        header("Location: foros.php");
        exit;
    }
    
    // Procesar imagen
    $imagen_nombre = "";
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = "./uploads/foros/";
        
        // Crear directorio si no existe
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($file_extension, $allowed_extensions)) {
            $imagen_nombre = uniqid() . "." . $file_extension;
            $upload_path = $upload_dir . $imagen_nombre;
            
            if (!move_uploaded_file($_FILES['imagen']['tmp_name'], $upload_path)) {
                $_SESSION['error'] = "Error al subir la imagen.";
                header("Location: foros.php");
                exit;
            }
        } else {
            $_SESSION['error'] = "Formato de imagen no válido. Use JPG, JPEG, PNG o GIF.";
            header("Location: foros.php");
            exit;
        }
    }
    
    // Insertar foro en la base de datos
    $stmt = $conexion->prepare("
        INSERT INTO foros (usu_id, nombre, descripcion, genero_id, creacion, est_id, imagen) 
        VALUES (?, ?, ?, ?, NOW(), 1, ?)
    ");
    
    $stmt->bind_param("issis", $usu_id, $nombre, $descripcion, $genero_id, $imagen_nombre);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Foro creado exitosamente.";
        $foro_id = $conexion->insert_id;
        header("Location: foro_detalle.php?id=" . $foro_id);
    } else {
        $_SESSION['error'] = "Error al crear el foro. Inténtalo de nuevo.";
        header("Location: foros.php");
    }
    
    $stmt->close();
} else {
    header("Location: foros.php");
}
?>