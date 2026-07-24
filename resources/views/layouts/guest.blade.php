<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Localist') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=bricolage-grotesque:600,700,800|inter:400,500,600,700" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-canvas font-sans text-ink antialiased">
        <div class="flex min-h-screen flex-col items-center justify-center px-4 py-12">
            <a href="{{ route('home') }}" class="font-display text-2xl font-bold tracking-tight text-ink">Localist</a>

            <div class="mt-8 w-full max-w-md">
                <div class="surface-card p-6 shadow-card sm:p-8">
                    {{ $slot }}
                </div>
                <p class="mt-6 text-center text-sm text-ink-muted">
                    <a href="{{ route('home') }}" class="transition hover:text-brand">&larr; Back to directory</a>
                </p>
            </div>
        </div>
    </body>
</html>
