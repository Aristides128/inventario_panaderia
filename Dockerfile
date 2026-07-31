# ============================================
# Stage 1: Install PHP Production Dependencies (Composer)
# ============================================
FROM composer:latest AS composer-builder

WORKDIR /app

# Copiar manifiestos de Composer
COPY composer.json composer.lock ./

# Instalar ÚNICAMENTE dependencias de producción y optimizar autoloader
RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts \
    --ignore-platform-reqs

# ============================================
# Stage 2: Final Ultra-Lightweight Production Image (Alpine Linux)
# ============================================
FROM php:8.3-cli-alpine AS production

LABEL maintainer="inventario_panaderia"

# Instalar librerías de sistema de Alpine y compilar extensiones PHP requeridas por Laravel y Filament
RUN apk add --no-cache \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    libzip-dev \
    icu-dev \
    oniguruma-dev \
    libxml2-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        intl \
        gd \
        zip \
        bcmath \
        opcache

# Configuración de rendimiento de OPcache para Laravel y Filament
RUN echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
 && echo "opcache.memory_consumption=128" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
 && echo "opcache.interned_strings_buffer=8" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
 && echo "opcache.max_accelerated_files=10000" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
 && echo "opcache.save_comments=1" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini

WORKDIR /var/www/html

# Copiar código fuente de la aplicación
COPY . .

# Copiar dependencias de producción de PHP desde Stage 1
COPY --from=composer-builder /app/vendor ./vendor

# Publicar assets de Filament dentro de la imagen
RUN php artisan filament:assets || true

# Copiar y configurar el script de inicio
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

# Asegurar directorios de almacenamiento y permisos adecuados
RUN mkdir -p storage/framework/cache/data \
             storage/framework/sessions \
             storage/framework/views \
             storage/logs \
             bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Variables de entorno por defecto para producción
ENV APP_ENV=production \
    APP_DEBUG=false

EXPOSE 8000

ENTRYPOINT ["docker-entrypoint"]

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
