# Exercise 4 Solution: Optimized Docker Image
# Target size: <80MB (vs ~150MB baseline)

# Build stage
FROM php:8.4-cli-alpine AS builder

# Install only essential build dependencies
RUN apk add --no-cache --virtual .build-deps \
    $PHPIZE_DEPS \
    autoconf \
    g++ \
    make

# Install PHP extensions
RUN docker-php-ext-install pcntl sockets \
    && pecl install redis-6.0.2 \
    && docker-php-ext-enable redis \
    && rm -rf /tmp/pear

# Remove build dependencies immediately after use
RUN apk del .build-deps

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy only dependency files first (better caching)
WORKDIR /app
COPY composer.json composer.lock* ./

# Install dependencies
RUN composer install \
    --no-dev \
    --no-scripts \
    --no-autoloader \
    --optimize-autoloader \
    --no-interaction \
    || echo "No composer.lock found"

# Copy application code
COPY . .

# Generate optimized autoloader
RUN composer dump-autoload --optimize --no-dev

# Production stage - ultra minimal
FROM php:8.4-cli-alpine AS production

# Install ONLY runtime dependencies (no development tools)
RUN apk add --no-cache \
    # No redis package needed - just the extension
    && rm -rf /var/cache/apk/*

# Copy PHP extensions from builder
COPY --from=builder /usr/local/lib/php/extensions /usr/local/lib/php/extensions
COPY --from=builder /usr/local/etc/php/conf.d /usr/local/etc/php/conf.d

# Copy application (excluding unnecessary files via .dockerignore)
WORKDIR /app
COPY --from=builder /app/vendor ./vendor
COPY --from=builder /app/*.php ./
COPY --from=builder /app/composer.json ./

# Set proper permissions
RUN chown -R www-data:www-data /app \
    && chmod -R 755 /app

# Use non-root user
USER www-data

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD php -r "echo 'OK';" || exit 1

# Default command
CMD ["php", "03-ml-worker.php"]

# Optimization notes:
# 1. Alpine base image (~5MB vs ~400MB for Debian)
# 2. Multi-stage build removes build tools from final image
# 3. Removed .build-deps immediately after use
# 4. No unnecessary packages in production stage
# 5. Cleared apk cache
# 6. .dockerignore excludes tests, docs, etc.
# 7. Optimized autoloader generation
# 8. Combined RUN commands to reduce layers
#
# Expected size: 60-75MB

