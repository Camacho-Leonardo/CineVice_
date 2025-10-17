<?php
session_start();
require_once("../conexion.php");
require './vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function mostrarNavbar() {
    echo '
    <nav class="bg-black/50 backdrop-blur-md border-b border-purple-500/30 sticky top-0 z-50">
        <div class="container mx-auto px-4 py-4">
            <a href="../../../index.php" class="group inline-block">
                <h1 class="text-3xl font-black bg-gradient-to-r from-pink-500 via-purple-500 to-blue-500 bg-clip-text text-transparent hover:scale-105 transition-transform duration-300">
                    CINE<span class="text-blue-400">VICE</span>
                </h1>
            </a>
        </div>
    </nav>';
}

function mostrarHeader($titulo) {
    echo '<!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>' . $titulo . ' - CineVice</title>
        <link rel="icon" type="image/png" href="../../../C-logo.png">
        <link href="../../../src/output.css" rel="stylesheet">
    </head>
    <body class="bg-gradient-to-br from-gray-900 via-purple-900 to-gray-900 min-h-screen">';
    mostrarNavbar();
}

// LOGIN
if (isset($_POST['inicioUsu'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conexion->prepare("SELECT * FROM usuarios WHERE email = ? AND est_id = 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $num_rows = $resultado->num_rows;
    
    if ($num_rows === 0) {
        $stmt->close();
        mostrarHeader('Correo no registrado');
        echo '
        <div class="container mx-auto px-4 py-16">
            <div class="max-w-md mx-auto bg-gray-800/80 backdrop-blur-lg rounded-2xl shadow-2xl border border-purple-500/30 p-8">
                <div class="text-center mb-6">
                    <div class="inline-block p-4 bg-red-500/20 rounded-full mb-4">
                        <svg class="w-16 h-16 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h2 class="text-3xl font-bold text-red-400 mb-3">Correo no registrado</h2>
                    <p class="text-gray-300 mb-2">No existe una cuenta registrada con el correo:</p>
                    <p class="text-purple-400 font-semibold text-lg">' . htmlspecialchars($email) . '</p>
                </div>
                <div class="space-y-3 mt-8">
                    <a href="formularios.php?registro" class="block">
                        <button class="w-full bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white font-semibold py-3 px-6 rounded-lg transition-all duration-300 transform hover:scale-105 shadow-lg">
                            ¿No tienes cuenta? Regístrate
                        </button>
                    </a>
                    <a href="formularios.php?inicio" class="block">
                        <button class="w-full bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white font-semibold py-3 px-6 rounded-lg transition-all duration-300 transform hover:scale-105 shadow-lg">
                            Volver a intentar
                        </button>
                    </a>
                </div>
            </div>
        </div>
        </body>
        </html>';
        exit;
    } else {
        $usuario = $resultado->fetch_assoc();
        $stmt->close();
        
        if ($password === $usuario['clave']) {
            // ✅ CORRECCIÓN: Ahora se incluye el rol_id en la sesión
            $_SESSION['usuario'] = [
                'id' => $usuario['usu_id'],
                'nombre' => $usuario['nombre'],
                'email' => $usuario['email'],
                'rol_id' => $usuario['rol_id'],  // ← AGREGADO
                'imagen' => $usuario['imagen']
            ];

            if (isset($_SESSION['temporal_pass']) && $password === $_SESSION['temporal_pass']) {
                $_SESSION['temporal'] = true;
            }

            header("Location: perfil.php");
            exit;
        } else {
            mostrarHeader('Contraseña incorrecta');
            echo '
            <div class="container mx-auto px-4 py-16">
                <div class="max-w-md mx-auto bg-gray-800/80 backdrop-blur-lg rounded-2xl shadow-2xl border border-purple-500/30 p-8">
                    <div class="text-center mb-6">
                        <div class="inline-block p-4 bg-yellow-500/20 rounded-full mb-4">
                            <svg class="w-16 h-16 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                        </div>
                        <h2 class="text-3xl font-bold text-yellow-400 mb-3">Contraseña incorrecta</h2>
                        <p class="text-gray-300">El correo es correcto, pero la contraseña no coincide.</p>
                    </div>
                    <div class="space-y-3 mt-8">
                        <a href="formularios.php?recuperar" class="block">
                            <button class="w-full bg-gradient-to-r from-yellow-500 to-orange-600 hover:from-yellow-600 hover:to-orange-700 text-black font-semibold py-3 px-6 rounded-lg transition-all duration-300 transform hover:scale-105 shadow-lg">
                                ¿Olvidaste tu contraseña?
                            </button>
                        </a>
                        <a href="formularios.php?inicio" class="block">
                            <button class="w-full bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white font-semibold py-3 px-6 rounded-lg transition-all duration-300 transform hover:scale-105 shadow-lg">
                                Volver a intentar
                            </button>
                        </a>
                    </div>
                </div>
            </div>
            </body>
            </html>';
            exit;
        }
    }

} elseif (isset($_POST['registroUsu'])) {
    $email = $_POST['email'];
    $nombre = $_POST['username'];
    $password = $_POST['password'];
    $password2 = $_POST['password2'];

    if ($password !== $password2) {
        mostrarHeader('Error en registro');
        echo '
        <div class="container mx-auto px-4 py-16">
            <div class="max-w-md mx-auto bg-gray-800/80 backdrop-blur-lg rounded-2xl shadow-2xl border border-purple-500/30 p-8 text-center">
                <div class="inline-block p-4 bg-red-500/20 rounded-full mb-4">
                    <svg class="w-16 h-16 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </div>
                <h2 class="text-3xl font-bold text-red-400 mb-4">Las contraseñas no coinciden</h2>
                <a href="formularios.php?registro" class="block mt-6">
                    <button class="w-full bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white font-semibold py-3 px-6 rounded-lg transition-all duration-300 transform hover:scale-105 shadow-lg">
                        Volver a intentar
                    </button>
                </a>
            </div>
        </div>
        </body>
        </html>';
        exit;
    }

    $stmt = $conexion->prepare("SELECT usu_id FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->close();
        mostrarHeader('Correo ya registrado');
        echo '
        <div class="container mx-auto px-4 py-16">
            <div class="max-w-md mx-auto bg-gray-800/80 backdrop-blur-lg rounded-2xl shadow-2xl border border-purple-500/30 p-8 text-center">
                <div class="inline-block p-4 bg-red-500/20 rounded-full mb-4">
                    <svg class="w-16 h-16 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                <h2 class="text-3xl font-bold text-red-400 mb-4">Este correo ya está registrado</h2>
                <div class="space-y-3 mt-6">
                    <a href="formularios.php?inicio" class="block">
                        <button class="w-full bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white font-semibold py-3 px-6 rounded-lg transition-all duration-300 transform hover:scale-105 shadow-lg">
                            Iniciar sesión
                        </button>
                    </a>
                    <a href="formularios.php?registro" class="block">
                        <button class="w-full bg-gray-600 hover:bg-gray-700 text-white font-semibold py-3 px-6 rounded-lg transition-all duration-300">
                            Intentar con otro correo
                        </button>
                    </a>
                </div>
            </div>
        </div>
        </body>
        </html>';
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

    mostrarHeader('Registro exitoso');
    echo '
    <div class="container mx-auto px-4 py-16">
        <div class="max-w-md mx-auto bg-gray-800/80 backdrop-blur-lg rounded-2xl shadow-2xl border border-purple-500/30 p-8 text-center">
            <div class="inline-block p-4 bg-green-500/20 rounded-full mb-4">
                <svg class="w-16 h-16 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <h2 class="text-3xl font-bold text-green-400 mb-4">¡Cuenta registrada correctamente!</h2>
            <p class="text-gray-300 mb-6">Tu cuenta ha sido creada exitosamente</p>
            <a href="formularios.php?inicio" class="block">
                <button class="w-full bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white font-semibold py-3 px-6 rounded-lg transition-all duration-300 transform hover:scale-105 shadow-lg">
                    Iniciar sesión
                </button>
            </a>
        </div>
    </div>
    </body>
    </html>';
    exit;

} elseif (isset($_POST['emailrec'])) {
    $email = $_POST['emailrec'];

    $stmt = $conexion->prepare("SELECT usu_id FROM usuarios WHERE email = ? AND est_id = 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows === 0) {
        $stmt->close();
        mostrarHeader('Correo no encontrado');
        echo '
        <div class="container mx-auto px-4 py-16">
            <div class="max-w-md mx-auto bg-gray-800/80 backdrop-blur-lg rounded-2xl shadow-2xl border border-purple-500/30 p-8 text-center">
                <div class="inline-block p-4 bg-red-500/20 rounded-full mb-4">
                    <svg class="w-16 h-16 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <h2 class="text-3xl font-bold text-red-400 mb-4">No existe una cuenta con este correo electrónico</h2>
                <a href="formularios.php?recuperar" class="block mt-6">
                    <button class="w-full bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white font-semibold py-3 px-6 rounded-lg transition-all duration-300 transform hover:scale-105 shadow-lg">
                        Volver a intentar
                    </button>
                </a>
            </div>
        </div>
        </body>
        </html>';
        exit;
    }
    $stmt->close();

    $temporal = substr(str_shuffle("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"), 0, 8);

    $stmt = $conexion->prepare("UPDATE usuarios SET clave=? WHERE email=?");
    $stmt->bind_param("ss", $temporal, $email);
    $stmt->execute();
    $stmt->close();

    $_SESSION['temporal_pass'] = $temporal;
    $_SESSION['recovery_email'] = $email;

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'cinevice.suport@gmail.com';
        $mail->Password = 'bqxg bpia fcav didf';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';

        $mail->setFrom('cinevice.suport@gmail.com', 'CineVice');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Recuperación de contraseña CineVice';
        $mail->Body = "<html><body><h2>Recuperación de contraseña - CineVice</h2><p>Tu nueva contraseña temporal es: <strong>$temporal</strong></p><p>Te recomendamos cambiarla después de iniciar sesión.</p></body></html>";

        $mail->send();
        mostrarHeader('Correo enviado');
        echo '
        <div class="container mx-auto px-4 py-16">
            <div class="max-w-md mx-auto bg-gray-800/80 backdrop-blur-lg rounded-2xl shadow-2xl border border-purple-500/30 p-8 text-center">
                <div class="inline-block p-4 bg-green-500/20 rounded-full mb-4">
                    <svg class="w-16 h-16 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 19v-8.93a2 2 0 01.89-1.664l7-4.666a2 2 0 012.22 0l7 4.666A2 2 0 0121 10.07V19M3 19a2 2 0 002 2h14a2 2 0 002-2M3 19l6.75-4.5M21 19l-6.75-4.5M3 10l6.75 4.5M21 10l-6.75 4.5m0 0l-1.14.76a2 2 0 01-2.22 0l-1.14-.76"></path>
                    </svg>
                </div>
                <h2 class="text-3xl font-bold text-green-400 mb-4">¡Correo enviado!</h2>
                <p class="text-gray-300 mb-6">Se ha enviado un correo con la contraseña temporal</p>
                <a href="formularios.php?inicio" class="block">
                    <button class="w-full bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white font-semibold py-3 px-6 rounded-lg transition-all duration-300 transform hover:scale-105 shadow-lg">
                        Iniciar sesión
                    </button>
                </a>
            </div>
        </div>
        </body>
        </html>';
    } catch (Exception $e) {
        mostrarHeader('Error al enviar correo');
        echo '
        <div class="container mx-auto px-4 py-16">
            <div class="max-w-md mx-auto bg-gray-800/80 backdrop-blur-lg rounded-2xl shadow-2xl border border-purple-500/30 p-8 text-center">
                <div class="inline-block p-4 bg-red-500/20 rounded-full mb-4">
                    <svg class="w-16 h-16 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h2 class="text-3xl font-bold text-red-400 mb-4">Error al enviar correo</h2>
                <p class="text-gray-300 mb-6">' . htmlspecialchars($mail->ErrorInfo) . '</p>
                <a href="formularios.php?recuperar" class="block">
                    <button class="w-full bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white font-semibold py-3 px-6 rounded-lg transition-all duration-300 transform hover:scale-105 shadow-lg">
                        Volver a intentar
                    </button>
                </a>
            </div>
        </div>
        </body>
        </html>';
    }
    exit;

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

    unset($_SESSION['temporal_pass']);
    unset($_SESSION['temporal']);
    unset($_SESSION['recovery_email']);

    mostrarHeader('Contraseña cambiada');
    echo '
    <div class="container mx-auto px-4 py-16">
        <div class="max-w-md mx-auto bg-gray-800/80 backdrop-blur-lg rounded-2xl shadow-2xl border border-purple-500/30 p-8 text-center">
            <div class="inline-block p-4 bg-green-500/20 rounded-full mb-4">
                <svg class="w-16 h-16 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                </svg>
            </div>
            <h2 class="text-3xl font-bold text-green-400 mb-4">¡Contraseña cambiada!</h2>
            <p class="text-gray-300 mb-6">Tu contraseña ha sido actualizada</p>
            <a href="perfil.php" class="block">
                <button class="w-full bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white font-semibold py-3 px-6 rounded-lg transition-all duration-300 transform hover:scale-105 shadow-lg">
                    Ir al perfil
                </button>
            </a>
        </div>
    </div>
    </body>
    </html>';
    exit;

} else {
    mostrarHeader('Acceso inválido');
    echo '
    <div class="container mx-auto px-4 py-16">
        <div class="max-w-md mx-auto bg-gray-800/80 backdrop-blur-lg rounded-2xl shadow-2xl border border-purple-500/30 p-8 text-center">
            <div class="inline-block p-4 bg-red-500/20 rounded-full mb-4">
                <svg class="w-16 h-16 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
            <h2 class="text-3xl font-bold text-red-400 mb-4">Acceso inválido</h2>
            <p class="text-gray-300 mb-6">No se detectó ninguna acción válida</p>
            <a href="../../../index.php" class="block">
                <button class="w-full bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white font-semibold py-3 px-6 rounded-lg transition-all duration-300 transform hover:scale-105 shadow-lg">
                    Volver al inicio
                </button>
            </a>
        </div>
    </div>
    </body>
    </html>';
}
?>