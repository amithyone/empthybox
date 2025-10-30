# BiggestLogs - Digital Marketplace Platform

A modern, secure Laravel-based digital marketplace for buying and selling verified social media accounts and digital goods.

## Features

🔥 **Core Functionalities**
- Product management with categories
- 4-digit PIN protection for credential reveal
- Instant digital delivery
- Wallet system with multi-gateway payments
- Replacement request system
- Support ticket system
- Admin dashboard

💳 **Payment Gateways**
- Paystack
- Stripe
- Razorpay
- PayVibe
- BTCPay Server
- CoinGate

🔒 **Security**
- Encrypted credential storage
- PIN-based access control
- CSRF protection
- Rate limiting
- Secure password hashing

## Installation

1. Clone the repository
```bash
git clone <repository-url>
cd Biggestlogs
```

2. Install dependencies
```bash
composer install
npm install
```

3. Configure environment
```bash
cp .env.example .env
php artisan key:generate
```

4. Update `.env` with your database credentials and API keys

5. Run migrations and seeders
```bash
php artisan migrate --seed
```

6. Build assets
```bash
npm run dev
# or for production
npm run build
```

7. Start the development server
```bash
php artisan serve
```

## Default Credentials

**Admin:**
- Email: admin@biggestlogs.com
- Password: password

**User:**
- Email: user@test.com
- Password: password

## Key Features Explained

### PIN Protection System
Each purchased log is protected by a unique 4-digit PIN. Users must enter the correct PIN to reveal their credentials. The PIN is encrypted and can only be used once.

### Replacement Policy
Users can request replacements for invalid logs directly from their order page. Admins can approve replacements, which will deliver a new log with a fresh PIN.

### Wallet System
Users can fund their wallets using multiple payment gateways. Wallet balance can be used for instant purchases without external payment processing.

## Admin Panel

Access the admin dashboard at `/admin/dashboard` with an admin account to:
- Manage replacement requests
- View all orders and users
- Monitor platform statistics

## Support

For support tickets, users can create tickets from the dashboard. Replacement requests are automatically converted to high-priority tickets.

## License

MIT License






