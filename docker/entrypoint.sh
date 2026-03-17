#!/usr/bin/env sh
set -eu

cd /app/backend

if [ -z "${APP_KEY:-}" ]; then
  echo "Error: APP_KEY is required. Set it in Render -> Environment." >&2
  exit 1
fi

mkdir -p \
  bootstrap/cache \
  database \
  storage/framework/cache/data \
  storage/framework/sessions \
  storage/framework/testing \
  storage/framework/views \
  storage/logs
touch database/database.sqlite

php artisan package:discover --ansi
php artisan migrate --force --seed

if [ "$#" -eq 0 ]; then
  exec php -S "0.0.0.0:${PORT:-8000}" -t public server.php
fi

exec "$@"
