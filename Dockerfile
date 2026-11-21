# syntax=docker/dockerfile:1

FROM php:8.3-cli-bookworm AS vendor
WORKDIR /app

RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    libzip-dev \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install -j"$(nproc)" zip
# pecl downloads sometimes arrive truncated ("unable to unpack"); retry them.
# Composer only needs the extension present here, not its TLS support.
RUN for attempt in 1 2 3; do \
        pecl install mongodb && break; \
        [ "$attempt" = 3 ] && exit 1; \
        echo "pecl install mongodb failed (attempt $attempt), retrying" >&2; rm -rf /tmp/pear; sleep 5; \
    done \
    && docker-php-ext-enable mongodb

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-scripts \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --ignore-platform-req=ext-gd \
    --ignore-platform-req=ext-exif

COPY . .
RUN composer dump-autoload --optimize --classmap-authoritative --no-scripts

FROM php:8.3-fpm-bookworm

# libssl-dev and pkg-config: the mongodb extension only gets TLS support when
# it is compiled against OpenSSL. Without them it builds anyway, and every TLS
# connection (MongoDB Atlas, mongodb+srv://) fails with "SSL not enabled in
# this build".
RUN apt-get update && apt-get install -y --no-install-recommends \
    curl \
    git \
    libicu-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libwebp-dev \
    libzip-dev \
    libssl-dev \
    pkg-config \
    nginx \
    supervisor \
    unzip \
    && mkdir -p /var/log/supervisor \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
        --with-webp \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        exif \
        gd \
        intl \
        opcache \
        pcntl \
        pdo_mysql \
        zip

# Each extension is retried on its own, since pecl downloads sometimes
# arrive truncated. The build fails if the mongodb driver has no TLS support,
# so an image that cannot reach a TLS MongoDB server is never published.
RUN for extension in redis mongodb; do \
        for attempt in 1 2 3; do \
            pecl install "$extension" && break; \
            [ "$attempt" = 3 ] && exit 1; \
            echo "pecl install $extension failed (attempt $attempt), retrying" >&2; rm -rf /tmp/pear; sleep 5; \
        done; \
    done \
    && docker-php-ext-enable redis mongodb \
    && php --ri mongodb | grep -i "ssl" \
    && php --ri mongodb | grep -Eqi "libmongoc SSL => enabled" \
    || { echo "ERROR: the mongodb extension was built without TLS (SSL) support" >&2; exit 1; }

COPY docker/php/conf.d/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY docker/php-fpm/zz-listen.conf /usr/local/etc/php-fpm.d/zz-listen.conf
COPY docker/nginx/default.conf /etc/nginx/sites-available/default
RUN ln -sf /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default

COPY docker/supervisord.conf /etc/supervisor/supervisord.conf
COPY docker/php-fpm.program.conf /etc/supervisor/conf.d/php-fpm.program.conf
COPY docker/nginx.program.conf /etc/supervisor/conf.d/nginx.program.conf
COPY docker/laravel-scheduler.conf /usr/local/etc/supervisor/laravel-scheduler.conf
COPY docker/laravel-queue-worker.conf /usr/local/etc/supervisor/laravel-queue-worker.conf

WORKDIR /var/www/html

COPY --from=vendor /app /var/www/html

RUN mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache/data storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --start-period=40s --retries=3 \
    CMD curl -fsS http://127.0.0.1/up || exit 1

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
