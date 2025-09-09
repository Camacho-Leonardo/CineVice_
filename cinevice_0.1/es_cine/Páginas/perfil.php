<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: formularios.php?inicio");
    exit;
}

require_once("../conexion.php");
$usuario = $_SESSION['usuario'];

// Si entró con contraseña temporal, lo redirigimos a cambio de contraseña
if (isset($_SESSION['temporal']) && $_SESSION['temporal'] === true) {
    header("Location: cambiar_clave.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Perfil - CineVice</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="icon" type="image/x-icon" href="../Imágenes/favicon.png">
</head>
<body>
<header class="navbar">
    <div class="nav-left">
        <a href="../index.php" id="home-link">
            <h1>Cine<strong id="colored-h1">Vice</strong></h1>
        </a>
        <div class="extra-buttons">
            <a href="../peliculas_series.php"><button>Películas/Series</button></a>
            <a href="../foros.php"><button>Foros</button></a>
        </div>
    </div>
    <div class="nav-center">
        <div class="search-wrapper">
            <input type="text" id="searchInput" placeholder="Buscar películas o géneros...">
            <img src="../Imágenes/lupa-busqueda.png" alt="Buscar" class="search-icon">
            <div id="suggestions"></div>
        </div>
        <select id="generoDropdown">
            <option value="">-- Ver por género --</option>
            <?php
            $genDropdown = mysqli_query($conexion, "SELECT * FROM generos");
            while ($g = mysqli_fetch_assoc($genDropdown)) {
                echo '<option value="' . strtolower($g['nombre']) . '">' . ucfirst($g['nombre']) . '</option>';
            }
            ?>
        </select>
    </div>
    <div class="nav-right">
        <span class="nav-username">👤 <?php echo htmlspecialchars($usuario['nombre']); ?></span>
        <a href="./perfil.php"><button>Perfil</button></a>
        <a href="./logout.php"><button class="log-out">Cerrar sesión</button></a>
    </div>
</header>

<main style="padding: 40px; color: white;">
    <h1>👋 Bienvenido, <?php echo htmlspecialchars($usuario['nombre']); ?></h1>
    <p><strong>Correo electrónico:</strong> <?php echo htmlspecialchars($usuario['email']); ?></p>
    <p><strong>ID de usuario:</strong> <?php echo $usuario['id']; ?></p>

    <p style="margin-top: 30px;">
        <a href="../index.php"><button>Ir al inicio</button></a>
        <a href="../peliculas_series.php"><button>Ver catálogo</button></a>
    </p>
</main>

<script src="../script.js"></script>
</body>
</html>