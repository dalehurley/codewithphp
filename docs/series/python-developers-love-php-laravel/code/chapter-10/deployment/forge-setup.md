# Laravel Forge Deployment Guide

This guide covers deploying the Task Manager application to production using Laravel Forge.

## Prerequisites

- Laravel Forge account ([forge.laravel.com](https://forge.laravel.com))
- Git repository (GitHub, GitLab, or Bitbucket)
- Domain name (optional - Forge provides a test domain)

## Step 1: Provision Laravel VPS

1. Log in to Laravel Forge
2. Click "Create Server"
3. Select "Laravel VPS" (new integrated VPS service)
4. Choose server size (start with smallest for testing)
5. Select region closest to your users
6. Click "Create Server"

Forge automatically:
- Provisions the VPS
- Installs PHP 8.4, Nginx, MySQL, Redis
- Configures firewall and security
- Sets up SSH keys

## Step 2: Create Site

1. In your server dashboard, click "Sites" → "New Site"
2. Enter your domain (or use Forge's provided domain)
3. Connect your Git repository:
   - **GitHub**: Authorize Forge to access your repos
   - **GitLab/Bitbucket**: Add repository URL and credentials
4. Select branch (usually `main` or `master`)

## Step 3: Configure Environment

In site settings, go to "Environment" and add:

```env
APP_NAME="Task Manager"
APP_ENV=production
APP_KEY=base64:... (generate with: php artisan key:generate)
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=forge
DB_USERNAME=forge
DB_PASSWORD=... (provided by Forge)

# Sanctum configuration
SANCTUM_STATEFUL_DOMAINS=your-domain.com
SESSION_DOMAIN=.your-domain.com
```

## Step 4: Deployment Script

Update deployment script in "Deployment Script" section:

```bash
cd /home/forge/your-domain.com
git pull origin main

# Install/update dependencies
$FORGE_COMPOSER install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# Run migrations
php artisan migrate --force

# Clear and cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart PHP-FPM
sudo -S service php8.4-fpm reload
```

## Step 5: SSL Certificate

1. Go to "SSL" tab in site settings
2. Click "Let's Encrypt"
3. Enter your domain
4. Click "Obtain Certificate"

Forge automatically:
- Configures SSL certificate
- Sets up auto-renewal
- Configures Nginx for HTTPS

## Step 6: Deploy

1. Click "Deploy Now" button
2. Watch deployment logs in real-time
3. Verify deployment succeeded

## Zero-Downtime Deployments

Zero-downtime deployments are enabled by default for new sites. This means:

- Forge creates a new release directory
- Runs deployment script
- Symlinks to new release when ready
- Old release stays available for rollback

## Queue Workers (Optional)

If you add queues later:

1. Go to "Daemons" tab
2. Click "New Daemon"
3. Command: `php /home/forge/your-domain.com/current/artisan queue:work --sleep=3 --tries=3`
4. Click "Start"

## Scheduled Tasks (Optional)

1. Go to "Scheduled Jobs" tab
2. Click "New Scheduled Job"
3. Command: `php /home/forge/your-domain.com/current/artisan schedule:run`
4. Frequency: `* * * * *` (every minute)
5. Click "Create"

## Troubleshooting

- **Deployment failed**: Check deployment logs in Forge. Common issues: missing environment variables, database connection errors, or composer install failures.
- **SSL certificate failed**: Ensure your domain DNS points to the Forge server IP. Wait for DNS propagation (can take up to 48 hours).
- **Database connection error**: Verify database credentials in environment variables match Forge's database settings.
- **500 error after deployment**: Check Laravel logs: `tail -f storage/logs/laravel.log` on server, or view in Forge's "Logs" tab.

## Resources

- [Laravel Forge Documentation](https://forge.laravel.com/docs)
- [Laravel VPS Information](https://laravel.com/blog/everything-you-need-to-know-about-the-new-forge-laravel-vps)

