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
        $poster = $_POST['poster'];
        $generos = isset($_POST['generos']) ? $_POST['generos'] : [];
        
        $query = "INSERT INTO pelis (nombre, descripcion, emision, duracion, episodios, tipo_id, pais, idioma, est_id, poster) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, ?)";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("sssiiisss", $nombre, $descripcion, $emision, $duracion, $episodios, $tipo_id, $pais, $idioma, $poster);
        
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
        $stmt->bind_param("issi", $usuario['id'], $nombre, $descripcion, $genero_id);
        
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

// Obtener todas las películas/series
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
</head>
<body class="min-h-screen transition-all duration-300" id="body">
    <!-- Navigation Bar -->
    <nav class="shadow-lg transition-all duration-300" id="navbar">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center space-x-4">
                    <a href="../../../index.php" class="group">
                        <h1 class="text-3xl font-black bg-gradient-to-r from-pink-500 via-purple-500 to-blue-500 bg-clip-text text-transparent hover:scale-105 transition-transform duration-300">
                            CINE<span class="text-blue-400">VICE</span>
                        </h1>
                    </a>
                    <div class="hidden md:flex space-x-2 ml-8">
                        <a href="../peliculas_series.php" class="px-4 py-2 rounded-lg transition-all duration-200 hover:bg-blue-500 hover:text-white">
                            Películas/Series
                        </a>
                        <a href="../foros.php" class="px-4 py-2 rounded-lg transition-all duration-200 hover:bg-blue-500 hover:text-white">
                            Foros
                        </a>
                        <a href="perfil.php" class="px-4 py-2 rounded-lg transition-all duration-200 hover:bg-blue-500 hover:text-white">
                            Mi Perfil
                        </a>
                    </div>
                </div>

                <div class="flex items-center space-x-4">
                    <button id="themeToggle" class="p-2 rounded-lg transition-colors duration-200 hover:bg-gray-200 dark:hover:bg-gray-700">
                        <i data-feather="sun" class="w-5 h-5 hidden dark:block"></i>
                        <i data-feather="moon" class="w-5 h-5 block dark:hidden"></i>
                    </button>
                    
                    <span class="hidden md:block font-medium">👑 Admin: <?php echo htmlspecialchars($usuario['nombre']); ?></span>
                    
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
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
            <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <!-- Estadísticas Dashboard -->
        <div class="mb-8">
            <h2 class="text-3xl font-bold mb-6">📊 Panel de Administración</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="rounded-xl shadow-lg p-6 transition-all duration-300" id="statCard1">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm opacity-70">Usuarios Activos</p>
                            <p class="text-3xl font-bold text-green-500"><?php echo $stats['usuarios_activos']; ?></p>
                        </div>
                        <i data-feather="users" class="w-12 h-12 text-green-500"></i>
                    </div>
                </div>

                <div class="rounded-xl shadow-lg p-6 transition-all duration-300" id="statCard2">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm opacity-70">Usuarios Bloqueados</p>
                            <p class="text-3xl font-bold text-red-500"><?php echo $stats['usuarios_bloqueados']; ?></p>
                        </div>
                        <i data-feather="user-x" class="w-12 h-12 text-red-500"></i>
                    </div>
                </div>

                <div class="rounded-xl shadow-lg p-6 transition-all duration-300" id="statCard3">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm opacity-70">Películas/Series</p>
                            <p class="text-3xl font-bold text-blue-500"><?php echo $stats['pelis_activas']; ?></p>
                        </div>
                        <i data-feather="film" class="w-12 h-12 text-blue-500"></i>
                    </div>
                </div>

                <div class="rounded-xl shadow-lg p-6 transition-all duration-300" id="statCard4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm opacity-70">Infracciones Pendientes</p>
                            <p class="text-3xl font-bold text-yellow-500"><?php echo $stats['infracciones_pendientes']; ?></p>
                        </div>
                        <i data-feather="alert-triangle" class="w-12 h-12 text-yellow-500"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs Navigation -->
        <div class="mb-6" id="tabsContainer">
            <div class="flex flex-wrap gap-2">
                <button onclick="showTab('usuarios')" class="tab-btn px-6 py-3 rounded-lg font-medium transition-all duration-200" data-tab="usuarios">
                    <i data-feather="users" class="w-4 h-4 inline mr-2"></i>Usuarios
                </button>
                <button onclick="showTab('peliculas')" class="tab-btn px-6 py-3 rounded-lg font-medium transition-all duration-200" data-tab="peliculas">
                    <i data-feather="film" class="w-4 h-4 inline mr-2"></i>Películas/Series
                </button>
                <button onclick="showTab('foros')" class="tab-btn px-6 py-3 rounded-lg font-medium transition-all duration-200" data-tab="foros">
                    <i data-feather="message-square" class="w-4 h-4 inline mr-2"></i>Foros
                </button>
                <button onclick="showTab('palabras')" class="tab-btn px-6 py-3 rounded-lg font-medium transition-all duration-200" data-tab="palabras">
                    <i data-feather="alert-circle" class="w-4 h-4 inline mr-2"></i>Palabras Prohibidas
                </button>
                <button onclick="showTab('infracciones')" class="tab-btn px-6 py-3 rounded-lg font-medium transition-all duration-200" data-tab="infracciones">
                    <i data-feather="alert-triangle" class="w-4 h-4 inline mr-2"></i>Infracciones
                </button>
            </div>
        </div>

        <!-- Tab: Usuarios -->
        <div id="tab-usuarios" class="tab-content hidden">
            <div class="rounded-2xl shadow-xl p-6 transition-all duration-300 mb-6" id="createUserCard">
                <h3 class="text-xl font-bold mb-4 flex items-center">
                    <i data-feather="user-plus" class="w-5 h-5 mr-2"></i>Crear Nuevo Usuario
                </h3>
                <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <input type="hidden" name="action_usuario" value="crear_usuario">
                    <input type="text" name="nombre" placeholder="Nombre" required class="px-4 py-2 rounded-lg border" id="inputField1">
                    <input type="email" name="email" placeholder="Email" required class="px-4 py-2 rounded-lg border" id="inputField2">
                    <input type="text" name="clave" placeholder="Contraseña" required class="px-4 py-2 rounded-lg border" id="inputField3">
                    <select name="rol_id" required class="px-4 py-2 rounded-lg border" id="selectField1">
                        <?php while ($rol = $roles_result->fetch_assoc()): ?>
                            <option value="<?php echo $rol['rol_id']; ?>"><?php echo $rol['nombre']; ?></option>
                        <?php endwhile; ?>
                    </select>
                    <button type="submit" class="md:col-span-2 px-6 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                        Añadir Película/Serie
                    </button>
                </form>
            </div>

            <div class="rounded-2xl shadow-xl p-6 transition-all duration-300" id="moviesTableCard">
                <h3 class="text-xl font-bold mb-4">Lista de Películas/Series</h3>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b" id="tableHeader">
                                <th class="text-left p-3">ID</th>
                                <th class="text-left p-3">Nombre</th>
                                <th class="text-left p-3">Tipo</th>
                                <th class="text-left p-3">Año</th>
                                <th class="text-left p-3">Géneros</th>
                                <th class="text-left p-3">Estado</th>
                                <th class="text-left p-3">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($peli = $pelis_result->fetch_assoc()): ?>
                                <tr class="border-b hover:bg-opacity-50 transition-colors" id="tableRow">
                                    <td class="p-3"><?php echo $peli['peli_id']; ?></td>
                                    <td class="p-3"><?php echo htmlspecialchars($peli['nombre']); ?></td>
                                    <td class="p-3"><?php echo $peli['tipo_desc']; ?></td>
                                    <td class="p-3"><?php echo $peli['emision']; ?></td>
                                    <td class="p-3 text-sm"><?php echo htmlspecialchars($peli['generos']); ?></td>
                                    <td class="p-3">
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="action_pelicula" value="actualizar_estado_pelicula">
                                            <input type="hidden" name="peli_id" value="<?php echo $peli['peli_id']; ?>">
                                            <select name="nuevo_estado" onchange="this.form.submit()" class="px-2 py-1 rounded border text-sm" id="selectInline">
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
                                        <span class="text-xs px-2 py-1 rounded <?php 
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
        <div id="tab-foros" class="tab-content hidden">
            <div class="rounded-2xl shadow-xl p-6 transition-all duration-300 mb-6" id="createForumCard">
                <h3 class="text-xl font-bold mb-4 flex items-center">
                    <i data-feather="message-circle" class="w-5 h-5 mr-2"></i>Crear Nuevo Foro
                </h3>
                <form method="POST" class="grid grid-cols-1 gap-4">
                    <input type="hidden" name="action_foro" value="crear_foro">
                    <input type="text" name="nombre" placeholder="Nombre del Foro" required class="px-4 py-2 rounded-lg border" id="inputField11">
                    <textarea name="descripcion" placeholder="Descripción del Foro" required class="px-4 py-2 rounded-lg border" rows="3" id="textareaField2"></textarea>
                    <select name="genero_id" class="px-4 py-2 rounded-lg border" id="selectField3">
                        <option value="">Sin género específico</option>
                        <?php 
                        $generos_result->data_seek(0);
                        while ($genero = $generos_result->fetch_assoc()): ?>
                            <option value="<?php echo $genero['gen_id']; ?>"><?php echo htmlspecialchars($genero['nombre']); ?></option>
                        <?php endwhile; ?>
                    </select>
                    <button type="submit" class="px-6 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                        Crear Foro
                    </button>
                </form>
            </div>

            <div class="rounded-2xl shadow-xl p-6 transition-all duration-300" id="forumsTableCard">
                <h3 class="text-xl font-bold mb-4">Lista de Foros</h3>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b" id="tableHeader">
                                <th class="text-left p-3">ID</th>
                                <th class="text-left p-3">Nombre</th>
                                <th class="text-left p-3">Creador</th>
                                <th class="text-left p-3">Género</th>
                                <th class="text-left p-3">Comentarios</th>
                                <th class="text-left p-3">Estado</th>
                                <th class="text-left p-3">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($foro = $foros_result->fetch_assoc()): ?>
                                <tr class="border-b hover:bg-opacity-50 transition-colors" id="tableRow">
                                    <td class="p-3"><?php echo $foro['foro_id']; ?></td>
                                    <td class="p-3"><?php echo htmlspecialchars($foro['nombre']); ?></td>
                                    <td class="p-3"><?php echo htmlspecialchars($foro['creador']); ?></td>
                                    <td class="p-3"><?php echo $foro['genero_nombre'] ? htmlspecialchars($foro['genero_nombre']) : 'N/A'; ?></td>
                                    <td class="p-3"><?php echo $foro['total_comentarios']; ?></td>
                                    <td class="p-3">
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="action_foro" value="actualizar_estado_foro">
                                            <input type="hidden" name="foro_id" value="<?php echo $foro['foro_id']; ?>">
                                            <select name="nuevo_estado" onchange="this.form.submit()" class="px-2 py-1 rounded border text-sm" id="selectInline">
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
                                        <span class="text-xs px-2 py-1 rounded <?php 
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
        <div id="tab-palabras" class="tab-content hidden">
            <div class="rounded-2xl shadow-xl p-6 transition-all duration-300 mb-6" id="createWordCard">
                <h3 class="text-xl font-bold mb-4 flex items-center">
                    <i data-feather="slash" class="w-5 h-5 mr-2"></i>Añadir Palabra Prohibida
                </h3>
                <form method="POST" class="flex gap-4">
                    <input type="hidden" name="action_palabra" value="crear_palabra">
                    <input type="text" name="palabra" placeholder="Palabra prohibida" required class="flex-1 px-4 py-2 rounded-lg border" id="inputField12">
                    <button type="submit" class="px-6 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors">
                        Añadir
                    </button>
                </form>
            </div>

            <div class="rounded-2xl shadow-xl p-6 transition-all duration-300" id="wordsTableCard">
                <h3 class="text-xl font-bold mb-4">Lista de Palabras Prohibidas</h3>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b" id="tableHeader">
                                <th class="text-left p-3">ID</th>
                                <th class="text-left p-3">Palabra</th>
                                <th class="text-left p-3">Estado</th>
                                <th class="text-left p-3">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($palabra = $palabras_result->fetch_assoc()): ?>
                                <tr class="border-b hover:bg-opacity-50 transition-colors" id="tableRow">
                                    <td class="p-3"><?php echo $palabra['pro_id']; ?></td>
                                    <td class="p-3 font-medium"><?php echo htmlspecialchars($palabra['palabra']); ?></td>
                                    <td class="p-3">
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="action_palabra" value="actualizar_estado_palabra">
                                            <input type="hidden" name="pro_id" value="<?php echo $palabra['pro_id']; ?>">
                                            <select name="nuevo_estado" onchange="this.form.submit()" class="px-2 py-1 rounded border text-sm" id="selectInline">
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
                                        <span class="text-xs px-2 py-1 rounded <?php 
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
        <div id="tab-infracciones" class="tab-content hidden">
            <div class="rounded-2xl shadow-xl p-6 transition-all duration-300" id="infractionsTableCard">
                <h3 class="text-xl font-bold mb-4 flex items-center">
                    <i data-feather="alert-triangle" class="w-5 h-5 mr-2 text-yellow-500"></i>Registro de Infracciones
                </h3>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b" id="tableHeader">
                                <th class="text-left p-3">ID</th>
                                <th class="text-left p-3">Usuario</th>
                                <th class="text-left p-3">Palabra</th>
                                <th class="text-left p-3">Origen</th>
                                <th class="text-left p-3">Contenido</th>
                                <th class="text-left p-3">Fecha</th>
                                <th class="text-left p-3">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($infraccion = $infracciones_result->fetch_assoc()): ?>
                                <tr class="border-b hover:bg-opacity-50 transition-colors" id="tableRow">
                                    <td class="p-3"><?php echo $infraccion['usu_pro_id']; ?></td>
                                    <td class="p-3"><?php echo htmlspecialchars($infraccion['usuario_nombre']); ?></td>
                                    <td class="p-3">
                                        <span class="px-2 py-1 bg-red-100 text-red-800 rounded text-sm font-medium">
                                            <?php echo htmlspecialchars($infraccion['palabra']); ?>
                                        </span>
                                    </td>
                                    <td class="p-3"><?php echo $infraccion['origen_nombre']; ?></td>
                                    <td class="p-3 max-w-xs truncate"><?php echo htmlspecialchars($infraccion['contenido']); ?></td>
                                    <td class="p-3 text-sm"><?php echo date('d/m/Y H:i', strtotime($infraccion['fecha'])); ?></td>
                                    <td class="p-3">
                                        <span class="text-xs px-2 py-1 rounded <?php 
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

        const savedTheme = localStorage.getItem('theme') || 'light';
        if (savedTheme === 'dark') {
            enableDarkMode();
        } else {
            enableLightMode();
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
            body.className = 'min-h-screen transition-all duration-300 dark bg-gray-900 text-white';
            navbar.className = 'shadow-lg transition-all duration-300 bg-gray-800 text-white';
            
            // Cards
            const cards = document.querySelectorAll('[id$="Card"]');
            cards.forEach(card => {
                card.className = card.className.replace(/bg-\w+-\d+/, 'bg-gray-800');
            });

            // Stat cards
            for (let i = 1; i <= 4; i++) {
                const statCard = document.getElementById(`statCard${i}`);
                if (statCard) statCard.className = 'rounded-xl shadow-lg p-6 transition-all duration-300 bg-gray-800 text-white';
            }

            // Tables
            const tableHeaders = document.querySelectorAll('#tableHeader');
            tableHeaders.forEach(th => {
                th.className = 'border-b border-gray-700';
            });

            const tableRows = document.querySelectorAll('#tableRow');
            tableRows.forEach(row => {
                row.className = 'border-b border-gray-700 hover:bg-gray-700 hover:bg-opacity-50 transition-colors';
            });

            // Inputs
            const inputs = document.querySelectorAll('[id^="inputField"], [id^="textareaField"], [id^="selectField"]');
            inputs.forEach(input => {
                input.className = input.className.replace(/border(\s|$)/, 'border border-gray-600 bg-gray-700 text-white ');
            });

            const selectInlines = document.querySelectorAll('#selectInline');
            selectInlines.forEach(select => {
                select.className = select.className.replace(/border/, 'border border-gray-600 bg-gray-700 text-white');
            });

            // Tabs
            const tabsContainer = document.getElementById('tabsContainer');
            if (tabsContainer) tabsContainer.className = 'mb-6 bg-gray-800 p-2 rounded-xl';

            const tabBtns = document.querySelectorAll('.tab-btn');
            tabBtns.forEach(btn => {
                if (btn.classList.contains('active')) {
                    btn.className = 'tab-btn px-6 py-3 rounded-lg font-medium transition-all duration-200 active bg-blue-600 text-white';
                } else {
                    btn.className = 'tab-btn px-6 py-3 rounded-lg font-medium transition-all duration-200 bg-gray-700 text-gray-300 hover:bg-gray-600';
                }
            });
        }

        function enableLightMode() {
            body.className = 'min-h-screen transition-all duration-300 bg-gradient-to-br from-pink-100 to-blue-100 text-gray-900';
            navbar.className = 'shadow-lg transition-all duration-300 bg-white text-gray-900';
            
            // Cards
            const cards = document.querySelectorAll('[id$="Card"]');
            cards.forEach(card => {
                card.className = card.className.replace(/bg-gray-\d+/, 'bg-white');
            });

            // Stat cards
            for (let i = 1; i <= 4; i++) {
                const statCard = document.getElementById(`statCard${i}`);
                if (statCard) statCard.className = 'rounded-xl shadow-lg p-6 transition-all duration-300 bg-white';
            }

            // Tables
            const tableHeaders = document.querySelectorAll('#tableHeader');
            tableHeaders.forEach(th => {
                th.className = 'border-b border-gray-200';
            });

            const tableRows = document.querySelectorAll('#tableRow');
            tableRows.forEach(row => {
                row.className = 'border-b border-gray-200 hover:bg-gray-50 hover:bg-opacity-50 transition-colors';
            });

            // Inputs
            const inputs = document.querySelectorAll('[id^="inputField"], [id^="textareaField"], [id^="selectField"]');
            inputs.forEach(input => {
                input.className = input.className.replace(/border-gray-\d+ bg-gray-\d+ text-white/, 'border bg-white text-gray-900');
            });

            const selectInlines = document.querySelectorAll('#selectInline');
            selectInlines.forEach(select => {
                select.className = select.className.replace(/border-gray-\d+ bg-gray-\d+ text-white/, 'border bg-white text-gray-900');
            });

            // Tabs
            const tabsContainer = document.getElementById('tabsContainer');
            if (tabsContainer) tabsContainer.className = 'mb-6 bg-white p-2 rounded-xl shadow';

            const tabBtns = document.querySelectorAll('.tab-btn');
            tabBtns.forEach(btn => {
                if (btn.classList.contains('active')) {
                    btn.className = 'tab-btn px-6 py-3 rounded-lg font-medium transition-all duration-200 active bg-blue-500 text-white';
                } else {
                    btn.className = 'tab-btn px-6 py-3 rounded-lg font-medium transition-all duration-200 bg-gray-100 text-gray-700 hover:bg-gray-200';
                }
            });
        }

        // Tab Management
        function showTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.add('hidden');
            });

            // Show selected tab
            document.getElementById('tab-' + tabName).classList.remove('hidden');

            // Update button styles
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
                if (body.classList.contains('dark')) {
                    btn.className = 'tab-btn px-6 py-3 rounded-lg font-medium transition-all duration-200 bg-gray-700 text-gray-300 hover:bg-gray-600';
                } else {
                    btn.className = 'tab-btn px-6 py-3 rounded-lg font-medium transition-all duration-200 bg-gray-100 text-gray-700 hover:bg-gray-200';
                }
            });

            // Style active button
            const activeBtn = document.querySelector(`[data-tab="${tabName}"]`);
            activeBtn.classList.add('active');
            if (body.classList.contains('dark')) {
                activeBtn.className = 'tab-btn px-6 py-3 rounded-lg font-medium transition-all duration-200 active bg-blue-600 text-white';
            } else {
                activeBtn.className = 'tab-btn px-6 py-3 rounded-lg font-medium transition-all duration-200 active bg-blue-500 text-white';
            }

            feather.replace();
        }

        // Show first tab by default
        showTab('usuarios');
    </script>
</body>
</html>
                        Crear Usuario
                    </button>
                </form>
            </div>

            <div class="rounded-2xl shadow-xl p-6 transition-all duration-300" id="usersTableCard">
                <h3 class="text-xl font-bold mb-4">Lista de Usuarios</h3>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b" id="tableHeader">
                                <th class="text-left p-3">ID</th>
                                <th class="text-left p-3">Nombre</th>
                                <th class="text-left p-3">Email</th>
                                <th class="text-left p-3">Rol</th>
                                <th class="text-left p-3">Estado</th>
                                <th class="text-left p-3">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($user = $usuarios_result->fetch_assoc()): ?>
                                <tr class="border-b hover:bg-opacity-50 transition-colors" id="tableRow">
                                    <td class="p-3"><?php echo $user['usu_id']; ?></td>
                                    <td class="p-3"><?php echo htmlspecialchars($user['nombre']); ?></td>
                                    <td class="p-3"><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td class="p-3">
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="action_usuario" value="cambiar_rol">
                                            <input type="hidden" name="usu_id" value="<?php echo $user['usu_id']; ?>">
                                            <select name="nuevo_rol" onchange="this.form.submit()" class="px-2 py-1 rounded border text-sm" id="selectInline">
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
                                            <select name="nuevo_estado" onchange="this.form.submit()" class="px-2 py-1 rounded border text-sm <?php 
                                                echo $user['est_id'] == 1 ? 'text-green-600' : 
                                                     ($user['est_id'] == 4 ? 'text-red-600' : 'text-gray-600'); 
                                            ?>" id="selectInline">
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
                                        <span class="text-xs px-2 py-1 rounded <?php 
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
        <div id="tab-peliculas" class="tab-content hidden">
            <div class="rounded-2xl shadow-xl p-6 transition-all duration-300 mb-6" id="createMovieCard">
                <h3 class="text-xl font-bold mb-4 flex items-center">
                    <i data-feather="plus-circle" class="w-5 h-5 mr-2"></i>Añadir Película/Serie
                </h3>
                <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <input type="hidden" name="action_pelicula" value="crear_pelicula">
                    <input type="text" name="nombre" placeholder="Nombre" required class="px-4 py-2 rounded-lg border" id="inputField4">
                    <input type="text" name="poster" placeholder="Nombre del archivo poster (ej: poster.jpg)" class="px-4 py-2 rounded-lg border" id="inputField5">
                    <textarea name="descripcion" placeholder="Descripción" required class="px-4 py-2 rounded-lg border md:col-span-2" rows="3" id="textareaField1"></textarea>
                    <input type="number" name="emision" placeholder="Año de emisión" required class="px-4 py-2 rounded-lg border" id="inputField6">
                    <input type="time" name="duracion" placeholder="Duración (HH:MM:SS)" step="1" value="00:00:00" class="px-4 py-2 rounded-lg border" id="inputField7">
                    <input type="number" name="episodios" placeholder="Episodios (0 si es película)" value="0" class="px-4 py-2 rounded-lg border" id="inputField8">
                    <select name="tipo_id" required class="px-4 py-2 rounded-lg border" id="selectField2">
                        <?php while ($tipo = $tipos_result->fetch_assoc()): ?>
                            <option value="<?php echo $tipo['tipo_id']; ?>"><?php echo $tipo['descripcion']; ?></option>
                        <?php endwhile; ?>
                    </select>
                    <input type="text" name="pais" placeholder="País" required class="px-4 py-2 rounded-lg border" id="inputField9">
                    <input type="text" name="idioma" placeholder="Idioma" required class="px-4 py-2 rounded-lg border" id="inputField10">
                    <div class="md:col-span-2">
                        <label class="block mb-2 font-medium">Géneros:</label>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                            <?php 
                            $generos_result->data_seek(0);
                            while ($genero = $generos_result->fetch_assoc()): ?>
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" name="generos[]" value="<?php echo $genero['gen_id']; ?>" class="rounded">
                                    <span><?php echo htmlspecialchars($genero['nombre']); ?></span>
                                </label>
                            <?php endwhile; ?>
                        </div>
                    </div>
                    <button type="submit" class="md:col-span-2 px-6 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">