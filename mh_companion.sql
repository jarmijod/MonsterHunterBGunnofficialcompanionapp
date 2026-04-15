-- Eliminar base de datos si existe
DROP DATABASE IF EXISTS `mh_companion`;

-- Crear base de datos con utf8mb4
CREATE DATABASE `mh_companion`;

-- Usar la base creada
USE `mh_companion`;

-- TABLA PARTIDA
DROP TABLE IF EXISTS PARTIDA;
CREATE TABLE PARTIDA (
  Id_partida INT UNSIGNED NOT NULL AUTO_INCREMENT,
  Nombre_partida VARCHAR(150) NOT NULL,
  Descripcion_partida TEXT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (Id_partida)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLA USUARIO
DROP TABLE IF EXISTS USUARIO;
CREATE TABLE USUARIO (
  Id_usuario INT UNSIGNED NOT NULL AUTO_INCREMENT,
  Nombre_usuario VARCHAR(100) NOT NULL UNIQUE,
  Contraseña_usuario VARCHAR(255) NOT NULL, -- almacenar hash (password_hash)
  Tipo_usuario ENUM('admin','player','gm') NOT NULL DEFAULT 'player',
  Rango_usuario INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (Id_usuario)
) ;

-- TABLA PARTIDA_USUARIO (relaciona usuarios con partidas)
DROP TABLE IF EXISTS PARTIDA_USUARIO;
CREATE TABLE PARTIDA_USUARIO (
  Id_partidausuario INT UNSIGNED NOT NULL AUTO_INCREMENT,
  Id_usuario INT UNSIGNED NOT NULL,
  Id_partida INT UNSIGNED NOT NULL,
  Rol_en_partida ENUM('player','gm','observer') DEFAULT 'player',
  joined_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (Id_partidausuario),
  INDEX (Id_usuario),
  INDEX (Id_partida),
  CONSTRAINT fk_partida_usuario_usuario FOREIGN KEY (Id_usuario) REFERENCES USUARIO(Id_usuario) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_partida_usuario_partida FOREIGN KEY (Id_partida) REFERENCES PARTIDA(Id_partida) ON DELETE CASCADE ON UPDATE CASCADE
) ;

-- TABLA RECURSO
DROP TABLE IF EXISTS RECURSO;
CREATE TABLE RECURSO (
  Id_recurso INT UNSIGNED NOT NULL AUTO_INCREMENT,
  Nombre_recurso VARCHAR(150) NOT NULL,
  Descripcion_recurso TEXT NULL,
  Rareza_recurso ENUM('comun','raro','legendario','epico') DEFAULT 'comun',
  Categoria_recurso VARCHAR(100) DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (Id_recurso),
  UNIQUE KEY uq_recurso_nombre (Nombre_recurso)
) ;

-- TABLA BAUL_RECURSO (inventario/baúl de recursos por usuario)
DROP TABLE IF EXISTS BAUL_RECURSO;
CREATE TABLE BAUL_RECURSO (
  Id_BaulRecurso INT UNSIGNED NOT NULL AUTO_INCREMENT,
  Id_usuario INT UNSIGNED NOT NULL,
  Id_recurso INT UNSIGNED NOT NULL,
  Cantidad_BaulRecurso INT NOT NULL DEFAULT 0,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (Id_BaulRecurso),
  INDEX (Id_usuario),
  INDEX (Id_recurso),
  CONSTRAINT fk_baulrecurso_usuario FOREIGN KEY (Id_usuario) REFERENCES USUARIO(Id_usuario) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_baulrecurso_recurso FOREIGN KEY (Id_recurso) REFERENCES RECURSO(Id_recurso) ON DELETE CASCADE ON UPDATE CASCADE,
  UNIQUE KEY uq_baul_usuario_recurso (Id_usuario,Id_recurso)
) ;

-- TABLA ARMADURA
DROP TABLE IF EXISTS `ARMADURA`;
CREATE TABLE `ARMADURA` (
  `Id_armadura` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `Nombre_armadura` VARCHAR(150) NOT NULL,
  `Rareza_armadura` ENUM('comun','raro','epico','legendario') DEFAULT 'comun',
  `Parte_armadura` VARCHAR(50) NOT NULL,
  `Habilidad_armadura` VARCHAR(200) NULL,
  `Escudo_armadura` VARCHAR(100) NULL,
  `ResistenciaElemental_armadura` JSON NULL,
  `Requisitos_armadura` JSON NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`Id_armadura`)
) ;

-- TABLA SET_ARMADURA
DROP TABLE IF EXISTS `SET_ARMADURA`;
CREATE TABLE `SET_ARMADURA` (
  `Id_SetArmadura` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `Id_armadura` INT UNSIGNED NOT NULL,
  `NombreSet_SetArmadura` VARCHAR(150) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`Id_SetArmadura`),
  INDEX (`Id_armadura`),
  CONSTRAINT `fk_setarmadura_armadura` FOREIGN KEY (`Id_armadura`) REFERENCES `ARMADURA`(`Id_armadura`) ON DELETE CASCADE ON UPDATE CASCADE
);

-- TABLA ARMA
DROP TABLE IF EXISTS `ARMA`;
CREATE TABLE `ARMA` (
  `Id_arma` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `Nombre_arma` VARCHAR(50) NOT NULL,
  `Tipo_arma` VARCHAR(50) NOT NULL,
  `Rareza_arma` ENUM('comun','raro','epico','legendario') DEFAULT 'comun',
  `Requisitos_arma` JSON NULL,
  `CartasAgregadas_arma` JSON NULL,
  `CartasQuitadas_arma` JSON NULL,
  `CartasDaño_arma` JSON NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`Id_arma`)
) ;

-- TABLA SET_ARMA
DROP TABLE IF EXISTS `SET_ARMA`;
CREATE TABLE `SET_ARMA` (
  `Id_SetArma` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `Id_Arma` INT UNSIGNED NOT NULL,
  `NombreSet_SetArma` VARCHAR(150) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`Id_SetArma`),
  INDEX (`Id_Arma`),
  CONSTRAINT `fk_setarma_arma` FOREIGN KEY (`Id_Arma`) REFERENCES `ARMA`(`Id_arma`) ON DELETE CASCADE ON UPDATE CASCADE
);

-- TABLA BAUL_EQUIPO (equipamiento por usuario; posible que varios registros existan)
DROP TABLE IF EXISTS BAUL_EQUIPO;
CREATE TABLE BAUL_EQUIPO (
  Id_BaulEquipo INT UNSIGNED NOT NULL AUTO_INCREMENT,
  Id_armadura INT UNSIGNED NULL,
  Id_arma INT UNSIGNED NULL,
  Id_usuario INT UNSIGNED NOT NULL,
  Estado_BaulEquipo ENUM('disponible','forjada','equipada','desechada') DEFAULT 'disponible',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (Id_BaulEquipo),
  INDEX (Id_armadura),
  INDEX (Id_arma),
  INDEX (Id_usuario),
  CONSTRAINT fk_baulequipo_armadura FOREIGN KEY (Id_armadura) REFERENCES ARMADURA(Id_armadura) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_baulequipo_arma FOREIGN KEY (Id_arma) REFERENCES ARMA(Id_arma) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_baulequipo_usuario FOREIGN KEY (Id_usuario) REFERENCES USUARIO(Id_usuario) ON DELETE CASCADE ON UPDATE CASCADE
);

-- TABLA MISION
DROP TABLE IF EXISTS MISION;
CREATE TABLE MISION (
  Id_Mision INT UNSIGNED NOT NULL AUTO_INCREMENT,
  Estado_Mision ENUM('available','assigned','completed','failed') DEFAULT 'available',
  Titulo_mision TEXT NULL,
  Descripción_Mision TEXT NULL,
  Rango_Mision VARCHAR(100) DEFAULT NULL,
  Recompensa_Mision TEXT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (Id_Mision)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLA MISION_CAZADOR (vincula misión con usuario/cazador)
DROP TABLE IF EXISTS MISION_CAZADOR;
CREATE TABLE MISION_CAZADOR (
  Id_MisionCazador INT UNSIGNED NOT NULL AUTO_INCREMENT,
  Id_Mision INT UNSIGNED NOT NULL,
  Id_Usuario INT UNSIGNED NOT NULL,
  Estado_MisionCazador ENUM('assigned','accepted','completed','failed','rejected') DEFAULT 'assigned',
  assigned_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (Id_MisionCazador),
  INDEX (Id_Mision),
  INDEX (Id_Usuario),
  CONSTRAINT fk_misioncazador_mision FOREIGN KEY (Id_Mision) REFERENCES MISION(Id_Mision) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_misioncazador_usuario FOREIGN KEY (Id_Usuario) REFERENCES USUARIO(Id_usuario) ON DELETE CASCADE ON UPDATE CASCADE
);

-- TABLA NOTA_CAZADOR
DROP TABLE IF EXISTS NOTA_CAZADOR;
CREATE TABLE NOTA_CAZADOR (
  Id_NotaCazador INT UNSIGNED NOT NULL AUTO_INCREMENT,
  Id_Usuario INT UNSIGNED NOT NULL,
  Monstruo_NotaCazador VARCHAR(150) NULL,
  Texto_NotaCazador TEXT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (Id_NotaCazador),
  INDEX (Id_Usuario),
  CONSTRAINT fk_notacazador_usuario FOREIGN KEY (Id_Usuario) REFERENCES USUARIO(Id_usuario) ON DELETE CASCADE ON UPDATE CASCADE
);

-- INSERT ADMINISTRADOR DE EJEMPLO (cambiar el hash de contraseña)
INSERT INTO `USUARIO` (`Nombre_usuario`, `Contraseña_usuario`, `Tipo_usuario`, `Rango_usuario`)
VALUES ('admin', '123', 'admin', 'Master'),
('jugador', '123', 'player', 'Master'),
('jugador2', '123', 'player', 'Master'),
('jugador3', '123', 'player', 'Master');

INSERT INTO ARMADURA 
  (Nombre_armadura, Rareza_armadura, Parte_armadura, Habilidad_armadura, Escudo_armadura, ResistenciaElemental_armadura, Requisitos_armadura) VALUES

('Lentes de Cuero', 'comun', 'Casco', '', '0', NULL, JSON_ARRAY()),
('Cota de cuero', 'comun', 'Pechera', '', '1', NULL, JSON_ARRAY()),
('Pantalones de Cuero', 'comun', 'Pantalones', '', '0', NULL, JSON_ARRAY()),

('Yelmo de Aleación', 'comun', 'Casco', '', '1', NULL, JSON_OBJECT('Mineral Machalita', 2, 'Mineral Carbalita', 1, 'Mineral Dragonita', 1)),
('Cota de Aleación', 'comun', 'Pechera', '', '1', NULL, JSON_OBJECT('Mineral Machalita', 1, 'Mineral Carbalita', 2, 'Mineral Dragonita', 1)),
('Grebas de Aleación', 'comun', 'Pantalones', 'Resistencia al Veneno \n Eres inmune al estado veneno', '0', NULL, JSON_OBJECT('Mineral Machalita', 1, 'Mineral Carbalita', 2, 'Mineral Dragonita', 1)),

('Yelmo de Hueso', 'comun', 'Casco', '', '1', NULL, JSON_OBJECT('Hueso de monstruo pequeño', 2, 'Hueso antiguo', 2)),
('Cota de Hueso', 'comun', 'Pechera', 'Bonus al Aturdir \n una vez por misión, aplica una ficha de aturdir extra', '0', NULL, JSON_OBJECT('Hueso de monstruo pequeño', 1, 'Hueso antiguo', 1)),
('Grebas de Hueso', 'comun', 'Pantalones', '', '1', NULL, JSON_OBJECT('Hueso de monstruo pequeño', 1, 'Hueso antiguo', 1)),

('Yelmo de Barroth', 'comun', 'Casco', 'Guardia Mejorada \n Gana 1 de armadura cuando juegues ataques que otorgan armadura', '1', JSON_OBJECT('water', 1), JSON_OBJECT('Cresta de Barroth', 1, 'Garra de Barroth', 1, 'Barro Fertil', 1)),
('Cota de Barroth', 'comun', 'Pechera', '', '1', JSON_OBJECT('water', 1), JSON_OBJECT('Coraza de Barroth', 1, 'Cresta de Barroth', 2, 'Garra de Barroth', 1, 'Hueso de Calidad', 2)),
('Grebas de Barroth', 'comun', 'Grebas', 'Resistencia al aturdir \n Eres inmune al estado aturdir', '1', JSON_OBJECT('water', 1), JSON_OBJECT('Cresta de Barroth', 1, 'Coraza de Barroth', 2, 'Barro Fertil', 1, 'Hueso Afilado', 1)),

('Yelmo de Jagras', 'comun', 'Casco', 'Velocidad al comer \n Una vez por misión, puedes usar acciones de combate y de preparación en el mismo turno', '1', JSON_OBJECT('water', 1), JSON_OBJECT('Piel de Gran Jagras', 1, 'Melena de Gran Jagras', 1, 'Garra de Gran Jagras', 1,'Hueso Antiguo',1)),
('Cota de Jagras', 'comun', 'Pechera', 'Mejora de Camarada \n Puedes usar la habilidad de tu Camarada 2 veces por misión', '1', JSON_OBJECT('water', 1), JSON_OBJECT('Piel de Gran Jagras', 1, 'Garra de Gran Jagras', 1, 'Escama de Gran Jagras', 1, 'Hueso de Monstruo Pequeño', 1)),
('Grebas de Jagras', 'comun', 'Grebas', '', '1', JSON_OBJECT('water', 1), JSON_OBJECT('Escama de Gran Jagras', 1, 'Piel de Gran Jagras', 1, 'Melena de Gran Jagras', 1)),

('Defensa de Kulu', 'comun', 'Casco', 'Bonus Punto Débil \n Las cartas de ataque con combo 2 o mas roban 1 carta de daño extra.', '1', JSON_OBJECT('Ice', 1), JSON_OBJECT('Escama de Kulu-Ya-Ku', 1, 'Piel de Kulu-Ya-Ku', 1, 'Pluma de Kulu-Ya-Ku', 1)),
('Cota de Kulu', 'comun', 'Pechera', 'Ojo Critico \n Cuando robes daño, roba 1 carta extra, despues elige 1 para colocar en la parte superior del mazo de daño antes de causar daño', '1', JSON_OBJECT('Ice', 1), JSON_OBJECT('Piel de Kulu-Ya-Ku', 2, 'Pluma de Kulu-Ya-Ku', 1, 'Pico de Kulu-Ya-Ku', 1, 'Gema de Wyvern Pajaro', 1)),
('Grebas de Kulu', 'comun', 'Grebas', '', '1', JSON_OBJECT('Ice', 2), JSON_OBJECT('Piel de Kulu-Ya-Ku', 1, 'Escama de Kulu-Ya-Ku', 2, 'Piel de Dracoalado', 3,'Cristal de Tierra',1)),

('Capucha de Pukei', 'comun', 'Casco', 'Experto en Hierbaesporas \n Cuando recuperas salud de cualquier cosa que no sea una pocion, recupera 1 de salud adicional.', '1', JSON_OBJECT('Water', 1), JSON_OBJECT('Coraza de Pukei-Pukei', 1, 'Cola de Pukei-Pukei', 1, 'Ala de Pukei-Pukei', 1)),
('Cota de Pukei', 'comun', 'Pechera', 'Botnico \n Durante la fase de recoleccion, cuando los cazadores ganen una pocion, todos pueden recuperar inmediatamente su salud maxima.', '1', JSON_OBJECT('Water', 1), JSON_OBJECT('escama de Pukei-Pukei', 2, 'Coraza de Pukei-Pukei', 1, 'Carbalita', 3)),
('Grebas de Pukei', 'comun', 'Grebas', 'Resistencia al Veneno \n Eres inmune al estado veneno.', '1', JSON_OBJECT('Water', 1), JSON_OBJECT('Coraza de Pukei-Pukei', 2, 'Vesicula de Pukei-Pukei', 2, 'Escama de Pukei-Pukei', 3,'Hueso Afilado',1));


-- Aquí debes conocer el Id_armadura asignado automáticamente. Suponiendo que son insertados en orden y empiezan en 1:

INSERT INTO SET_ARMADURA (Id_armadura, NombreSet_SetArmadura) VALUES
(1, 'Inicial'),
(2, 'Inicial'),
(3, 'Inicial'),

(4, 'Aleación'),
(5, 'Aleación'),
(6, 'Aleación'),

(7, 'Hueso'),
(8, 'Hueso'),
(9, 'Hueso'),

(10, 'Barroth'),
(11, 'Barroth'),
(12, 'Barroth'),

(13,'Gran Jagras'),
(14, 'Gran Jagras'),
(15, 'Gran Jagras'),

(16,'Kulu-Ya-Ku'),
(17, 'Kulu-Ya-Ku'),
(18, 'Kulu-Ya-Ku'),

(19,'Pukei-Pukei'),
(20, 'Pukei-Pukei'),
(21, 'Pukei-Pukei');

INSERT INTO ARMA 
  (Tipo_arma, Nombre_arma, Rareza_arma, Requisitos_arma, CartasAgregadas_arma, CartasQuitadas_arma, CartasDaño_arma) VALUES

('Espada larga', 'Katana Ferrea', 'comun', JSON_ARRAY(), JSON_ARRAY(), JSON_ARRAY(), JSON_OBJECT('1',9,'2',3)),
('Espada larga', 'Gracia Ferrea', 'comun',
 JSON_OBJECT('Mineral Dragonita',1,'Mineral Machalita',1,'Hueso de monstruo Mediano',1),
 JSON_OBJECT('Enhanced Spirit Thrust', 2),
 JSON_OBJECT('Spirit Thrust ', 2),
 JSON_OBJECT('1',7,'2',5)
),
('Espada larga', 'Palabra Ferrea', 'comun',
 JSON_OBJECT('Mineral Fucium', 2,'Mineral Carbalita', 2,'Mineral Dragonita', 3,'Cristal de Dracovena', 2),
 JSON_OBJECT('Enhanced Spirit Thrust', 2,'Enhanced Overhead Slash ', 2),
 JSON_OBJECT('Spirit Thrust ', 2,'Overhead Slash', 2),
 JSON_OBJECT('1',5,'2',5,'3',2)
);


INSERT INTO SET_ARMA (Id_Arma, NombreSet_SetArma) VALUES
(1, 'Aleación'),
(2, 'Aleación'),
(3, 'Aleación');

INSERT INTO RECURSO (Nombre_recurso, Descripcion_recurso, Rareza_recurso, Categoria_recurso) VALUES
-- Categoría Común
('Mineral Carbalita', '', 'comun', 'Común'),
('Mineral Machalita', '', 'comun', 'Común'),
('Mineral Dragonita', '', 'comun', 'Común'),
('Mineral Fucium', '', 'comun', 'Común'),
('Hueso de Calidad', '', 'comun', 'Común'),
('Hueso de monstruo pequeño', '', 'comun', 'Común'),
('Hueso de monstruo Mediano', '', 'comun', 'Común'),
('Hueso de monstruo Grande', '', 'comun', 'Común'),
('Hueso Afilado', '', 'comun', 'Común'),
('Hueso escudo', '', 'comun', 'Común'),
('Hueso antiguo', '', 'comun', 'Común'),
('Hueso fósil', '', 'comun', 'Común'),
('Cristal de Dracovena', '', 'comun', 'Común'),
('Piel de Dracoalado', '', 'comun', 'Común'),

-- Categoría Otros
('Piedra de lava', '', 'comun', 'Otros'),
('Cristal coralino', '', 'comun', 'Otros'),
('Barro Fertil', '', 'comun', 'Otros'),
('Piel Calida', '', 'comun', 'Otros'),
('Vesicula de Pukei-Pukei', '', 'comun', 'Otros'),
('Medula de Diablos', '', 'comun', 'Otros'),
('Cristal Reluciente', '', 'comun', 'Otros'),
('Novacristal', '', 'comun', 'Otros'),
('Vesicula Torrencial', '', 'comun', 'Otros'),
('Vesicula Infernal', '', 'comun', 'Otros'),
('Cuerno Majestuoso', '', 'comun', 'Otros'),
('Hueso de Drgón Anciano', '', 'comun', 'Otros'),
('Escencia de Dragón Anciano', '', 'comun', 'Otros'),

-- Categoría Gran Jagras
('Garra de Gran Jagras', '', 'comun', 'Gran Jagras'),
('Piel de Gran Jagras', '', 'comun', 'Gran Jagras'),
('Escama de Gran Jagras', '', 'comun', 'Gran Jagras'),
('Melena de Gran Jagras', '', 'comun', 'Gran Jagras'),
('Garra Afilada', '', 'comun', 'Gran Jagras'),
('Garra Perforante', '', 'comun', 'Gran Jagras'),

-- Categoría Barroth
('Garra de Barroth', '', 'comun', 'Barroth'),
('Cresta de Barroth', '', 'comun', 'Barroth'),
('Caparazón de Barroth', '', 'comun', 'Barroth'),
('Coraza de Barroth', '', 'comun', 'Barroth'),

-- Categoría Kulu-Ya-Ku
('Pluma de Kulu-Ya-Ku', '', 'comun', 'Kulu-Ya-Ku'),
('Pico de Kulu-Ya-Ku', '', 'comun', 'Kulu-Ya-Ku'),
('Cristal de Tierra', '', 'comun', 'Kulu-Ya-Ku'),
('Escama de Kulu-Ya-Ku', '', 'comun', 'Kulu-Ya-Ku'),
('Piel de Kulu-Ya-Ku', '', 'comun', 'Kulu-Ya-Ku'),
('Gema de Wyvern Pajaro', '', 'comun', 'Kulu-Ya-Ku'),

-- Categoría Pukei-Pukei
('Ala de Pukei-Pukei', '', 'comun', 'Pukei-Pukei'),
('Escama de Pukei-Pukei', '', 'comun', 'Pukei-Pukei'),
('Pendola de Pukei-Pukei', '', 'comun', 'Pukei-Pukei'),
('Coraza de Pukei-Pukei', '', 'comun', 'Pukei-Pukei'),
('Vesicula Venenosa', '', 'comun', 'Pukei-Pukei'),
('Cola de Pukei-Pukei', '', 'comun', 'Pukei-Pukei'),
('Vesicula pukei-pukei', '', 'comun', 'Pukei-Pukei'),

-- Categoría Tobi-Kadachi
('Piel de Tobi-Kadachi', '', 'comun', 'Tobi-Kadachi'),
('Escama de Tobi-Kadachi', '', 'comun', 'Tobi-Kadachi'),
('Vesicula Electrica', '', 'comun', 'Tobi-Kadachi'),
('Electrodo de Tobi-Kadachi', '', 'comun', 'Tobi-Kadachi'),
('Membrana de Tobi-Kadachi', '', 'comun', 'Tobi-Kadachi'),
('Garra de Tobi-Kadachi', '', 'comun', 'Tobi-Kadachi'),
('Vesicula de Rayo', '', 'comun', 'Tobi-Kadachi'),

-- Categoría Anjanath
('Piel de Anjanath', '', 'comun', 'Anjanath'),
('Escama de Anjanath', '', 'comun', 'Anjanath'),
('Hueso Nasal de Anjanath', '', 'comun', 'Anjanath'),
('Cola de Anjanath', '', 'comun', 'Anjanath'),
('Colmillo de Anjanath', '', 'comun', 'Anjanath'),
('Vesicula Flamigera', '', 'comun', 'Anjanath'),


-- Categoría Jyuratodus
('Aleta de Jyuratodus', '', 'comun', 'Jyuratodus'),
('Vesicula Acuosa', '', 'comun', 'Jyuratodus'),
('Escama de Jyuratodus', '', 'comun', 'Jyuratodus'),
('Caparazón de Jyuratodus', '', 'comun', 'Jyuratodus'),
('Coraza de Jyuratodus', '', 'comun', 'Jyuratodus'),
('Colmillo de Jyuratodus', '', 'comun', 'Jyuratodus'),
('Escama de Gaju', '', 'comun', 'Jyuratodus'),

-- Categoría Rathalos
('Garra de Rathalos', '', 'comun', 'Rathalos'),
('escama de Rathalos', '', 'comun', 'Rathalos'),
('Membrana de Rathalos', '', 'comun', 'Rathalos'),
('Cola de Rathalos', '', 'comun', 'Rathalos'),
('Tuetano de Rathalos', '', 'comun', 'Rathalos'),
('Placa de Rathalos', '', 'comun', 'Rathalos'),
('Ala de Rathalos', '', 'comun', 'Rathalos'),
('Coraza de Rathalos', '', 'comun', 'Rathalos'),
('Caparazon de Rathalos', '', 'comun', 'Rathalos'),
('Medula de Rathalos', '', 'comun', 'Rathalos'),


-- Categoría Rathalos Celeste
('Garra de Rathalos Celeste', '', 'comun', 'Rathalos Celeste'),
('escama de Rathalos Celeste', '', 'comun', 'Rathalos Celeste'),
('Ala de Rathalos Celeste', '', 'comun', 'Rathalos Celeste'),
('Placa de Rathalos Celeste', '', 'comun', 'Rathalos Celeste'),
('Tuetano de Rathalos Celeste', '', 'comun', 'Rathalos Celeste'),
('Cola de Rathalos Celeste', '', 'comun', 'Rathalos Celeste'),
('Coraza de Rathalos Celeste', '', 'comun', 'Rathalos Celeste'),


-- Categoría Diablos
('Caparazon Diablos', '', 'comun', 'Diablos'),
('Coraza de Diablos', '', 'comun', 'Diablos'),
('Cuerno Retorcidos', '', 'comun', 'Diablos'),
('Colmillo de Diablos', '', 'comun', 'Diablos'),
('Cresta de Diablos', '', 'comun', 'Diablos'),


-- Categoría Diablos Negra

('Coraza de Diablos Negra', '', 'comun', 'Diablos Negra'),
('Colmillo Negro en Espiral', '', 'comun', 'Diablos Negra'),
('Cresta de Diablos Negra', '', 'comun', 'Diablos Negra'),
('Gema de Wyvern', '', 'comun', 'Diablos Negra'),

-- Categoría Kushala Daora
('Membrana de Daora', '', 'comun', 'Kushala Daora'),
('Gema de Daora', '', 'comun', 'Kushala Daora'),
('Garra de Daora', '', 'comun', 'Kushala Daora'),
('Escama Draconiana de Daora', '', 'comun', 'Kushala Daora'),
('Coraza de Daora', '', 'comun', 'Kushala Daora'),
('Cuerno de Daora', '', 'comun', 'Kushala Daora'),
('Cola de Daora', '', 'comun', 'Kushala Daora'),

-- Categoría Nergigante
('Escama de Dragon inmortal', '', 'comun', 'Nergigante'),
('Garra de Nergigante', '', 'comun', 'Nergigante'),
('Coraza de Nergigante', '', 'comun', 'Nergigante'),
('Placa de Regeneración de Nergigante', '', 'comun', 'Nergigante'),
('Cuerno de Nergigante', '', 'comun', 'Nergigante'),
('Cola de Nergigante', '', 'comun', 'Nergigante'),
('Gema de Nergigante', '', 'comun', 'Nergigante'),

-- Categoría Teostra
('Escama de Dragon de Fuego', '', 'comun', 'Teostra'),
('Cuerno de Teostra', '', 'comun', 'Teostra'),
('Membrana de Teostra', '', 'comun', 'Teostra'),
('Gema de Teostra', '', 'comun', 'Teostra'),
('Polvo de Teostra', '', 'comun', 'Teostra'),
('Coraza de Teostra', '', 'comun', 'Teostra'),
('Garra de Teostra', '', 'comun', 'Teostra'),
('Melena de Teostra', '', 'comun', 'Teostra'),
('Cola de Teostra', '', 'comun', 'Teostra');


INSERT IGNORE INTO BAUL_RECURSO (Id_usuario, Id_recurso, Cantidad_BaulRecurso)
SELECT u.Id_usuario, r.Id_recurso, 0
FROM USUARIO u
CROSS JOIN RECURSO r
LEFT JOIN BAUL_RECURSO b ON b.Id_usuario = u.Id_usuario AND b.Id_recurso = r.Id_recurso
WHERE b.Id_BaulRecurso IS NULL;

-- Llenar BAUL_EQUIPO con todas las armaduras existentes
INSERT INTO BAUL_EQUIPO (Id_armadura, Id_arma, Id_usuario, Estado_BaulEquipo)
SELECT a.Id_armadura, NULL, u.Id_usuario, 'disponible'
FROM ARMADURA a
JOIN USUARIO u ON u.Tipo_usuario = 'player';

-- Llenar BAUL_EQUIPO con todas las armas existentes
INSERT INTO BAUL_EQUIPO (Id_armadura, Id_arma, Id_usuario, Estado_BaulEquipo)
SELECT NULL, ar.Id_arma, u.Id_usuario, 'disponible'
FROM ARMA ar
JOIN USUARIO u ON u.Tipo_usuario = 'player';

ALTER TABLE BAUL_EQUIPO
ADD COLUMN IdArmaAnterior_BaulEquipo INT UNSIGNED NULL,
ADD CONSTRAINT fk_baulequipo_armaanterior FOREIGN KEY (IdArmaAnterior_BaulEquipo) REFERENCES ARMA(Id_arma) ON DELETE SET NULL ON UPDATE CASCADE;
