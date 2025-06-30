<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página Película</title>
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
                    <li><a href="../Páginas/peliculas.php">Películas</a></li>
                    <li><a href="../Páginas/series.php">Series</a></li>
                    <li><a href="../Páginas/foros.php">Foros</a></li>
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
    <!-- <h1 id="h1">Página Película</h1> -->

    <section class="seccion peli" id="peli-color">
            <h2>Titulo Pelicula</h2>

            <div class="pelis-container">
        <div class="peli-p1">

                <div class="peli-cover">
                    <a href="./Páginas/pelicula.php"><img src="../Imágenes/Posters/andor.jpg" width="200vw" alt="Titulo"></a>
                    ⭐️ rating: 8,6/10
                    <!-- Joel y Ellie, una pareja conectada a través de la dureza del mundo en el que viven, se ven obligados a soportar circunstancias brutales y asesinos despiadados en un viaje por la América posterior a una pandemia. -->
                </div>

                <div class="peli-info">
                    <p>Titulo</p>
                    <!-- rating: 8,3/10 -->
                    Carl, un antiguo detective de primera que se siente atormentado por la culpa tras un ataque que dejó a su compañero paralítico y a otro policía muerto. A su vuelta al trabajo, Carl es asignado a un caso sin resolver que consumirá su vida.
                </div>
        </div>

        <div class="peli-p2">
            <h2>Opiniones</h2>
                <div class="opiniones">
                    
                <div class="opinion">
                        <div class="op-img"><img src="../Imágenes/Posters/gatillero.jpg" width="100px" height="100px" alt="Usuario"></div>
                        <div class="op-opinion">
                        <h4>Usuario</h4>
                        <p>Opinion 1 Blab bla bla</p>
                        </div>        
                    </div>
                    
                </div>
        </div>

            </div>
        </section>
</body>

</html>