#!/bin/bash

# verify-services.sh - Verify all Docker services are running for Laravel Sail
# Usage: chmod +x verify-services.sh && ./verify-services.sh

echo "Verifying Laravel Sail Services..."
echo "=================================="
echo ""

# Check if docker is running
if ! command -v docker &> /dev/null; then
    echo "❌ Docker is not installed or not in PATH"
    exit 1
fi

# Check if sail exists
if [ ! -f "vendor/bin/sail" ]; then
    echo "❌ Laravel Sail not found. Run: composer require laravel/sail --dev"
    exit 1
fi

# Get container status
echo "Checking container status..."
./vendor/bin/sail ps

echo ""
echo "Service Status:"
echo "=================================="

# Check Laravel container
if ./vendor/bin/sail ps | grep -q "laravel.test.*Up"; then
    echo "✅ Laravel/PHP container: Running"
else
    echo "❌ Laravel/PHP container: Not running"
fi

# Check MySQL container
if ./vendor/bin/sail ps | grep -q "mysql.*Up"; then
    echo "✅ MySQL container: Running"
    # Try to ping MySQL
    if ./vendor/bin/sail artisan tinker --execute="DB::connection()->getPdo()" &> /dev/null; then
        echo "   ✅ MySQL is responding to connections"
    else
        echo "   ⚠️  MySQL may still be initializing (wait 10-15 seconds)"
    fi
else
    echo "❌ MySQL container: Not running"
fi

# Check Redis container
if ./vendor/bin/sail ps | grep -q "redis.*Up"; then
    echo "✅ Redis container: Running"
else
    echo "❌ Redis container: Not running"
fi

# Check Mailhog container
if ./vendor/bin/sail ps | grep -q "mailhog.*Up"; then
    echo "✅ Mailhog container: Running"
else
    echo "❌ Mailhog container: Not running"
fi

echo ""
echo "Access Points:"
echo "=================================="
echo "Laravel:  http://localhost"
echo "Mailhog:  http://localhost:8025"
echo "MySQL:    localhost:3306"
echo "Redis:    localhost:6379"

echo ""
echo "Next Steps:"
echo "=================================="
if ./vendor/bin/sail ps | grep -q "laravel.test.*Up"; then
    echo "1. Open http://localhost in your browser"
    echo "2. Run migrations: ./vendor/bin/sail artisan migrate"
    echo "3. Continue to Chapter 03"
else
    echo "1. Start containers: ./vendor/bin/sail up -d"
    echo "2. Wait 10-15 seconds for initialization"
    echo "3. Run this script again"
fi







