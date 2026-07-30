#!/bin/sh
set -e

# Asegurar directorios de almacenamiento y permisos
mkdir -p storage/framework/cache/data \
         storage/framework/sessions \
         storage/framework/views \
         storage/logs \
         bootstrap/cache

chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Crear enlace simbólico de almacenamiento público si no existe
if [ ! -L public/storage ]; then
    php artisan storage:link || true
fi

# Caché de configuración y rutas para máximo rendimiento en producción
if [ "$APP_ENV" = "production" ]; then
    echo "Optimizando caches de producción..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

# Ejecutar migraciones automáticamente si se habilita la variable de entorno
if [ "$AUTO_MIGRATE" = "true" ]; then
    echo "Ejecutando migraciones de base de datos..."
    php artisan migrate --force
fi

# Ejecutar seeders automáticamente si se habilita la variable de entorno
if [ "$AUTO_SEED" = "true" ]; then
    echo "Ejecutando seeders de base de datos..."
    php artisan db:seed --force
fi

# Ejecutar el comando del contenedor (por defecto php artisan serve)
exec "$@"
