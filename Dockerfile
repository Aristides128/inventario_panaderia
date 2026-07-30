# ============================================
# Stage 1: Build Frontend Assets (Vite / Tailwind CSS)
# ============================================
FROM node:20-alpine AS frontend-builder

WORKDIR /app

# Copiar manifiestos de paquetes
COPY package.json package-lock.json* ./

# Instalar dependencias para la compilación frontend
RUN npm ci || npm install

# Copiar recursos y configuración de Vite
COPY vite.config.js ./
COPY resources/ ./resources/
COPY public/ ./public/

# Compilar assets de producción (salida en public/build)
RUN npm run build

# ============================================
# Stage 2: Install PHP Production Dependencies
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
# Stage 3: Final Ultra-Lightweight Production Image (Alpine Linux ~170MB)
# ============================================
FROM php:8.3-cli-alpine AS production

LABEL maintainer="inventario_panaderia"

# Instalar librerías de sistema de Alpine y compilar extensiones PHP
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

WORKDIR /var/www/html

# Copiar código fuente de la aplicación
COPY . .

# Copiar dependencias de producción de PHP desde Stage 2
COPY --from=composer-builder /app/vendor ./vendor

# Copiar assets frontend compilados desde Stage 1
COPY --from=frontend-builder /app/public/build ./public/build

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
