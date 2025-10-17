<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formularios - CineVice</title>
    <link href="../../../src/output.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="../Imágenes/c-logo.png">
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
</head>
<body class="min-h-screen transition-all duration-300" id="body">
    <!-- Navigation Bar -->
    <nav class="shadow-lg transition-all duration-300" id="navbar">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
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
                    </div>
                </div>

                <!-- Theme Toggle -->
                <button id="themeToggle" class="p-2 rounded-lg transition-colors duration-200 hover:bg-gray-200 dark:hover:bg-gray-700">
                    <i data-feather="sun" class="w-5 h-5 hidden dark:block"></i>
                    <i data-feather="moon" class="w-5 h-5 block dark:hidden"></i>
                </button>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-md mx-auto px-4 py-12">
        <?php
        if(isset($_GET['inicio'])) {
        ?>
        <!-- Login Form -->
        <div class="rounded-2xl shadow-2xl p-8 transition-all duration-300" id="formCard">
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full mb-4 transition-colors duration-300" id="iconBg">
                    <i data-feather="log-in" class="w-8 h-8 text-blue-500"></i>
                </div>
                <h1 class="text-3xl font-bold mb-2">Iniciar sesión</h1>
                <p class="opacity-70">Bienvenido de vuelta a CineVice</p>
            </div>

            <form action="usuario.php" method="POST" autocomplete="off" class="space-y-6">
                <div>
                    <label for="email" class="block text-sm font-medium mb-2">Correo electrónico</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-feather="mail" class="w-5 h-5 opacity-50"></i>
                        </div>
                        <input type="email" id="email" name="email" placeholder="correo@ejemplo.com" required 
                               class="w-full pl-10 pr-4 py-3 rounded-lg border-2 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" id="input1" />
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium mb-2">Contraseña</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-feather="lock" class="w-5 h-5 opacity-50"></i>
                        </div>
                        <input type="password" id="password" name="password" placeholder="••••••••" required 
                               class="w-full pl-10 pr-4 py-3 rounded-lg border-2 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" id="input2" />
                    </div>
                </div>

                <input type="hidden" name="inicioUsu" value="1">
                
                <button type="submit" class="w-full py-3 bg-gradient-to-r from-blue-500 to-purple-600 text-white font-semibold rounded-lg hover:from-blue-600 hover:to-purple-700 transform hover:scale-105 transition-all duration-200 shadow-lg">
                    Iniciar sesión
                </button>
            </form>

            <div class="mt-8 space-y-3">
                <a href="../Páginas/formularios.php?recuperar" class="block text-center py-2 rounded-lg transition-all duration-200 hover:bg-blue-500 hover:text-white" id="secondaryBtn1">
                    Recuperar contraseña
                </a>
                <div class="text-center opacity-70">
                    <span>¿No tienes cuenta?</span>
                    <a href="../Páginas/formularios.php?registro" class="text-blue-500 hover:text-blue-600 font-semibold ml-1">
                        Registrarse
                    </a>
                </div>
            </div>
        </div>
        <?php
        } elseif(isset($_GET['registro'])) {
        ?>
        <!-- Register Form -->
        <div class="rounded-2xl shadow-2xl p-8 transition-all duration-300" id="formCard">
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full mb-4 transition-colors duration-300" id="iconBg">
                    <i data-feather="user-plus" class="w-8 h-8 text-blue-500"></i>
                </div>
                <h1 class="text-3xl font-bold mb-2">Crear cuenta</h1>
                <p class="opacity-70">Únete a la comunidad de CineVice</p>
            </div>

            <form action="usuario.php" method="POST" autocomplete="off" class="space-y-6">
                <div>
                    <label for="email" class="block text-sm font-medium mb-2">Correo electrónico</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-feather="mail" class="w-5 h-5 opacity-50"></i>
                        </div>
                        <input type="email" id="email" name="email" placeholder="correo@ejemplo.com" required 
                               class="w-full pl-10 pr-4 py-3 rounded-lg border-2 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" id="input1" />
                    </div>
                </div>

                <div>
                    <label for="username" class="block text-sm font-medium mb-2">Usuario</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-feather="user" class="w-5 h-5 opacity-50"></i>
                        </div>
                        <input type="text" id="username" name="username" placeholder="Tu nombre de usuario" required 
                               class="w-full pl-10 pr-4 py-3 rounded-lg border-2 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" id="input2" />
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium mb-2">Contraseña</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-feather="lock" class="w-5 h-5 opacity-50"></i>
                        </div>
                        <input type="password" id="password" name="password" placeholder="••••••••" minlength="6" required 
                               class="w-full pl-10 pr-4 py-3 rounded-lg border-2 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" id="input3" />
                    </div>
                </div>

                <div>
                    <label for="password2" class="block text-sm font-medium mb-2">Confirmar contraseña</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-feather="lock" class="w-5 h-5 opacity-50"></i>
                        </div>
                        <input type="password" id="password2" name="password2" placeholder="Repite la contraseña" minlength="6" required 
                               class="w-full pl-10 pr-4 py-3 rounded-lg border-2 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" id="input4" />
                    </div>
                </div>

                <input type="hidden" name="registroUsu" value="1">
                
                <button type="submit" class="w-full py-3 bg-gradient-to-r from-blue-500 to-purple-600 text-white font-semibold rounded-lg hover:from-blue-600 hover:to-purple-700 transform hover:scale-105 transition-all duration-200 shadow-lg">
                    Registrarse
                </button>
            </form>

            <div class="mt-8 text-center opacity-70">
                <span>¿Ya tienes cuenta?</span>
                <a href="../Páginas/formularios.php?inicio" class="text-blue-500 hover:text-blue-600 font-semibold ml-1">
                    Iniciar sesión
                </a>
            </div>
        </div>
        <?php
        } elseif(isset($_GET['recuperar'])) {
        ?>
        <!-- Password Recovery Form -->
        <div class="rounded-2xl shadow-2xl p-8 transition-all duration-300" id="formCard">
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full mb-4 transition-colors duration-300" id="iconBg">
                    <i data-feather="key" class="w-8 h-8 text-blue-500"></i>
                </div>
                <h1 class="text-3xl font-bold mb-2">Recuperar Contraseña</h1>
                <p class="opacity-70">Te enviaremos un correo para recuperar tu cuenta</p>
            </div>

            <form action="usuario.php" method="POST" autocomplete="off" class="space-y-6">
                <div>
                    <label for="email" class="block text-sm font-medium mb-2">Correo electrónico</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-feather="mail" class="w-5 h-5 opacity-50"></i>
                        </div>
                        <input type="email" id="email" name="emailrec" placeholder="correo@ejemplo.com" required 
                               class="w-full pl-10 pr-4 py-3 rounded-lg border-2 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" id="input1" />
                    </div>
                </div>

                <input type="hidden" name="recuperarUsu" value="1">
                
                <button type="submit" class="w-full py-3 bg-gradient-to-r from-blue-500 to-purple-600 text-white font-semibold rounded-lg hover:from-blue-600 hover:to-purple-700 transform hover:scale-105 transition-all duration-200 shadow-lg">
                    Enviar correo de recuperación
                </button>
            </form>

            <div class="mt-8 text-center opacity-70">
                <a href="../Páginas/formularios.php?inicio" class="text-blue-500 hover:text-blue-600 font-semibold">
                    Volver al inicio de sesión
                </a>
            </div>
        </div>
        <?php
        }
        ?>
    </main>

    <script>
        // Initialize Feather Icons
        feather.replace();

        // Theme Toggle Functionality
        const themeToggle = document.getElementById('themeToggle');
        const body = document.getElementById('body');
        const navbar = document.getElementById('navbar');
        const formCard = document.getElementById('formCard');
        const iconBg = document.getElementById('iconBg');

        // Get all input elements
        const inputs = document.querySelectorAll('input[type="email"], input[type="password"], input[type="text"]');
        const secondaryBtns = document.querySelectorAll('[id^="secondaryBtn"]');

        // Check for saved theme preference
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
            formCard.className = 'rounded-2xl shadow-2xl p-8 transition-all duration-300 bg-gray-800 text-white';
            iconBg.className = 'inline-flex items-center justify-center w-16 h-16 rounded-full mb-4 transition-colors duration-300 bg-gray-700';
            
            inputs.forEach(input => {
                input.className = 'w-full pl-10 pr-4 py-3 rounded-lg border-2 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-700 border-gray-600 text-white placeholder-gray-400';
            });

            secondaryBtns.forEach(btn => {
                btn.className = 'block text-center py-2 rounded-lg transition-all duration-200 hover:bg-blue-500 hover:text-white bg-gray-700 text-white';
            });
        }

        function enableLightMode() {
            body.className = 'min-h-screen transition-all duration-300 bg-gradient-to-br from-pink-100 to-blue-100 text-gray-900';
            navbar.className = 'shadow-lg transition-all duration-300 bg-white text-gray-900';
            formCard.className = 'rounded-2xl shadow-2xl p-8 transition-all duration-300 bg-white text-gray-900';
            iconBg.className = 'inline-flex items-center justify-center w-16 h-16 rounded-full mb-4 transition-colors duration-300 bg-blue-50';
            
            inputs.forEach(input => {
                input.className = 'w-full pl-10 pr-4 py-3 rounded-lg border-2 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white border-gray-300 text-gray-900';
            });

            secondaryBtns.forEach(btn => {
                btn.className = 'block text-center py-2 rounded-lg transition-all duration-200 hover:bg-blue-500 hover:text-white bg-gray-100 text-gray-900';
            });
        }
    </script>
</body>
</html>