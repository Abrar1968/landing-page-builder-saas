#!/bin/bash

# Landing Page Builder SaaS - Rollback Script
# Usage: ./scripts/rollback.sh

set -e

echo "⏪ Starting rollback..."

# Rollback last migration
echo "🗃️ Rolling back last migration..."
php artisan migrate:rollback --force

# Clear caches
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

echo "✅ Rollback complete!"
echo "⚠️  Don't forget to restore the previous code version if needed."
