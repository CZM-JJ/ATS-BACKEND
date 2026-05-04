FROM php:8.2-cli-alpine

WORKDIR /var/www

# Install build dependencies
RUN apk add --no-cache --virtual .build-deps \
    build-base \
    autoconf \
    oniguruma-dev && \
    apk add --no-cache \
    git \
    curl \
    postgresql-dev \
    oniguruma \
    zip \
    unzip \
    libpq && \
    docker-php-ext-install \
    pdo \
    pdo_mysql \
    pdo_pgsql \
    mbstring \
    exif \
    pcntl \
    bcmath && \
    apk del .build-deps

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1

# Copy dependency files first (better caching)
COPY composer.json composer.lock ./

# Install PHP dependencies
RUN composer install \
    --no-dev \
    --no-interaction \
    --optimize-autoloader \
    --no-scripts

# Copy application code
COPY . .

# Run composer scripts post-install
RUN composer run-script post-install-cmd || true

# Copy and setup entrypoint
COPY --chmod=755 entrypoint.sh .

# Create storage directories with proper permissions
RUN mkdir -p \
    storage/framework/cache \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

EXPOSE 8000

ENTRYPOINT ["./entrypoint.sh"]
CMD ["sh", "-c", "php -d variables_order=EGPCS artisan serve --host=0.0.0.0 --port=${PORT:-8000}"]
