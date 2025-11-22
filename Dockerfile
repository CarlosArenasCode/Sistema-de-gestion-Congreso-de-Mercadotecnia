FROM php:8.2-apache

LABEL maintainer="GJA Team"

# Instalar dependencias (Incluyendo CRON)
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    cron \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    && rm -rf /var/lib/apt/lists/*

# Extensiones PHP
RUN docker-php-ext-install pdo_mysql mysqli mbstring exif pcntl bcmath gd zip
RUN a2enmod rewrite headers

# Configuración Apache
ENV APACHE_DOCUMENT_ROOT=/var/www/html/Proyecto_conectado
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Permisos
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html
RUN mkdir -p /var/www/html/Proyecto_conectado/uploads \
    /var/www/html/Proyecto_conectado/constancias_pdf \
    /var/www/html/Proyecto_conectado/php/logs \
    && chown -R www-data:www-data /var/www/html/Proyecto_conectado/uploads \
    && chown -R www-data:www-data /var/www/html/Proyecto_conectado/constancias_pdf \
    && chown -R www-data:www-data /var/www/html/Proyecto_conectado/php/logs \
    && chmod -R 777 /var/www/html/Proyecto_conectado/php/logs

# Configuración PHP
RUN cp "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini" \
    && echo "date.timezone = America/Mexico_City" >> "$PHP_INI_DIR/php.ini"

# Copiar código
COPY ./Proyecto_conectado /var/www/html/Proyecto_conectado

# --- CONFIGURACIÓN CRON ---
COPY crontab /etc/cron.d/my-cron
RUN chmod 0644 /etc/cron.d/my-cron && \
    crontab /etc/cron.d/my-cron && \
    touch /var/log/cron.log

EXPOSE 80

# Iniciar Cron y Apache
CMD cron && apache2-foreground