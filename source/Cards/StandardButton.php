<?php

declare(strict_types=1);

namespace MunicipioQualityExtensions\Cards;

final class StandardButton
{
    public static function select(mixed $buttons, string $standardText): ?array
    {
        if (!is_array($buttons) || count($buttons) !== 1) {
            return null;
        }
        $button = reset($buttons);
        if (
            !is_array($button)
            || CardLink::hasAction($button)
            || !is_array($button['attributeList'] ?? [])
            || CardLink::hasAction($button['attributeList'] ?? [])
        ) {
            return null;
        }
        $text = CardLink::text((string) ($button['text'] ?? ''));
        return $text === '' || $text === CardLink::text($standardText) ? $button : null;
    }
}
