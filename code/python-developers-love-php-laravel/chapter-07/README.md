# Chapter 07: Testing, Deployment, DevOps - Code Examples

This directory contains code examples comparing Python testing, deployment, and DevOps practices to Laravel equivalents.

## Directory Structure

- `testing/` - Testing examples (pytest vs PHPUnit, fixtures, mocks, factories)
- `cicd/` - CI/CD workflow examples (GitHub Actions for Python and Laravel)
- `docker/` - Docker configuration examples (Dockerfile and docker-compose)
- `deployment/` - Deployment configuration examples (Procfile, vapor.yml)
- `queues/` - Background job examples (Celery vs Laravel Queues)

## Running Examples

### Testing Examples

**Python (pytest):**
```bash
pip install pytest pytest-cov
pytest test_user.py -v
```

**Laravel (PHPUnit):**
```bash
php artisan test
# Or
vendor/bin/phpunit
```

### CI/CD Examples

Copy workflow files to `.github/workflows/` in your repository and push to GitHub.

### Docker Examples

**Python:**
```bash
docker build -t python-app -f Dockerfile.python .
docker-compose -f docker-compose.python.yml up
```

**Laravel:**
```bash
docker build -t laravel-app -f Dockerfile.laravel .
docker-compose -f docker-compose.laravel.yml up
```

### Queue Examples

**Celery:**
```bash
celery -A tasks worker --loglevel=info
celery -A celery_beat beat --loglevel=info
```

**Laravel:**
```bash
php artisan queue:work
php artisan schedule:run
```

## Notes

- All examples are simplified for educational purposes
- Production deployments should include security hardening, monitoring, and error handling
- Environment variables should be set appropriately for each example
- Some examples require additional setup (Redis, databases, etc.)

