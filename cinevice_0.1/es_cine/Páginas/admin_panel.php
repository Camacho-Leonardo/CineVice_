<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: formularios.php?inicio");
    exit;
}

require_once("../conexion.php");
$usuario = $_SESSION['usuario'];

// Verificar si es administrador
if ($usuario['rol_id'] != 1) {
    header("Location: perfil.php");
    exit;
}

// Mensajes de feedback
$success_message = "";
$error_message = "";

// Directorio para subir imágenes
$upload_dir = "../Imágenes/";

// ============================================
// GESTIÓN DE USUARIOS
// ============================================
if (isset($_POST['action_usuario'])) {
    $action = $_POST['action_usuario'];
    
    if ($action === 'actualizar_estado') {
        $usu_id = $_POST['usu_id'];
        $nuevo_estado = $_POST['nuevo_estado'];
        
        $query = "UPDATE usuarios SET est_id = ? WHERE usu_id = ?";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("ii", $nuevo_estado, $usu_id);
        
        if ($stmt->execute()) {
            $success_message = "Estado del usuario actualizado correctamente";
        } else {
            $error_message = "Error al actualizar estado del usuario";
        }
    }
    
    if ($action === 'cambiar_rol') {
        $usu_id = $_POST['usu_id'];
        $nuevo_rol = $_POST['nuevo_rol'];
        
        $query = "UPDATE usuarios SET rol_id = ? WHERE usu_id = ?";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("ii", $nuevo_rol, $usu_id);
        
        if ($stmt->execute()) {
            $success_message = "Rol del usuario actualizado correctamente";
        } else {
            $error_message = "Error al actualizar rol del usuario";
        }
    }
    
    if ($action === 'crear_usuario') {
        $nombre = $_POST['nombre'];
        $email = $_POST['email'];
        $clave = $_POST['clave'];
        $rol_id = $_POST['rol_id'];
        
        $query = "INSERT INTO usuarios (nombre, email, clave, rol_id, est_id) VALUES (?, ?, ?, ?, 1)";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("sssi", $nombre, $email, $clave, $rol_id);
        
        if ($stmt->execute()) {
            $success_message = "Usuario creado correctamente";
        } else {
            $error_message = "Error al crear usuario. El email puede estar duplicado.";
        }
    }
}

// ============================================
// GESTIÓN DE PELÍCULAS
// ============================================
if (isset($_POST['action_pelicula'])) {
    $action = $_POST['action_pelicula'];
    
    if ($action === 'crear_pelicula') {
        $nombre = $_POST['nombre'];
        $descripcion = $_POST['descripcion'];
        $emision = $_POST['emision'];
        $duracion = $_POST['duracion'];
        $episodios = $_POST['episodios'];
        $tipo_id = $_POST['tipo_id'];
        $pais = $_POST['pais'];
        $idioma = $_POST['idioma'];
        $generos = isset($_POST['generos']) ? $_POST['generos'] : [];
        
        // Manejo de la imagen del poster
        $poster_nombre = "";
        
        // Opción 1: Subir archivo
        if (isset($_FILES['poster_file']) && $_FILES['poster_file']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $filename = $_FILES['poster_file']['name'];
            $filetype = pathinfo($filename, PATHINFO_EXTENSION);
            
            if (in_array(strtolower($filetype), $allowed)) {
                $new_filename = uniqid() . '_' . $filename;
                $upload_path = $upload_dir . $new_filename;
                
                if (move_uploaded_file($_FILES['poster_file']['tmp_name'], $upload_path)) {
                    $poster_nombre = $new_filename;
                } else {
                    $error_message = "Error al subir la imagen del poster";
                }
            } else {
                $error_message = "Formato de imagen no permitido. Use: jpg, jpeg, png, gif, webp";
            }
        }
        // Opción 2: Nombre manual
        elseif (!empty($_POST['poster'])) {
            $poster_nombre = $_POST['poster'];
        }
        
        if (empty($error_message)) {
            $query = "INSERT INTO pelis (nombre, descripcion, emision, duracion, episodios, tipo_id, pais, idioma, est_id, poster) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, ?)";
            $stmt = $conexion->prepare($query);
            $stmt->bind_param("sssiiisss", $nombre, $descripcion, $emision, $duracion, $episodios, $tipo_id, $pais, $idioma, $poster_nombre);
            
            if ($stmt->execute()) {
                $peli_id = $conexion->insert_id;
                
                // Insertar géneros
                if (!empty($generos)) {
                    $query_gen = "INSERT INTO pelis_generos (peli_id, gen_id) VALUES (?, ?)";
                    $stmt_gen = $conexion->prepare($query_gen);
                    foreach ($generos as $gen_id) {
                        $stmt_gen->bind_param("ii", $peli_id, $gen_id);
                        $stmt_gen->execute();
                    }
                }
                
                $success_message = "Película/Serie creada correctamente";
            } else {
                $error_message = "Error al crear película/serie";
            }
        }
    }
    
    if ($action === 'actualizar_estado_pelicula') {
        $peli_id = $_POST['peli_id'];
        $nuevo_estado = $_POST['nuevo_estado'];
        
        $query = "UPDATE pelis SET est_id = ? WHERE peli_id = ?";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("ii", $nuevo_estado, $peli_id);
        
        if ($stmt->execute()) {
            $success_message = "Estado de la película actualizado";
        } else {
            $error_message = "Error al actualizar estado";
        }
    }
}

// ============================================
// GESTIÓN DE FOROS
// ============================================
if (isset($_POST['action_foro'])) {
    $action = $_POST['action_foro'];
    
    if ($action === 'crear_foro') {
        $nombre = $_POST['nombre'];
        $descripcion = $_POST['descripcion'];
        $genero_id = !empty($_POST['genero_id']) ? $_POST['genero_id'] : NULL;
        
        $query = "INSERT INTO foros (usu_id, nombre, descripcion, genero_id, creacion, est_id) 
                  VALUES (?, ?, ?, ?, NOW(), 1)";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("issi", $usuario['usu_id'], $nombre, $descripcion, $genero_id);
        
        if ($stmt->execute()) {
            $success_message = "Foro creado correctamente";
        } else {
            $error_message = "Error al crear foro";
        }
    }
    
    if ($action === 'actualizar_estado_foro') {
        $foro_id = $_POST['foro_id'];
        $nuevo_estado = $_POST['nuevo_estado'];
        
        $query = "UPDATE foros SET est_id = ? WHERE foro_id = ?";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("ii", $nuevo_estado, $foro_id);
        
        if ($stmt->execute()) {
            $success_message = "Estado del foro actualizado";
        } else {
            $error_message = "Error al actualizar estado del foro";
        }
    }
}

// ============================================
// GESTIÓN DE PALABRAS PROHIBIDAS
// ============================================
if (isset($_POST['action_palabra'])) {
    $action = $_POST['action_palabra'];
    
    if ($action === 'crear_palabra') {
        $palabra = $_POST['palabra'];
        
        $query = "INSERT INTO prohibidas (palabra, est_id) VALUES (?, 1)";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("s", $palabra);
        
        if ($stmt->execute()) {
            $success_message = "Palabra prohibida añadida";
        } else {
            $error_message = "Error al añadir palabra";
        }
    }
    
    if ($action === 'actualizar_estado_palabra') {
        $pro_id = $_POST['pro_id'];
        $nuevo_estado = $_POST['nuevo_estado'];
        
        $query = "UPDATE prohibidas SET est_id = ? WHERE pro_id = ?";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("ii", $nuevo_estado, $pro_id);
        
        if ($stmt->execute()) {
            $success_message = "Estado de palabra actualizado";
        } else {
            $error_message = "Error al actualizar estado";
        }
    }
}

// ============================================
// CONSULTAS PARA DASHBOARD
// ============================================

// Obtener todos los usuarios
$usuarios_query = "SELECT u.*, r.nombre as rol_nombre, e.nombre as estado_nombre 
                   FROM usuarios u 
                   JOIN roles r ON u.rol_id = r.rol_id 
                   JOIN estados e ON u.est_id = e.est_id 
                   ORDER BY u.usu_id DESC";
$usuarios_result = $conexion->query($usuarios_query);

// Obtener todas las películas/series ACTIVAS para mostrar en la web
$pelis_query = "SELECT p.*, t.descripcion as tipo_desc, e.nombre as estado_nombre,
                GROUP_CONCAT(g.nombre SEPARATOR ', ') as generos
                FROM pelis p 
                JOIN tipos t ON p.tipo_id = t.tipo_id 
                JOIN estados e ON p.est_id = e.est_id
                LEFT JOIN pelis_generos pg ON p.peli_id = pg.peli_id
                LEFT JOIN generos g ON pg.gen_id = g.gen_id
                GROUP BY p.peli_id
                ORDER BY p.peli_id DESC";
$pelis_result = $conexion->query($pelis_query);

// Obtener todos los foros
$foros_query = "SELECT f.*, u.nombre as creador, e.nombre as estado_nombre, g.nombre as genero_nombre,
                (SELECT COUNT(*) FROM comentarios WHERE foro_id = f.foro_id) as total_comentarios
                FROM foros f 
                JOIN usuarios u ON f.usu_id = u.usu_id 
                JOIN estados e ON f.est_id = e.est_id 
                LEFT JOIN generos g ON f.genero_id = g.gen_id
                ORDER BY f.foro_id DESC";
$foros_result = $conexion->query($foros_query);

// Obtener palabras prohibidas
$palabras_query = "SELECT p.*, e.nombre as estado_nombre 
                   FROM prohibidas p 
                   JOIN estados e ON p.est_id = e.est_id 
                   ORDER BY p.pro_id DESC";
$palabras_result = $conexion->query($palabras_query);

// Obtener infracciones de palabras prohibidas
$infracciones_query = "SELECT up.*, u.nombre as usuario_nombre, p.palabra, 
                       o.nombre as origen_nombre, e.nombre as estado_nombre
                       FROM usu_pro up 
                       JOIN usuarios u ON up.usu_id = u.usu_id 
                       JOIN prohibidas p ON up.pro_id = p.pro_id 
                       JOIN origenes o ON up.ori_id = o.ori_id 
                       JOIN estados e ON up.est_id = e.est_id
                       ORDER BY up.fecha DESC";
$infracciones_result = $conexion->query($infracciones_query);

// Obtener géneros
$generos_query = "SELECT * FROM generos ORDER BY nombre";
$generos_result = $conexion->query($generos_query);

// Obtener tipos
$tipos_query = "SELECT * FROM tipos";
$tipos_result = $conexion->query($tipos_query);

// Obtener roles
$roles_query = "SELECT * FROM roles WHERE est_id = 1";
$roles_result = $conexion->query($roles_query);

// Obtener estados
$estados_query = "SELECT * FROM estados";
$estados_result = $conexion->query($estados_query);

// Estadísticas generales
$stats_query = "SELECT 
    (SELECT COUNT(*) FROM usuarios WHERE est_id = 1) as usuarios_activos,
    (SELECT COUNT(*) FROM usuarios WHERE est_id = 4) as usuarios_bloqueados,
    (SELECT COUNT(*) FROM pelis WHERE est_id = 1) as pelis_activas,
    (SELECT COUNT(*) FROM foros WHERE est_id = 1) as foros_activos,
    (SELECT COUNT(*) FROM comentarios WHERE est_id = 1) as comentarios_totales,
    (SELECT COUNT(*) FROM opiniones WHERE est_id = 1) as opiniones_totales,
    (SELECT COUNT(*) FROM usu_pro WHERE est_id = 3) as infracciones_pendientes";
$stats_result = $conexion->query($stats_query);
$stats = $stats_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - CineVice</title>
    <link href="../../../src/output.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="../Imágenes/c-logo.png">
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
    <style>
        .tab-content { display: none; }
        .tab-content.active { display: block; }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-pink-100 to-blue-100" id="body">
    <!-- Navigation Bar -->
    <nav class="bg-white shadow-lg" id="navbar">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center space-x-4">
                    <a href="../../../index.php" class="group">
                        <h1 class="text-3xl font-black bg-gradient-to-r from-pink-500 via-purple-500 to-blue-500 bg-clip-text text-transparent hover:scale-105 transition-transform duration-300">
                            CINE<span class="text-blue-400">VICE</span>
                        </h1>
                    </a>
                    <div class="hidden md:flex space-x-2 ml-8">
                        <a href="../peliculas_series.php" class="px-4 py-2 rounded-lg text-gray-700 hover:bg-blue-500 hover:text-white transition-all duration-200">
                            Películas/Series
                        </a>
                        <a href="../foros.php" class="px-4 py-2 rounded-lg text-gray-700 hover:bg-blue-500 hover:text-white transition-all duration-200">
                            Foros
                        </a>
                        <a href="perfil.php" class="px-4 py-2 rounded-lg text-gray-700 hover:bg-blue-500 hover:text-white transition-all duration-200">
                            Mi Perfil
                        </a>
                    </div>
                </div>

                <div class="flex items-center space-x-4">
                    <button id="themeToggle" class="p-2 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors duration-200">
                        <i data-feather="sun" class="w-5 h-5 hidden dark-icon"></i>
                        <i data-feather="moon" class="w-5 h-5 light-icon"></i>
                    </button>
                    
                    <span class="hidden md:block font-medium text-gray-700" id="adminName">👑 Admin: <?php echo htmlspecialchars($usuario['nombre']); ?></span>
                    
                    <a href="./logout.php" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors duration-200">
                        Cerrar Sesión
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 py-8">
        <?php if ($success_message): ?>
            <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                ✓ <?php echo $success_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
            <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                ✗ <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <!-- Estadísticas Dashboard -->
        <div class="mb-8">
            <h2 class="text-3xl font-bold mb-6 text-gray-800" id="mainTitle">📊 Panel de Administración</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-all duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Usuarios Activos</p>
                            <p class="text-3xl font-bold text-green-500"><?php echo $stats['usuarios_activos']; ?></p>
                        </div>
                        <i data-feather="users" class="w-12 h-12 text-green-500"></i>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-all duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Usuarios Bloqueados</p>
                            <p class="text-3xl font-bold text-red-500"><?php echo $stats['usuarios_bloqueados']; ?></p>
                        </div>
                        <i data-feather="user-x" class="w-12 h-12 text-red-500"></i>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-all duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Películas/Series</p>
                            <p class="text-3xl font-bold text-blue-500"><?php echo $stats['pelis_activas']; ?></p>
                        </div>
                        <i data-feather="film" class="w-12 h-12 text-blue-500"></i>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-all duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Infracciones Pendientes</p>
                            <p class="text-3xl font-bold text-yellow-500"><?php echo $stats['infracciones_pendientes']; ?></p>
                        </div>
                        <i data-feather="alert-triangle" class="w-12 h-12 text-yellow-500"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs Navigation -->
        <div class="mb-6 bg-white p-2 rounded-xl shadow" id="tabsContainer">
            <div class="flex flex-wrap gap-2">
                <button onclick="showTab('usuarios')" class="tab-btn px-6 py-3 rounded-lg font-medium transition-all duration-200 bg-blue-500 text-white" data-tab="usuarios">
                    <i data-feather="users" class="w-4 h-4 inline mr-2"></i>Usuarios
                </button>
                <button onclick="showTab('peliculas')" class="tab-btn px-6 py-3 rounded-lg font-medium transition-all duration-200 bg-gray-100 text-gray-700 hover:bg-gray-200" data-tab="peliculas">
                    <i data-feather="film" class="w-4 h-4 inline mr-2"></i>Películas/Series
                </button>
                <button onclick="showTab('foros')" class="tab-btn px-6 py-3 rounded-lg font-medium transition-all duration-200 bg-gray-100 text-gray-700 hover:bg-gray-200" data-tab="foros">
                    <i data-feather="message-square" class="w-4 h-4 inline mr-2"></i>Foros
                </button>
                <button onclick="showTab('palabras')" class="tab-btn px-6 py-3 rounded-lg font-medium transition-all duration-200 bg-gray-100 text-gray-700 hover:bg-gray-200" data-tab="palabras">
                    <i data-feather="alert-circle" class="w-4 h-4 inline mr-2"></i>Palabras Prohibidas
                </button>
                <button onclick="showTab('infracciones')" class="tab-btn px-6 py-3 rounded-lg font-medium transition-all duration-200 bg-gray-100 text-gray-700 hover:bg-gray-200" data-tab="infracciones">
                    <i data-feather="alert-triangle" class="w-4 h-4 inline mr-2"></i>Infracciones
                </button>
            </div>
        </div>

        <!-- Tab: Usuarios -->
        <div id="tab-usuarios" class="tab-content active">
            <div class="bg-white rounded-2xl shadow-xl p-6 mb-6">
                <h3 class="text-xl font-bold mb-4 flex items-center text-gray-800">
                    <i data-feather="user-plus" class="w-5 h-5 mr-2"></i>Crear Nuevo Usuario
                </h3>
                <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <input type="hidden" name="action_usuario" value="crear_usuario">
                    <input type="text" name="nombre" placeholder="Nombre" required class="px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <input type="email" name="email" placeholder="Email" required class="px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <input type="text" name="clave" placeholder="Contraseña" required class="px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <select name="rol_id" required class="px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Seleccionar Rol</option>
                        <?php 
                        $roles_result->data_seek(0);
                        while ($rol = $roles_result->fetch_assoc()): ?>
                            <option value="<?php echo $rol['rol_id']; ?>"><?php echo $rol['nombre']; ?></option>
                        <?php endwhile; ?>
                    </select>
                    <button type="submit" class="md:col-span-2 px-6 py-3 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors font-medium shadow-md hover:shadow-lg">
                        <i data-feather="plus" class="w-4 h-4 inline mr-2"></i>Crear Usuario
                    </button>
                </form>
            </div>

            <div class="bg-white rounded-2xl shadow-xl p-6">
                <h3 class="text-xl font-bold mb-4 text-gray-800">Lista de Usuarios</h3>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200 bg-gray-50">
                                <th class="text-left p-3 font-semibold text-gray-700">ID</th>
                                <th class="text-left p-3 font-semibold text-gray-700">Nombre</th>
                                <th class="text-left p-3 font-semibold text-gray-700">Email</th>
                                <th class="text-left p-3 font-semibold text-gray-700">Rol</th>
                                <th class="text-left p-3 font-semibold text-gray-700">Estado</th>
                                <th class="text-left p-3 font-semibold text-gray-700">Estado Visual</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $usuarios_result->data_seek(0);
                            while ($user = $usuarios_result->fetch_assoc()): ?>
                                <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                                    <td class="p-3 text-gray-700"><?php echo $user['usu_id']; ?></td>
                                    <td class="p-3 text-gray-900 font-medium"><?php echo htmlspecialchars($user['nombre']); ?></td>
                                    <td class="p-3 text-gray-600"><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td class="p-3">
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="action_usuario" value="cambiar_rol">
                                            <input type="hidden" name="usu_id" value="<?php echo $user['usu_id']; ?>">
                                            <select name="nuevo_rol" onchange="this.form.submit()" class="px-2 py-1 rounded border border-gray-300 text-sm bg-white text-gray-700">
                                                <?php 
                                                $roles_result->data_seek(0);
                                                while ($rol = $roles_result->fetch_assoc()): ?>
                                                    <option value="<?php echo $rol['rol_id']; ?>" <?php echo $user['rol_id'] == $rol['rol_id'] ? 'selected' : ''; ?>>
                                                        <?php echo $rol['nombre']; ?>
                                                    </option>
                                                <?php endwhile; ?>
                                            </select>
                                        </form>
                                    </td>
                                    <td class="p-3">
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="action_usuario" value="actualizar_estado">
                                            <input type="hidden" name="usu_id" value="<?php echo $user['usu_id']; ?>">
                                            <select name="nuevo_estado" onchange="this.form.submit()" class="px-2 py-1 rounded border border-gray-300 text-sm bg-white text-gray-700">
                                                <?php 
                                                $estados_result->data_seek(0);
                                                while ($estado = $estados_result->fetch_assoc()): ?>
                                                    <option value="<?php echo $estado['est_id']; ?>" <?php echo $user['est_id'] == $estado['est_id'] ? 'selected' : ''; ?>>
                                                        <?php echo $estado['nombre']; ?>
                                                    </option>
                                                <?php endwhile; ?>
                                            </select>
                                        </form>
                                    </td>
                                    <td class="p-3">
                                        <span class="text-xs px-3 py-1 rounded-full font-medium <?php 
                                            echo $user['est_id'] == 1 ? 'bg-green-100 text-green-800' : 
                                                 ($user['est_id'] == 4 ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800'); 
                                        ?>">
                                            <?php echo $user['estado_nombre']; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Tab: Películas/Series -->
        <div id="tab-peliculas" class="tab-content">
            <div class="bg-white rounded-2xl shadow-xl p-6 mb-6">
                <h3 class="text-xl font-bold mb-4 flex items-center text-gray-800">
                    <i data-feather="plus-circle" class="w-5 h-5 mr-2"></i>Añadir Película/Serie
                </h3>
                <form method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <input type="hidden" name="action_pelicula" value="crear_pelicula">
                    
                    <input type="text" name="nombre" placeholder="Nombre de la película/serie" required class="px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    
                    <div class="flex flex-col">
                        <label class="text-sm font-medium text-gray-700 mb-1">Año de emisión</label>
                        <input type="number" name="emision" placeholder="2024" required class="px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    
                    <textarea name="descripcion" placeholder="Descripción" required class="px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent md:col-span-2" rows="3"></textarea>
                    
                    <div class="flex flex-col">
                        <label class="text-sm font-medium text-gray-700 mb-1">Duración (HH:MM:SS)</label>
                        <input type="time" name="duracion" step="1" value="00:00:00" class="px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    
                    <div class="flex flex-col">
                        <label class="text-sm font-medium text-gray-700 mb-1">Episodios (0 si es película)</label>
                        <input type="number" name="episodios" placeholder="0" value="0" min="0" class="px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    
                    <select name="tipo_id" required class="px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Seleccionar tipo</option>
                        <?php 
                        $tipos_result->data_seek(0);
                        while ($tipo = $tipos_result->fetch_assoc()): ?>
                            <option value="<?php echo $tipo['tipo_id']; ?>"><?php echo $tipo['descripcion']; ?></option>
                        <?php endwhile; ?>
                    </select>
                    
                    <input type="text" name="pais" placeholder="País" required class="px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    
                    <input type="text" name="idioma" placeholder="Idioma" required class="px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent md:col-span-2">
                    
                    <div class="md:col-span-2 p-4 border-2 border-dashed border-gray-300 rounded-lg bg-gray-50">
                        <label class="block mb-2 font-medium text-gray-700">
                            <i data-feather="image" class="w-4 h-4 inline mr-2"></i>Poster de la película/serie
                        </label>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Opción 1: Subir archivo desde tu PC</label>
                                <input type="file" name="poster_file" accept="image/*" class="w-full px-4 py-2 rounded-lg border border-gray-300 bg-white file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-blue-500 file:text-white hover:file:bg-blue-600 file:cursor-pointer">
                                <p class="text-xs text-gray-500 mt-1">Formatos: JPG, PNG, GIF, WEBP</p>
                            </div>
                            <div class="text-center text-gray-500 font-medium">- O -</div>
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Opción 2: Escribir nombre del archivo</label>
                                <input type="text" name="poster" placeholder="poster.jpg" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                        </div>
                    </div>
                    
                    <div class="md:col-span-2 p-4 border border-gray-300 rounded-lg bg-gray-50">
                        <label class="block mb-3 font-medium text-gray-700">
                            <i data-feather="tag" class="w-4 h-4 inline mr-2"></i>Géneros (selecciona al menos uno)
                        </label>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            <?php 
                            $generos_result->data_seek(0);
                            while ($genero = $generos_result->fetch_assoc()): ?>
                                <label class="flex items-center space-x-2 cursor-pointer hover:bg-white p-2 rounded transition-colors">
                                    <input type="checkbox" name="generos[]" value="<?php echo $genero['gen_id']; ?>" class="rounded text-blue-500 focus:ring-2 focus:ring-blue-500">
                                    <span class="text-sm text-gray-700"><?php echo htmlspecialchars($genero['nombre']); ?></span>
                                </label>
                            <?php endwhile; ?>
                        </div>
                    </div>
                    
                    <button type="submit" class="md:col-span-2 px-6 py-3 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors font-medium shadow-md hover:shadow-lg text-lg">
                        <i data-feather="plus" class="w-5 h-5 inline mr-2"></i>Añadir Película/Serie
                    </button>
                </form>
            </div>

            <div class="bg-white rounded-2xl shadow-xl p-6">
                <h3 class="text-xl font-bold mb-4 text-gray-800">Lista de Películas/Series</h3>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200 bg-gray-50">
                                <th class="text-left p-3 font-semibold text-gray-700">ID</th>
                                <th class="text-left p-3 font-semibold text-gray-700">Nombre</th>
                                <th class="text-left p-3 font-semibold text-gray-700">Tipo</th>
                                <th class="text-left p-3 font-semibold text-gray-700">Año</th>
                                <th class="text-left p-3 font-semibold text-gray-700">Géneros</th>
                                <th class="text-left p-3 font-semibold text-gray-700">Estado</th>
                                <th class="text-left p-3 font-semibold text-gray-700">Estado Visual</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $pelis_result->data_seek(0);
                            while ($peli = $pelis_result->fetch_assoc()): ?>
                                <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                                    <td class="p-3 text-gray-700"><?php echo $peli['peli_id']; ?></td>
                                    <td class="p-3 text-gray-900 font-medium"><?php echo htmlspecialchars($peli['nombre']); ?></td>
                                    <td class="p-3 text-gray-600"><?php echo $peli['tipo_desc']; ?></td>
                                    <td class="p-3 text-gray-600"><?php echo $peli['emision']; ?></td>
                                    <td class="p-3 text-sm text-gray-600 max-w-xs truncate"><?php echo htmlspecialchars($peli['generos']); ?></td>
                                    <td class="p-3">
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="action_pelicula" value="actualizar_estado_pelicula">
                                            <input type="hidden" name="peli_id" value="<?php echo $peli['peli_id']; ?>">
                                            <select name="nuevo_estado" onchange="this.form.submit()" class="px-2 py-1 rounded border border-gray-300 text-sm bg-white text-gray-700">
                                                <?php 
                                                $estados_result->data_seek(0);
                                                while ($estado = $estados_result->fetch_assoc()): ?>
                                                    <option value="<?php echo $estado['est_id']; ?>" <?php echo $peli['est_id'] == $estado['est_id'] ? 'selected' : ''; ?>>
                                                        <?php echo $estado['nombre']; ?>
                                                    </option>
                                                <?php endwhile; ?>
                                            </select>
                                        </form>
                                    </td>
                                    <td class="p-3">
                                        <span class="text-xs px-3 py-1 rounded-full font-medium <?php 
                                            echo $peli['est_id'] == 1 ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'; 
                                        ?>">
                                            <?php echo $peli['estado_nombre']; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Tab: Foros -->
        <div id="tab-foros" class="tab-content">
            <div class="bg-white rounded-2xl shadow-xl p-6 mb-6">
                <h3 class="text-xl font-bold mb-4 flex items-center text-gray-800">
                    <i data-feather="message-circle" class="w-5 h-5 mr-2"></i>Crear Nuevo Foro
                </h3>
                <form method="POST" class="grid grid-cols-1 gap-4">
                    <input type="hidden" name="action_foro" value="crear_foro">
                    <input type="text" name="nombre" placeholder="Nombre del Foro" required class="px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <textarea name="descripcion" placeholder="Descripción del Foro" required class="px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent" rows="3"></textarea>
                    <select name="genero_id" class="px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Sin género específico</option>
                        <?php 
                        $generos_result->data_seek(0);
                        while ($genero = $generos_result->fetch_assoc()): ?>
                            <option value="<?php echo $genero['gen_id']; ?>"><?php echo htmlspecialchars($genero['nombre']); ?></option>
                        <?php endwhile; ?>
                    </select>
                    <button type="submit" class="px-6 py-3 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors font-medium shadow-md hover:shadow-lg">
                        <i data-feather="plus" class="w-4 h-4 inline mr-2"></i>Crear Foro
                    </button>
                </form>
            </div>

            <div class="bg-white rounded-2xl shadow-xl p-6">
                <h3 class="text-xl font-bold mb-4 text-gray-800">Lista de Foros</h3>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200 bg-gray-50">
                                <th class="text-left p-3 font-semibold text-gray-700">ID</th>
                                <th class="text-left p-3 font-semibold text-gray-700">Nombre</th>
                                <th class="text-left p-3 font-semibold text-gray-700">Creador</th>
                                <th class="text-left p-3 font-semibold text-gray-700">Género</th>
                                <th class="text-left p-3 font-semibold text-gray-700">Comentarios</th>
                                <th class="text-left p-3 font-semibold text-gray-700">Estado</th>
                                <th class="text-left p-3 font-semibold text-gray-700">Estado Visual</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $foros_result->data_seek(0);
                            while ($foro = $foros_result->fetch_assoc()): ?>
                                <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                                    <td class="p-3 text-gray-700"><?php echo $foro['foro_id']; ?></td>
                                    <td class="p-3 text-gray-900 font-medium"><?php echo htmlspecialchars($foro['nombre']); ?></td>
                                    <td class="p-3 text-gray-600"><?php echo htmlspecialchars($foro['creador']); ?></td>
                                    <td class="p-3 text-gray-600"><?php echo $foro['genero_nombre'] ? htmlspecialchars($foro['genero_nombre']) : 'N/A'; ?></td>
                                    <td class="p-3 text-gray-600"><?php echo $foro['total_comentarios']; ?></td>
                                    <td class="p-3">
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="action_foro" value="actualizar_estado_foro">
                                            <input type="hidden" name="foro_id" value="<?php echo $foro['foro_id']; ?>">
                                            <select name="nuevo_estado" onchange="this.form.submit()" class="px-2 py-1 rounded border border-gray-300 text-sm bg-white text-gray-700">
                                                <?php 
                                                $estados_result->data_seek(0);
                                                while ($estado = $estados_result->fetch_assoc()): ?>
                                                    <option value="<?php echo $estado['est_id']; ?>" <?php echo $foro['est_id'] == $estado['est_id'] ? 'selected' : ''; ?>>
                                                        <?php echo $estado['nombre']; ?>
                                                    </option>
                                                <?php endwhile; ?>
                                            </select>
                                        </form>
                                    </td>
                                    <td class="p-3">
                                        <span class="text-xs px-3 py-1 rounded-full font-medium <?php 
                                            echo $foro['est_id'] == 1 ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'; 
                                        ?>">
                                            <?php echo $foro['estado_nombre']; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Tab: Palabras Prohibidas -->
        <div id="tab-palabras" class="tab-content">
            <div class="bg-white rounded-2xl shadow-xl p-6 mb-6">
                <h3 class="text-xl font-bold mb-4 flex items-center text-gray-800">
                    <i data-feather="slash" class="w-5 h-5 mr-2"></i>Añadir Palabra Prohibida
                </h3>
                <form method="POST" class="flex gap-4">
                    <input type="hidden" name="action_palabra" value="crear_palabra">
                    <input type="text" name="palabra" placeholder="Palabra prohibida" required class="flex-1 px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-red-500 focus:border-transparent">
                    <button type="submit" class="px-6 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors font-medium shadow-md hover:shadow-lg">
                        <i data-feather="plus" class="w-4 h-4 inline mr-2"></i>Añadir
                    </button>
                </form>
            </div>

            <div class="bg-white rounded-2xl shadow-xl p-6">
                <h3 class="text-xl font-bold mb-4 text-gray-800">Lista de Palabras Prohibidas</h3>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200 bg-gray-50">
                                <th class="text-left p-3 font-semibold text-gray-700">ID</th>
                                <th class="text-left p-3 font-semibold text-gray-700">Palabra</th>
                                <th class="text-left p-3 font-semibold text-gray-700">Estado</th>
                                <th class="text-left p-3 font-semibold text-gray-700">Estado Visual</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $palabras_result->data_seek(0);
                            while ($palabra = $palabras_result->fetch_assoc()): ?>
                                <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                                    <td class="p-3 text-gray-700"><?php echo $palabra['pro_id']; ?></td>
                                    <td class="p-3 text-gray-900 font-medium"><?php echo htmlspecialchars($palabra['palabra']); ?></td>
                                    <td class="p-3">
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="action_palabra" value="actualizar_estado_palabra">
                                            <input type="hidden" name="pro_id" value="<?php echo $palabra['pro_id']; ?>">
                                            <select name="nuevo_estado" onchange="this.form.submit()" class="px-2 py-1 rounded border border-gray-300 text-sm bg-white text-gray-700">
                                                <?php 
                                                $estados_result->data_seek(0);
                                                while ($estado = $estados_result->fetch_assoc()): ?>
                                                    <option value="<?php echo $estado['est_id']; ?>" <?php echo $palabra['est_id'] == $estado['est_id'] ? 'selected' : ''; ?>>
                                                        <?php echo $estado['nombre']; ?>
                                                    </option>
                                                <?php endwhile; ?>
                                            </select>
                                        </form>
                                    </td>
                                    <td class="p-3">
                                        <span class="text-xs px-3 py-1 rounded-full font-medium <?php 
                                            echo $palabra['est_id'] == 1 ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800'; 
                                        ?>">
                                            <?php echo $palabra['estado_nombre']; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Tab: Infracciones -->
        <div id="tab-infracciones" class="tab-content">
            <div class="bg-white rounded-2xl shadow-xl p-6">
                <h3 class="text-xl font-bold mb-4 flex items-center text-gray-800">
                    <i data-feather="alert-triangle" class="w-5 h-5 mr-2 text-yellow-500"></i>Registro de Infracciones
                </h3>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200 bg-gray-50">
                                <th class="text-left p-3 font-semibold text-gray-700">ID</th>
                                <th class="text-left p-3 font-semibold text-gray-700">Usuario</th>
                                <th class="text-left p-3 font-semibold text-gray-700">Palabra</th>
                                <th class="text-left p-3 font-semibold text-gray-700">Origen</th>
                                <th class="text-left p-3 font-semibold text-gray-700">Contenido</th>
                                <th class="text-left p-3 font-semibold text-gray-700">Fecha</th>
                                <th class="text-left p-3 font-semibold text-gray-700">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $infracciones_result->data_seek(0);
                            while ($infraccion = $infracciones_result->fetch_assoc()): ?>
                                <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                                    <td class="p-3 text-gray-700"><?php echo $infraccion['usu_pro_id']; ?></td>
                                    <td class="p-3 text-gray-900 font-medium"><?php echo htmlspecialchars($infraccion['usuario_nombre']); ?></td>
                                    <td class="p-3">
                                        <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm font-medium">
                                            <?php echo htmlspecialchars($infraccion['palabra']); ?>
                                        </span>
                                    </td>
                                    <td class="p-3 text-gray-600"><?php echo $infraccion['origen_nombre']; ?></td>
                                    <td class="p-3 max-w-xs truncate text-gray-600"><?php echo htmlspecialchars($infraccion['contenido']); ?></td>
                                    <td class="p-3 text-sm text-gray-600"><?php echo date('d/m/Y H:i', strtotime($infraccion['fecha'])); ?></td>
                                    <td class="p-3">
                                        <span class="text-xs px-3 py-1 rounded-full font-medium <?php 
                                            echo $infraccion['est_id'] == 3 ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800'; 
                                        ?>">
                                            <?php echo $infraccion['estado_nombre']; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <script>
        feather.replace();

        // Theme Management
        const themeToggle = document.getElementById('themeToggle');
        const body = document.getElementById('body');
        const navbar = document.getElementById('navbar');
        const mainTitle = document.getElementById('mainTitle');
        const adminName = document.getElementById('adminName');
        const tabsContainer = document.getElementById('tabsContainer');

        // Cargar tema guardado
        const savedTheme = localStorage.getItem('theme') || 'light';
        if (savedTheme === 'dark') {
            enableDarkMode();
        }

        themeToggle.addEventListener('click', () => {
            if (body.classList.contains('dark')) {
                enableLightMode();
                localStorage.setItem('theme', 'light');
            } else {
                enableDarkMode();
                localStorage.setItem('theme', 'dark');
            }
            feather.replace();
        });

        function enableDarkMode() {
            body.className = 'min-h-screen bg-gray-900 text-white dark transition-all duration-300';
            navbar.className = 'bg-gray-800 shadow-lg';
            mainTitle.className = 'text-3xl font-bold mb-6 text-white';
            adminName.className = 'hidden md:block font-medium text-gray-200';
            tabsContainer.className = 'mb-6 bg-gray-800 p-2 rounded-xl shadow';
            
            // Mostrar/ocultar iconos
            document.querySelectorAll('.dark-icon').forEach(icon => icon.classList.remove('hidden'));
            document.querySelectorAll('.light-icon').forEach(icon => icon.classList.add('hidden'));
            
            // Cards principales
            document.querySelectorAll('.bg-white').forEach(card => {
                if (!card.closest('select') && !card.closest('input') && !card.querySelector('select')) {
                    card.className = card.className.replace('bg-white', 'bg-gray-800');
                }
            });
            
            // Textos
            document.querySelectorAll('.text-gray-800, .text-gray-900').forEach(text => {
                text.className = text.className.replace(/text-gray-[89]00/, 'text-white');
            });
            
            document.querySelectorAll('.text-gray-700').forEach(text => {
                if (!text.closest('select')) {
                    text.className = text.className.replace('text-gray-700', 'text-gray-200');
                }
            });
            
            document.querySelectorAll('.text-gray-600').forEach(text => {
                text.className = text.className.replace('text-gray-600', 'text-gray-300');
            });
            
            // Bordes de tablas
            document.querySelectorAll('.border-gray-200, .border-gray-100').forEach(border => {
                border.className = border.className.replace(/border-gray-[12]00/, 'border-gray-700');
            });
            
            document.querySelectorAll('.bg-gray-50').forEach(bg => {
                if (!bg.classList.contains('focus:ring-2')) {
                    bg.className = bg.className.replace('bg-gray-50', 'bg-gray-700');
                }
            });
            
            // Hover effects
            document.querySelectorAll('.hover\\:bg-gray-50').forEach(hover => {
                hover.className = hover.className.replace('hover:bg-gray-50', 'hover:bg-gray-700');
            });
            
            document.querySelectorAll('.hover\\:bg-gray-200').forEach(hover => {
                hover.className = hover.className.replace('hover:bg-gray-200', 'hover:bg-gray-600');
            });
            
            // Inputs y selects - mantener fondo claro para legibilidad
            document.querySelectorAll('input[type="text"], input[type="email"], input[type="number"], input[type="time"], input[type="file"], textarea, select').forEach(input => {
                if (!input.classList.contains('rounded-full')) {
                    input.className = input.className.replace(/border-gray-\d+/, 'border-gray-600 bg-gray-700 text-white');
                }
            });
            
            // Tabs
            updateTabStyles();
        }

        function enableLightMode() {
            body.className = 'min-h-screen bg-gradient-to-br from-pink-100 to-blue-100 transition-all duration-300';
            navbar.className = 'bg-white shadow-lg';
            mainTitle.className = 'text-3xl font-bold mb-6 text-gray-800';
            adminName.className = 'hidden md:block font-medium text-gray-700';
            tabsContainer.className = 'mb-6 bg-white p-2 rounded-xl shadow';
            
            // Mostrar/ocultar iconos
            document.querySelectorAll('.dark-icon').forEach(icon => icon.classList.add('hidden'));
            document.querySelectorAll('.light-icon').forEach(icon => icon.classList.remove('hidden'));
            
            // Recargar la página para restaurar clases originales
            // O restaurar manualmente (más complejo)
            location.reload();
        }

        function updateTabStyles() {
            const isDark = body.classList.contains('dark');
            document.querySelectorAll('.tab-btn').forEach(btn => {
                if (btn.classList.contains('bg-blue-500')) {
                    // Botón activo
                    btn.className = isDark 
                        ? 'tab-btn px-6 py-3 rounded-lg font-medium transition-all duration-200 bg-blue-600 text-white'
                        : 'tab-btn px-6 py-3 rounded-lg font-medium transition-all duration-200 bg-blue-500 text-white';
                } else {
                    // Botón inactivo
                    btn.className = isDark
                        ? 'tab-btn px-6 py-3 rounded-lg font-medium transition-all duration-200 bg-gray-700 text-gray-300 hover:bg-gray-600'
                        : 'tab-btn px-6 py-3 rounded-lg font-medium transition-all duration-200 bg-gray-100 text-gray-700 hover:bg-gray-200';
                }
            });
        }

        // Tab Management
        function showTab(tabName) {
            // Ocultar todos los tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });

            // Mostrar el tab seleccionado
            document.getElementById('tab-' + tabName).classList.add('active');

            // Actualizar estilos de botones
            const isDark = body.classList.contains('dark');
            document.querySelectorAll('.tab-btn').forEach(btn => {
                if (btn.dataset.tab === tabName) {
                    // Botón activo
                    btn.className = isDark
                        ? 'tab-btn px-6 py-3 rounded-lg font-medium transition-all duration-200 bg-blue-600 text-white'
                        : 'tab-btn px-6 py-3 rounded-lg font-medium transition-all duration-200 bg-blue-500 text-white';
                } else {
                    // Botón inactivo
                    btn.className = isDark
                        ? 'tab-btn px-6 py-3 rounded-lg font-medium transition-all duration-200 bg-gray-700 text-gray-300 hover:bg-gray-600'
                        : 'tab-btn px-6 py-3 rounded-lg font-medium transition-all duration-200 bg-gray-100 text-gray-700 hover:bg-gray-200';
                }
            });

            feather.replace();
        }

        // Mostrar primer tab por defecto
        showTab('usuarios');
        
        // Preview de imagen seleccionada
        document.querySelectorAll('input[type="file"]').forEach(input => {
            input.addEventListener('change', function(e) {
                if (e.target.files && e.target.files[0]) {
                    const fileName = e.target.files[0].name;
                    console.log('Archivo seleccionado:', fileName);
                }
            });
        });
    </script>
</body>
</html>