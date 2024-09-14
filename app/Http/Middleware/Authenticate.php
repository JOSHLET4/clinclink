<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        // Si la solicitud espera una respuesta JSON (usualmente en una API), devuelve un error 401 con un mensaje personalizado.
        if ($request->expectsJson()) {
            // Devolvemos una respuesta JSON indicando que el usuario no está autenticado.
            return response()->json(['error' => 'No estás autenticado. Por favor, inicia sesión.'], 401);
        }

        // Si no es una solicitud de API, redirige a la página de login (usualmente para aplicaciones web)
        return route('login');
    }
}