<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formularios</title>
    <link rel="stylesheet" href="../Estilos/estilos.css">
    <link rel="icon" type="image/x-icon" href="../Imágenes/favicon.png">
    <style>
        #h1{
            font-size: 80px;
            position: absolute;
            top: 50%;
            left: 40%;
        }
    </style>
</head>

<body>
    <header>
        <div class="title-container">
            <div class="title">
                <a href="../index.php" id="home-link">
                    <h1>Cine<strong id="colored-h1">Vice</strong></h1>
                </a>
            </div>
        </div>

        <div class="nav-bar-container">
            <nav class="nav-bar">
                <ul>
                    <li><a href="../peliculas_series.php">Películas/Series</a></li>
                    <li><a href="../foros.php">Foros</a></li> 
                </ul>
            </nav>
        </div>

        <div class="logs-container">
            <div class="logs">
                <a href="../Páginas/formularios.php?inicio"><button class="log-in">Iniciar sesión</button></a>
                <a href="../Páginas/formularios.php?registro"><button class="sing-in">Registrarse</button></a>
            </div>
        </div>
    </header>
    <?php
        if(isset($_GET['inicio'])){
                ?>

<div class="login-wrapper">
    <h1>Iniciar sesión</h1>

    <form action="usuario.php" method="POST" autocomplete="off">
      <label for="email">Correo electrónico</label>
      <input type="email" id="email" name="email" placeholder="correo@ejemplo.com" required />

      <label for="password">Contraseña</label>
      <input type="password" id="password" name="password" placeholder="••••••••" required />
      <input type="hidden" name="inicioUsu">

      <button class="btn-primary" type="submit" name="submitLogin">Iniciar sesión</button>
    </form>

    <div class="secondary-actions">
      <a href="../Páginas/formularios.php?recuperar"><button type="button" onclick="window.location.href='recuperar.html'">
        Recuperar contraseña
      </button></a>
      <a href="../Páginas/formularios.php?registro"><button type="button" onclick="window.location.href='registro.html'">
        Registrarse
      </button></a>
  
    </div>
  </div>                
                
                
<?php
        }else if(isset($_GET['registro'])){
        ?>
          <div class="login-wrapper">
    <h1>Crear cuenta</h1>

    <form action="usuario.php" method="POST" autocomplete="off">
      <label for="email">Correo electrónico</label>
      <input type="email" id="email" name="email" placeholder="correo@ejemplo.com" required />

      <label for="username">Usuario</label>
      <input type="text" id="email" name="username" placeholder="Tu nombre de usuario" required />

      <label for="password">Contraseña</label>
      <input type="password" id="password" name="password" placeholder="••••••••" minlength="6" required />

      <label for="password2">Confirmar contraseña</label>
      <input type="password" id="password2" name="password2" placeholder="Repite la contraseña" minlength="6" required />
      <input type="hidden" name="registroUsu">
      <a href="../Páginas/usuario.php"><button class="btn-primary" type="submit" >Registro</button></a>
    </form>

    <div class="secondary-actions">
      <span>¿Ya tienes cuenta?</span>
      <a href="../Páginas/formularios.php?inicio"><button type="button" onclick="window.location.href='login.html'">
        Iniciar sesión
      </button></a>
    </div>
  </div>
        <?php }
   else if (isset($_GET['recuperar'])){
   
      ?>
        <div class="login-wrapper">
    <h1>Recuperar Contraseña</h1>

    <form action="usuario.php" method="POST" autocomplete="off">
      <label for="email">Correo electrónico para recuperar la contraseña</label>
      <input type="email" id="email" name="emailrec" placeholder="correo@ejemplo.com" required />
    
      <a href="../Páginas/usuario.php"><button class="btn-primary" type="submit" >Enviar</button></a>
    </form>
    <?php
     }
     
    ?>
</body>

</html>