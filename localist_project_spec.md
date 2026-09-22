# Localist: Project Specification & Build Blueprint

*A directory platform for local service businesses. Public directory pages plus a Livewire portal where businesses claim and manage their own paid listing. Same architecture as a regenerative-medicine clinic directory, different vertical.*

> **Internal note (delete before sharing publicly):** This project is built to demonstrate the exact stack and architecture a Laravel + Livewire directory job asks for, a public programmatic directory, a Livewire/Alpine management portal, Cloudflare in front, and technical SEO. Every requirement below maps to something a client can see on a screen-share. Build it in the phase order given so there's always a demoable slice.

> **Post-launch pivot (2026-09-17):** the plan below was written and built against 5,000 synthetic Malaysian trade listings (plumbers, electricians, and similar). Real trade data at any usable volume doesn't exist in open datasets, so the seed was replaced with ~5,700 real Indonesian businesses (restaurants, cafes, salons, clinics, hotels, and similar) pulled from Foursquare Open Source Places. The architecture below (data model, portal, SEO, caching) is unchanged; only the seed data, category taxonomy, city list, currency, and copy moved from synthetic/Malaysia to real/Indonesia. See `database/seed/fetch_indonesia_listings.py`.

---

## 1. Concept

Localist is a searchable directory of local service businesses (plumbers, electricians, cleaners, tutors, movers, and similar). Visitors browse by category and city and land on individual business pages. Business owners claim their listing, manage its content through a portal, and pay to upgrade to a featured tier for better placement and more detail.

**Two halves, one product:**

- **Public directory**: thousands of programmatically generated pages, fast, cached, and SEO-optimized. This is what the public and search engines see.
- **Owner portal**: a Livewire + Alpine app behind auth where businesses manage their own paid listing. This is the revenue side.

**Scale target:** ~5,000 public pages (individual listings + category × city index pages). Large enough to be a real programmatic site, small enough for one developer to build and seed credibly.

---

## 2. Tech stack

| Layer | Choice | Why |
|---|---|---|
| Framework | Laravel 12, PHP 8.2 | Matches the target job and the Flux project |
| Portal UI | Livewire 3 + Alpine.js | The core requirement, a reactive management portal without a separate SPA |
| Public UI | Blade + Tailwind CSS 4 | Server-rendered, cache-friendly, fast to build |
| Build | Vite | Same as Flux; asset bundling |
| Database | MySQL 8 | Familiar, fine for this scale |
| Auth | Laravel Breeze (Blade) + Socialite (optional Google login) | Reuses the pattern already proven in Flux |
| Payments | Laravel Cashier + Stripe (test mode) | Paid tiers; test mode is enough for a demo |
| Maps/geocoding | Google Maps / Geocoding API | Lat/lng for listings and map embeds |
| Edge/cache | Cloudflare | Caching + page rules; the job lists it explicitly |
| Deploy | Docker + Railway/Render | Same deployment story as Flux |

---

## 3. Architecture overview

```
Browser
  ├── Public pages (Blade, GET, cached at Cloudflare edge)
  │     /  /category/{c}  /{city}/{category}  /business/{slug}
  └── Owner portal (Livewire, auth cookie → Cloudflare bypasses cache)
        /dashboard  /listing/edit  /listing/photos  /billing  /leads

Laravel app
  ├── Controllers → Blade views (public, cacheable)
  ├── Livewire components (portal, stateful)
  ├── Models: Business, Category, City, Plan, User, Lead, Media, Redirect
  ├── SEO services: sitemap generator, JSON-LD builders
  ├── Console commands: listings:import (CSV), sitemap:generate
  └── Jobs: geocode listing, purge Cloudflare cache on publish

MySQL  ·  Stripe  ·  Google Geocoding  ·  Cloudflare API (purge)
```

**Cache boundary:** public routes are anonymous and cacheable; portal routes require auth and carry a session cookie, which Cloudflare uses to bypass the cache. Keep the two route groups cleanly separated so the caching rule is a one-line page rule, not a per-URL mess.

---

## 4. Data model

Core tables. Slugs everywhere for clean, SEO-friendly URLs.

**users**: `id, name, email, password, role (owner|admin), google_id?`

**businesses** (the listing):
`id, user_id? (claimant, null until claimed), name, slug, category_id, city_id, description, address, lat, lng, phone, website, email, hours (json), plan_id, status (draft|pending|published), featured_until?, views_count, created_at`

**categories**: `id, name, slug, icon, description`

**cities**: `id, name, slug, region, lat, lng`

**plans**: `id, name (Free|Featured|Premium), price_monthly, max_photos, allows_website, priority_rank`

**leads** (enquiries from public → business): `id, business_id, name, email, phone?, message, created_at, read_at?`

**media**: `id, business_id, path, alt, sort_order`

**redirects**: `id, from_path, to_path, status_code (301)`, populated automatically when a slug changes, so old URLs never 404 (directly answers the "redirects and canonicals" requirement).

Relationships: a Business belongs to a Category, a City, a Plan, and optionally a User (the owner who claimed it). Category and City each have many Businesses. Business has many Media and many Leads.

---

## 5. Design system

A calm, high-trust marketplace system (refined 2026). Identity stays teal; depth comes from **surface-level shift** (a faintly tinted canvas under pure-white surfaces) and **hairline elevation borders**, not heavy shadows. Tokens live as CSS variables in `resources/css/app.css` and are exposed to Tailwind v4 utilities via `@theme inline`, so every value switches automatically with `prefers-color-scheme`. Pattern: *Marketplace / Directory* (search is the CTA). Style lineage: Swiss-modern structure, human warmth.

### Color tokens

Neutral surface scale (cool-tinted). Utility = the Tailwind class stem, e.g. `bg-surface`, `text-ink`, `border-line`.

| Token | Light | Dark | Use |
|---|---|---|---|
| `canvas` | `#f6f8f9` | `#0a1417` | Page background |
| `canvas-2` | `#eef2f4` | `#0e1a1e` | Alternating section bands, footer |
| `surface` | `#ffffff` | `#131e22` | Cards, panels — float above canvas |
| `sunken` | `#edf1f3` | `#0c1519` | Wells, empty-state chips |
| `line-subtle` | `#ebeff2` | `#1e2a2f` | Faint dividers |
| `line` | `#dce3e8` | `#29373d` | Default borders |
| `line-strong` | `#c6d0d6` | `#3a4a51` | Emphasis / dashed borders |
| `ink` | `#0e1b21` | `#e7eef0` | Primary text |
| `ink-muted` | `#55666f` | `#98a8b0` | Secondary text |
| `ink-subtle` | `#8a969d` | `#64757d` | Tertiary text, placeholders, icons |

Brand & semantic:

| Token | Light | Dark | Use |
|---|---|---|---|
| `brand` | `#0f766e` | `#2dd4bf` | Links, icons, primary button fill |
| `brand-strong` | `#0b5d57` | `#5eead4` | Hover / active |
| `on-brand` | `#ffffff` | `#04211e` | Text/icon on a brand fill |
| `brand-soft` | `#e5f1ef` | `#10312d` | Tinted wells, monograms, chip hover |
| `brand-line` | `#bcdcd7` | `#1e4842` | Brand-tinted rings/borders |
| `accent*` | amber `#f59e0b` | `#fbbf24` | Featured badges & highlights (`accent-soft` / `accent-text` / `accent-line`) |
| `success*` | `#16a34a` | `#34d399` | "Open now", confirmations (`success-soft` / `success-text`) |
| `danger*` | `#dc2626` | `#f87171` | Errors, destructive (`danger-soft` / `danger-text`) |

All text pairs meet WCAG AA (≥4.5:1) on their intended surface.

### Typography

- **Display / headings:** Bricolage Grotesque (600–800), utility `font-display`. Restraint: hero, section headings, business names. Loaded via Bunny Fonts.
- **UI / body:** Inter (400–700), `font-sans`. Base 16px, line-height 1.6.
- **Numbers/stats:** `tabular-nums`.
- Scale: hero up to ~3.75rem; section h2 1.75–1.875rem; card/aside headings 1–1.125rem; eyebrow 0.75rem uppercase, tracking 0.16em (`.eyebrow`).

### Spacing, layout & elevation

- 4px base scale (4, 8, 12, 16, 20, 24, 32, 40, 48, 64, 80).
- Generous section rhythm: `py-16` mobile → `py-20`+ desktop; `mt-24` before the footer.
- Max content width 76rem (1216px); listing-detail reading column 45rem.
- Radius: `rounded-xl` (12px) cards, `rounded-lg` (8px) inputs/buttons, `rounded-2xl`/`3xl` hero & CTA panels, full pills/badges.
- Elevation: borders carry it. `--shadow-card` (hairline) at rest; `--shadow-lift` on hover only via `.hover-lift` (2px rise + brand-tinted edge).
- Glassmorphism: `.glass-header` — sticky, translucent `bg-canvas/80` + `backdrop-blur-md backdrop-saturate-150` + hairline border (opaque fallback via `@supports`).

### Component classes (`app.css`)

`.btn` + `.btn-primary` / `.btn-ghost`, `.field` (inputs/select/textarea), `.chip` + `.chip-active` (filters/pills), `.surface-card` (de-boxed card base), `.hover-lift`, `.eyebrow`, `.glass-header`.

Blade components: `x-public-layout`, `x-business-card`, `x-category-tile`, `x-category-icon`, `x-breadcrumbs`.

### Accessibility floor

Visible `:focus-visible` ring on every interactive element; skip-to-content link; inline SVG icons (no emoji) marked `aria-hidden`; form labels present (visually hidden where space is tight); `prefers-reduced-motion` neutralises transitions; verified responsive at 375 / 768 / 1024 / 1440.

---

## 6. Public pages (directory side)

| Route | Page | SEO focus |
|---|---|---|
| `/` | Home, search, popular categories, featured businesses, popular cities | Site-level; internal links out to categories/cities |
| `/category/{category}` | All businesses in a category, filterable by city | `ItemList` JSON-LD, canonical, paginated |
| `/{city}` | All businesses in a city, grouped by category | `ItemList`, canonical |
| `/{city}/{category}` | The money page, businesses of a category in a city (the bulk of the ~5,000 pages) | `ItemList` + `BreadcrumbList`, canonical, internal links to nearby cities/related categories |
| `/business/{slug}` | Individual listing, details, hours, map, photos, enquiry form | `LocalBusiness` JSON-LD, `BreadcrumbList`, canonical |
| `/search?q=` | Search results | `noindex` (thin/duplicate) |
| `/claim/{slug}` | Claim-this-listing entry point → registration | - |

All public pages: server-rendered Blade, anonymous, cacheable. Featured listings rank above free ones via `plans.priority_rank`.

---

## 7. Owner portal (Livewire side)

Behind auth. Each Livewire component is stateful and updates without full page reloads. This is the part that directly demonstrates the job's core requirement.

| Route | Component | What it does |
|---|---|---|
| `/dashboard` | `Portal\Dashboard` | Listing status, views this month, unread leads, plan summary |
| `/listing/edit` | `Portal\EditListing` | Live-editing form: name, description, category, address, hours, contact. Inline validation, auto-slug preview, save with toast. Alpine handles the hours editor and unsaved-changes guard. |
| `/listing/photos` | `Portal\Photos` | Upload, drag-to-reorder (Alpine), delete, respects plan's `max_photos` limit |
| `/billing` | `Portal\Billing` | Current plan, upgrade/downgrade via Stripe Checkout (Cashier), invoice history |
| `/leads` | `Portal\Leads` | Inbox of enquiries, mark read/unread, live unread counter |
| `/settings` | `Portal\Settings` | Account, password, notification prefs |

**Admin (role = admin):** `/admin`, approve pending listings, manage categories/cities, run imports, view all businesses. Can be Livewire too, or a lightweight Filament panel if you want to save time (note it as a deliberate shortcut).

---

## 8. Technical SEO implementation

This section is where the job's "technical SEO" bullet gets demonstrated concretely.

**Structured data (JSON-LD):**
- `LocalBusiness` on every listing page (name, address, geo, phone, hours, aggregateRating if reviews exist).
- `BreadcrumbList` on listing and city/category pages.
- `ItemList` on category and city/category index pages.
- Build these in a small `SeoService` / Blade component, not inline strings, so they're testable.

**Sitemaps:**
- Sitemap index at `/sitemap.xml` pointing to per-section sitemaps (`/sitemap-businesses.xml`, `/sitemap-categories.xml`, `/sitemap-cities.xml`), chunked at 50k URLs.
- Generated by an artisan command `sitemap:generate`, run on a schedule and after imports.

**Canonicals:** every public page emits a self-referencing canonical; paginated pages canonical to page 1 or use `rel=next/prev`. Search and filtered views are `noindex`.

**Redirects:** a `redirects` table + middleware. When a business or city slug changes, write a `301` from the old path. Old URLs never 404, the exact "redirects and canonicals" requirement.

**Internal linking:** listing pages link to their city and category; category/city pages link to related categories and nearby cities. This is what makes a programmatic site actually rank, and it's cheap to build with related-query scopes.

---

## 9. Cloudflare / caching

- **Page rule 1:** cache everything under public routes (`Cache-Control: public, max-age=...`), respect origin headers.
- **Page rule 2:** bypass cache when the session/auth cookie is present (portal + admin).
- **Cache purge on publish:** when a listing is published or edited, dispatch a job that calls the Cloudflare API to purge that listing's URL (and its category/city index). Keeps edits visible immediately without disabling caching globally.
- Set sensible `Cache-Control` and `ETag` at the Laravel level so behavior is correct even without Cloudflare in front (important for local/staging demos).

---

## 10. Bulk data import

- Artisan command `listings:import {file.csv}` seeds the directory: name, category, city, address, phone, website.
- Idempotent: re-running updates existing rows by a natural key (name + city) instead of duplicating.
- Geocodes each address to lat/lng via a queued job (batch-friendly, respects API rate limits).
- Ships with a sample CSV (~5,000 synthetic rows) so the site looks real on first boot. Document the data as synthetic.

This covers the job's "import and process content and data in bulk" line.

---

## 11. Build phases

Each phase ends in something demoable. Don't start a phase before the previous one runs.

### Phase 0: Foundation (setup)
- Laravel 12 app, MySQL, Tailwind + Vite, Breeze auth, Docker, deploy skeleton.
- Migrations for all tables in §4.
- **Done when:** app boots, migrates, deploys, login works.

### Phase 1: Public directory + data
- Seed data via the CSV importer (§10) + geocoding.
- Public routes and Blade pages (§6): home, category, city, city/category, listing detail.
- Featured-first ordering.
- **Done when:** ~5,000 pages are browsable and the directory feels real.

### Phase 2: Owner portal (the core)
- Claim flow → register → dashboard.
- Livewire components: EditListing, Photos, Dashboard, Leads (§7).
- Alpine for the hours editor, photo reorder, unsaved-changes guard.
- **Done when:** an owner can claim a listing and edit it live end to end.

### Phase 3: Payments + SEO
- Plans, Stripe Checkout via Cashier, plan gating (photo limits, featured placement).
- Full SEO layer (§8): JSON-LD, sitemaps, canonicals, redirects, internal linking.
- **Done when:** upgrading to Featured changes ranking, and structured data validates in Google's Rich Results test.

### Phase 4: Cloudflare, polish, deploy
- Cloudflare page rules + purge-on-publish job (§9).
- Dark mode, empty states, admin approval flow, mobile pass.
- Lighthouse/perf pass on public pages.
- **Done when:** live, cached, fast, and screen-share ready.

---

## 12. Testing

Keep it proportional. This is a portfolio build, not a bank.

- **Feature tests:** claim flow, listing edit saves, plan gating (a free user can't add a 6th photo), lead submission, redirect middleware fires on slug change.
- **Unit:** SEO builders output valid JSON-LD; sitemap chunking; import idempotency.
- **One correctness check that matters:** slug change must always create a working 301 (assert old path → 301 → new path). That's the thing that silently breaks SEO if it regresses.

---

## 13. Deliverables & talking points (for the application)

When screen-sharing, these are the moments that map to the job:

- Edit a listing in the portal and watch it update live → **Livewire + Alpine**.
- Open a `/{city}/{category}` page and view source → **programmatic pages + JSON-LD + canonicals**.
- Change a slug, hit the old URL → **301 redirect**.
- Show the Cloudflare cache HIT header on a public page, then a portal page bypassing it → **Cloudflare**.
- Run `listings:import` → **bulk data import**.
- Upgrade a plan in Stripe test mode → **third-party API + paid listings**.

---

## 14. Deliberate shortcuts (be honest about these)

- Admin panel may use Filament instead of hand-built Livewire to save time. Fine, note it.
- Reviews/ratings are stretch scope; stub `aggregateRating` only if reviews exist.
- Synthetic seed data, clearly labeled, same honesty stance as the Tally project.
- Single Stripe test-mode account; no real billing or dunning logic.

---

## 15. Out of scope

Multi-language content, a mobile app, real user reviews at scale, and a recommendation engine. The point is to prove the directory + Livewire portal + Cloudflare + SEO architecture, not to ship a startup.
