-- Este archivo lo ejecuta MySQL solito la primera vez que se crea la base de datos.
-- Aquí creo la tabla de mensajes y le pongo unos datos de ejemplo.

-- Para que los acentos y la ñ se guarden bien
SET NAMES utf8mb4;

-- La tabla donde se guardan los mensajes de la app
CREATE TABLE IF NOT EXISTS mensajes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  autor VARCHAR(60) NOT NULL,
  mensaje VARCHAR(255) NOT NULL,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Unos mensajes de ejemplo, para que la app no arranque vacía
INSERT INTO mensajes (autor, mensaje) VALUES
  ('Nicky', '¡Hola Mundo desde MySQL! Si ves esto, la app se conectó bien a la base de datos.'),
  ('Nicky', 'Práctica 1: repositorio con ramas main y dev.'),
  ('Nicky', 'Práctica 6: servidor Nginx con Docker Compose.');