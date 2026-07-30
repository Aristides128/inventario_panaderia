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
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
fi

# Ejecutar migraciones automáticamente si se habilita la variable de entorno
if [ "$AUTO_MIGRATE" = "true" ]; then
    echo "Ejecutando migraciones de base de datos..."
    php artisan migrate --force || echo "Advertencia: No se pudieron ejecutar las migraciones."
fi

# Ejecutar seeders automáticamente si se habilita la variable de entorno
if [ "$AUTO_SEED" = "true" ]; then
    echo "Ejecutando seeders de base de datos..."
    php artisan db:seed --force || echo "Advertencia: No se pudieron ejecutar los seeders."
fi

# Asignar puerto dinámico proporcionado por la plataforma (por defecto 8000)
PORT=${PORT:-8000}
echo "Iniciando servidor en el puerto $PORT..."

# Si se pasa un comando personalizado se ejecuta, de lo contrario arranca php artisan serve
if [ "$#" -eq 0 ] || [ "$1" = "php" ]; then
    exec php artisan serve --host=0.0.0.0 --port="$PORT"
else
    exec "$@"
fi
