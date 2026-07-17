# Localist

A directory platform for local service businesses. Public programmatic directory (~5,400 pages) + a Livewire owner portal where businesses claim and manage their paid listing.

**Stack:** Laravel 12 · Livewire 3 + Alpine · Blade + Tailwind CSS 4 · MySQL · Stripe (Cashier) · Cloudflare

> Seed data is synthetic (5,000 generated Malaysian listings) — clearly labeled as demo content.

## Local setup

```bash
composer install && npm install
cp .env.example .env && php artisan key:generate
# point DB_* at MySQL, create database "localist"
php artisan migrate --seed
php artisan listings:import database/seed/listings.csv   # 5,000 listings + sitemaps
npm run build
php artisan serve
```

Login: `admin@localist.test` / `password` (admin role → `/admin`).

## Stripe (test mode)

Upgrades run through Stripe Checkout via Cashier. Add to `.env`:

```
STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
STRIPE_PRICE_FEATURED=price_...   # recurring monthly test prices
STRIPE_PRICE_PREMIUM=price_...
```

Without keys, `/billing` renders but upgrade shows a friendly "not configured" notice. Renewal/cancellation sync would use Cashier's webhook (`/stripe/webhook`) — the demo applies plans on the Checkout success callback.

## Cloudflare

Cache boundary is already correct at the Laravel level: public routes send `Cache-Control: public, max-age=600` + ETag; portal routes are behind auth and stay no-cache.

Dashboard config (two cache rules):

1. **Cache everything** on public paths — respect origin headers (origin already sends them).
2. **Bypass cache** when the request has a session cookie (`laravel_session`) — covers portal + admin.

Purge-on-publish: editing/approving a listing dispatches a job that purges that listing's URL plus its city/category/home index pages. Add to `.env`:

```
CLOUDFLARE_ZONE_ID=...
CLOUDFLARE_API_TOKEN=...   # token with Zone.Cache Purge permission
```

Unconfigured, the job logs and no-ops (safe locally).

## Deploy (Docker)

```bash
docker build -t localist .
docker run -p 8080:80 --env-file .env.production localist
```

The image runs migrations, caches config/routes/views, and serves via Apache. Point it at any managed MySQL (Railway/Render/PlanetScale). Queue: set `QUEUE_CONNECTION=sync` for a single-container demo, or run a worker container with `php artisan queue:work`.

## Tests

```bash
php artisan test
```

Covers: directory pages + featured ranking, claim flow, live editing, **slug change → working 301** (the check that silently breaks SEO if it regresses), photo plan gating, leads, JSON-LD validity, sitemap chunking, billing plan changes, cache headers, purge dispatch, admin approval.
