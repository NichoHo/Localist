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
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700|jetbrains-mono:500,600" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-canvas font-sans text-ink antialiased">
    <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded focus:bg-accent focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-on-accent">Skip to content</a>

    <header x-data="{ 
                atTop: true, 
                show: true, 
                lastY: 0,
                handleScroll() {
                    let y = window.scrollY;
                    this.atTop = y <= 0;
                    if (y > this.lastY && y > 50) {
                        this.show = false;
                    } else if (y < this.lastY) {
                        this.show = true;
                    }
                    this.lastY = y;
                }
            }"
            @scroll.window="handleScroll()"
            class="header-bar trade-band transition-transform duration-300"
            :class="{ '-translate-y-full': !show, 'translate-y-0': show, 'shadow-md': !atTop }">
        <div class="mx-auto flex max-w-[76rem] items-center gap-4 px-4 py-3 sm:px-6">
            <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-2.5">
                <span class="font-display text-lg font-bold tracking-tight text-header-ink">Localist</span>
            </a>

            <form action="{{ route('search') }}" method="get" class="relative hidden max-w-md flex-1 md:block">
                <label for="header-search" class="sr-only">Search local services</label>
                <input id="header-search" type="search" name="q" value="{{ request('q') }}" placeholder="Search plumbers, electricians, cleaners…"
                    class="w-full rounded border border-header-line bg-black/20 px-3.5 py-2 text-sm text-header-ink placeholder:text-header-ink/45 transition focus:border-accent focus:outline-none">
            </form>

            <nav class="ml-auto flex items-center gap-1 text-sm font-medium sm:gap-2">
                @auth
                    <a href="{{ route('dashboard') }}" class="rounded px-3 py-2 text-header-ink/80 transition hover:text-header-ink">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="hidden rounded px-3 py-2 text-header-ink/80 transition hover:text-header-ink sm:inline-flex">Log in</a>
                    <a href="{{ route('register') }}" class="btn btn-accent">
                        List your business
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="size-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
                    </a>
                @endauth
            </nav>
        </div>
    </header>

    <main id="main">
        {{ $slot }}
    </main>

    <footer class="trade-band mt-24">
        <div class="mx-auto max-w-[76rem] px-4 py-16 sm:px-6">
            <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-4">
                <div class="lg:col-span-1">
                    <div class="flex items-center gap-2.5">
                        <span class="font-display text-lg font-bold tracking-tight text-header-ink">Localist</span>
                    </div>
                    <p class="mt-4 max-w-xs text-sm leading-relaxed text-header-ink/60">Find and compare trusted local service businesses across Malaysia. A demo directory built with synthetic data.</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.14em] text-header-ink/50">Popular categories</p>
                    <ul class="mt-4 space-y-2.5 text-sm">
                        @foreach (cache()->remember('footer.categories', 3600, fn () => \App\Models\Category::orderBy('name')->limit(8)->get()) as $cat)
                            <li><a href="{{ route('category', $cat) }}" class="text-header-ink/70 transition hover:text-header-ink">{{ $cat->name }}</a></li>
                        @endforeach
                    </ul>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.14em] text-header-ink/50">Popular cities</p>
                    <ul class="mt-4 space-y-2.5 text-sm">
                        @foreach (cache()->remember('footer.cities', 3600, fn () => \App\Models\City::withCount('businesses')->orderByDesc('businesses_count')->limit(8)->get()) as $city)
                            <li><a href="{{ route('city', $city) }}" class="text-header-ink/70 transition hover:text-header-ink">{{ $city->name }}</a></li>
                        @endforeach
                    </ul>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.14em] text-header-ink/50">For businesses</p>
                    <ul class="mt-4 space-y-2.5 text-sm">
                        <li><a href="{{ route('register') }}" class="text-header-ink/70 transition hover:text-header-ink">List your business</a></li>
                        <li><a href="{{ route('login') }}" class="text-header-ink/70 transition hover:text-header-ink">Owner login</a></li>
                    </ul>
                </div>
            </div>
            <div class="mt-12 flex flex-col gap-2 border-t border-header-line pt-6 text-sm text-header-ink/45 sm:flex-row sm:items-center sm:justify-between">
                <p>&copy; {{ date('Y') }} Localist. Demo project — synthetic data.</p>
                <p>Built with Laravel, Livewire &amp; Tailwind.</p>
            </div>
        </div>
    </footer>
    @livewireScripts
</body>
</html>
