# NSS-Practica7-WebApp

**Práctica 7 de Electiva 2 (DevOps) - ITLA**

Hecha por Nicole Alejandra Santana Sánchez, matrícula 2025-1213.

Profesor: Elvis Cruz.

## ¿De qué trata?

Es una app web Hola Mundo en **PHP** que se conecta a una base de datos **MySQL**. Cada una corre en su propio contenedor y las levanto juntas con **Docker Compose**.

En la página se ve si la conexión funcionó, la versión de MySQL, y una lista de mensajes que vienen de la base de datos. También hay un formulario para guardar mensajes nuevos.

## ¿Cómo está organizado?

```
NSS-Practica7-WebApp/
├── src/
│   └── index.php        → la app que se conecta a MySQL
├── db/
│   └── init.sql         → crea la tabla y los mensajes de ejemplo
├── Dockerfile           → mi imagen de PHP con Apache y el conector de MySQL
├── docker-compose.yml   → los dos servicios: app y db
└── README.md
```

## El Dockerfile

Parto de la imagen `php:8.3-apache`, que ya trae el servidor Apache. Pero PHP no trae de fábrica el conector de MySQL, así que se lo instalo con `docker-php-ext-install pdo_mysql`. Después copio mi carpeta `src` a donde Apache busca las páginas.

## Los servicios del docker-compose.yml

- **app:** el servidor de aplicación. Se construye con mi Dockerfile (`build: .`) y sale por el puerto `8080`.
- **db:** la base de datos `mysql:8.4`. La primera vez que arranca, ejecuta mi `init.sql` solito.

Algunas cosas que agregué:

- **depends_on con service_healthy:** la app espera a que MySQL esté listo de verdad antes de arrancar.
- **healthcheck en la db:** Docker revisa cada 10 segundos si MySQL ya acepta conexiones.
- **Volumen `datos-mysql`:** los mensajes no se pierden aunque apague los contenedores.
- **El puerto de MySQL no está abierto hacia afuera:** solo la app puede hablar con la base de datos.
- **Variables de entorno:** la app lee los datos de conexión de ahí, así la contraseña no está escrita en el `index.php`.

Ojo: las contraseñas están en el `docker-compose.yml` porque es una práctica. En un proyecto real irían en un archivo `.env` o como secrets.

## ¿Cómo lo corro?

```bash
docker compose up -d --build   # construye mi imagen y levanta los dos contenedores
docker ps                      # muestra los contenedores; la db debe salir (healthy)
docker compose logs app        # muestra los logs de la app
docker compose down            # apaga y borra los contenedores (los datos se quedan en el volumen)
```

Después se abre `http://localhost:8080` en el navegador.

## ¿Dónde lo probé?

Como mi disco no tiene espacio para Docker Desktop, lo corrí en **GitHub Codespaces**, que ya trae Docker. Los archivos y los comandos son los mismos.