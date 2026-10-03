#!/bin/bash
set -e

echo "[BOOT] Iniciando contenedor Simple Stock Flow API..."

if [ ! -f /var/www/html/.env ]; then
    echo "[BOOT] Creando .env..."
    cp /var/www/html/.env.example /var/www/html/.env
fi

if [ ! -f /var/www/html/vendor/autoload.php ]; then
    echo "[BOOT] Instalando dependencias de Composer..."
    composer config policy.advisories.block false || true
    composer install --no-interaction --prefer-dist --optimize-autoloader --no-audit
fi

echo "[BOOT] Generando APP_KEY..."
php artisan key:generate --force || true

echo "[BOOT] Ejecutando migraciones..."
php artisan migrate --force || true

echo "[BOOT] Servidor Simple Stock Flow iniciado en puerto 8000"
exec php artisan serve --host=0.0.0.0 --port=8000
