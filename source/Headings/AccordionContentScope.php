<?php

declare(strict_types=1);

namespace MunicipioQualityExtensions\Headings;

use DOMElement;

final class AccordionContentScope
{
    public static function foreign(DOMElement $element): bool
    {
        return (
            in_array($element->tagName, ['dialog', 'template'], true)
            || preg_match('/(?:^|\s)modularity-mod-/', $element->getAttribute('class'))
            || preg_match(
                '/(?:^|\s)c-(?:card|block|box|newsitem|segment|collection|accordion|modal)(?=\s|$)/',
                $element->getAttribute('class'),
            )
        );
    }

    public static function owned(DOMElement $heading, ?DOMElement $boundary = null): bool
    {
        for (
            $parent = $heading->parentNode;
            $parent instanceof DOMElement && $parent !== $boundary;
            $parent = $parent->parentNode
        ) {
            if (self::foreign($parent)) {
                return false;
            }
        }
        return true;
    }
}
