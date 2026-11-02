@props(['title' => 'Localist | Find trusted local businesses', 'description' => 'Browse trusted local businesses across Malaysia.', 'noindex' => false])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>{{ $title }}</title>
    <meta name="description" content="{{ $description }}">
    <link rel="canonical" href="{{ url()->current() }}">
    @if ($noindex)
        <meta name="robots" content="noindex">
    @endif
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=albert-sans:400,500,600,700,800" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-canvas pb-24 font-sans text-ink antialiased lg:pb-0">
    <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-full focus:bg-accent focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-on-accent">Skip to content</a>

    <header class="sticky top-0 z-40 border-b border-line bg-canvas/90 backdrop-blur">
        <div class="mx-auto flex max-w-[76rem] items-center gap-4 px-4 py-3 sm:px-6 lg:gap-8 lg:py-3.5">
            <a href="{{ route('home') }}" class="shrink-0 font-display text-xl font-bold tracking-tight text-ink lg:text-2xl">Localist</a>

            <form action="{{ route('search') }}" method="get" class="relative hidden max-w-md flex-1 md:block">
                <label for="header-search" class="sr-only">Search local services</label>
                <x-icon name="search" class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-ink-subtle" />
                <input id="header-search" type="search" name="q" value="{{ request('q') }}" placeholder="Search restaurants, cafes, salons…"
                    class="w-full rounded-full border border-line-strong bg-surface py-2 pl-10 pr-4 text-sm text-ink placeholder:text-ink-subtle transition focus:border-ink focus:outline-none focus:ring-2 focus:ring-ink/10">
            </form>

            <nav class="ml-auto flex items-center gap-1 text-sm font-medium sm:gap-2">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-ghost">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="hidden rounded-full px-4 py-2 text-ink-muted transition-colors hover:text-ink sm:inline-flex">Log in</a>
                    <a href="{{ route('register') }}" class="btn btn-primary">
                        List your business
                    </a>
                @endauth
            </nav>
        </div>
    </header>

    <main id="main">
        {{ $slot }}
    </main>

    <footer class="mt-20 border-t border-line bg-surface lg:mt-28">
        <div class="mx-auto max-w-[76rem] px-4 py-12 sm:px-6 lg:py-16">
            <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <span class="font-display text-xl font-bold tracking-tight text-ink">Localist</span>
                    <p class="mt-4 max-w-xs text-sm leading-relaxed text-ink-muted">Find and compare local businesses across Malaysia. A portfolio project built on real, openly-licensed listing data; most listings are unclaimed until their owner steps in.</p>
                </div>
                <div>
                    <p class="eyebrow">Popular categories</p>
                    <ul class="mt-4 space-y-2.5 text-sm">
                        @foreach (cache()->remember('footer.categories', 3600, fn () => \App\Models\Category::orderBy('name')->limit(8)->get()) as $cat)
                            <li><a href="{{ route('category', $cat) }}" class="text-ink-muted transition-colors hover:text-ink">{{ $cat->name }}</a></li>
                        @endforeach
                    </ul>
                </div>
                <div>
                    <p class="eyebrow">Popular cities</p>
                    <ul class="mt-4 space-y-2.5 text-sm">
                        @foreach (cache()->remember('footer.cities', 3600, fn () => \App\Models\City::withCount('businesses')->orderByDesc('businesses_count')->limit(8)->get()) as $city)
                            <li><a href="{{ route('city', $city) }}" class="text-ink-muted transition-colors hover:text-ink">{{ $city->name }}</a></li>
                        @endforeach
                    </ul>
                </div>
                <div>
                    <p class="eyebrow">For businesses</p>
                    <ul class="mt-4 space-y-2.5 text-sm">
                        <li><a href="{{ route('register') }}" class="text-ink-muted transition-colors hover:text-ink">List your business</a></li>
                        <li><a href="{{ route('login') }}" class="text-ink-muted transition-colors hover:text-ink">Owner login</a></li>
                    </ul>
                </div>
            </div>
            <div class="mt-12 flex flex-col gap-2 border-t border-line pt-6 text-sm text-ink-subtle sm:flex-row sm:items-center sm:justify-between">
                <p>&copy; {{ date('Y') }} Localist. Portfolio project, real open-data listings.</p>
                <p>Built with Laravel, Livewire &amp; Tailwind.</p>
            </div>
        </div>
    </footer>

    {{-- Mobile: a page can supply its own sticky action bar (the listing page does); otherwise the tab bar. --}}
    @isset($actions)
        {{ $actions }}
    @else
        @php
            $tabs = [
                ['Explore', route('home'), 'home', request()->routeIs('home', 'category', 'city', 'city.category', 'business')],
                ['Search', route('search'), 'search', request()->routeIs('search')],
                auth()->check()
                    ? ['Dashboard', route('dashboard'), 'dashboard', false]
                    : ['List', route('register'), 'plus', request()->routeIs('register')],
                auth()->check()
                    ? ['Account', route('profile.edit'), 'user', false]
                    : ['Log in', route('login'), 'user', request()->routeIs('login')],
            ];
        @endphp
        <nav class="tabbar grid-cols-4" aria-label="Main">
            @foreach ($tabs as [$label, $href, $icon, $active])
                <a href="{{ $href }}" class="tabbar-item" @if ($active) aria-current="page" @endif>
                    <span class="tabbar-icon"><x-icon :name="$icon" class="size-5" /></span>
                    {{ $label }}
                </a>
            @endforeach
        </nav>
    @endisset
    @livewireScripts
</body>
</html>
