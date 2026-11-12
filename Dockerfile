# Mi imagen para la Práctica 7: PHP con Apache, lista para hablar con MySQL

# Parto de la imagen oficial de PHP que ya trae el servidor web Apache
FROM php:8.3-apache

# PHP no trae de fábrica la extensión para conectarse a MySQL, así que se la instalo
RUN docker-php-ext-install pdo_mysql

# Copio mi código a la carpeta donde Apache busca las páginas
COPY src/ /var/www/html/

# Apache escucha en el puerto 80 dentro del contenedor
EXPOSE 80