<?php

declare(strict_types=1);

namespace MunicipioQualityExtensions\Cards;

/** Move navigation attributes to the actual link and remove the old wrapper name. */
final class HeadingLink
{
    private const ATTRIBUTES = ['target', 'rel', 'download', 'hreflang', 'referrerpolicy'];

    public static function render(string $heading, string $href, array $attributes): string
    {
        $link = ' class="qx-card__primary-link" href="' . self::escape($href) . '"';
        foreach (self::ATTRIBUTES as $key) {
            if (is_scalar($attributes[$key] ?? null)) {
                $link .= ' ' . $key . '="' . self::escape((string) $attributes[$key]) . '"';
            }
        }
        return '<a' . $link . '>' . self::escape(CardLink::text($heading)) . '</a>';
    }

    public static function wrapperAttributes(array $attributes): array
    {
        return array_diff_key($attributes, array_flip([...self::ATTRIBUTES, 'href', 'aria-label', 'aria-labelledby']));
    }

    private static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
