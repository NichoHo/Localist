<?php

namespace App\Http\Middleware;

use App\Models\Redirect;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandleRedirects
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($response->getStatusCode() === 404 && $request->isMethod('GET')) {
            $redirect = Redirect::where('from_path', '/'.ltrim($request->path(), '/'))->first();
            if ($redirect) {
                return redirect($redirect->to_path, $redirect->status_code);
            }
        }

        return $response;
    }
}
