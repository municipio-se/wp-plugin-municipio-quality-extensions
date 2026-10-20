<?php

declare(strict_types=1);

namespace MunicipioQualityExtensions\Headings;

use DOMElement;

final class ContactBannerCard
{
    /** An empty replacement removes only an empty template heading; null preserves it. */
    public function level(DOMElement $heading, array $nodes, DOMElement $root, int $level): ?string
    {
        $card = ContactBannerScope::card($heading);
        if ($card === null || ContactBannerScope::root($card) !== $root) {
            return null;
        }
        $titles = array_filter(
            $nodes,
            static fn($node) => ContactBannerScope::card($node) === $card && ContactBannerScope::title($node, $card),
        );
        if (count($titles) > 1) {
            return null;
        }
        $title = $titles === [] ? null : reset($titles);
        $hasTitle = $title !== null && !ContactBannerScope::blank($title);
        if ($heading === $title) {
            return $hasTitle ? (string) $level : '';
        }
        $body = ContactBannerScope::ancestor($heading, 'c-card__body');
        $depth = $heading->getAttribute('data-qx-contact-depth');
        if (
            $body !== null
            && ContactBannerScope::ancestor($body, 'c-card') === $card
            && AccordionContentScope::owned($heading, $body)
            && preg_match('/^[0-5]$/D', $depth)
        ) {
            return (string) min(6, $level + (int) $hasTitle + (int) $depth);
        }
        return null;
    }
}
