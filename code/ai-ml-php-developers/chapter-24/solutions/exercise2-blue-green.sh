#!/bin/bash

# Exercise 2 Solution: Blue-Green Deployment
# Implements zero-downtime deployment strategy

set -e

BLUE_PREFIX="blue"
GREEN_PREFIX="green"
CURRENT_COLOR=""

echo "🔵🟢 Blue-Green Deployment Script"
echo "================================="

# Determine current deployment color
get_current_color() {
    if docker compose -p $BLUE_PREFIX ps -q app > /dev/null 2>&1; then
        echo "blue"
    elif docker compose -p $GREEN_PREFIX ps -q app > /dev/null 2>&1; then
        echo "green"
    else
        echo "none"
    fi
}

CURRENT_COLOR=$(get_current_color)

if [ "$CURRENT_COLOR" == "none" ]; then
    DEPLOY_COLOR="blue"
    echo "No current deployment found. Starting with $DEPLOY_COLOR"
elif [ "$CURRENT_COLOR" == "blue" ]; then
    DEPLOY_COLOR="green"
    OLD_COLOR="blue"
    echo "Current: $OLD_COLOR → Deploying: $DEPLOY_COLOR"
else
    DEPLOY_COLOR="blue"
    OLD_COLOR="green"
    echo "Current: $OLD_COLOR → Deploying: $DEPLOY_COLOR"
fi

# Pull latest code
echo ""
echo "📦 Pulling latest code..."
git pull origin main

# Build new deployment
echo "🔨 Building $DEPLOY_COLOR deployment..."
docker compose -p $DEPLOY_COLOR -f docker-compose.yml -f docker-compose.prod.yml build

# Start new deployment
echo "🚀 Starting $DEPLOY_COLOR containers..."
docker compose -p $DEPLOY_COLOR -f docker-compose.yml -f docker-compose.prod.yml up -d

# Wait for health check
echo "🏥 Waiting for health check..."
MAX_ATTEMPTS=30
ATTEMPT=0

while [ $ATTEMPT -lt $MAX_ATTEMPTS ]; do
    if docker compose -p $DEPLOY_COLOR exec -T app php 06-health-check.php > /dev/null 2>&1; then
        echo "✅ $DEPLOY_COLOR deployment is healthy!"
        break
    fi

    ATTEMPT=$((ATTEMPT + 1))
    echo "Attempt $ATTEMPT/$MAX_ATTEMPTS..."
    sleep 2
done

if [ $ATTEMPT -eq $MAX_ATTEMPTS ]; then
    echo "❌ Health check failed after $MAX_ATTEMPTS attempts"
    echo "Rolling back..."
    docker compose -p $DEPLOY_COLOR -f docker-compose.yml -f docker-compose.prod.yml down
    exit 1
fi

# Switch traffic (update nginx or load balancer config)
echo "🔄 Switching traffic to $DEPLOY_COLOR..."
# In production, this would update load balancer to point to new deployment
# For demo, we just note that traffic would be switched

sleep 2

# Stop old deployment
if [ "$CURRENT_COLOR" != "none" ]; then
    echo "🛑 Stopping $OLD_COLOR deployment..."
    docker compose -p $OLD_COLOR -f docker-compose.yml -f docker-compose.prod.yml down
    echo "✅ $OLD_COLOR deployment stopped"
fi

echo ""
echo "🎉 Deployment complete!"
echo "Active deployment: $DEPLOY_COLOR"
echo ""
echo "To rollback, run:"
echo "  docker compose -p $OLD_COLOR -f docker-compose.yml -f docker-compose.prod.yml up -d"

