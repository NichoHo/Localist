@props(['title' => 'Localist | Find trusted local services', 'description' => 'Browse trusted local service businesses across Malaysia.', 'noindex' => false])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <meta name="description" content="{{ $description }}">
    <link rel="canonical" href="{{ url()->current() }}">
    @if ($noindex)
        <meta name="robots" content="noindex">
    @endif
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=bricolage-grotesque:600,700,800|inter:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-canvas font-sans text-ink antialiased">
    <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-lg focus:bg-brand focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-on-brand">Skip to content</a>

    <header class="glass-header">
        <div class="mx-auto flex max-w-[76rem] items-center gap-4 px-4 py-3.5 sm:px-6">
            <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-2.5">
                <span class="font-display text-lg font-bold tracking-tight text-ink">Localist</span>
            </a>

            <form action="{{ route('search') }}" method="get" class="relative hidden max-w-md flex-1 md:block">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="pointer-events-none absolute left-3 top-1/2 size-[1.05rem] -translate-y-1/2 text-ink-subtle" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m1.35-5.4a6.75 6.75 0 1 1-13.5 0 6.75 6.75 0 0 1 13.5 0Z" />
                </svg>
                <label for="header-search" class="sr-only">Search local services</label>
                <input id="header-search" type="search" name="q" value="{{ request('q') }}" placeholder="Search plumbers, electricians, cleaners…" class="field pl-10">
            </form>

            <nav class="ml-auto flex items-center gap-1 text-sm font-medium sm:gap-2">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-ghost">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="hidden rounded-lg px-3 py-2.5 text-ink-muted transition hover:text-brand sm:inline-flex">Log in</a>
                    <a href="{{ route('register') }}" class="btn btn-primary">List your business</a>
                @endauth
            </nav>
        </div>
    </header>

    <main id="main">
        {{ $slot }}
    </main>

    <footer class="mt-24 border-t border-line bg-canvas-2">
        <div class="mx-auto max-w-[76rem] px-4 py-16 sm:px-6">
            <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-4">
                <div class="lg:col-span-1">
                    <div class="flex items-center gap-2.5">
                        <span class="font-display text-lg font-bold tracking-tight text-ink">Localist</span>
                    </div>
                    <p class="mt-4 max-w-xs text-sm leading-relaxed text-ink-muted">Find and compare trusted local service businesses across Malaysia. A demo directory built with synthetic data.</p>
                </div>
                <div>
                    <p class="eyebrow">Popular categories</p>
                    <ul class="mt-4 space-y-2.5 text-sm">
                        @foreach (cache()->remember('footer.categories', 3600, fn () => \App\Models\Category::orderBy('name')->limit(8)->get()) as $cat)
                            <li><a href="{{ route('category', $cat) }}" class="text-ink-muted transition hover:text-brand">{{ $cat->name }}</a></li>
                        @endforeach
                    </ul>
                </div>
                <div>
                    <p class="eyebrow">Popular cities</p>
                    <ul class="mt-4 space-y-2.5 text-sm">
                        @foreach (cache()->remember('footer.cities', 3600, fn () => \App\Models\City::withCount('businesses')->orderByDesc('businesses_count')->limit(8)->get()) as $city)
                            <li><a href="{{ route('city', $city) }}" class="text-ink-muted transition hover:text-brand">{{ $city->name }}</a></li>
                        @endforeach
                    </ul>
                </div>
                <div>
                    <p class="eyebrow">For businesses</p>
                    <ul class="mt-4 space-y-2.5 text-sm">
                        <li><a href="{{ route('register') }}" class="text-ink-muted transition hover:text-brand">List your business</a></li>
                        <li><a href="{{ route('login') }}" class="text-ink-muted transition hover:text-brand">Owner login</a></li>
                    </ul>
                </div>
            </div>
            <div class="mt-12 flex flex-col gap-2 border-t border-line-subtle pt-6 text-sm text-ink-subtle sm:flex-row sm:items-center sm:justify-between">
                <p>&copy; {{ date('Y') }} Localist. Demo project — synthetic data.</p>
                <p>Built with Laravel, Livewire &amp; Tailwind.</p>
            </div>
        </div>
    </footer>
</body>
</html>
