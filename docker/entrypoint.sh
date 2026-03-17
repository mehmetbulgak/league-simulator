#!/usr/bin/env sh
set -eu

cd /app/backend

if [ -z "${APP_KEY:-}" ]; then
  echo "Error: APP_KEY is required. Set it in Render -> Environment." >&2
  exit 1
fi

mkdir -p storage bootstrap/cache database
touch database/database.sqlite

php artisan package:discover --ansi
php artisan migrate --force --seed

if [ "$#" -eq 0 ]; then
  exec php -S "0.0.0.0:${PORT:-8000}" -t public public/index.php
fi

exec "$@"
