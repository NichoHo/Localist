# --- PHP dependencies ---
FROM composer:2 AS deps
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --ignore-platform-req=ext-bcmath
COPY . .
RUN composer dump-autoload --optimize --no-dev

# --- Frontend assets (pagination views come from the composer stage; app.css @source scans them) ---
FROM node:22-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json vite.config.js ./
RUN npm ci
COPY resources ./resources
COPY --from=deps /app/vendor/laravel/framework/src/Illuminate/Pagination ./vendor/laravel/framework/src/Illuminate/Pagination
RUN npm run build

# --- Runtime ---
FROM php:8.3-apache
RUN apt-get update && apt-get install -y --no-install-recommends libpng-dev libjpeg-dev libfreetype6-dev libwebp-dev \
    && docker-php-ext-configure gd --with-jpeg --with-freetype --with-webp \
    && docker-php-ext-install pdo_mysql gd exif opcache bcmath \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf /etc/apache2/apache2.conf

WORKDIR /var/www/html
COPY --from=deps /app .
COPY --from=assets /app/public/build ./public/build
RUN chown -R www-data:www-data storage bootstrap/cache

# Config/route/view caches are built at container start (env vars aren't known at build time).
COPY <<'EOF' /usr/local/bin/start.sh
#!/bin/sh
set -e
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force
php artisan storage:link || true
exec apache2-foreground
EOF
RUN chmod +x /usr/local/bin/start.sh

EXPOSE 80
CMD ["start.sh"]
