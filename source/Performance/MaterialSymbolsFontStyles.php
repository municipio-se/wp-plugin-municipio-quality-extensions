<?php

declare(strict_types=1);

namespace MunicipioQualityExtensions\Performance;

final class MaterialSymbolsFontStyles
{
    public function filterTag(string $html, string $handle, string $href): string
    {
        if (is_admin() || !str_starts_with($handle, 'fonts-material-')) {
            return $html;
        }
        $root = realpath(get_template_directory() . '/assets/dist/fonts/material');
        $urlRoot = parse_url(get_template_directory_uri() . '/assets/dist/fonts/material/', PHP_URL_PATH);
        $path = parse_url($href, PHP_URL_PATH);
        if (!$root || !is_string($path) || !is_string($urlRoot) || !str_starts_with($path, $urlRoot)) {
            return $html;
        }
        $file = realpath($root . '/' . rawurldecode(substr($path, strlen($urlRoot))));
        if (!$file || !str_starts_with($file, $root . '/') || !is_readable($file)) {
            return $html;
        }
        $source = file_get_contents($file);
        $css = is_string($source) ? self::withoutFontFace($source) : null;

        // Preserve upstream's spacing and sizing. Unknown stylesheet formats keep
        // the original link rather than silently losing either layout or icons.
        return $css === null ? $html : '<style>' . $css . '</style>';
    }

    public static function withoutFontFace(string $css): ?string
    {
        $count = 0;
        $result = preg_replace('/@font-face\s*\{[^{}]*\}/i', '', $css, -1, $count);
        if (
            $result === null
            || $count === 0
            || stripos($result, '@font-face') !== false
            || stripos($result, '</style') !== false
        ) {
            return null;
        }
        return $result;
    }
}
