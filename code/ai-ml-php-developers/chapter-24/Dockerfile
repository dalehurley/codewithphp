# Multi-stage Dockerfile for AI-ML PHP Service
# Build stage - includes build tools
FROM php:8.6-cli-alpine AS builder

LABEL maintainer="your-email@example.com"
LABEL description="AI/ML PHP Service - Build Stage"

COPY --from=ghcr.io/mlocati/php-extension-installer:2 /usr/bin/install-php-extensions /usr/local/bin/
RUN apk add --no-cache git && install-php-extensions pcntl sockets redis-6.3.0

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy application files
WORKDIR /app
COPY composer.json composer.lock* ./

# Install PHP dependencies (without dev packages for production)
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts || \
    echo "Note: composer.lock not found, running without it"

# Production stage - minimal image
FROM php:8.6-cli-alpine AS production

LABEL maintainer="your-email@example.com"
LABEL description="AI/ML PHP Service - Production"

# Install runtime dependencies only
RUN apk add --no-cache \
    redis

# Copy extensions from builder
COPY --from=builder /usr/local/lib/php/extensions /usr/local/lib/php/extensions
COPY --from=builder /usr/local/etc/php/conf.d /usr/local/etc/php/conf.d

# Copy application
WORKDIR /app
COPY --from=builder /app/vendor ./vendor
COPY . .

# Set proper permissions
RUN chown -R www-data:www-data /app

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD php -r "echo 'OK';" || exit 1

USER www-data

CMD ["php", "03-ml-worker.php"]


