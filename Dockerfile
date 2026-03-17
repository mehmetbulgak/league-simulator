# syntax=docker/dockerfile:1

FROM node:20-bookworm-slim AS frontend-build
WORKDIR /app

COPY frontend/package.json frontend/package-lock.json ./frontend/
RUN cd frontend && npm ci

COPY frontend ./frontend
RUN mkdir -p backend/public/spa
RUN cd frontend && npm run build


FROM composer:2 AS backend-build
WORKDIR /app/backend

COPY backend/ ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-scripts --optimize-autoloader


FROM php:8.3-cli-bookworm AS runtime
WORKDIR /app/backend

RUN apt-get update \
  && apt-get install -y --no-install-recommends libsqlite3-dev \
  && docker-php-ext-install pdo_sqlite \
  && rm -rf /var/lib/apt/lists/*

COPY --from=backend-build /app/backend /app/backend
COPY --from=frontend-build /app/backend/public/spa /app/backend/public/spa

COPY docker/entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint \
  && mkdir -p storage bootstrap/cache database \
  && touch database/database.sqlite \
  && chown -R www-data:www-data storage bootstrap/cache database

USER www-data

ENV APP_ENV=production \
  APP_DEBUG=false \
  LOG_CHANNEL=stderr \
  CACHE_STORE=file \
  SESSION_DRIVER=file \
  QUEUE_CONNECTION=sync \
  DB_CONNECTION=sqlite

ENTRYPOINT ["entrypoint"]
