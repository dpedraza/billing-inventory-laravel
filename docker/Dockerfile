FROM php:8.5-cli-alpine

LABEL description="Billing & Inventory Management System — entorno de desarrollo (PHP 8.5 CLI)"

# ─── Dependencias del sistema ─────────────────────────────────────────────────
RUN apk add --no-cache \
    bash \
    curl \
    git \
    unzip \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    icu-dev \
    libxml2-dev \
    oniguruma-dev \
    postgresql-dev \
    postgresql-client \
    nodejs \
    npm

# ─── Extensiones PHP ──────────────────────────────────────────────────────────
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_pgsql \
        pgsql \
        gd \
        zip \
        bcmath \
        intl \
        pcntl

# ─── Composer y pnpm ──────────────────────────────────────────────────────────
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN npm install -g pnpm@11.5.0

# El contenedor corre con el UID del host: HOME y caches deben ser escribibles.
ENV HOME=/tmp \
    COMPOSER_HOME=/tmp/composer \
    npm_config_store_dir=/tmp/pnpm-store

# ─── Directorio de trabajo ────────────────────────────────────────────────────
WORKDIR /var/www/html

# ─── Entrypoint ───────────────────────────────────────────────────────────────
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 8000

# Servidor embebido de PHP con el router de Laravel. No se usa `artisan serve`
# porque filtra las variables de entorno (DB_HOST de compose no llegaría a la app).
ENV PHP_CLI_SERVER_WORKERS=4

ENTRYPOINT ["/entrypoint.sh"]
CMD ["sh", "-c", "cd public && exec php -S 0.0.0.0:8000 ../vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php"]
