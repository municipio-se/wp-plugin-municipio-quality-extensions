<?php

declare(strict_types=1);

namespace MunicipioQualityExtensions\Cards;

/** Keep action controls out of the heading-link conversion. */
final class CardLink
{
    public static function text(string $value): string
    {
        return trim(
            preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8')) ?? '',
        );
    }

    public static function hasAction(array $attributes): bool
    {
        foreach (array_keys($attributes) as $key) {
            if (
                preg_match(
                    '/^(?:on|data-)|^(?:role|tabindex|contenteditable|aria-haspopup|aria-controls|aria-expanded|formaction|type)$/i',
                    (string) $key,
                )
                && !in_array($key, ['data-component', 'data-uid', 'data-js-item-id', 'data-observe-resizes'], true)
            ) {
                return true;
            }
        }
        return false;
    }

    public static function validUrl(mixed $href): bool
    {
        return (
            is_string($href)
            && preg_match('~^(?:https?://|mailto:|tel:|/|\#|\?)~i', $href) === 1
            && preg_match('/[\x00-\x20]/', $href) === 0
        );
    }

    public static function buttonAttributes(array $button, array $attributes): array
    {
        foreach (['target', 'rel', 'download', 'hreflang', 'referrerpolicy'] as $key) {
            $value = $button[$key] ?? $button['attributeList'][$key] ?? null;
            if ($value !== null) {
                $attributes[$key] = $value;
            }
        }
        return $attributes;
    }
}
