<?php

namespace App\Support;

use Illuminate\Support\Facades\Route;

class LoginUrl
{
    /** @param  array<string, mixed>  $parameters */
    public static function to(array $parameters = []): string
    {
        if (Route::has('login')) {
            return route('login', $parameters);
        }

        $query = $parameters !== [] ? '?'.http_build_query($parameters) : '';

        return url('/login'.$query);
    }
}
