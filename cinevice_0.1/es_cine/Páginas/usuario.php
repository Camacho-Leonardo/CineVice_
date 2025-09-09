<?php
session_start();
require_once("../conexion.php");
require './vendor/autoload.php'; // PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// LOGIN
if (isset($_POST['inicioUsu'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // DEBUG: Mostrar qué datos llegan
    echo "<!-- DEBUG: Email recibido: " . htmlspecialchars($email) . " -->";
    echo "<!-- DEBUG: Password recibido: " . htmlspecialchars($password) . " -->";

    // Primero verificamos si el usuario existe
    $stmt = $conexion->prepare("SELECT * FROM usuarios WHERE email = ? AND est_id = 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $num_rows = $resultado->num_rows;
    
    // DEBUG: Mostrar cuántas filas se encontraron
    echo "<!-- DEBUG: Filas encontradas: " . $num_rows . " -->";
    
    if ($num_rows === 0) {
        // El correo no está registrado
        $stmt->close();
        echo "<h2 style='color:red; text-align:center;'>❌ Correo no registrado</h2>";
        echo "<p style='text-align:center;'>No existe una cuenta registrada con el correo: <strong>" . htmlspecialchars($email) . "</strong></p>";
        echo "<p style='text-align:center;'>";
        echo "<a href='formularios.php?registro'><button style='margin:5px; padding:8px 15px; background:#28a745; color:white; border:none; border-radius:5px; cursor:pointer;'>¿No tienes cuenta? Regístrate</button></a> ";
        echo "<a href='formularios.php?inicio'><button style='margin:5px; padding:8px 15px; background:#007bff; color:white; border:none; border-radius:5px; cursor:pointer;'>Volver a intentar</button></a>";
        echo "</p>";
    } else {
        // El usuario existe, verificar contraseña
        $usuario = $resultado->fetch_assoc();
        $stmt->close();
        
        // DEBUG: Mostrar la contraseña almacenada (solo para debugging)
        echo "<!-- DEBUG: Contraseña en BD: " . htmlspecialchars($usuario['clave']) . " -->";
        echo "<!-- DEBUG: Contraseñas coinciden: " . ($password === $usuario['clave'] ? 'SÍ' : 'NO') . " -->";
        
        if ($password === $usuario['clave']) {
            // Contraseña correcta, iniciar sesión
            $_SESSION['usuario'] = [
                'id' => $usuario['usu_id'],
                'nombre' => $usuario['nombre'],
                'email' => $usuario['email'],
                'imagen' => $usuario['imagen']
            ];

            // Si la contraseña enviada coincide con la temporal que se generó, marcamos la sesión
            if (isset($_SESSION['temporal_pass']) && $password === $_SESSION['temporal_pass']) {
                $_SESSION['temporal'] = true;
            }

            header("Location: perfil.php");
            exit;
        } else {
            // Usuario existe pero contraseña incorrecta
            echo "<h2 style='color:red; text-align:center;'>🔒 Contraseña incorrecta</h2>";
            echo "<p style='text-align:center;'>El correo es correcto, pero la contraseña no coincide.</p>";
            echo "<p style='text-align:center;'>";
            echo "<a href='formularios.php?recuperar'><button style='margin:5px; padding:8px 15px; background:#ffc107; color:black; border:none; border-radius:5px; cursor:pointer;'>¿Olvidaste tu contraseña?</button></a> ";
            echo "<a href='formularios.php?inicio'><button style='margin:5px; padding:8px 15px; background:#007bff; color:white; border:none; border-radius:5px; cursor:pointer;'>Volver a intentar</button></a>";
            echo "</p>";
        }
    }

// REGISTRO
} elseif (isset($_POST['registroUsu'])) {
    $email = $_POST['email'];
    $nombre = $_POST['username'];
    $password = $_POST['password'];
    $password2 = $_POST['password2'];

    if ($password !== $password2) {
        echo "<h2 style='color:red; text-align:center;'>Las contraseñas no coinciden</h2>";
        exit;
    }

    $stmt = $conexion->prepare("SELECT usu_id FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        echo "<h2 style='color:red; text-align:center;'>Este correo ya está registrado</h2>";
        exit;
    }
    $stmt->close();

    $rol = 2;
    $est = 1;
    $imagen = "";

    $stmt = $conexion->prepare("INSERT INTO usuarios (rol_id, nombre, email, clave, est_id, imagen) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssis", $rol, $nombre, $email, $password, $est, $imagen);
    $stmt->execute();
    $stmt->close();

    echo "<h2 style='color:green; text-align:center;'>Cuenta registrada correctamente</h2>";
    echo "<p style='text-align:center;'><a href='formularios.php?inicio'>Iniciar sesión</a></p>";

// RECUPERACIÓN
} elseif (isset($_POST['emailrec'])) {
    $email = $_POST['emailrec'];

    // Verificar que el correo existe
    $stmt = $conexion->prepare("SELECT usu_id FROM usuarios WHERE email = ? AND est_id = 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows === 0) {
        $stmt->close();
        echo "<h2 style='color:red; text-align:center;'>No existe una cuenta con este correo electrónico</h2>";
        echo "<p style='text-align:center;'><a href='formularios.php?recuperar'>Volver a intentar</a></p>";
        exit;
    }
    $stmt->close();

    // Generar contraseña temporal
    $temporal = substr(str_shuffle("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"), 0, 8);

    // Guardar temporal en la base de datos
    $stmt = $conexion->prepare("UPDATE usuarios SET clave=? WHERE email=?");
    $stmt->bind_param("ss", $temporal, $email);
    $stmt->execute();
    $stmt->close();

    // Guardar en sesión para detectar que es temporal
    $_SESSION['temporal_pass'] = $temporal;
    $_SESSION['recovery_email'] = $email;

    // Enviar correo con PHPMailer
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'cinevice.suport@gmail.com';
        $mail->Password = 'bqxg bpia fcav didf';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8'; // Configurar codificación UTF-8

        $mail->setFrom('cinevice.suport@gmail.com', 'CineVice');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Recuperación de contraseña CineVice';
        $mail->Body = "
            <html>
            <head>
                <meta charset='UTF-8'>
                <title>Recuperación de contraseña</title>
            </head>
            <body>
                <h2>🔐 Recuperación de contraseña - CineVice</h2>
                <p>Hola,</p>
                <p>Has solicitado recuperar tu contraseña para tu cuenta de CineVice.</p>
                <p><strong>Tu nueva contraseña temporal es: <span style='background:#f0f0f0; padding:5px; font-family:monospace; font-size:18px;'>$temporal</span></strong></p>
                <p>⚠️ <strong>Importante:</strong> Esta contraseña es temporal. Te recomendamos cambiarla inmediatamente después de iniciar sesión.</p>
                <p>Si no solicitaste este cambio, contacta con nuestro soporte.</p>
                <br>
                <p>Saludos,<br>El equipo de CineVice</p>
            </body>
            </html>
        ";

        $mail->send();
        echo "<h2 style='color:green; text-align:center;'>Se ha enviado un correo con la contraseña temporal</h2>";
        echo "<p style='text-align:center;'><a href='formularios.php?inicio'>Iniciar sesión con la contraseña temporal</a></p>";
    } catch (Exception $e) {
        echo "<h2 style='color:red; text-align:center;'>Error al enviar correo: {$mail->ErrorInfo}</h2>";
        echo "<p style='text-align:center;'><a href='formularios.php?recuperar'>Volver a intentar</a></p>";
    }

// CAMBIO DE CONTRASEÑA
} elseif (isset($_POST['cambiarClave'])) {
    if (!isset($_SESSION['usuario'])) {
        header("Location: formularios.php?inicio");
        exit;
    }

    $nueva = $_POST['nueva'];
    $id = $_SESSION['usuario']['id'];

    $stmt = $conexion->prepare("UPDATE usuarios SET clave=? WHERE usu_id=?");
    $stmt->bind_param("si", $nueva, $id);
    $stmt->execute();
    $stmt->close();

    // Ya no es temporal
    unset($_SESSION['temporal_pass']);
    unset($_SESSION['temporal']);
    unset($_SESSION['recovery_email']);

    echo "<h2 style='color:green; text-align:center;'>Contraseña cambiada correctamente</h2>";
    echo "<p style='text-align:center;'><a href='perfil.php'>Ir al perfil</a></p>";

} else {
    echo "<h2 style='color:red; text-align:center;'>Acceso inválido</h2>";
}
?>