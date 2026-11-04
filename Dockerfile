# ---------- Tahap 1: build aset Vite (JS) ----------
FROM node:22-slim AS assets
WORKDIR /app
COPY package.json ./
# lockfile dibuat di Windows; dihapus agar paket biner Linux terpasang dengan benar
RUN npm install --no-audit --no-fund
COPY vite.config.js ./
COPY resources ./resources
RUN npm run build

# ---------- Tahap 2: paket PHP (tanpa paket dev) ----------
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --no-scripts --no-autoloader --ignore-platform-reqs
COPY . .
RUN composer dump-autoload --optimize --no-dev --no-scripts --ignore-platform-reqs

# ---------- Tahap 3: image akhir ----------
FROM php:8.3-apache
RUN a2enmod rewrite headers
ENV PORT=10000
COPY docker/ports.conf /etc/apache2/ports.conf
COPY docker/000-default.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html
COPY --from=vendor /app/ ./
COPY --from=assets /app/public/build ./public/build

RUN mkdir -p storage/app/public storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && php artisan package:discover --ansi \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 10000
CMD ["apache2-foreground"]
