---
title: "09: Build Tools - TypeScript Compiler vs PHP"
description: "Understand PHP's interpreted nature and when build tools are needed. Learn about asset bundling, PHAR creation, and optimization for production."
series: "php-typescript-developers"
chapter: 9
order: 9
difficulty: "Intermediate"
prerequisites:
  - "/series/php-typescript-developers/chapters/08-code-quality"
---

# Build Tools: TypeScript Compiler vs PHP

## Overview

Coming from TypeScript, you're used to compilation steps. PHP is different—it's interpreted and runs directly without compilation. However, PHP projects still use build tools for assets, optimization, and distribution. This chapter explains when and why.

## Learning Objectives

By the end of this chapter, you'll be able to:

- ✅ Understand why PHP doesn't require compilation
- ✅ Use Vite/Webpack for frontend asset bundling
- ✅ Create PHAR archives for distribution
- ✅ Optimize PHP with OPcache
- ✅ Set up production-ready deployments
- ✅ Use Docker for consistent environments

## The Fundamental Difference

### TypeScript/JavaScript Build Process

```bash
# TypeScript needs compilation
npm run build

# What happens:
# 1. TypeScript → JavaScript (tsc)
# 2. Bundle modules (Webpack/Vite/esbuild)
# 3. Minify (Terser)
# 4. Tree-shake unused code
# 5. Generate source maps
# Output: dist/ directory with compiled files
```

**tsconfig.json:**
```json
{
  "compilerOptions": {
    "target": "ES2020",
    "module": "ESNext",
    "outDir": "./dist",
    "sourceMap": true
  }
}
```

**Result:** You deploy the `dist/` directory, not `src/`.

### PHP: No Compilation Needed

```bash
# PHP runs directly
php index.php

# No build step required!
# 1. PHP interpreter reads .php files
# 2. Executes immediately
# 3. No compilation needed
```

**You deploy your `src/` directory as-is.**

## When PHP Projects DO Need Build Tools

### 1. Frontend Assets (CSS, JS)

Even though PHP doesn't need compilation, your **frontend assets** do:

**Vite (Modern, Fast)**

```bash
npm install --save-dev vite
```

**vite.config.js:**
```javascript
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
  plugins: [
    laravel({
      input: ['resources/css/app.css', 'resources/js/app.js'],
      refresh: true,
    }),
  ],
});
```

**Build:**
```bash
npm run build

# Output:
# public/build/assets/app-[hash].js
# public/build/assets/app-[hash].css
```

**Laravel Mix (Laravel's Webpack wrapper)**

```bash
npm install --save-dev laravel-mix
```

**webpack.mix.js:**
```javascript
const mix = require('laravel-mix');

mix.js('resources/js/app.js', 'public/js')
   .sass('resources/sass/app.scss', 'public/css')
   .version(); // Cache busting
```

**Build:**
```bash
npm run production
```

### 2. TypeScript in PHP Projects

Yes, you can use TypeScript for your frontend while using PHP for backend!

**package.json:**
```json
{
  "scripts": {
    "dev": "vite",
    "build": "vite build"
  },
  "devDependencies": {
    "typescript": "^5.0.0",
    "vite": "^4.0.0"
  }
}
```

**Your project:**
```
project/
├── src/           # PHP backend (no build needed)
│   └── *.php
├── resources/     # TypeScript frontend (needs build)
│   ├── ts/
│   └── css/
├── public/        # Build output + entry point
│   ├── index.php  # PHP entry point
│   └── build/     # Compiled TS/CSS
└── package.json
```

## PHAR: PHP Archives (Like npm packages)

### JavaScript Bundle

```bash
# Webpack bundles everything into one file
npm run build
# Output: dist/bundle.js
```

### PHP PHAR

PHAR files package your PHP application into a single executable:

**Create PHAR:**
```php
<?php
// build-phar.php
$phar = new Phar('myapp.phar');
$phar->startBuffering();
$phar->buildFromDirectory(__DIR__ . '/src');
$phar->setStub($phar->createDefaultStub('index.php'));
$phar->stopBuffering();
```

**Build:**
```bash
php build-phar.php
```

**Run:**
```bash
php myapp.phar
# or
./myapp.phar  # If made executable
```

**Use Cases:**
- CLI tools (like Composer, PHPUnit)
- Distributable applications
- Self-contained packages

**Example: Composer is a PHAR:**
```bash
php composer.phar install
```

### Box: PHAR Builder

Box is a more advanced PHAR builder:

```bash
composer require --dev humbug/box
```

**box.json:**
```json
{
  "main": "bin/app.php",
  "output": "build/app.phar",
  "directories": ["src"],
  "files": ["composer.json"],
  "stub": true,
  "compression": "GZ"
}
```

**Build:**
```bash
vendor/bin/box compile
```

## Optimization for Production

### TypeScript/JavaScript Optimization

```json
{
  "scripts": {
    "build": "webpack --mode production"
  }
}
```

What happens:
- Minification
- Tree-shaking
- Code splitting
- Compression

### PHP Optimization

PHP doesn't need minification, but it has **OPcache**:

**OPcache** caches compiled PHP bytecode in memory.

**Enable in php.ini:**
```ini
; Enable OPcache
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=10000
opcache.revalidate_freq=0

; Production settings
opcache.validate_timestamps=0  ; Never check for file changes
opcache.fast_shutdown=1
```

**Check OPcache status:**
```php
<?php
var_dump(opcache_get_status());
```

**Performance impact:** 2-3x faster responses!

### Autoload Optimization

**Composer can optimize autoloading:**

```bash
# Development
composer dump-autoload

# Production (optimized)
composer dump-autoload --optimize
composer install --optimize-autoloader --no-dev
```

What it does:
- Creates a classmap (like tree-shaking)
- Removes dev dependencies
- Faster class loading

## Deployment Comparison

### Node.js/TypeScript Deployment

```bash
# 1. Build application
npm run build

# 2. Deploy dist/ directory
rsync -av dist/ user@server:/var/www/app/

# 3. Start application
pm2 start dist/server.js

# 4. Set up reverse proxy (nginx)
```

**Directory structure on server:**
```
/var/www/app/
└── dist/           # Only compiled code
    ├── server.js
    └── assets/
```

### PHP Deployment

```bash
# 1. NO BUILD STEP (just copy source)
rsync -av src/ user@server:/var/www/app/

# 2. Install dependencies
composer install --no-dev --optimize-autoloader

# 3. Configure web server (Apache/Nginx)
# PHP runs via PHP-FPM
```

**Directory structure on server:**
```
/var/www/app/
├── src/            # Source code (uncompiled)
├── vendor/         # Dependencies
└── public/         # Web root
    └── index.php   # Entry point
```

## Docker for PHP

### Node.js Dockerfile

```dockerfile
FROM node:18-alpine

WORKDIR /app

COPY package*.json ./
RUN npm ci --only=production

COPY dist/ ./dist/

CMD ["node", "dist/server.js"]
```

### PHP Dockerfile

```dockerfile
FROM php:8.2-fpm-alpine

WORKDIR /var/www/app

# Install dependencies
RUN apk add --no-cache \
    composer \
    nginx

# Copy application
COPY --chown=www-data:www-data . .

# Install Composer dependencies
RUN composer install --no-dev --optimize-autoloader

# Enable OPcache
RUN docker-php-ext-install opcache

# Configure PHP
COPY php.ini /usr/local/etc/php/conf.d/custom.ini

EXPOSE 80
CMD ["php-fpm"]
```

**Key differences:**
- **Node.js:** Copy `dist/` (built files)
- **PHP:** Copy `src/` (source files) + run Composer
- **PHP:** Use PHP-FPM + Nginx (not standalone server like Node)

## Asset Bundling in PHP Projects

### Modern Stack (Vite + PHP)

**Directory structure:**
```
project/
├── app/            # PHP application
│   └── Controllers/
├── resources/      # Frontend source
│   ├── js/
│   │   └── app.ts
│   └── css/
│       └── app.css
├── public/         # Web root
│   ├── index.php   # PHP entry
│   └── build/      # Vite output
├── vite.config.js
└── package.json
```

**package.json:**
```json
{
  "type": "module",
  "scripts": {
    "dev": "vite",
    "build": "vite build"
  },
  "devDependencies": {
    "vite": "^4.0.0",
    "typescript": "^5.0.0",
    "@vitejs/plugin-vue": "^4.0.0"
  }
}
```

**vite.config.js:**
```javascript
import { defineConfig } from 'vite';

export default defineConfig({
  root: 'resources',
  build: {
    outDir: '../public/build',
    manifest: true,
    rollupOptions: {
      input: 'resources/js/app.ts'
    }
  }
});
```

**Use in PHP:**
```php
<!-- public/index.php -->
<!DOCTYPE html>
<html>
<head>
    <?php
    // Read Vite manifest
    $manifest = json_decode(file_get_contents('build/manifest.json'), true);
    $jsFile = $manifest['resources/js/app.ts']['file'];
    $cssFile = $manifest['resources/js/app.ts']['css'][0] ?? null;
    ?>

    <?php if ($cssFile): ?>
        <link rel="stylesheet" href="/build/<?= $cssFile ?>">
    <?php endif; ?>
</head>
<body>
    <div id="app"></div>
    <script type="module" src="/build/<?= $jsFile ?>"></script>
</body>
</html>
```

## CI/CD Pipeline Comparison

### TypeScript/Node.js

```yaml
# .github/workflows/deploy.yml
name: Deploy

on:
  push:
    branches: [main]

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - uses: actions/setup-node@v3
        with:
          node-version: '18'

      # Build step required
      - run: npm ci
      - run: npm run build
      - run: npm test

      # Deploy compiled code
      - name: Deploy
        run: rsync -av dist/ ${{ secrets.DEPLOY_SERVER }}:/var/www/app/
```

### PHP

```yaml
# .github/workflows/deploy.yml
name: Deploy

on:
  push:
    branches: [main]

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'

      # NO build step for PHP code
      - run: composer install --no-dev --optimize-autoloader
      - run: composer test

      # Deploy source code directly
      - name: Deploy
        run: rsync -av --exclude 'node_modules' --exclude '.git' . ${{ secrets.DEPLOY_SERVER }}:/var/www/app/
```

### PHP + Frontend Assets

```yaml
jobs:
  deploy:
    steps:
      # ...

      # Build frontend assets
      - uses: actions/setup-node@v3
      - run: npm ci
      - run: npm run build

      # Install PHP dependencies
      - run: composer install --no-dev

      # Deploy everything
      - name: Deploy
        run: |
          rsync -av \
            --exclude 'node_modules' \
            --exclude 'resources/js' \
            --exclude 'resources/css' \
            . ${{ secrets.DEPLOY_SERVER }}:/var/www/app/
```

## Development Workflow

### TypeScript Development

```bash
# Terminal 1: Watch and rebuild
npm run dev

# Terminal 2: Run server
npm start
```

### PHP Development

```bash
# Option 1: Built-in server (dev only)
php -S localhost:8000

# Option 2: Laravel Artisan
php artisan serve

# Option 3: Docker
docker-compose up

# If using frontend assets:
# Terminal 2: Watch assets
npm run dev
```

## Performance Considerations

### TypeScript/Node.js

**Optimization happens at build time:**
- Dead code elimination
- Minification
- Compression
- Code splitting

### PHP

**Optimization happens at runtime:**
- OPcache (bytecode caching)
- Autoload optimization
- Database query caching
- Application-level caching (Redis, Memcached)

**No dead code elimination needed** because PHP only loads what it uses.

## Key Takeaways

1. **PHP is interpreted** - No compilation step required
2. **Frontend assets still need bundling** - Use Vite/Webpack
3. **PHAR files** package PHP apps (like bundled JS)
4. **OPcache** is PHP's runtime optimization
5. **Deployment is simpler** - Deploy source code directly
6. **Composer optimization** replaces some build steps
7. **Docker works differently** - No `npm run build` in Dockerfile

## Comparison Table

| Aspect | TypeScript/Node.js | PHP |
|--------|-------------------|-----|
| **Compilation** | Required | Not needed |
| **Build Output** | `dist/` directory | N/A |
| **Deploy** | Compiled files | Source files |
| **Optimization** | Build-time | Runtime (OPcache) |
| **Frontend Assets** | Webpack/Vite | Same (Webpack/Vite) |
| **Dependencies** | `node_modules/` | `vendor/` |
| **Production Prep** | `npm run build` | `composer install --optimize` |
| **Caching** | Application level | OPcache + application level |

## Quick Reference

| Task | TypeScript/Node.js | PHP |
|------|-------------------|-----|
| **Start dev server** | `npm run dev` | `php -S localhost:8000` |
| **Build for production** | `npm run build` | N/A (or `composer install --optimize`) |
| **Build assets** | `npm run build` | `npm run build` (same!) |
| **Optimize code** | Build-time minification | OPcache configuration |
| **Deploy** | Upload `dist/` | Upload `src/` + run Composer |
| **Create package** | `npm pack` | Create PHAR |

## Next Steps

Now that you understand the build process (or lack thereof), let's explore debugging tools.

**Next Chapter:** [10: Debugging: Node Inspector vs Xdebug](/series/php-typescript-developers/chapters/10-debugging)

## Resources

- [OPcache Documentation](https://www.php.net/manual/en/book.opcache.php)
- [PHAR Documentation](https://www.php.net/manual/en/book.phar.php)
- [Box PHAR Builder](https://github.com/box-project/box)
- [Vite](https://vitejs.dev/)
- [Laravel Mix](https://laravel-mix.com/)
- [Composer Optimization](https://getcomposer.org/doc/articles/autoloader-optimization.md)

---

**Questions or feedback?** Open an issue on [GitHub](https://github.com/dalehurley/codewithphp/issues)
