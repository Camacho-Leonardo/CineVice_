<?php
session_start();
require_once("../conexion.php");

// LOGIN
if (isset($_POST['inicioUsu'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conexion->prepare("SELECT * FROM usuarios WHERE email = ? AND est_id = 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $usuario = $resultado->fetch_assoc();
    $stmt->close();

    if ($usuario && $password === $usuario['clave']) {
        $_SESSION['usuario'] = [
            'id' => $usuario['usu_id'],
            'nombre' => $usuario['nombre'],
            'email' => $usuario['email'],
            'imagen' => $usuario['imagen']
        ];
        header("Location: perfil.php");
        exit;
    } else {
        echo "<h2 style='color:red; text-align:center;'>Credenciales incorrectas</h2>";
        echo "<p style='text-align:center;'><a href='formularios.php?inicio'>Volver a intentar</a></p>";
    }

// REGISTRO
} elseif (isset($_POST['registroUsu'])) {
    $email = $_POST['email'];
    $nombre = $_POST['username'];
    $password = $_POST['password'];
    $password2 = $_POST['password2'];

    if ($password !== $password2) {
        echo "<h2 style='color:red; text-align:center;'>Las contraseñas no coinciden</h2>";
        echo "<p style='text-align:center;'><a href='formularios.php?registro'>Volver</a></p>";
        exit;
    }

    // Evitar duplicados
    $stmt = $conexion->prepare("SELECT usu_id FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        echo "<h2 style='color:red; text-align:center;'>Este correo ya está registrado</h2>";
        echo "<p style='text-align:center;'><a href='formularios.php?registro'>Volver</a></p>";
        exit;
    }
    $stmt->close();

    // Insertar usuario nuevo
    $rol = 2; // usuario común
    $est = 1; // activo
    $imagen = ""; // predeterminado

    $stmt = $conexion->prepare("INSERT INTO usuarios (rol_id, nombre, email, clave, est_id, imagen) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssis", $rol, $nombre, $email, $password, $est, $imagen);
    if ($stmt->execute()) {
        echo "<h2 style='color:green; text-align:center;'>Cuenta registrada correctamente</h2>";
        echo "<p style='text-align:center;'><a href='formularios.php?inicio'>Iniciar sesión</a></p>";
    } else {
        echo "<h2 style='color:red; text-align:center;'>Error al registrar</h2>";
    }
    $stmt->close();

// RECUPERACIÓN
} elseif (isset($_POST['emailrec'])) {
    echo "<h2 style='text-align:center;'>Se ha enviado un correo de recuperación (simulado)</h2>";
} else {
    echo "<h2 style='color:red; text-align:center;'>Acceso inválido</h2>";
}
?>
