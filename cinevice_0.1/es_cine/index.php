<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CineVice</title>
    <link rel="stylesheet" href="./Estilos/estilos.css">
    <link rel="icon" type="image/x-icon" href="./Imágenes/favicon.png">
</head>

<body>
    <header>
        <div class="title-container">
            <div class="title">
                <a href="./index.php" id="home-link">
                    <h1>Cine<strong id="colored-h1">Vice</strong></h1>
                </a>
            </div>
        </div>

        <div class="nav-bar-container">
            <nav class="nav-bar">
                <ul>
                    <li><a href="./peliculas_series.php">Películas/Series</a></li>
                    <li><a href="./foros.php">Foros</a></li> 
                    
                </ul>
            </nav>
        </div>

        <div class="logs-container">
            <div class="logs">
                <a href="./Páginas/formularios.php?inicio"><button class="log-in">Iniciar sesión</button></a>
                <a href="./Páginas/formularios.php?registro"><button class="sing-in">Registrarse</button></a>
            </div>
        </div>
    </header>

    <main>
        <section>
            <div class="carousel-container">
                <div class="carousel" id="carousel">
                    <div class="slide active">
                        <div class="slide-info">
                            <div class="slide-info-img-container"><img src="./Imágenes/Carrousel/stitch_titulo.png" alt="stitch titulo" style="position: relative; right: 30px;"></div>
                            <p>Una solitaria niña hawaiana se hace amiga de un extraterrestre fugitivo y ayuda a sanar a su fragmentada familia.</p>
                            <!-- 7,0/10 -->
                        </div>

                        <img src="./Imágenes/Carrousel/stitch_carrousel.webp" alt="Pelicula 1">
                    </div>

                    <div class="slide">
                        <div class="slide-info">
                            <div class="slide-info-img-container"><img src="./Imágenes/Carrousel/minecraft_titulo.png" alt="minecraft titulo" style="position: relative; left: 60px;"></div>
                            <p style="position: relative; bottom: 80px;">El malvado dragón de Ender está en su camino a la destrucción, haciendo que una chica joven y su grupo de aventureros amigos intenten salvar Overworld.</p>
                            <!-- 5,7/10 -->
                        </div>

                        <img src="./Imágenes/Carrousel/minecraft_carrousel.webp" alt="Pelicula 2">
                    </div>

                    <div class="slide">
                        <div class="slide-info">
                            <div class="slide-info-img-container"><img src="./Imágenes/Carrousel/eleternauta_titulo.png" alt="eternauta titulo" style="position: relative; right: 50px;"></div>
                            <p style="position: relative; bottom: 120px;">Sigue a Juan Salvo junto con un grupo de supervivientes mientras luchan contra una amenaza alienígena que se encuentra bajo la dirección de una fuerza invisible después de que una terrible nevada se cobra la vida de millones de personas.</p>
                            <!-- 7,4/10 -->
                        </div>

                        <img src="./Imágenes/Carrousel/eternauta_carrousel.webp" alt="Pelicula 3">
                    </div>

                    <div class="slide">
                        <div class="slide-info">
                            <div class="slide-info-img-container"><img src="./Imágenes/Carrousel/thelastofus_titulo.png" alt="the last of us titulo" style="position: relative; right: 180px;"></div>
                            <p style="position: relative; bottom: 20px;">Joel y Ellie, una pareja conectada a través de la dureza del mundo en el que viven, se ven obligados a soportar circunstancias brutales y asesinos despiadados en un viaje por la América posterior a una pandemia.</p>
                            <!-- 8,6/10 -->
                        </div>

                        <img src="./Imágenes/Carrousel/thelasofus_carrousel.webp" alt="Pelicula 4">
                    </div>

                    <div class="slide">
                        <div class="slide-info">
                            <div class="slide-info-img-container"><img src="./Imágenes/Carrousel/misionimposible_titulo.png" alt="mision imposible titulo" style="position: relative; left: 50px;"></div>
                            <p style="position: relative; bottom: 70px;">Ethan y su equipo tienen la misión de encontrar y destruir a una IA conocida como La Entidad. El viaje por todo el mundo da lugar a increíbles escenas de acción y a más de un giro inesperado.</p>
                            <!-- 7,5/10 -->
                        </div>

                        <img src="./Imágenes/Carrousel/misionimposible_carrousel.webp" alt="Pelicula 5">
                    </div>
                </div>

                <button class="nav prev" onclick="prevSlide()"><img src="./Imágenes/flecha-izquierda-carrusel.png" alt="left-arrow"></button>
                <button class="nav next" onclick="nextSlide()"><img src="./Imágenes/flecha-derecha-carrusel.png" alt="right-arrow"></button>

                <div class="indicators" id="indicators"></div>
            </div>
        </section>

        <section class="seccion" id="seccion-color">
            <h2>Próximos lanzamientos</h2>

            <div class="pelis-container">
                <div class="pelis">
                    <a href="./Páginas/pelicula.php"><img src="./Imágenes/Posters/comoentrenaratudragon.jpg" alt="Cómo entrenar a tu dragón"></a>
                    <p>Cómo entrenar a tu dragón</p>
                    <p><strong>Lanzamiento: 12 jun 2025</strong></p>
                </div>

                <div class="pelis">
                    <a href="./Páginas/pelicula.php"><img src="./Imágenes/Posters/fueradetemporada.jpg" alt="Fuera de temporada"></a>
                    <p>Fuera de temporada</p>
                    <p><strong>Lanzamiento: 12 jun 2025</strong></p>
                </div>

                <div class="pelis">
                    <a href="./Páginas/pelicula.php"><img src="./Imágenes/Posters/gatillero.jpg" alt="Gatillero"></a>
                    <p>Gatillero</p>
                    <p><strong>Lanzamiento: 12 jun 2025</strong></p>
                </div>

                <div class="pelis">
                    <a href="./Páginas/pelicula.php"><img src="./Imágenes/Posters/superman.jpg" alt="Superman"></a>
                    <p>Superman</p>
                    <p><strong>Lanzamiento: 10 jul 2025</strong></p>
                </div>

                <div class="pelis">
                    <a href="./Páginas/pelicula.php"><img src="./Imágenes/Posters/fantasticfour.jpg" alt="Los 4 Fantásticos: Primeros pasos"></a>
                    <p>Los 4 Fantásticos: Primeros pasos</p>
                    <p><strong>Lanzamiento: 24 jul 2025</strong></p>
                </div>

            </div>
        </section>

        <section class="seccion">
            <h2>Películas más populares</h2>

            <div class="pelis-container">
                <div class="pelis">
                    <a href="./Páginas/pelicula.php"><img src="./Imágenes/Posters/lospecadores.jpg" alt="Los pecadores"></a>
                    <p>Los pecadores</p>
                    <!-- rating: 7,9/10 -->
                    <!-- Tratando de descubrir sus problemáticas vidas detrás, los hermanos gemelos regresan a su ciudad natal para comenzar de nuevo, solo para descubrir que un mal aún mayor los espera para darles la bienvenida nuevamente. -->
                </div>

                <div class="pelis">
                    <a href="./Páginas/pelicula.php"><img src="./Imágenes/Posters/fuentedelajuventud.jpg" alt="La fuente de la eterna juventud"></a>
                    <p>La fuente de la eterna juventud</p>
                    <!-- rating: 5,7/10 -->
                    <!-- Dos hermanos unen sus fuerzas para buscar la legendaria fuente de la juventud. Utilizando pistas históricas, se embarcan en una búsqueda épica llena de aventuras. Si tienen éxito, la mítica fuente podría concederles la inmortalidad. -->
                </div>

                <div class="pelis">
                    <a href="./Páginas/pelicula.php"><img src="./Imágenes/Posters/capitanamerica.jpg" alt="Capitán América: Brave New World"></a>
                    <p>Capitán América: Brave New World</p>
                    <!-- rating: 5,7/10 -->
                    <!-- ???  -->
                </div>

                <div class="pelis">
                    <a href="./Páginas/pelicula.php"><img src="./Imágenes/Posters/destinofinal.jpg" alt="Destino final: Lazos de sangre"></a>
                    <p>Destino final: Lazos de sangre</p>
                    <!-- rating: 7,0/10 -->
                    <!-- Atormentada por una pesadilla violenta recurrente, una estudiante universitaria regresa a casa para encontrar a la única persona que puede romper el ciclo y salvar a su familia del horrible destino que inevitablemente les espera. -->
                </div>

                <div class="pelis">
                    <a href="./Páginas/pelicula.php"><img src="./Imágenes/Posters/karatekid.jpg" alt="Karate Kid: Legends"></a>
                    <p>Karate Kid: Legends</p>
                    <!-- rating: 6,6/10 -->
                    <!-- Daniel llega a Beijing, donde el Sr. Han lo ha estado buscando. Han tiene un nuevo protegido, Li Fong. Los dos mentores deben colaborar para instruir a Li Fong, pero queda por ver si sus enfoques educativos serán compatibles. -->
                </div>

            </div>
        </section>

        <section class="seccion" id="seccion-color-alt">
            <h2>Series más populares</h2>

            <div class="pelis-container">
                <div class="pelis">
                    <a href="./Páginas/pelicula.php"><img src="./Imágenes/Posters/thelastofus.jpg" alt="The Last of Us"></a>
                    <p>The Last of Us</p>
                    <!-- rating: 8,6/10 -->
                    <!-- Joel y Ellie, una pareja conectada a través de la dureza del mundo en el que viven, se ven obligados a soportar circunstancias brutales y asesinos despiadados en un viaje por la América posterior a una pandemia. -->
                </div>

                <div class="pelis">
                    <a href="./Páginas/pelicula.php"><img src="./Imágenes/Posters/dept.q.jpg" alt="Department Q"></a>
                    <p>Department Q</p>
                    <!-- rating: 8,3/10 -->
                    <!-- Carl, un antiguo detective de primera que se siente atormentado por la culpa tras un ataque que dejó a su compañero paralítico y a otro policía muerto. A su vuelta al trabajo, Carl es asignado a un caso sin resolver que consumirá su vida. -->
                </div>

                <div class="pelis">
                    <a href="./Páginas/pelicula.php"><img src="./Imágenes/Posters/sirenas.jpg" alt="Sirenas"></a>
                    <p>Sirenas</p>
                    <!-- rating: 6,8/10 -->
                    <!--Devon está preocupada por la relación enfermiza de su hermana con su nuevo jefe. -->
                </div>

                <div class="pelis">
                    <a href="./Páginas/pelicula.php"><img src="./Imágenes/Posters/mobland.jpg" alt="MobLand"></a>
                    <p>MobLand</p>
                    <!-- rating: 8,5/10 -->
                    <!--Muestra a dos generaciones de gángsters, sus negocios, las relaciones que tejen y el hombre al que llaman para arreglar sus problemas.  -->
                </div>

                <div class="pelis">
                    <a href="./Páginas/pelicula.php"><img src="./Imágenes/Posters/andor.jpg" alt="Andor"></a>
                    <p>Andor</p>
                    <!-- rating: 8,5/10 -->
                    <!-- Precuela de "Rogue One: Una historia de Star Wars" que sigue las aventuras de Cassian Andor durante sus años de formación con La Rebelión. -->
                </div>
            </div>
        </section>

    </main>

    <footer>
        <p>&copy; 2025 CineVice</p>
    </footer>

    <script>
        let currentSlide = 0;
        const slides = document.querySelectorAll('.slide');
        const indicatorsContainer = document.getElementById('indicators');

        function showSlide(index) {
            slides.forEach((slide, i) => {
                slide.classList.remove('active');
                indicatorsContainer.children[i].classList.remove('active');
            });

            slides[index].classList.add('active');
            indicatorsContainer.children[index].classList.add('active');
        }

        function nextSlide() {
            currentSlide = (currentSlide + 1) % slides.length;
            showSlide(currentSlide);
        }

        function prevSlide() {
            currentSlide = (currentSlide - 1 + slides.length) % slides.length;
            showSlide(currentSlide);
        }

        function createIndicators() {
            slides.forEach((_, index) => {
                const dot = document.createElement('button');
                dot.addEventListener('click', () => {
                    currentSlide = index;
                    showSlide(currentSlide);
                });
                if (index === 0) dot.classList.add('active');
                indicatorsContainer.appendChild(dot);
            });
        }

        createIndicators();
        showSlide(currentSlide);

        setInterval(nextSlide, 8000);
    </script>
</body>

</html>