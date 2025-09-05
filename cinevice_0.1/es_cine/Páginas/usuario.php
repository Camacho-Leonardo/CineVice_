<?php
session_start();
require_once("../conexion.php");
require './vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// =======================
// LOGIN
// =======================
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

// =======================
// REGISTRO
// =======================
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

// =======================
// RECUPERACIÓN DE CONTRASEÑA
// =======================
} elseif (isset($_POST['emailrec'])) {
    $email = $_POST['emailrec'];

    // Verificar que el correo exista
    $stmt = $conexion->prepare("SELECT * FROM usuarios WHERE email = ? AND est_id = 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $usuario = $resultado->fetch_assoc();
    $stmt->close();

    if (!$usuario) {
        echo "<h2 style='color:red; text-align:center;'>Correo no registrado</h2>";
        echo "<p style='text-align:center;'><a href='formularios.php?recuperar'>Volver</a></p>";
        exit;
    }

    // Generar contraseña temporal
    $nueva_contraseña = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);

    // Actualizar en BD
    $stmt = $conexion->prepare("UPDATE usuarios SET clave = ? WHERE usu_id = ?");
    $stmt->bind_param("si", $nueva_contraseña, $usuario['usu_id']);
    $stmt->execute();
    $stmt->close();

    // Enviar correo usando PHPMailer
    $mail = new PHPMailer(true);
    $mail->CharSet = 'UTF-8';
$mail->Encoding = 'base64';

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'cinevice.suport@gmail.com';
        $mail->Password = 'bqxg bpia fcav didf'; // tu clave de aplicación
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('cinevice.suport@gmail.com', 'CineVice');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Recuperación de contraseña CineVice';
        $mail->Body    = 'Hola <strong>' . htmlspecialchars($usuario['nombre']) . '</strong>,<br>Tu nueva contraseña temporal es: <strong>' . $nueva_contraseña . '</strong><br>Te recomendamos cambiarla después de iniciar sesión.';

        $mail->send();
        echo "<h2 style='text-align:center;color:green;'>Correo de recuperación enviado correctamente</h2>";
        echo "<p style='text-align:center;'><a href='formularios.php?inicio'>Iniciar sesión</a></p>";
    } catch (Exception $e) {
        echo "<h2 style='text-align:center;color:red;'>Error al enviar el correo: {$mail->ErrorInfo}</h2>";
    }

// =======================
// ACCESO INVÁLIDO
// =======================
} else {
    echo "<h2 style='color:red; text-align:center;'>Acceso inválido</h2>";
}
?>
