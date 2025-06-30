<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Foros</title>
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
                    <li><a href="./es_cine/peliculas_series.php">Películas/Series</a></li>
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
        if(isset($_POST['emailrec'])){
           ?>
            <h1>Email de recuperacion enviado</h1>   
           
        <?php
        }else if(isset($_POST['registroUsu'])){
            ?>
            <h1>Cuenta registrada</h1>
            
            
            <?php
        }else if(isset($_POST['inicioUsu'])){
            if($_POST['email']=="admin@admin.com" && $_POST['password']=="123456"){
                echo "Bienvenido";
            }else{
                echo "credenciales Incorrectas";
            }
        }
    
    ?>
</body>

</html>