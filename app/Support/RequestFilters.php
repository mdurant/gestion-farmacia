<?php

namespace App\Support;

use Illuminate\Http\Request;

class RequestFilters
{
    public static function optionalBoolean(Request $request, string $key): ?bool
    {
        if (! $request->has($key)) {
            return null;
        }

        $value = $request->input($key);

        if ($value === null || $value === '') {
            return null;
        }

        return $request->boolean($key);
    }

    public static function optionalString(Request $request, string $key): ?string
    {
        $value = trim($request->string($key)->toString());

        return $value !== '' ? $value : null;
    }

    public static function optionalInteger(Request $request, string $key): ?int
    {
        if (! $request->filled($key)) {
            return null;
        }

        return $request->integer($key);
    }
}
