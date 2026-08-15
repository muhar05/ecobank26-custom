FROM php:8.4-cli-alpine

# Composer (official image layer)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Build-time dependencies for compiling PHP extensions
RUN apk add --no-cache \
        curl \
        git \
        mysql-client \
        unzip \
        oniguruma-dev \
        libzip-dev \
        icu-dev \
        libxml2-dev \
        freetype-dev \
        libjpeg-turbo-dev \
        libpng-dev \
        libwebp-dev \
        zlib-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        exif \
        fileinfo \
        gd \
        intl \
        mbstring \
        opcache \
        pcntl \
        pdo_mysql \
        zip \
    && docker-php-source delete \
    && apk del --no-cache \
        oniguruma-dev \
        libzip-dev \
        icu-dev \
        libxml2-dev \
        freetype-dev \
        libjpeg-turbo-dev \
        libpng-dev \
        libwebp-dev \
        zlib-dev \
    && rm -rf /tmp/pear

# Runtime-only dependencies required by already-compiled extensions
RUN apk add --no-cache \
        libzip \
        icu-libs \
        libintl \
        libxml2 \
        freetype \
        libjpeg-turbo \
        libpng \
        libwebp \
        zlib

# PHP configuration
COPY docker/php/php.ini /usr/local/etc/php/conf.d/zz-app.ini

WORKDIR /var/www/html

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
