<?php

use App\Http\Middleware\HandleRedirects;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Prepend: 404s from route model binding are thrown inside SubstituteBindings,
        // so this must sit outside it to see the rendered 404 response.
        $middleware->web(prepend: [HandleRedirects::class]);
        // Behind Cloudflare + the host's proxy the throttles key on the visitor IP only if proxies are trusted.
        // ponytail: '*' trusts any hop; restrict to Cloudflare's IP list if the origin is ever reachable directly
        $middleware->trustProxies(at: '*');
        // Cached HTML carries another visitor's CSRF token; the beacon has no body worth protecting.
        $middleware->validateCsrfTokens(except: ['business/*/view']);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
