-- CREACIÓN DE BASE DE DATOS
DROP DATABASE IF EXISTS cinevice;
CREATE DATABASE cinevice;
USE cinevice;

-- TABLA ESTADOS
CREATE TABLE estados (
  est_id INT PRIMARY KEY AUTO_INCREMENT,
  nombre VARCHAR(15)
);

-- TABLA ROLES
CREATE TABLE roles (
  rol_id INT PRIMARY KEY AUTO_INCREMENT,
  nombre VARCHAR(20),
  est_id INT,
  FOREIGN KEY(est_id) REFERENCES estados(est_id)
);

-- TABLA USUARIOS
CREATE TABLE usuarios (
  usu_id INT PRIMARY KEY AUTO_INCREMENT,
  rol_id INT,
  nombre VARCHAR(20),
  email VARCHAR(30),
  clave VARCHAR(20),
  est_id INT,
  imagen VARCHAR(50),
  FOREIGN KEY(est_id) REFERENCES estados(est_id),
  FOREIGN KEY(rol_id) REFERENCES roles(rol_id),
  UNIQUE(email)
);

-- TABLA PROHIBIDAS
CREATE TABLE prohibidas (
  pro_id INT PRIMARY KEY AUTO_INCREMENT,
  palabra VARCHAR(20),
  est_id INT,
  FOREIGN KEY(est_id) REFERENCES estados(est_id)
);

-- TABLA GENEROS
CREATE TABLE generos (
  gen_id INT PRIMARY KEY AUTO_INCREMENT,
  nombre VARCHAR(15),
  descripcion VARCHAR(500)
);

-- TABLA TIPOS
CREATE TABLE tipos (
  tipo_id INT PRIMARY KEY AUTO_INCREMENT,
  descripcion VARCHAR(20)
);

-- TABLA PELIS
CREATE TABLE pelis (
  peli_id INT PRIMARY KEY AUTO_INCREMENT,
  nombre VARCHAR(50),
  descripcion VARCHAR(500),
  emision YEAR,
  duracion TIME,
  episodios INT,
  tipo_id INT,
  pais VARCHAR(20),
  idioma VARCHAR(15),
  est_id INT,
  poster VARCHAR(50),
  FOREIGN KEY(est_id) REFERENCES estados(est_id),
  FOREIGN KEY(tipo_id) REFERENCES tipos(tipo_id)
);

-- RELACIÓN PELIS - GENEROS
CREATE TABLE pelis_generos (
  peli_id INT,
  gen_id INT,
  PRIMARY KEY (peli_id, gen_id),
  FOREIGN KEY (peli_id) REFERENCES pelis(peli_id),
  FOREIGN KEY (gen_id) REFERENCES generos(gen_id)
);

-- TABLA FOROS
CREATE TABLE foros (
  foro_id INT PRIMARY KEY AUTO_INCREMENT,
  usu_id INT,
  nombre VARCHAR(50),
  descripcion VARCHAR(500),
  creacion DATETIME,
  est_id INT,
  imagen VARCHAR(50),
  FOREIGN KEY(est_id) REFERENCES estados(est_id),
  FOREIGN KEY(usu_id) REFERENCES usuarios(usu_id)
);

-- TABLA ORIGENES
CREATE TABLE origenes (
  ori_id INT PRIMARY KEY AUTO_INCREMENT,
  nombre VARCHAR(15)
);

-- TABLA OPINIONES
CREATE TABLE opiniones (
  op_id INT PRIMARY KEY AUTO_INCREMENT,
  usu_id INT,
  peli_id INT,
  puntuacion INT,
  contenido VARCHAR(500),
  op_likes INT,
  op_dislikes INT,
  fecha DATETIME,
  est_id INT,
  FOREIGN KEY(est_id) REFERENCES estados(est_id),
  FOREIGN KEY(usu_id) REFERENCES usuarios(usu_id),
  FOREIGN KEY(peli_id) REFERENCES pelis(peli_id)
);

-- TABLA COMENTARIOS
CREATE TABLE comentarios (
  com_id INT PRIMARY KEY AUTO_INCREMENT,
  usu_id INT,
  foro_id INT,
  contenido VARCHAR(500),
  fecha DATETIME,
  est_id INT,
  FOREIGN KEY(usu_id) REFERENCES usuarios(usu_id),
  FOREIGN KEY(foro_id) REFERENCES foros(foro_id),
  FOREIGN KEY(est_id) REFERENCES estados(est_id)
);

-- TABLA USU_PRO
CREATE TABLE usu_pro (
  usu_pro_id INT PRIMARY KEY AUTO_INCREMENT,
  usu_id INT,
  pro_id INT,
  ori_id INT,
  origen INT,
  contenido VARCHAR(500),
  fecha DATETIME,
  est_id INT,
  FOREIGN KEY(usu_id) REFERENCES usuarios(usu_id),
  FOREIGN KEY(pro_id) REFERENCES prohibidas(pro_id),
  FOREIGN KEY(ori_id) REFERENCES origenes(ori_id),
  FOREIGN KEY(est_id) REFERENCES estados(est_id)
);

-- ========================
-- DATOS DE PRUEBA
-- ========================

-- ESTADOS
INSERT INTO estados (est_id, nombre) VALUES
(1, "activo"),
(2, "inactivo"),
(3, "en revision"),
(4, "bloqueado");

-- ROLES
INSERT INTO roles (rol_id, nombre, est_id) VALUES
(1, "admin", 1),
(2, "usuario", 1);

-- USUARIOS
INSERT INTO usuarios (usu_id, rol_id, nombre, email, clave, est_id, imagen) VALUES
(1001, 1, "adminsuper", "super@gmail.com", "adminclave", 1, ""),
(1002, 1, "sudoadmin", "linuxadmin@hotmail.com", "sudoclave", 2, ""),
(1003, 2, "elopinionado", "opinionado@yahoo.com", "ope", 1, ""),
(1004, 2, "genio123", "genio@gmail.com", "aladin", 1, "");

-- PROHIBIDAS
INSERT INTO prohibidas (pro_id, palabra, est_id) VALUES
(1001, "matar", 2),
(1002, "bomba", 1),
(1003, "terrorismo", 2);

-- GENEROS
INSERT INTO generos (gen_id, nombre, descripcion) VALUES
(1, "romance", "De amor, relaciones, etc"),
(2, "terror", "Asusta"),
(3, "comedia", "Da gracia, divertida"),
(4, "fantasia", "No realista, magia, etc"),
(5, "acción","Adrenalina");

-- TIPOS
INSERT INTO tipos (tipo_id, descripcion) VALUES
(1, "pelicula"),
(2, "serie");

-- PELIS
INSERT INTO pelis (peli_id, nombre, descripcion, emision, duracion, episodios, tipo_id, pais, idioma, est_id, poster) VALUES
(1001, "La Bella y la Bestia", "Una película clásica de Disney, etc", 1991, '01:24:00', 1, 1, "USA", "Inglés", 1, "bella.jpg"),
(1002, "El Viaje de Chihiro", "Una peli fantástica del estudio Ghibli, etc", 2001, '02:05:00', 1, 1, "Japón", "Japonés", 1, "chihiro.jpg"),
(1003, "It", "Película de terror basada en la novela de Stephen King", 2017, '02:15:00', 1, 1, "USA", "Inglés", 1, "it.jpg"),
(1004, "Amelie", "Comedia romántica francesa", 2001, '02:02:00', 1, 1, "Francia", "Francés", 1, "amelie.jpg"),
(1005,"Los cazafantasmas","Tres antiguos profesores de parapsicología se establecen como un servicio único de eliminación de fantasmas.", 1984,'01:45:00',1,1,"Estadounidense","Inglés",1,"caza_fantasma.jpg"),
(1006,"El Conjuro","Nada",2013,'00:00:00',1,1,"USA","Inglés",1,"el_conjuro.jpg"),
(1007,"E.T.","Nada",2013,'00:00:00',1,1,"USA","Inglés",1,"e.t..jpg"),
(1008,"Son como niños","Nada",2013,'00:00:00',1,1,"USA","Inglés",1,"son_como_niños.jpg"),
(1009,"Minecraft","Nada",2013,'00:00:00',1,1,"USA","Inglés",1,"Minecraft.jpg"),
(1010,"Lilo","Nada",2013,'00:00:00',1,1,"USA","Inglés",1,"lilo.jpg"),
(1011,"Los pibes","Nada",2013,'00:00:00',1,1,"USA","Inglés",1,"los_pibes.jpg"),
(1012,"Poker face","Nada",2013,'00:00:00',1,1,"USA","Inglés",1,"poker_face.jpg"),
(1013,"Rick y Morty","Nada",2013,'00:00:00',1,1,"USA","Inglés",1,"rick_morty.jpg"),
(1014,"Frozen","Nada",2013,'00:00:00',1,1,"USA","Inglés",1,"frozen.jpg"),
(1015,"The simpsons","Nada",2013,'00:00:00',1,1,"USA","Inglés",1,"the_simpsons.jpg"),
(1016,"One piece","Nada",2013,'00:00:00',1,1,"USA","Inglés",1,"one_piece.jpg"),
(1017,"Merlina","Nada",2013,'00:00:00',1,1,"USA","Inglés",1,"merlina.jpg"),
(1018,"El oficina","Nada",2013,'00:00:00',1,1,"USA","Inglés",1,"el_oficina.jpg"),
(1019,"You","Nada",2013,'00:00:00',1,1,"USA","Inglés",1,"you.jpg"),
(1020,"Yo antes de tí","Nada",2013,'00:00:00',1,1,"USA","Inglés",1,"yo_antes_de_ti.jpg"),
(1021,"We were liars","Nada",2013,'00:00:00',1,1,"USA","Inglés",1,"we_were_liars.jpg"),
(1022,"love","Nada",2013,'00:00:00',1,1,"USA","Inglés",1,"love.jpg"),
(1023,"Grey's Anatomy","Nada",2013,'00:00:00',1,1,"USA","Inglés",1,"greys_anatomy.jpg"),
(1024,"El amigos","Nada",2013,'00:00:00',1,1,"USA","Inglés",1,"elamigos.jpg"),
(1025,"Cincuenta sombras","Nada",2013,'00:00:00',1,1,"USA","Inglés",1,"cincuenta_sombras.jpg"),
(1026,"Bride Hard","Nada",2013,'00:00:00',1,1,"USA","Inglés",1,"bride_hard.jpg"),
(1027,"Blanca Nieves","Nada",2013,'00:00:00',1,1,"USA","Inglés",1,"blanca_nieves.jpg"),
(1028,"Anora","Nada",2013,'00:00:00',1,1,"USA","Inglés",1,"anora.jpg"),
(1029,"Exterminio 1","Nada",2013,'00:00:00',1,1,"USA","Inglés",1,"28_days_later_2002.jpg"),
(1030,"Exterminio 2","Nada",2013,'00:00:00',1,1,"USA","Inglés",1,"exterminio_2.jpg"),
(1031,"28 days later (2025)","Nada",2013,'00:00:00',1,1,"USA","Inglés",1,"28_years_later_2025.jpg"),
(1032,"Destino final: Lazos de sangre","Nada",2013,'00:00:00',1,1,"USA","Inglés",1,"destino_final_lazos_de_sangre.jpg"),
(1033,"Pecadores","Nada",2013,'00:00:00',1,1,"USA","Inglés",1,"pecadores.jpg"),
(1034,"Stranger Things","Nada",2013,'00:00:00',1,1,"USA","Inglés",1,"stranger_things.jpg"),
(1035,"The last of us","Nada",2013,'00:00:00',1,1,"USA","Inglés",1,"the_last_of_us.jpg"),
(1036,"Tiburon","Nada",2013,'00:00:00',1,1,"USA","Inglés",1,"tiburon.jpg"),
(1037,"The walking dead","Nada",2013,'00:00:00',1,1,"USA","Inglés",1,"the_walking_dead.jpg"),
(1038,"The toxic avengers","Nada",2013,'00:00:00',1,1,"USA","Inglés",1,"the_toxic_avengers.jpg"),
(1039,"Andor","Nada",2013,'00:00:00',1,1,"USA","Inglés",1,"andor.jpg"),
(1040,"Arcane","Nada",2013,'00:00:00',1,1,"USA","Inglés",1,"arcane.jpg"),
(1041,"Como entrenar a tu dragon","Nada",2013,'00:00:00',1,1,"USA","Inglés",1,"como_entrenar_a_tu_dragon.jpg"),
(1042,"Ironheart","Nada",2013,'00:00:00',1,1,"USA","Inglés",1,"ironheart.jpg"),
(1043,"K Pop: Demon Hunters","Nada",2013,'00:00:00',1,1,"USA","Inglés",1,"k_pop_demon_hunters.jpg"),
(1044,"GOT","Nada",2013,'00:00:00',1,1,"USA","Inglés",1,"got.jpg"),
(1045,"The mandalorian","Nada",2013,'00:00:00',1,1,"USA","Inglés",1,"the_mandalorian.jpg"),
(1046,"Frieren","Nada",2013,'00:00:00',1,1,"USA","Inglés",1,"frieren.jpg"),
(1047,"Elio","Nada",2013,'00:00:00',1,1,"USA","Inglés",1,"elio.jpg"),
(1048,"Capitan America: breve new world","Nada",2013,'00:00:00',1,1,"USA","Inglés",1,"capitan_america_breve_new_world.jpg"),
(1049, "Batman","Nada",2013,'00:00:00',1,1,"USA","Inglés",1,"el_caballero_de_la_noche.jpg"),
(1050,"Fallout","Nada",2013,'00:00:00',1,1,"USA","Inglés",1,"fallout.jpg"),
(1051,"Goodboy","Nada",2013,'00:00:00',1,1,"USA","Inglés",1,"goodboy.jpg"),
(1052,"Invincible","Nada",2013,'00:00:00',1,1,"USA","Inglés",1,"invincible.jpg"),
(1053,"John Wick 4","Nada",2013,'00:00:00',1,1,"USA","Inglés",1,"john_wick_4.jpg"),
(1054,"Jurassic Park","Nada",2013,'00:00:00',1,1,"USA","Inglés",1,"jurassic_park.jpg"),
(1055,"lv rbts","Nada",2013,'00:00:00',1,1,"USA","Inglés",1,"lv_rbts.jpg"),
(1056,"Otro día para matar","Nada",2013,'00:00:00',1,1,"USA","Inglés",1,"otro_dia_para_matar.jpg"),
(1057,"Paradise","Nada",2013,'00:00:00',1,1,"USA","Inglés",1,"paradise.jpg");






-- PELIS-GENEROS
INSERT INTO pelis_generos (peli_id, gen_id) VALUES
(1001, 1), -- romance
(1001, 4), -- fantasia
(1002, 4), -- fantasia
(1003, 2), -- terror
(1004, 1), -- romance
(1004, 3), -- comedia
(1005, 5), -- acción
(1006, 2), -- terror
(1007, 4), -- fantasia
(1008, 3), -- comedia
(1009, 3), -- comedia
(1010, 3), -- comedia
(1011, 3), -- comedia
(1011,5), -- acción
(1012, 3), -- comedia
(1013, 3), -- comedia
(1014, 3), -- comedia
(1015,3), -- comedia
(1016,3), -- comedia 
(1017,3),-- comedia
(1018,3), -- comedia
(1019,1), -- romance
(1020,1), -- romance
(1021,1), -- romance
(1022,1), -- romance
(1023,1), -- romance
(1024,1), -- romance
(1025,1), -- romance
(1026,1), -- romance
(1027,1), -- romance
(1028,1), -- romance
(1029,2), -- terror
(1030,2), -- terror
(1031,2), -- terror
(1032,2), -- terror
(1033,2), -- terror
(1034,2), -- terror
(1035,2), -- terror
(1036,2), -- terror
(1037,2), -- terror
(1038,2), -- terror
(1039,4), -- fantasia
(1040,4), -- fantasia
(1041,4), -- fantasia 
(1042,4), -- fantasia
(1043,4), -- fantasia
(1044,4), -- fantasia 
(1045,4), -- fantasia
(1046,4), -- fantasia
(1047,4), -- fantasia
(1048,5), -- accion
(1049,5), -- accion
(1050,5), -- accion
(1051,5), -- accion 
(1052,5), -- accion 
(1053,5), -- accion 
(1054,5), -- accion
(1055,5), -- accion
(1056,5), -- accion 
(1057,5); -- accion
-- ORIGENES
INSERT INTO origenes (ori_id, nombre) VALUES
(1, "opiniones"),
(2, "comentarios");

-- OPINIONES
INSERT INTO opiniones (op_id, usu_id, peli_id, puntuacion, contenido, op_likes, op_dislikes, fecha, est_id) VALUES
(1001, 1001, 1001, 5, "Me encanta", 10, 2, NOW(), 1),
(1002, 1001, 1002, 3, "Meh", 5, 3, NOW(), 1);

-- FOROS
INSERT INTO foros (foro_id, usu_id, nombre, descripcion, creacion, est_id, imagen) VALUES
(1001, 1001, "Mi primer Foro", "foro desc", NOW(), 1, ""),
(1002, 1002, "Foro2", "mi desc", NOW(), 1, "");

-- COMENTARIOS
INSERT INTO comentarios (com_id, usu_id, foro_id, contenido, fecha, est_id) VALUES
(1001, 1001, 1001, "Hello world", NOW(), 1),
(1002, 1003, 1001, "Hola :)", NOW(), 1),
(1003, 1004, 1001, "Grrr voy a tirar una bomba", NOW(), 3);

-- USU_PRO
INSERT INTO usu_pro (usu_pro_id, usu_id, pro_id, ori_id, origen, contenido, fecha, est_id) VALUES
(1001, 1004, 1002, 2, 1003, "Grrr voy a tirar una bomba", NOW(), 3);



