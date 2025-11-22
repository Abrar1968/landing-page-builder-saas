# Deployment Guide

## Server Requirements

### Minimum Specifications
- **CPU**: 2 vCPUs
- **RAM**: 4 GB
- **Storage**: 50 GB SSD
- **OS**: Ubuntu 22.04 LTS

### Recommended Specifications
- **CPU**: 4 vCPUs
- **RAM**: 8 GB
- **Storage**: 100 GB SSD
- **OS**: Ubuntu 22.04 LTS

### Software Requirements
- PHP 8.2+
- MySQL 8.0+
- Redis 6+
- Nginx 1.18+
- Node.js 18+
- Composer 2+
- Supervisor
- Certbot

---

## Ubuntu Server Setup

### Initial Server Configuration

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install essential packages
sudo apt install -y curl wget git unzip software-properties-common apt-transport-https ca-certificates

# Add PHP repository
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

# Install PHP and extensions
sudo apt install -y php8.2-fpm php8.2-cli php8.2-common php8.2-mysql php8.2-zip php8.2-gd php8.2-mbstring php8.2-curl php8.2-xml php8.2-bcmath php8.2-intl php8.2-readline php8.2-redis php8.2-imagick

# Install MySQL
sudo apt install -y mysql-server

# Install Redis
sudo apt install -y redis-server

# Install Nginx
sudo apt install -y nginx

# Install Node.js 18
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install -y nodejs

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Supervisor
sudo apt install -y supervisor
```

### Create Application User

```bash
# Create deploy user
sudo adduser deploy
sudo usermod -aG www-data deploy

# Set up SSH for deploy user
sudo mkdir -p /home/deploy/.ssh
sudo cp ~/.ssh/authorized_keys /home/deploy/.ssh/
sudo chown -R deploy:deploy /home/deploy/.ssh
sudo chmod 700 /home/deploy/.ssh
sudo chmod 600 /home/deploy/.ssh/authorized_keys
```

### Create Application Directory

```bash
# Create application directory
sudo mkdir -p /var/www/landing-page-builder
sudo chown -R deploy:www-data /var/www/landing-page-builder
sudo chmod -R 775 /var/www/landing-page-builder
```

---

## Nginx Configuration

### Main Application with Wildcard Subdomain Support

Create `/etc/nginx/sites-available/landing-page-builder`:

```nginx
# Main application
server {
    listen 80;
    listen [::]:80;
    server_name yourdomain.com www.yourdomain.com;
    root /var/www/landing-page-builder/public;

    index index.php index.html;

    charset utf-8;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_proxied expired no-cache no-store private auth;
    gzip_types text/plain text/css text/xml text/javascript application/x-javascript application/xml application/javascript application/json;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_buffer_size 128k;
        fastcgi_buffers 4 256k;
        fastcgi_busy_buffers_size 256k;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Static file caching
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|woff|woff2|ttf|svg|eot)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }
}

# Wildcard subdomain for landing pages
server {
    listen 80;
    listen [::]:80;
    server_name *.yourdomain.com;
    root /var/www/landing-page-builder/public;

    index index.php index.html;

    charset utf-8;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}

# Custom domain support
server {
    listen 80;
    listen [::]:80;
    server_name ~^(?<custom_domain>.+)$;
    root /var/www/landing-page-builder/public;

    index index.php index.html;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### Enable Site

```bash
# Enable site
sudo ln -s /etc/nginx/sites-available/landing-page-builder /etc/nginx/sites-enabled/

# Remove default site
sudo rm /etc/nginx/sites-enabled/default

# Test configuration
sudo nginx -t

# Reload Nginx
sudo systemctl reload nginx
```

---

## SSL Configuration with Let's Encrypt

### Install Certbot

```bash
sudo apt install -y certbot python3-certbot-nginx
```

### Obtain SSL Certificates

```bash
# Main domain certificate
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com

# Wildcard certificate (requires DNS challenge)
sudo certbot certonly --manual --preferred-challenges=dns \
  -d yourdomain.com \
  -d "*.yourdomain.com" \
  --agree-tos \
  --email admin@yourdomain.com
```

### Nginx SSL Configuration

Update `/etc/nginx/sites-available/landing-page-builder`:

```nginx
# Redirect HTTP to HTTPS
server {
    listen 80;
    listen [::]:80;
    server_name yourdomain.com www.yourdomain.com *.yourdomain.com;
    return 301 https://$host$request_uri;
}

# Main application (HTTPS)
server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name yourdomain.com www.yourdomain.com;
    root /var/www/landing-page-builder/public;

    # SSL certificates
    ssl_certificate /etc/letsencrypt/live/yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yourdomain.com/privkey.pem;
    ssl_trusted_certificate /etc/letsencrypt/live/yourdomain.com/chain.pem;

    # SSL configuration
    ssl_session_timeout 1d;
    ssl_session_cache shared:SSL:50m;
    ssl_session_tickets off;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384:ECDHE-ECDSA-CHACHA20-POLY1305:ECDHE-RSA-CHACHA20-POLY1305:DHE-RSA-AES128-GCM-SHA256:DHE-RSA-AES256-GCM-SHA384;
    ssl_prefer_server_ciphers off;

    # HSTS
    add_header Strict-Transport-Security "max-age=63072000" always;

    # OCSP Stapling
    ssl_stapling on;
    ssl_stapling_verify on;
    resolver 8.8.8.8 8.8.4.4 valid=300s;
    resolver_timeout 5s;

    index index.php index.html;
    charset utf-8;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_proxied expired no-cache no-store private auth;
    gzip_types text/plain text/css text/xml text/javascript application/x-javascript application/xml application/javascript application/json;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_buffer_size 128k;
        fastcgi_buffers 4 256k;
        fastcgi_busy_buffers_size 256k;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Static file caching
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|woff|woff2|ttf|svg|eot)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }
}

# Wildcard subdomain (HTTPS)
server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name *.yourdomain.com;
    root /var/www/landing-page-builder/public;

    ssl_certificate /etc/letsencrypt/live/yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yourdomain.com/privkey.pem;

    ssl_session_timeout 1d;
    ssl_session_cache shared:SSL:50m;
    ssl_protocols TLSv1.2 TLSv1.3;

    index index.php index.html;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### Auto-Renewal

```bash
# Test renewal
sudo certbot renew --dry-run

# Certbot auto-renewal is installed automatically
# Verify timer is active
sudo systemctl status certbot.timer
```

---

## Database Setup

### MySQL Configuration

```bash
# Secure MySQL installation
sudo mysql_secure_installation

# Login to MySQL
sudo mysql
```

```sql
-- Create database
CREATE DATABASE landing_page_builder CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create user
CREATE USER 'lpb_user'@'localhost' IDENTIFIED BY 'your_secure_password_here';

-- Grant privileges
GRANT ALL PRIVILEGES ON landing_page_builder.* TO 'lpb_user'@'localhost';
FLUSH PRIVILEGES;

EXIT;
```

### MySQL Performance Tuning

Edit `/etc/mysql/mysql.conf.d/mysqld.cnf`:

```ini
[mysqld]
# InnoDB settings
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
innodb_flush_log_at_trx_commit = 2
innodb_flush_method = O_DIRECT

# Query cache (disabled in MySQL 8.0+)
# Use application-level caching instead

# Connection settings
max_connections = 200
wait_timeout = 600
interactive_timeout = 600

# Logging
slow_query_log = 1
slow_query_log_file = /var/log/mysql/slow.log
long_query_time = 2

# Character set
character-set-server = utf8mb4
collation-server = utf8mb4_unicode_ci
```

```bash
# Restart MySQL
sudo systemctl restart mysql
```

### Redis Configuration

Edit `/etc/redis/redis.conf`:

```conf
maxmemory 512mb
maxmemory-policy allkeys-lru
```

```bash
# Restart Redis
sudo systemctl restart redis-server
sudo systemctl enable redis-server
```

---

## Queue Worker with Supervisor

### Create Supervisor Configuration

Create `/etc/supervisor/conf.d/landing-page-builder-worker.conf`:

```ini
[program:landing-page-builder-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/landing-page-builder/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=deploy
numprocs=4
redirect_stderr=true
stdout_logfile=/var/www/landing-page-builder/storage/logs/worker.log
stopwaitsecs=3600
```

### Supervisor for Laravel Horizon (Optional)

Create `/etc/supervisor/conf.d/horizon.conf`:

```ini
[program:horizon]
process_name=%(program_name)s
command=php /var/www/landing-page-builder/artisan horizon
autostart=true
autorestart=true
user=deploy
redirect_stderr=true
stdout_logfile=/var/www/landing-page-builder/storage/logs/horizon.log
stopwaitsecs=3600
```

### Enable Supervisor

```bash
# Reload supervisor
sudo supervisorctl reread
sudo supervisorctl update

# Start workers
sudo supervisorctl start landing-page-builder-worker:*

# Check status
sudo supervisorctl status
```

---

## Cron Jobs

### Laravel Scheduler

Add to crontab for deploy user:

```bash
sudo crontab -u deploy -e
```

Add this line:

```cron
* * * * * cd /var/www/landing-page-builder && php artisan schedule:run >> /dev/null 2>&1
```

### Additional Cron Jobs

```cron
# Clear expired password reset tokens daily at 3 AM
0 3 * * * cd /var/www/landing-page-builder && php artisan auth:clear-resets >> /dev/null 2>&1

# Clear old telescope entries weekly
0 4 * * 0 cd /var/www/landing-page-builder && php artisan telescope:prune --hours=168 >> /dev/null 2>&1

# Backup database daily at 2 AM
0 2 * * * cd /var/www/landing-page-builder && php artisan backup:run >> /dev/null 2>&1

# Clean old backups weekly
0 5 * * 0 cd /var/www/landing-page-builder && php artisan backup:clean >> /dev/null 2>&1
```

---

## Performance Optimization

### PHP-FPM Configuration

Edit `/etc/php/8.2/fpm/pool.d/www.conf`:

```ini
[www]
user = deploy
group = www-data
listen = /var/run/php/php8.2-fpm.sock
listen.owner = www-data
listen.group = www-data

pm = dynamic
pm.max_children = 50
pm.start_servers = 10
pm.min_spare_servers = 5
pm.max_spare_servers = 20
pm.max_requests = 500

php_admin_value[memory_limit] = 256M
php_admin_value[upload_max_filesize] = 50M
php_admin_value[post_max_size] = 50M
php_admin_value[max_execution_time] = 300
```

### PHP OPcache Configuration

Edit `/etc/php/8.2/fpm/conf.d/10-opcache.ini`:

```ini
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=10000
opcache.revalidate_freq=0
opcache.validate_timestamps=0
opcache.save_comments=1
opcache.fast_shutdown=1
```

```bash
# Restart PHP-FPM
sudo systemctl restart php8.2-fpm
```

### Laravel Optimization Commands

```bash
# Run these after each deployment
cd /var/www/landing-page-builder

# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Cache events
php artisan event:cache

# Optimize autoloader
composer install --optimize-autoloader --no-dev
```

### Nginx Performance

Edit `/etc/nginx/nginx.conf`:

```nginx
worker_processes auto;
worker_rlimit_nofile 65535;

events {
    worker_connections 4096;
    multi_accept on;
    use epoll;
}

http {
    sendfile on;
    tcp_nopush on;
    tcp_nodelay on;
    keepalive_timeout 65;
    types_hash_max_size 2048;
    client_max_body_size 50M;

    # Gzip
    gzip on;
    gzip_vary on;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_types text/plain text/css text/xml application/json application/javascript application/rss+xml application/atom+xml image/svg+xml;

    # Security
    server_tokens off;

    include /etc/nginx/mime.types;
    default_type application/octet-stream;
    include /etc/nginx/conf.d/*.conf;
    include /etc/nginx/sites-enabled/*;
}
```

---

## GitHub Actions CI/CD Workflow

### Create Workflow File

Create `.github/workflows/deploy.yml`:

```yaml
name: Deploy to Production

on:
  push:
    branches:
      - main
  workflow_dispatch:

jobs:
  tests:
    name: Run Tests
    runs-on: ubuntu-latest

    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: password
          MYSQL_DATABASE: testing
        ports:
          - 3306:3306
        options: --health-cmd="mysqladmin ping" --health-interval=10s --health-timeout=5s --health-retries=3

      redis:
        image: redis:alpine
        ports:
          - 6379:6379
        options: --health-cmd="redis-cli ping" --health-interval=10s --health-timeout=5s --health-retries=3

    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
          extensions: dom, curl, libxml, mbstring, zip, pcntl, pdo, pdo_mysql, bcmath, soap, intl, gd, exif, iconv, imagick, redis
          coverage: none

      - name: Setup Node.js
        uses: actions/setup-node@v4
        with:
          node-version: '18'
          cache: 'npm'

      - name: Get composer cache directory
        id: composer-cache
        run: echo "dir=$(composer config cache-files-dir)" >> $GITHUB_OUTPUT

      - name: Cache composer dependencies
        uses: actions/cache@v4
        with:
          path: ${{ steps.composer-cache.outputs.dir }}
          key: ${{ runner.os }}-composer-${{ hashFiles('**/composer.lock') }}
          restore-keys: ${{ runner.os }}-composer-

      - name: Install Composer dependencies
        run: composer install --no-progress --prefer-dist --optimize-autoloader

      - name: Install NPM dependencies
        run: npm ci

      - name: Build assets
        run: npm run build

      - name: Prepare Laravel Application
        run: |
          cp .env.example .env
          php artisan key:generate
          php artisan config:clear

      - name: Run tests
        env:
          DB_CONNECTION: mysql
          DB_HOST: 127.0.0.1
          DB_PORT: 3306
          DB_DATABASE: testing
          DB_USERNAME: root
          DB_PASSWORD: password
          REDIS_HOST: 127.0.0.1
        run: php artisan test

  deploy:
    name: Deploy to Production
    runs-on: ubuntu-latest
    needs: tests
    if: github.ref == 'refs/heads/main'

    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'

      - name: Setup Node.js
        uses: actions/setup-node@v4
        with:
          node-version: '18'
          cache: 'npm'

      - name: Install Composer dependencies
        run: composer install --no-dev --no-progress --prefer-dist --optimize-autoloader

      - name: Install NPM dependencies
        run: npm ci

      - name: Build assets
        run: npm run build

      - name: Deploy to server
        uses: appleboy/ssh-action@v1.0.3
        with:
          host: ${{ secrets.SERVER_HOST }}
          username: ${{ secrets.SERVER_USERNAME }}
          key: ${{ secrets.SSH_PRIVATE_KEY }}
          script: |
            cd /var/www/landing-page-builder

            # Pull latest changes
            git pull origin main

            # Install dependencies
            composer install --no-dev --optimize-autoloader
            npm ci
            npm run build

            # Run migrations
            php artisan migrate --force

            # Clear and cache
            php artisan config:cache
            php artisan route:cache
            php artisan view:cache
            php artisan event:cache

            # Restart queue workers
            php artisan queue:restart

            # Restart PHP-FPM
            sudo systemctl reload php8.2-fpm

            echo "Deployment completed successfully!"

      - name: Notify on success
        if: success()
        run: echo "Deployment successful!"

      - name: Notify on failure
        if: failure()
        run: echo "Deployment failed!"
```

### GitHub Secrets Required

Add these secrets in GitHub repository settings:

- `SERVER_HOST`: Your server IP or hostname
- `SERVER_USERNAME`: SSH username (e.g., `deploy`)
- `SSH_PRIVATE_KEY`: Private SSH key for authentication

### Alternative: Zero-Downtime Deployment

Create `.github/workflows/deploy-zero-downtime.yml`:

```yaml
name: Zero-Downtime Deploy

on:
  push:
    branches:
      - main

jobs:
  deploy:
    name: Deploy
    runs-on: ubuntu-latest

    steps:
      - name: Deploy to server
        uses: appleboy/ssh-action@v1.0.3
        with:
          host: ${{ secrets.SERVER_HOST }}
          username: ${{ secrets.SERVER_USERNAME }}
          key: ${{ secrets.SSH_PRIVATE_KEY }}
          script: |
            set -e

            APP_DIR="/var/www/landing-page-builder"
            RELEASES_DIR="$APP_DIR/releases"
            SHARED_DIR="$APP_DIR/shared"
            CURRENT_DIR="$APP_DIR/current"
            RELEASE=$(date +%Y%m%d%H%M%S)
            NEW_RELEASE_DIR="$RELEASES_DIR/$RELEASE"

            # Create new release directory
            mkdir -p $NEW_RELEASE_DIR

            # Clone repository
            git clone --depth 1 git@github.com:your-org/landing-page-builder.git $NEW_RELEASE_DIR

            # Link shared files
            ln -nfs $SHARED_DIR/.env $NEW_RELEASE_DIR/.env
            ln -nfs $SHARED_DIR/storage $NEW_RELEASE_DIR/storage

            # Install dependencies
            cd $NEW_RELEASE_DIR
            composer install --no-dev --optimize-autoloader
            npm ci
            npm run build

            # Run migrations
            php artisan migrate --force

            # Cache
            php artisan config:cache
            php artisan route:cache
            php artisan view:cache
            php artisan event:cache

            # Switch symlink
            ln -nfs $NEW_RELEASE_DIR $CURRENT_DIR

            # Restart services
            php artisan queue:restart
            sudo systemctl reload php8.2-fpm

            # Keep only last 5 releases
            cd $RELEASES_DIR
            ls -dt */ | tail -n +6 | xargs -r rm -rf

            echo "Zero-downtime deployment completed!"
```

---

## Application Deployment

### Initial Deployment

```bash
# Clone repository
cd /var/www
sudo git clone https://github.com/your-org/landing-page-builder.git
sudo chown -R deploy:www-data landing-page-builder
cd landing-page-builder

# Install dependencies
composer install --optimize-autoloader --no-dev
npm ci
npm run build

# Setup environment
cp .env.example .env
php artisan key:generate

# Edit .env with production values
nano .env

# Set permissions
sudo chown -R deploy:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Run migrations and seeders
php artisan migrate --force
php artisan db:seed --force

# Cache everything
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Create storage link
php artisan storage:link
```

### Environment Variables (.env)

```env
APP_NAME="Landing Page Builder"
APP_ENV=production
APP_KEY=base64:generated_key_here
APP_DEBUG=false
APP_URL=https://yourdomain.com

LOG_CHANNEL=daily
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=landing_page_builder
DB_USERNAME=lpb_user
DB_PASSWORD=your_secure_password_here

BROADCAST_DRIVER=redis
CACHE_DRIVER=redis
FILESYSTEM_DISK=local
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
SESSION_LIFETIME=120

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=

STRIPE_KEY=your_stripe_key
STRIPE_SECRET=your_stripe_secret
STRIPE_WEBHOOK_SECRET=your_webhook_secret
```

---

## Monitoring and Maintenance

### Log Rotation

Create `/etc/logrotate.d/landing-page-builder`:

```
/var/www/landing-page-builder/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0640 deploy www-data
    sharedscripts
    postrotate
        /usr/lib/php/php8.2-fpm-reopenlogs
    endscript
}
```

### Health Check Script

Create `/var/www/landing-page-builder/health-check.sh`:

```bash
#!/bin/bash

# Check Nginx
if ! systemctl is-active --quiet nginx; then
    echo "Nginx is down!"
    sudo systemctl restart nginx
fi

# Check PHP-FPM
if ! systemctl is-active --quiet php8.2-fpm; then
    echo "PHP-FPM is down!"
    sudo systemctl restart php8.2-fpm
fi

# Check MySQL
if ! systemctl is-active --quiet mysql; then
    echo "MySQL is down!"
    sudo systemctl restart mysql
fi

# Check Redis
if ! systemctl is-active --quiet redis-server; then
    echo "Redis is down!"
    sudo systemctl restart redis-server
fi

# Check queue workers
if ! supervisorctl status landing-page-builder-worker:* | grep -q "RUNNING"; then
    echo "Queue workers are down!"
    sudo supervisorctl restart landing-page-builder-worker:*
fi

echo "Health check completed at $(date)"
```

```bash
# Make executable
chmod +x /var/www/landing-page-builder/health-check.sh

# Add to cron (every 5 minutes)
*/5 * * * * /var/www/landing-page-builder/health-check.sh >> /var/log/health-check.log 2>&1
```

---

## Firewall Configuration

```bash
# Install UFW
sudo apt install -y ufw

# Allow SSH
sudo ufw allow 22/tcp

# Allow HTTP and HTTPS
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

# Enable firewall
sudo ufw enable

# Check status
sudo ufw status
```

---

## Troubleshooting

### Common Issues

```bash
# Permission issues
sudo chown -R deploy:www-data /var/www/landing-page-builder
sudo chmod -R 775 /var/www/landing-page-builder/storage
sudo chmod -R 775 /var/www/landing-page-builder/bootstrap/cache

# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear

# Check logs
tail -f /var/www/landing-page-builder/storage/logs/laravel.log
tail -f /var/log/nginx/error.log
tail -f /var/log/mysql/error.log

# Check PHP-FPM status
sudo systemctl status php8.2-fpm

# Check queue worker status
sudo supervisorctl status

# Restart all services
sudo systemctl restart nginx php8.2-fpm mysql redis-server
sudo supervisorctl restart all
```
