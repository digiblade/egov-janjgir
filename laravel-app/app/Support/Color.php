<?php

namespace App\Support;

final class Color
{
    public static function hex(mixed $value, string $fallback): string
    {
        return is_string($value) && preg_match('/\A#[0-9a-fA-F]{6}\z/D', $value) === 1
            ? strtoupper($value)
            : strtoupper($fallback);
    }

    public static function readableText(string $background, mixed $preferred): string
    {
        $background = self::hex($background, '#FFFFFF');
        $preferred = self::hex($preferred, '#102D55');

        if (self::contrastRatio($background, $preferred) >= 4.5) {
            return $preferred;
        }

        $dark = '#102D55';
        $light = '#FFFFFF';

        return self::contrastRatio($background, $dark) >= self::contrastRatio($background, $light)
            ? $dark
            : $light;
    }

    private static function contrastRatio(string $first, string $second): float
    {
        $firstLuminance = self::luminance($first);
        $secondLuminance = self::luminance($second);

        return (max($firstLuminance, $secondLuminance) + 0.05)
            / (min($firstLuminance, $secondLuminance) + 0.05);
    }

    private static function luminance(string $color): float
    {
        $channels = array_map(
            static fn (int $channel): float => ($channel / 255) <= 0.04045
                ? ($channel / 255) / 12.92
                : ((($channel / 255) + 0.055) / 1.055) ** 2.4,
            sscanf(substr(self::hex($color, '#FFFFFF'), 1), '%2x%2x%2x'),
        );

        return (0.2126 * $channels[0]) + (0.7152 * $channels[1]) + (0.0722 * $channels[2]);
    }
}
