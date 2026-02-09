# Memento Trips

Tour-booking & travel-agency platform built on **Laravel 10** with a modular architecture
(`nwidart/laravel-modules`).

## Requirements

| Dependency | Version |
|------------|---------|
| PHP        | ≥ 8.1   |
| MySQL      | ≥ 5.7   |
| Composer   | ≥ 2.x   |
| Node.js    | ≥ 18    |

## Quick Start

```bash
# 1. Clone
git clone <repo-url> memento-trips && cd memento-trips

# 2. Install
composer install
cp .env.example .env
php artisan key:generate

# 3. Database
#    Create a MySQL database, then fill DB_* values in .env
php artisan migrate --seed

# 4. Storage link
php artisan storage:link

# 5. Serve
php artisan serve          # http://localhost:8000
```

## Project Structure

```
app/
├── Console/Commands/       # Theme scaffold commands
├── Http/Controllers/       # Core controllers (Home, Auth, Admin, User, Agency)
├── Http/Middleware/         # Auth guards, demo mode, maintenance
├── Models/                 # Admin, User, core models
├── Themes/Core/            # Theme management engine
├── Helpers/                # EmailHelper
└── Mail/                   # Transactional emails
Modules/                    # nwidart modular packages
├── TourBooking/            # ★ Main module — services, bookings, payments
├── Blog/                   # Blog posts & categories
├── Page/                   # CMS pages
├── GlobalSetting/          # Site-wide config
├── PaymentGateway/         # Stripe, PayPal, Razorpay, Mollie, etc.
├── PaymentWithdraw/        # Agency payout requests
├── SupportTicket/          # Help-desk tickets
├── EmailSetting/           # SMTP & template config
├── SeoSetting/             # Meta tags & OG config
├── Category/               # Global category taxonomy
├── FAQ/                    # Frequently asked questions
├── Newsletter/             # Subscriber management
├── ContactMessage/         # Contact form submissions
├── Wishlist/               # User wishlists
├── Coupon/                 # Discount codes
├── Partner/                # Partner logos
├── Brand/                  # Brand management
├── Team/                   # Team members
├── Testimonial/            # Client reviews
├── Currency/               # Multi-currency support
├── Language/               # Multi-language support
├── Listing/                # Directory listings
└── EventCalendar/          # Calendar events
Cms/themes/theme1/          # Active front-end theme (Blade views + assets)
resources/views/            # Core views (admin, agency, user, auth, components)
public/
├── frontend/assets/        # Front-end CSS/JS/images
├── backend/                # Admin panel CSS/JS
└── global/                 # Shared vendor libs (jQuery, Toastr, DataTables, etc.)
```

## Roles

| Role     | Guard   | Dashboard route        |
|----------|---------|------------------------|
| Admin    | `admin` | `/admin/dashboard`     |
| Agency   | `web`   | `/agency/dashboard`    |
| User     | `web`   | `/user/dashboard`      |

## Payment Gateways

Stripe · PayPal · Razorpay · Mollie · Flutterwave · Paystack · Instamojo · Bank Transfer

Configured via **Admin → Payment Settings** or `.env` keys.

## Theme System

Themes live in `Cms/themes/`. The active theme is managed through the `App\Themes\Core\Theme`
engine. Each theme has its own controllers, views, config, routes, and assets.

```bash
# List themes
php artisan theme:list

# Activate a theme
php artisan theme:activate theme1
```

## Key Packages

| Package | Purpose |
|---------|---------|
| `nwidart/laravel-modules` | Modular architecture |
| `intervention/image` | Image processing |
| `barryvdh/laravel-dompdf` | PDF generation (invoices) |
| `maatwebsite/excel` | Excel import/export |
| `laravel/socialite` | Social login (Google, Facebook) |
| `mews/purifier` | HTML sanitization |
| `stripe/stripe-php` | Stripe payments |
| `srmklive/paypal` | PayPal payments |
| `razorpay/razorpay` | Razorpay payments |
| `mollie/laravel-mollie` | Mollie payments |

## Environment Variables

See [`.env.example`](.env.example) for all available configuration keys.

## Deployment

See [`DEPLOY.md`](DEPLOY.md) for Ploi.io deployment instructions.

## License

Proprietary — All rights reserved.
