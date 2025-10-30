#!/bin/bash

# SmartDash Installation Script
# Automates setup for Chapter 25 capstone project

echo "🚀 SmartDash Installation Script"
echo "=================================="
echo ""

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Check PHP version
echo "Checking PHP version..."
PHP_VERSION=$(php -r "echo PHP_VERSION;")
PHP_MAJOR=$(php -r "echo PHP_MAJOR_VERSION;")
PHP_MINOR=$(php -r "echo PHP_MINOR_VERSION;")

if [ "$PHP_MAJOR" -lt 8 ] || ([ "$PHP_MAJOR" -eq 8 ] && [ "$PHP_MINOR" -lt 4 ]); then
    echo -e "${RED}✗ PHP 8.4+ required. Found: $PHP_VERSION${NC}"
    exit 1
fi
echo -e "${GREEN}✓ PHP $PHP_VERSION${NC}"

# Check Composer
echo "Checking Composer..."
if ! command -v composer &> /dev/null; then
    echo -e "${RED}✗ Composer not found. Install from https://getcomposer.org${NC}"
    exit 1
fi
echo -e "${GREEN}✓ Composer installed${NC}"

# Check Node.js
echo "Checking Node.js..."
if ! command -v node &> /dev/null; then
    echo -e "${RED}✗ Node.js not found. Install from https://nodejs.org${NC}"
    exit 1
fi
NODE_VERSION=$(node -v)
echo -e "${GREEN}✓ Node.js $NODE_VERSION${NC}"

echo ""
echo "Installing dependencies..."
echo ""

# Install Composer dependencies
echo "Installing PHP dependencies..."
composer install --no-interaction --prefer-dist --optimize-autoloader

if [ $? -ne 0 ]; then
    echo -e "${RED}✗ Composer install failed${NC}"
    exit 1
fi
echo -e "${GREEN}✓ PHP dependencies installed${NC}"

# Install Node dependencies
echo "Installing Node.js dependencies..."
npm install

if [ $? -ne 0 ]; then
    echo -e "${RED}✗ npm install failed${NC}"
    exit 1
fi
echo -e "${GREEN}✓ Node.js dependencies installed${NC}"

echo ""
echo "Configuring environment..."
echo ""

# Copy .env file if it doesn't exist
if [ ! -f .env ]; then
    cp env.example .env
    echo -e "${GREEN}✓ Created .env file${NC}"
    
    # Generate app key
    php artisan key:generate --no-interaction
    echo -e "${GREEN}✓ Generated application key${NC}"
    
    echo ""
    echo -e "${YELLOW}⚠️  Important: Edit .env and add your API keys:${NC}"
    echo "   - OPENAI_API_KEY=sk-..."
    echo "   - GOOGLE_CLOUD_VISION_KEY=..."
    echo ""
else
    echo -e "${YELLOW}⚠️  .env file already exists (skipping)${NC}"
fi

# Database setup
echo "Setting up database..."
echo ""
echo "Which database are you using?"
echo "1) MySQL/MariaDB"
echo "2) PostgreSQL"
echo "3) SQLite (for development/testing)"
read -p "Enter choice (1-3) [default: 3]: " db_choice
db_choice=${db_choice:-3}

case $db_choice in
    1)
        echo ""
        read -p "Database name [smartdash]: " DB_NAME
        DB_NAME=${DB_NAME:-smartdash}
        read -p "Database host [127.0.0.1]: " DB_HOST
        DB_HOST=${DB_HOST:-127.0.0.1}
        read -p "Database user [root]: " DB_USER
        DB_USER=${DB_USER:-root}
        read -sp "Database password: " DB_PASS
        echo ""
        
        # Update .env
        sed -i.bak "s/DB_CONNECTION=.*/DB_CONNECTION=mysql/" .env
        sed -i.bak "s/DB_DATABASE=.*/DB_DATABASE=$DB_NAME/" .env
        sed -i.bak "s/DB_USERNAME=.*/DB_USERNAME=$DB_USER/" .env
        sed -i.bak "s/DB_PASSWORD=.*/DB_PASSWORD=$DB_PASS/" .env
        rm .env.bak
        ;;
    2)
        echo ""
        read -p "Database name [smartdash]: " DB_NAME
        DB_NAME=${DB_NAME:-smartdash}
        read -p "Database host [127.0.0.1]: " DB_HOST
        DB_HOST=${DB_HOST:-127.0.0.1}
        read -p "Database user [postgres]: " DB_USER
        DB_USER=${DB_USER:-postgres}
        read -sp "Database password: " DB_PASS
        echo ""
        
        # Update .env
        sed -i.bak "s/DB_CONNECTION=.*/DB_CONNECTION=pgsql/" .env
        sed -i.bak "s/DB_DATABASE=.*/DB_DATABASE=$DB_NAME/" .env
        sed -i.bak "s/DB_USERNAME=.*/DB_USERNAME=$DB_USER/" .env
        sed -i.bak "s/DB_PASSWORD=.*/DB_PASSWORD=$DB_PASS/" .env
        rm .env.bak
        ;;
    3)
        # Use SQLite
        touch database/database.sqlite
        sed -i.bak "s/DB_CONNECTION=.*/DB_CONNECTION=sqlite/" .env
        rm .env.bak
        echo -e "${GREEN}✓ Created SQLite database${NC}"
        ;;
esac

# Run migrations
echo ""
echo "Running database migrations..."
php artisan migrate --force

if [ $? -ne 0 ]; then
    echo -e "${RED}✗ Migrations failed${NC}"
    echo "Please check your database configuration in .env"
    exit 1
fi
echo -e "${GREEN}✓ Migrations completed${NC}"

# Seed database
echo ""
read -p "Seed database with sample data? (y/n) [y]: " seed_choice
seed_choice=${seed_choice:-y}

if [ "$seed_choice" = "y" ] || [ "$seed_choice" = "Y" ]; then
    php artisan db:seed
    echo -e "${GREEN}✓ Database seeded${NC}"
fi

# Create storage link
echo ""
echo "Creating storage symlink..."
php artisan storage:link
echo -e "${GREEN}✓ Storage link created${NC}"

# Build assets
echo ""
read -p "Build frontend assets? (y/n) [y]: " build_choice
build_choice=${build_choice:-y}

if [ "$build_choice" = "y" ] || [ "$build_choice" = "Y" ]; then
    echo "Building assets..."
    npm run build
    echo -e "${GREEN}✓ Assets built${NC}"
fi

echo ""
echo "=================================="
echo -e "${GREEN}✅ Installation complete!${NC}"
echo "=================================="
echo ""
echo "Next steps:"
echo "1. Edit .env and add your API keys (if not already done)"
echo "2. Start the development server:"
echo "   ${YELLOW}php artisan serve${NC}"
echo "3. Start the queue worker (in another terminal):"
echo "   ${YELLOW}php artisan queue:work${NC}"
echo "4. Visit: ${YELLOW}http://localhost:8000/dashboard${NC}"
echo ""
echo "Run tests:"
echo "   ${YELLOW}php 06-run-all-tests.php${NC}"
echo ""
echo "Happy coding! 🚀"

