# Dockerfile para Sistema de Gestión - Congreso de Mercadotecnia
FROM php:8.2-apache

# Instalar extensiones PHP necesarias y dependencias del sistema
RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libzip-dev \
    unzip \
    git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install pdo pdo_mysql mysqli zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Habilitar mod_rewrite de Apache para URLs amigables (opcional)
RUN a2enmod rewrite

# Configurar DocumentRoot a /var/www/html/Proyecto_conectado
ENV APACHE_DOCUMENT_ROOT=/var/www/html/Proyecto_conectado

# Actualizar configuración de Apache para apuntar al subdirectorio
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Copiar código del proyecto al contenedor
COPY . /var/www/html/

# Crear directorio para archivos generados (PDFs, uploads, etc.)
RUN mkdir -p /var/www/html/Proyecto_conectado/uploads \
    && mkdir -p /var/www/html/Proyecto_conectado/constancias_pdf \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Exponer puerto 80
EXPOSE 80

# Comando por defecto: iniciar Apache
CMD ["apache2-foreground"]
