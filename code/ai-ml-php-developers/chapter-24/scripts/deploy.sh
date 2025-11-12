#!/bin/bash

set -e  # Exit on any error

echo "🚀 Deploying AI-ML Service..."

# Load environment variables
if [ -f .env.production ]; then
    export $(cat .env.production | grep -v '^#' | xargs)
else
    echo "Error: .env.production not found"
    exit 1
fi

# Pull latest code
echo "📦 Pulling latest code..."
git pull origin main

# Build Docker images
echo "🔨 Building Docker images..."
docker compose -f docker-compose.yml -f docker-compose.prod.yml build

# Stop old containers
echo "🛑 Stopping old containers..."
docker compose -f docker-compose.yml -f docker-compose.prod.yml down

# Start new containers
echo "▶️  Starting new containers..."
docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d

# Wait for health check
echo "🏥 Waiting for health check..."
sleep 10

# Check if service is healthy
if curl -f http://localhost/health > /dev/null 2>&1; then
    echo "✅ Deployment successful!"
else
    echo "❌ Health check failed!"
    docker compose -f docker-compose.yml -f docker-compose.prod.yml logs
    exit 1
fi

# Clean up old images
echo "🧹 Cleaning up..."
docker image prune -f

echo "🎉 Deployment complete!"


