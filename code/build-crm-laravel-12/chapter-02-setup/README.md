# Chapter 02: Setting Up Laravel 12 Project & Dev Environment

This directory contains reference materials and example configurations for Chapter 02 of the Build CRM with Laravel 12 series.

## Contents

- **`.env.example`** - Reference environment configuration showing the correct Docker service names
- **`verify-services.sh`** - Bash script to verify all Docker services are running
- **`docker-compose-reference.yml`** - Reference Docker Compose configuration

## Quick Reference

### Environment Configuration

The `.env.example` file shows the correct configuration for Docker services. Key settings:

```ini
DB_HOST=mysql              # Docker service name
DB_PORT=3306
DB_DATABASE=crm_app
DB_USERNAME=sail
DB_PASSWORD=password

REDIS_HOST=redis           # Docker service name
REDIS_PORT=6379

MAIL_HOST=mailhog          # Docker service name
MAIL_PORT=1025
```

### Verifying Services

Run the included verification script:

```bash
chmod +x verify-services.sh
./verify-services.sh
```

This checks that:
- Laravel/PHP container is running
- MySQL container is running and responsive
- Redis container is running
- Mailhog container is running

### Docker Networking

When running inside Docker containers:
- Use service names (mysql, redis, mailhog) instead of localhost or 127.0.0.1
- Docker's internal DNS resolves service names to container IPs
- Services communicate over Docker's bridge network

## Troubleshooting

### Containers not starting?

```bash
# Check container status
./vendor/bin/sail ps

# View container logs
./vendor/bin/sail logs laravel.test
./vendor/bin/sail logs mysql
./vendor/bin/sail logs redis
./vendor/bin/sail logs mailhog

# Restart all containers
./vendor/bin/sail restart
```

### Database connection errors?

1. Verify `.env` uses `DB_HOST=mysql` (not 127.0.0.1)
2. Wait 10-15 seconds for MySQL to fully initialize
3. Run `./vendor/bin/sail artisan migrate` to test connection

### Port conflicts?

If port 80 is already in use:

```bash
# Find what's using port 80
lsof -i :80

# Or modify docker-compose.yml to use a different port (e.g., 8000:80)
# Then access http://localhost:8000
```

## Next Steps

After completing this chapter:

1. Verify all services are running with `./vendor/bin/sail ps`
2. Access Laravel at `http://localhost`
3. Access Mailhog at `http://localhost:8025`
4. Run migrations: `./vendor/bin/sail artisan migrate`
5. Move to Chapter 03 to install Inertia and React

## Resources

- [Laravel Sail Documentation](https://laravel.com/docs/12.x/sail)
- [Docker Networking Guide](https://docs.docker.com/engine/network/)
- [Chapter 02: Setting Up Laravel 12 Project & Dev Environment](/series/build-crm-laravel-12/chapters/02-setting-up-laravel-12-project-dev-environment)

