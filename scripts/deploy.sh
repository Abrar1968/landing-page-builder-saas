#!/bin/bash

# Landing Page Builder SaaS - Deployment Script
# Usage: ./scripts/deploy.sh

set -e

echo "🚀 Starting deployment..."

# Pull latest changes
echo "📥 Pulling latest changes..."
git pull origin main

# Install PHP dependencies
echo "📦 Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

# Install and build frontend
echo "🎨 Building frontend assets..."
npm ci --production
npm run build

# Run database migrations
echo "🗃️ Running database migrations..."
php artisan migrate --force

# Clear and cache configurations
echo "⚡ Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Restart queue workers
echo "🔄 Restarting queue workers..."
php artisan queue:restart

# Clear old cached data
echo "🧹 Clearing old cache..."
php artisan cache:clear

echo "✅ Deployment complete!"
