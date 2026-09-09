<?php

declare(strict_types=1);

namespace MunicipioQualityExtensions\Cards;

/** Context identifies a verified caller; Segment and custom slots keep their own rendering. */
final class CardEligibility
{
    private const CONTEXTS = ['module.posts.index', 'module.manual-input.card', 'module.navigation.cards'];

    public static function supports(array $data): bool
    {
        // Component Library appends its own context before dispatching the data filter.
        $contexts = array_values(array_diff((array) ($data['context'] ?? []), ['component.card']));
        $heading = $data['heading'] ?? null;
        return (
            count($contexts) === 1
            && in_array(reset($contexts), self::CONTEXTS, true)
            && !(bool) ($data['collapsible'] ?? false)
            && (string) ($data['slot'] ?? '') === ''
            && is_string($heading)
            && CardLink::text($heading) !== ''
            && preg_match(
                '/<(?:a|button|input|select|textarea|summary|iframe|video|audio)\b|\b(?:tabindex|contenteditable)\s*=/i',
                $heading,
            ) === 0
        );
    }
}
