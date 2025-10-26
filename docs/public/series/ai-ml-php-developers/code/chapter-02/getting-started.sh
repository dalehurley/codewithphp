#!/bin/bash

# Getting Started Script for Chapter 02
# This script automates the initial setup and verification

set -e  # Exit on any error

echo ""
echo "🚀 AI/ML PHP Development Environment Setup"
echo "=========================================="
echo ""

# Check if PHP is available
if ! command -v php &> /dev/null; then
    echo "❌ PHP is not installed or not in PATH"
    echo "   Please install PHP 8.4+ first"
    exit 1
fi

# Check PHP version
PHP_VERSION=$(php -r "echo PHP_VERSION;")
echo "✅ Found PHP $PHP_VERSION"

# Check if Composer is available
if ! command -v composer &> /dev/null; then
    echo "❌ Composer is not installed or not in PATH"
    echo "   Please install Composer first"
    exit 1
fi

COMPOSER_VERSION=$(composer --version --no-ansi | head -n1)
echo "✅ Found $COMPOSER_VERSION"

echo ""
echo "📦 Installing dependencies..."
echo ""

# Install dependencies
composer install

if [ $? -eq 0 ]; then
    echo ""
    echo "✅ Dependencies installed successfully"
else
    echo ""
    echo "❌ Dependency installation failed"
    exit 1
fi

echo ""
echo "🔍 Running environment verification..."
echo ""

# Run verification script
php verify-installation.php

if [ $? -eq 0 ]; then
    echo ""
    echo "🎉 Setup complete! Your environment is ready."
    echo ""
    echo "Next steps:"
    echo "  1. Run: php test-phpml.php"
    echo "  2. Run: php test-rubixml.php"
    echo "  3. Proceed to Chapter 03"
    echo ""
else
    echo ""
    echo "⚠️  Some checks failed. Please review the output above."
    echo "   Refer to Chapter 02 troubleshooting section for help."
    echo ""
    exit 1
fi

