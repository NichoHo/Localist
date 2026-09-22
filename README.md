# Localist

[![CI](https://github.com/NichoHo/Localist/actions/workflows/ci.yml/badge.svg)](https://github.com/NichoHo/Localist/actions/workflows/ci.yml)

A directory platform for local businesses. Public programmatic directory (~5,700 pages) + a Livewire owner portal where businesses claim and manage their paid listing.

**Stack:** Laravel 12 · Livewire 3 + Alpine · Blade + Tailwind CSS 4 · MySQL · Stripe (Cashier) · Cloudflare

> Seed data is real: ~5,700 Indonesian restaurants, cafes, salons, clinics and other local businesses, pulled from [Foursquare Open Source Places](https://opensource.foursquare.com/os-places/) (Apache 2.0). Real names, addresses and phone/website, no fabricated content, most listings unclaimed until an owner steps in. `database/seed/fetch_indonesia_listings.py` documents and regenerates the extraction (requires `pip install duckdb`).

## Local setup

```bash
composer install && npm install
cp .env.example .env && php artisan key:generate
# point DB_* at MySQL, create database "localist"
php artisan migrate --seed
php artisan listings:import database/seed/listings.csv   # ~5,700 listings + sitemaps
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

Without keys, `/billing` renders but upgrade shows a friendly "not configured" notice.

Plans sync two ways. The Checkout success callback applies the plan at once. Cashier's webhook (`POST /stripe/webhook`) handles everything after that: renewals, cancellations, and changes made in the Stripe dashboard. A cancelled subscription drops the listing back to Free. To test locally with the [Stripe CLI](https://stripe.com/docs/stripe-cli):

```bash
stripe listen --forward-to localhost:8000/stripe/webhook
```

Put the `whsec_...` it prints into `STRIPE_WEBHOOK_SECRET`. In production, add the same endpoint in the Stripe dashboard.

## Enquiries

Leads land in the owner portal (`/leads`, unread badge on the dashboard). The form is rate limited (5 per minute per IP) and has a honeypot field. Bots that fill it get a fake success and nothing is stored.

## Cloudflare

Cache boundary is already correct at the Laravel level: public routes send `Cache-Control: public, max-age=600` + ETag; portal routes are behind auth and stay no-cache.

Dashboard config (two cache rules):

1. **Cache everything** on public paths, respect origin headers (origin already sends them).
2. **Bypass cache** when the request has a session cookie (`laravel_session`), covers portal + admin.

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

## Ops

- **Pulse** at `/pulse` (admin role only): slow queries, slow requests, exceptions, queue health. Runs on the app database, no extra service.
- **View counts**: listing pages are Cloudflare-cached, so the page fires `navigator.sendBeacon` to `POST /business/{slug}/view` instead of counting on render. Bots without JS are not counted.
- **Photos**: uploads are rotated (EXIF), capped at 1600px wide and stored as WebP via GD. The Docker image builds GD with WebP and EXIF support. Locally, enable `extension=gd` in `php.ini`.

## Tests

```bash
vendor/bin/pint --test && php artisan test
```

CI runs the same on every push and pull request to `main`, plus `npm run build`.

Covers: directory pages + featured ranking, claim flow, live editing, **slug change → working 301** (the check that silently breaks SEO if it regresses), photo plan gating + WebP resize, leads + honeypot + throttle, view beacon, JSON-LD validity, sitemap chunking, billing plan changes, Stripe webhook plan sync, cache headers, purge dispatch, admin approval, Pulse access.
