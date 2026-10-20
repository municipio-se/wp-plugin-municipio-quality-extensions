<?php

declare(strict_types=1);

namespace MunicipioQualityExtensions\Headings;

use DOMElement;

final class AccordionScope
{
    public function label(DOMElement $heading, string $type): bool
    {
        if (!$this->moduleOwned($heading, $type) || $this->ancestor($heading, 'c-accordion') !== null) {
            return false;
        }
        if ($type === 'mod-posts') {
            return (bool) preg_match('/^mod-posts-.+-label$/D', $heading->getAttribute('id'));
        }
        return (
            $this->ancestor($heading, 'c-card__header') !== null
            || $this->ancestor($heading, 'u-margin__bottom--2') !== null
        );
    }

    public function moduleOwned(DOMElement $heading, string $type): bool
    {
        $modules = 0;
        for ($parent = $heading->parentNode; $parent instanceof DOMElement; $parent = $parent->parentNode) {
            if (
                in_array($parent->tagName, ['dialog', 'template'], true)
                || AccordionContent::hasClass($parent, 'c-modal')
            ) {
                return false;
            }
            if (preg_match_all(
                '/(?:^|\s)modularity-(mod-[a-z-]+)(?=\s|$)/',
                $parent->getAttribute('class'),
                $matches,
            )) {
                foreach ($matches[1] as $module) {
                    if ($module !== $type || ++$modules > 1) {
                        return false;
                    }
                }
            }
        }
        return true;
    }

    public function ancestor(DOMElement $element, string $class): ?DOMElement
    {
        for ($parent = $element->parentNode; $parent instanceof DOMElement; $parent = $parent->parentNode) {
            if (AccordionContent::hasClass($parent, $class)) {
                return $parent;
            }
        }
        return null;
    }
}
