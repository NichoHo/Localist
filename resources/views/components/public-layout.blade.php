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
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white dark:bg-gray-950 font-sans text-gray-900 dark:text-gray-100 antialiased">
    <header class="border-b border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950">
        <div class="mx-auto flex max-w-[75rem] items-center gap-6 px-4 py-4">
            <a href="{{ route('home') }}" class="shrink-0 text-xl font-bold text-teal-700 dark:text-teal-400">Localist</a>
            <form action="{{ route('search') }}" method="get" class="hidden max-w-md flex-1 sm:block">
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Search plumbers, electricians, cleaners…"
                    class="w-full rounded-md border-gray-300 dark:border-gray-700 text-sm focus:border-teal-600 focus:ring-teal-600 dark:bg-gray-900 dark:text-gray-100 dark:placeholder-gray-500">
            </form>
            <nav class="ml-auto flex items-center gap-4 text-sm font-medium">
                @auth
                    <a href="{{ route('dashboard') }}" class="text-gray-600 dark:text-gray-300 hover:text-teal-700 dark:hover:text-teal-400">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="text-gray-600 dark:text-gray-300 hover:text-teal-700 dark:hover:text-teal-400">Log in</a>
                    <a href="{{ route('register') }}" class="rounded-md bg-teal-700 px-3 py-2 text-white hover:bg-teal-800">List your business</a>
                @endauth
            </nav>
        </div>
    </header>

    <main>
        {{ $slot }}
    </main>

    <footer class="mt-16 border-t border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-900">
        <div class="mx-auto grid max-w-[75rem] gap-8 px-4 py-12 sm:grid-cols-3">
            <div>
                <p class="text-lg font-bold text-teal-700 dark:text-teal-400">Localist</p>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Find trusted local service businesses across Malaysia. Demo directory with synthetic data.</p>
            </div>
            <div>
                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">Popular categories</p>
                <ul class="mt-3 space-y-2 text-sm">
                    @foreach (cache()->remember('footer.categories', 3600, fn () => \App\Models\Category::orderBy('name')->limit(8)->get()) as $cat)
                        <li><a href="{{ route('category', $cat) }}" class="text-gray-600 dark:text-gray-300 hover:text-teal-700 dark:hover:text-teal-400">{{ $cat->name }}</a></li>
                    @endforeach
                </ul>
            </div>
            <div>
                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">Popular cities</p>
                <ul class="mt-3 space-y-2 text-sm">
                    @foreach (cache()->remember('footer.cities', 3600, fn () => \App\Models\City::withCount('businesses')->orderByDesc('businesses_count')->limit(8)->get()) as $city)
                        <li><a href="{{ route('city', $city) }}" class="text-gray-600 dark:text-gray-300 hover:text-teal-700 dark:hover:text-teal-400">{{ $city->name }}</a></li>
                    @endforeach
                </ul>
            </div>
        </div>
    </footer>
</body>
</html>
