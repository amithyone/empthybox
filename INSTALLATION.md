# BiggestLogs Installation Guide

## Prerequisites

- PHP 8.1 or higher
- Composer
- Node.js and npm
- MySQL or PostgreSQL
- Web server (Apache/Nginx) or PHP built-in server

## Step-by-Step Installation

### 1. Install Dependencies

```bash
composer install
npm install
```

### 2. Environment Configuration

Copy the example environment file:
```bash
cp .env.example .env
```

Generate application key:
```bash
php artisan key:generate
```

### 3. Configure Database

Edit `.env` file and update database credentials:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=biggestlogs
DB_USERNAME=root
DB_PASSWORD=your_password
```

Create the database:
```sql
CREATE DATABASE biggestlogs;
```

### 4. Run Migrations

```bash
php artisan migrate --seed
```

This will:
- Create all database tables
- Seed default admin and test user
- Create sample categories and products

### 5. Storage Setup

Create storage link:
```bash
php artisan storage:link
```

### 6. Build Assets

For development:
```bash
npm run dev
```

For production:
```bash
npm run build
```

### 7. Start Development Server

```bash
php artisan serve
```

Visit `http://localhost:8000` in your browser.

## Default Login Credentials

**Admin Account:**
- Email: `admin@biggestlogs.com`
- Password: `password`

**Test User Account:**
- Email: `user@test.com`
- Password: `password`

## Payment Gateway Setup

To enable payment gateways, add your API keys to `.env`:

```env
PAYSTACK_PUBLIC_KEY=your_key
PAYSTACK_SECRET_KEY=your_secret

STRIPE_KEY=your_key
STRIPE_SECRET=your_secret

RAZORPAY_KEY=your_key
RAZORPAY_SECRET=your_secret
```

## Features to Test

1. **User Registration & Login**
2. **Browse Products** - View available digital goods
3. **Purchase** - Buy products using wallet or payment gateway
4. **PIN Reveal** - Enter 4-digit PIN to view credentials
5. **Wallet** - Deposit funds and view transaction history
6. **Support Tickets** - Create and manage support tickets
7. **Replacement Request** - Request log replacement if invalid
8. **Admin Panel** - Approve replacements and manage orders

## Troubleshooting

### Permission Issues
```bash
chmod -R 775 storage bootstrap/cache
```

### Clear Cache
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

### Database Issues
```bash
php artisan migrate:fresh --seed
```

## Production Deployment

1. Set `APP_ENV=production` and `APP_DEBUG=false` in `.env`
2. Run `php artisan config:cache`
3. Run `php artisan route:cache`
4. Run `php artisan view:cache`
5. Build production assets: `npm run build`
6. Set up proper web server configuration






