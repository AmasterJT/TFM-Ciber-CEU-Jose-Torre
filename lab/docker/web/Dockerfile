FROM php:8.1-apache

RUN docker-php-ext-install mysqli pdo pdo_mysql

# Activar mod_rewrite
RUN a2enmod rewrite

# Configuración PHP vulnerable (TFM)
RUN echo "display_errors=On" >> /usr/local/etc/php/php.ini

# Copiar config Apache si quieres personalizar
