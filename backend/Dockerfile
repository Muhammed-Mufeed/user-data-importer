FROM php:8.3-cli-alpine

# Install system dependencies & PostgreSQL dev libraries
RUN apk add --no-cache \
    postgresql-dev \
    libzip-dev \
    zip \
    unzip \
    git

# Install PHP extensions required by PDO PostgreSQL
RUN docker-php-ext-install pdo pdo_pgsql pgsql zip

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app/backend

# Copy composer definitions and install dependencies
COPY composer.json composer.lock* ./
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Copy application source code
COPY . /app

EXPOSE 8000

# Default command starts the PHP built-in REST API server
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
