# Database Setup Guide for Kanha Ecommerce

## Prerequisites
- XAMPP with MySQL running
- Laravel project in `c:\xampp\htdocs\ecommerceKanha\frontend`

## Step 1: Configure Environment Variables

1. Copy `.env.example` to `.env` in the `frontend` directory:
```bash
cd c:\xampp\htdocs\ecommerceKanha\frontend
copy .env.example .env
```

2. Update your `.env` file with the following database configuration:
```env
APP_NAME="Kanha Ecommerce"
APP_ENV=local
APP_KEY=base64:YOUR_APP_KEY_HERE
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=kanha_ecommerce
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=database
QUEUE_CONNECTION=database

# CORS Configuration for React Frontend
SANCTUM_STATEFUL_DOMAINS=localhost:3000,127.0.0.1:3000
SESSION_DOMAIN=localhost
```

## Step 2: Create Database

1. Open phpMyAdmin (http://localhost/phpmyadmin)
2. Create a new database named `kanha_ecommerce`
3. Set collation to `utf8mb4_unicode_ci`

## Step 3: Generate Application Key

```bash
cd c:\xampp\htdocs\ecommerceKanha\frontend
php artisan key:generate
```

## Step 4: Run Database Migrations

```bash
php artisan migrate
```

This will create all the necessary tables:
- users
- user_profiles
- user_addresses
- categories
- products
- product_attributes
- product_images
- orders
- order_items
- order_status_history
- cart
- cart_items
- wishlist
- reviews
- review_images
- review_helpful
- banners
- pages
- settings
- personal_access_tokens (for API authentication)

## Step 5: Seed Database (Optional)

Create sample data for testing:

```bash
php artisan db:seed
```

## Step 6: Configure Laravel Sanctum for API Authentication

1. Publish Sanctum configuration:
```bash
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

2. Add Sanctum middleware to `app/Http/Kernel.php`:
```php
'api' => [
    \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
    'throttle:api',
    \Illuminate\Routing\Middleware\SubstituteBindings::class,
],
```

## Step 7: Start Laravel Development Server

```bash
php artisan serve --host=0.0.0.0 --port=8000
```

Your Laravel backend will be available at: http://localhost:8000

## Step 8: Test API Endpoints

Test key endpoints:
- GET http://localhost:8000/api/products - Get all products
- GET http://localhost:8000/api/categories - Get all categories
- POST http://localhost:8000/api/auth/register - Register user
- POST http://localhost:8000/api/auth/login - Login user
- GET http://localhost:8000/api/cart - Get cart (requires authentication)

## Step 9: Configure CORS (if needed)

If you encounter CORS issues, install and configure Laravel CORS:

```bash
composer require fruitcake/laravel-cors
```

Update `config/cors.php`:
```php
'paths' => ['api/*', 'sanctum/csrf-cookie'],
'allowed_methods' => ['*'],
'allowed_origins' => ['http://localhost:3000', 'http://127.0.0.1:3000'],
'allowed_origins_patterns' => [],
'allowed_headers' => ['*'],
'exposed_headers' => [],
'max_age' => 0,
'supports_credentials' => true,
```

## Troubleshooting

### Common Issues:

1. **Migration Errors**: Ensure MySQL is running and database exists
2. **Permission Errors**: Check file permissions on storage and bootstrap/cache
3. **CORS Errors**: Verify CORS configuration and Sanctum setup
4. **API Authentication**: Ensure Sanctum is properly configured

### Useful Commands:

```bash
# Reset database (WARNING: This will delete all data)
php artisan migrate:fresh --seed

# Check migration status
php artisan migrate:status

# Create storage link for file uploads
php artisan storage:link

# Clear application cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

## Next Steps

After database setup:
1. Test all API endpoints
2. Verify React frontend can connect to Laravel backend
3. Test user registration and authentication
4. Test product listing and cart functionality
5. Configure payment gateways (Stripe/PayPal)
6. Set up email configuration for notifications
