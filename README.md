# Castimize

Multi-channel e-commerce fulfillment platform for 3D printing services. Built with Laravel 11, Laravel Nova 4, and integrations with Etsy, WooCommerce, Stripe, Shippo, and Exact Online.

## Tech Stack

- **PHP 8.3** / **Laravel 11** / **Laravel Nova 4**
- **MySQL 8.4** / **Redis** (cache/queue)
- **Vite** for frontend bundling
- **Docker** via Laravel Sail

## Requirements

- Docker Desktop
- PHP 8.3+ (for running Composer locally)
- Node.js 20+ & npm
- Composer 2.x
- [mkcert](https://github.com/FiloSottile/mkcert) (optional, for local HTTPS)

## Installation

### 1. Clone the Repository

```bash
git clone <repository-url> castimize
cd castimize
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Environment Configuration

Copy the example environment file:

```bash
cp .env.example .env
```

Generate the application key:

```bash
php artisan key:generate
```

### 4. Configure Environment Variables

Edit `.env` and configure the following sections:

#### Application Settings

```env
APP_NAME=Castimize
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost
APP_TIMEZONE=Europe/Amsterdam
```

#### Database (Docker/Sail)

```env
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=castimize
DB_USERNAME=sail
DB_PASSWORD=password
```

#### Redis (Docker/Sail)

```env
REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379
CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
```

#### Nova License

```env
NOVA_LICENSE_KEY=your-nova-license-key
```

#### External Services (configure as needed)

```env
# WooCommerce
WOOCOMMERCE_STORE_URL=https://your-store.com
WOOCOMMERCE_CONSUMER_KEY=ck_xxx
WOOCOMMERCE_CONSUMER_SECRET=cs_xxx

# Stripe
STRIPE_KEY=pk_test_xxx
STRIPE_SECRET=sk_test_xxx
STRIPE_WEBHOOK_SECRET=whsec_xxx

# Shippo
SHIPPO_API_KEY=shippo_test_xxx

# Etsy
ETSY_CLIENT_ID=xxx
ETSY_CLIENT_SECRET=xxx

# Exact Online
EXACT_CLIENT_ID=xxx
EXACT_CLIENT_SECRET=xxx
EXACT_DIVISION=xxx

# Sentry (error monitoring)
SENTRY_LARAVEL_DSN=https://xxx@sentry.io/xxx

# AWS S3 (file storage)
AWS_ACCESS_KEY_ID=xxx
AWS_SECRET_ACCESS_KEY=xxx
AWS_DEFAULT_REGION=eu-west-1
AWS_BUCKET=castimize
```

### 5. Start Docker Containers

```bash
docker compose up -d
```

This starts:
- **laravel.test** - PHP application server
- **mysql** - MySQL 8.4 database
- **redis** - Redis cache/queue server
- **nginx** - Reverse proxy with SSL (if configured)

### 6. Run Database Migrations

```bash
docker compose exec laravel.test php artisan migrate
```

Or with seeders:

```bash
docker compose exec laravel.test php artisan migrate --seed
```

### 7. Install Frontend Dependencies

```bash
npm install
```

### 8. Build Frontend Assets

For development (with hot reload):

```bash
npm run dev
```

For production:

```bash
npm run build
```

### 9. Build Nova Components

```bash
npm run build-select-with-overview
npm run build-po-status-card
npm run build-select-manufacturer-with-overview
npm run build-inline-text-edit
npm run build-custom-styles
```

Or build all at once:

```bash
npm run build-select-with-overview && npm run build-po-status-card && npm run build-select-manufacturer-with-overview && npm run build-inline-text-edit && npm run build-custom-styles
```

### 10. Access the Application

- **Application**: http://localhost
- **Nova Admin**: http://localhost/admin

## HTTPS Setup (Optional)

For local HTTPS with trusted certificates:

### 1. Install mkcert

```bash
# macOS
brew install mkcert

# Linux
sudo apt install mkcert
```

### 2. Generate Certificates

```bash
mkdir -p docker/ssl
cd docker/ssl
mkcert -install
mkcert localhost app.castimize.test 127.0.0.1 ::1
cd ../..
```

### 3. Create Nginx Configuration

Create `docker/nginx/default.conf`:

```nginx
server {
    listen 80;
    server_name localhost app.castimize.test;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl;
    server_name localhost app.castimize.test;

    ssl_certificate /etc/nginx/ssl/localhost+3.pem;
    ssl_certificate_key /etc/nginx/ssl/localhost+3-key.pem;

    location / {
        proxy_pass http://laravel.test;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

### 4. Update Environment

```env
APP_URL=https://localhost
```

### 5. Add Host Entry (optional)

```bash
sudo echo "127.0.0.1 app.castimize.test" >> /etc/hosts
```

### 6. Restart Containers

```bash
docker compose down && docker compose up -d
```

Now access: https://localhost or https://app.castimize.test

## Development

### Running Tests

```bash
# All tests
vendor/bin/phpunit

# Specific test
vendor/bin/phpunit --filter=TestName

# With coverage
vendor/bin/phpunit --coverage-html coverage
```

### Code Formatting

```bash
# Check and fix formatting
vendor/bin/pint

# Only changed files
vendor/bin/pint --dirty
```

### Queue Worker

For processing background jobs:

```bash
docker compose exec laravel.test php artisan queue:work
```

### Scheduler

For running scheduled tasks:

```bash
docker compose exec laravel.test php artisan schedule:work
```

## Useful Commands

```bash
# Clear all caches
php artisan optimize:clear

# Rebuild caches
php artisan optimize

# View routes
php artisan route:list

# Create new migration
php artisan make:migration create_table_name

# Fresh database with seeds
php artisan migrate:fresh --seed

# Generate IDE helper files
php artisan ide-helper:generate
php artisan ide-helper:models -N
php artisan ide-helper:meta
```

## Docker Commands

```bash
# Start containers
docker compose up -d

# Stop containers
docker compose down

# View logs
docker compose logs -f

# View specific service logs
docker compose logs -f laravel.test

# Execute command in container
docker compose exec laravel.test <command>

# Rebuild containers
docker compose build --no-cache
```

## Project Structure

```
├── app/
│   ├── Console/Commands/     # Artisan commands
│   ├── DTO/                  # Data Transfer Objects
│   ├── Enums/                # Type-safe enums
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/V1/       # REST API endpoints
│   │   │   └── Webhooks/     # Webhook handlers
│   │   ├── Requests/         # Form request validation
│   │   └── Resources/        # API resources
│   ├── Jobs/                 # Queued jobs
│   ├── Models/               # Eloquent models
│   ├── Nova/                 # Nova resources & components
│   ├── Observers/            # Model observers
│   └── Services/             # Business logic
│       ├── Admin/
│       ├── Etsy/
│       ├── Exact/
│       ├── Mail/
│       ├── Payment/
│       ├── Shippo/
│       └── Woocommerce/
├── bootstrap/
│   └── app.php               # Application bootstrap
├── config/                   # Configuration files
├── database/
│   ├── factories/            # Model factories
│   ├── migrations/           # Database migrations
│   └── seeders/              # Database seeders
├── docker/
│   ├── nginx/                # Nginx configuration
│   └── ssl/                  # SSL certificates
├── nova-components/          # Custom Nova components
├── public/                   # Public assets
├── resources/
│   ├── css/
│   ├── js/
│   └── views/
├── routes/
│   ├── api.php               # API routes
│   ├── console.php           # Console commands
│   └── web.php               # Web routes
├── storage/                  # Storage directory
├── tests/
│   ├── Feature/              # Feature tests
│   ├── Unit/                 # Unit tests
│   └── Traits/               # Test traits
├── compose.yaml              # Docker Compose config
├── phpunit.xml               # PHPUnit config
└── vite.config.js            # Vite config
```

## External Integrations

| Service | Purpose | Documentation |
|---------|---------|---------------|
| WooCommerce | Order/customer sync | [WooCommerce REST API](https://woocommerce.github.io/woocommerce-rest-api-docs/) |
| Etsy | Product listings, orders | [Etsy Open API v3](https://developers.etsy.com/documentation/) |
| Stripe | Payment processing | [Stripe API](https://stripe.com/docs/api) |
| Shippo | Shipping labels, tracking | [Shippo API](https://goshippo.com/docs/intro) |
| Exact Online | Accounting sync | [Exact Online API](https://start.exactonline.nl/docs/HlpRestAPIResources.aspx) |
| Sentry | Error monitoring | [Sentry Laravel](https://docs.sentry.io/platforms/php/guides/laravel/) |

## Troubleshooting

### Permission Issues

```bash
# Fix storage permissions
chmod -R 775 storage bootstrap/cache
```

### Database Connection Refused

Make sure MySQL container is running and healthy:

```bash
docker compose ps
docker compose logs mysql
```

### Redis Connection Failed

Check Redis container:

```bash
docker compose exec redis redis-cli ping
```

### Nova Assets Not Loading

Publish Nova assets:

```bash
php artisan nova:publish
```

### Queue Jobs Not Processing

Start the queue worker:

```bash
docker compose exec laravel.test php artisan queue:work --tries=3
```

## License

Proprietary - All rights reserved.
