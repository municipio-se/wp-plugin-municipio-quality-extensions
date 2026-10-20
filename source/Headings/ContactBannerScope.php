<?php

declare(strict_types=1);

namespace MunicipioQualityExtensions\Headings;

use DOMElement;

final class ContactBannerScope
{
    public static function ancestor(DOMElement $node, string $class): ?DOMElement
    {
        for ($parent = $node->parentNode; $parent instanceof DOMElement; $parent = $parent->parentNode) {
            if (AccordionContent::hasClass($parent, $class)) {
                return $parent;
            }
        }
        return null;
    }

    public static function root(DOMElement $node): ?DOMElement
    {
        for ($parent = $node->parentNode; $parent instanceof DOMElement; $parent = $parent->parentNode) {
            if (
                AccordionContent::hasClass($parent, 'mod-contact-banner')
                && $parent->getAttribute('id') === 'contact'
            ) {
                return $parent;
            }
            if (preg_match('/(?:^|\s)modularity-mod-/', $parent->getAttribute('class'))) {
                return null;
            }
        }
        return null;
    }

    public static function card(DOMElement $node): ?DOMElement
    {
        $card = self::ancestor($node, 'c-card');
        return $card !== null && preg_match('/(?:^|\s)card-item-\d+(?:\s|$)/', $card->getAttribute('class'))
            ? $card
            : null;
    }

    public static function title(DOMElement $heading, DOMElement $card): bool
    {
        // ACF blocks can render the template without a module post ID.
        $header = self::ancestor($heading, 'c-card__header');
        return (
            $header !== null
            && self::ancestor($header, 'c-card') === $card
            && AccordionContentScope::owned($heading, $header)
            && preg_match('/^mod-contactbanner-.*-\d+$/D', $heading->getAttribute('id')) === 1
        );
    }

    public static function blank(DOMElement $heading): bool
    {
        return preg_match('/^[\s\p{Z}]*$/uD', $heading->textContent) === 1 && $heading->childElementCount === 0;
    }
}
