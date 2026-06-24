<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GeoLocationService
{
    public function resolve(?string $ip): string
    {
        if ($ip === null || $ip === '' || $this->isPrivateIp($ip)) {
            return 'Ubicación no disponible';
        }

        try {
            $response = Http::timeout(2)
                ->get("http://ip-api.com/json/{$ip}", [
                    'fields' => 'status,country,city',
                ]);

            if ($response->successful() && $response->json('status') === 'success') {
                $city = (string) $response->json('city', '');
                $country = (string) $response->json('country', '');
                $label = trim("{$city}, {$country}", ', ');

                return $label !== '' ? $label : 'Ubicación no disponible';
            }
        } catch (\Throwable) {
            // Sin conexión o servicio no disponible.
        }

        return 'Ubicación no disponible';
    }

    private function isPrivateIp(string $ip): bool
    {
        return filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE,
        ) === false;
    }
}
