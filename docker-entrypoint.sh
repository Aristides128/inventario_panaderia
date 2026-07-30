#!/bin/sh

# Directorios de almacenamiento y permisos
mkdir -p storage/framework/cache/data \
         storage/framework/sessions \
         storage/framework/views \
         storage/logs \
         bootstrap/cache \
         storage/app/public/images

chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Eliminar cachés obsoletas del entorno local
rm -f bootstrap/cache/packages.php bootstrap/cache/services.php bootstrap/cache/config.php

# Redescubrir paquetes instalados en producción
php artisan package:discover || true
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true

# Recrear el enlace simbólico de almacenamiento público para la imagen del logo
rm -rf public/storage
php artisan storage:link --force || true
php artisan filament:assets || true

# Ejecutar migraciones automáticamente si se habilita la variable de entorno
if [ "$AUTO_MIGRATE" = "true" ]; then
    echo "Ejecutando migraciones de base de datos..."
    php artisan migrate --force || echo "Advertencia: Las migraciones fallaron o la BD no está lista aún."
fi

# Ejecutar seeders automáticamente si se habilita la variable de entorno
if [ "$AUTO_SEED" = "true" ]; then
    echo "Ejecutando seeders de base de datos..."
    php artisan db:seed --force || echo "Advertencia: Los seeders fallaron."
fi

# Asignar puerto dinámico proporcionado por la plataforma (por defecto 8000)
PORT=${PORT:-8000}
echo "Iniciando servidor web en el puerto $PORT..."

# Arrancar el servidor web de Laravel
exec php artisan serve --host=0.0.0.0 --port="$PORT"
