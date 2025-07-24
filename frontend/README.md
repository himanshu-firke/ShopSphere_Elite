# EcommerceKanha Frontend

This is the Laravel-based frontend for the EcommerceKanha platform.

## Tech Stack

- **Framework**: Laravel 12 (PHP 8+)
- **Frontend**: Laravel Blade Templates
- **Styling**: Tailwind CSS
- **Authentication**: Laravel Breeze
- **Build Tool**: Vite
- **Code Quality**: ESLint + Prettier

## Prerequisites

- PHP 8.2 or higher
- Composer
- Node.js 18+ and npm
- MySQL database

## Installation

1. **Install PHP dependencies:**
   ```bash
   composer install
   ```

2. **Install Node.js dependencies:**
   ```bash
   npm install
   ```

3. **Environment Setup:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database Setup:**
   ```bash
   php artisan migrate
   ```

5. **Build Assets:**
   ```bash
   npm run build
   ```

## Development

### Starting the Development Server

```bash
# Start Laravel development server
php artisan serve

# In another terminal, start Vite for asset compilation
npm run dev
```

### Available Scripts

- `npm run dev` - Start Vite development server
- `npm run build` - Build assets for production
- `npm run lint` - Run ESLint
- `npm run lint:fix` - Fix ESLint issues automatically
- `npm run format` - Format code with Prettier
- `npm run format:check` - Check code formatting
- `npm run test` - Run PHP tests
- `npm run test:watch` - Run tests in watch mode

## Project Structure

```
frontend/
├── app/                 # Laravel application logic
├── resources/
│   ├── css/            # Stylesheets
│   ├── js/             # JavaScript files
│   └── views/          # Blade templates
├── routes/             # Route definitions
├── database/           # Database migrations and seeders
└── public/             # Public assets
```

## Features

- ✅ Laravel 12 with PHP 8+
- ✅ Tailwind CSS for styling
- ✅ Laravel Breeze authentication
- ✅ ESLint and Prettier for code quality
- ✅ Vite for fast asset compilation
- ✅ Responsive design ready
- ✅ TypeScript support

## Next Steps

1. Set up database schema (Task 2)
2. Implement user authentication (Task 3)
3. Create product catalog (Task 4)
4. Build shopping cart functionality (Task 5)
