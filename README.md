# Ridhi Sidhi Garments

Laravel 12 storefront and admin application for women’s ethnic and contemporary clothing.

## Core features

- Clothing catalog with categories, brands, variants, sizes, colors, materials, search, and size charts
- Guest and customer carts, wishlists, reviews, addresses, checkout, and order history
- COD and Razorpay online payments
- Flat per-item shipping (₹99 by default), product overrides, and an optional free-shipping threshold
- Manual fulfillment with invoices, shipping labels, tracking details, returns, cancellations, and refunds
- Admin product, inventory, customer, order, report, tax, payment, and maintenance management
- Transactional email through Brevo or SMTP
- SEO metadata and sitemap for active catalog and policy pages

## Requirements

- PHP 8.2+
- Composer
- Node.js and npm
- MySQL 8+ or MariaDB 10.6+

## Local setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install
npm run build
php artisan serve
```

Visit `http://localhost:8000`. If queued email is enabled with `QUEUE_CONNECTION=database`, run `php artisan queue:work` in a second terminal.

No Laravel scheduler tasks are registered. Pending Razorpay payments can be reconciled manually with `php artisan orders:sync-payments` or through the protected `/cron/sync-payments` endpoint.

## Demo credentials

Seeded accounts are for local/demo use only. Change or remove them before production.

| Role | URL | Email | Password |
|---|---|---|---|
| Administrator | `/admin` | `admin@ridhisidhi.test` | `Admin@12345` |
| Customer | `/login` | `shopper@ridhisidhi.test` | `Customer@12345` |

## Database

The example database is `ridhi_sidhi_garments`:

```bash
mysql -u root -e "CREATE DATABASE IF NOT EXISTS ridhi_sidhi_garments CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
php artisan migrate --seed
```

Configure local credentials in `.env`; never commit that file. The tracked `.env.example` and `.env.hostinger.example` contain placeholders only.

## Payments and shipping

- COD is enabled by default and can be toggled in Admin → Settings → Payments.
- Razorpay is available when `RAZORPAY_KEY_ID` and `RAZORPAY_KEY_SECRET` are configured. Local mock mode is controlled by `RAZORPAY_MOCK`.
- `DEFAULT_PRODUCT_SHIPPING=99` is the fallback flat charge per product unit. Products can override it or be marked for free shipping.
- The optional order-value free-shipping threshold is managed in Admin → Settings → Payments.

## Production

See [DEPLOYMENT.md](DEPLOYMENT.md) and start from `.env.hostinger.example`.
