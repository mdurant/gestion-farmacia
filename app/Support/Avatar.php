<?php

namespace App\Support;

final class Avatar
{
    /** @var list<int> */
    private const HUES = [220, 260, 300, 340, 12, 24, 180, 200, 145, 95];

    public static function initials(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name)) ?: [];
        $parts = array_values(array_filter($parts, fn (string $part): bool => $part !== ''));

        if (count($parts) >= 2) {
            return strtoupper(substr($parts[0], 0, 1).substr($parts[1], 0, 1));
        }

        return strtoupper(substr($name, 0, min(2, strlen($name))));
    }

    /** @return array{background: string, color: string} */
    public static function colors(string $seed): array
    {
        $hue = self::HUES[crc32($seed) % count(self::HUES)];

        return [
            'background' => "hsl({$hue} 72% 90%)",
            'color' => "hsl({$hue} 48% 34%)",
        ];
    }
}
