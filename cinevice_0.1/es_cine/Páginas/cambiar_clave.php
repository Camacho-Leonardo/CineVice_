<?php
session_start();
if (!isset($_SESSION['usuario']) || !isset($_SESSION['temporal'])) {
    header("Location: formularios.php?inicio");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambiar Contraseña - CineVice</title>
    <link rel="stylesheet" href="../Estilos/estilos.css">
    <link rel="icon" type="image/x-icon" href="../Imágenes/favicon.png">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .change-password-wrapper {
            background: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            max-width: 400px;
            width: 100%;
            text-align: center;
        }
        
        .change-password-wrapper h1 {
            color: #333;
            margin-bottom: 10px;
        }
        
        .alert {
            background: #ffeaa7;
            border: 1px solid #fdcb6e;
            color: #2d3436;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-weight: bold;
        }
        
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            box-sizing: border-box;
        }
        
        input[type="password"]:focus {
            outline: none;
            border-color: #2a5298;
        }
        
        .btn-primary {
            width: 100%;
            padding: 12px;
            background: #2a5298;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .btn-primary:hover {
            background: #1e3c72;
        }
    </style>
</head>
<body>
    <div class="change-password-wrapper">
        <h1>🔒 Cambiar Contraseña</h1>
        
        <div class="alert">
            <strong>⚠️ Contraseña Temporal</strong><br>
            Has iniciado sesión con una contraseña temporal. Por favor, establece una nueva contraseña segura.
        </div>
        
        <form action="usuario.php" method="POST">
            <div class="form-group">
                <label for="nueva">Nueva contraseña:</label>
                <input type="password" name="nueva" id="nueva" required minlength="6" placeholder="Mínimo 6 caracteres">
            </div>
            
            <input type="hidden" name="cambiarClave" value="1">
            <button type="submit" class="btn-primary">Cambiar contraseña</button>
        </form>
    </div>
</body>
</html>