# Dockerfile para Sistema de Gestión de Congreso de Mercadotecnia
# Base: PHP 8.2 con Apache
FROM php:8.2-apache

# Información del mantenedor
LABEL maintainer="GJA Team"
LABEL description="Sistema de Gestión de Congreso Universitario con PHP, MySQL y 2FA"

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    && rm -rf /var/lib/apt/lists/*

# Instalar extensiones PHP necesarias
RUN docker-php-ext-install pdo_mysql mysqli mbstring exif pcntl bcmath gd zip

# Habilitar mod_rewrite de Apache
RUN a2enmod rewrite headers

# Configurar Document Root de Apache
ENV APACHE_DOCUMENT_ROOT=/var/www/html/Proyecto_conectado
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Configurar permisos de Apache
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Crear directorios para archivos generados
RUN mkdir -p /var/www/html/Proyecto_conectado/uploads \
    /var/www/html/Proyecto_conectado/constancias_pdf \
    && chown -R www-data:www-data /var/www/html/Proyecto_conectado/uploads \
    && chown -R www-data:www-data /var/www/html/Proyecto_conectado/constancias_pdf

# Configurar PHP
RUN cp "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"
RUN echo "upload_max_filesize = 50M" >> "$PHP_INI_DIR/php.ini" \
    && echo "post_max_size = 50M" >> "$PHP_INI_DIR/php.ini" \
    && echo "max_execution_time = 300" >> "$PHP_INI_DIR/php.ini" \
    && echo "memory_limit = 256M" >> "$PHP_INI_DIR/php.ini" \
    && echo "date.timezone = America/Mexico_City" >> "$PHP_INI_DIR/php.ini"

# Copiar código del proyecto
COPY ./Proyecto_conectado /var/www/html/Proyecto_conectado

# Establecer permisos finales
RUN chown -R www-data:www-data /var/www/html/Proyecto_conectado \
    && find /var/www/html/Proyecto_conectado -type d -exec chmod 755 {} \; \
    && find /var/www/html/Proyecto_conectado -type f -exec chmod 644 {} \;

# Exponer puerto 80
EXPOSE 80

# Comando de inicio
CMD ["apache2-foreground"]
