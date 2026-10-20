<?php

declare(strict_types=1);

namespace MunicipioQualityExtensions\Headings;

use DOMElement;

final class PostsScope
{
    private const TITLES = [
        'c-card__heading' => 'c-card',
        'c-block__heading' => 'c-block',
        'c-box__heading' => 'c-box',
        'c-newsitem__heading' => 'c-newsitem',
        'c-segment__title' => 'c-segment',
    ];

    public function isOwned(DOMElement $heading): bool
    {
        $modules = 0;
        for ($parent = $heading->parentNode; $parent instanceof DOMElement; $parent = $parent->parentNode) {
            if (in_array($parent->tagName, ['dialog', 'template'], true)) {
                return false;
            }
            $classes = $this->classes($parent);
            if (
                array_intersect($classes, [
                    'c-modal',
                    'c-accordion',
                    'c-typography',
                    'c-card__content',
                    'c-card__body-content',
                    'c-block__content',
                    'c-box__content',
                    'c-newsitem__content',
                    'c-segment__text',
                ]) !== []
            ) {
                return false;
            }
            foreach (preg_grep('/^modularity-mod-[a-z-]+$/D', $classes) as $class) {
                if ($class !== 'modularity-mod-posts' || ++$modules > 1) {
                    return false;
                }
            }
        }
        return true;
    }

    public function isPostTitle(DOMElement $heading): bool
    {
        $owners = array_intersect_key(self::TITLES, array_flip($this->classes($heading)));
        $owner = reset($owners);
        if ($owner === false && preg_match('/^post-.+-.+-title$/D', $heading->getAttribute('id'))) {
            $owner = 'c-collection__item';
        }
        if ($owner === false) {
            return false;
        }
        for ($parent = $heading->parentNode; $parent instanceof DOMElement; $parent = $parent->parentNode) {
            $classes = $this->classes($parent);
            // Segment's card layout deliberately nests its title inside a Card.
            if ($owner === 'c-segment' && in_array('c-segment__content-card', $classes, true)) {
                continue;
            }
            if (in_array($owner, $classes, true)) {
                return true;
            }
            // A different presentation component owns this subtree.
            if (array_intersect($classes, [...array_values(self::TITLES), 'c-collection__item']) !== []) {
                return false;
            }
        }
        return false;
    }

    private function classes(DOMElement $element): array
    {
        return explode(' ', str_replace(["\t", "\n", "\r", "\f"], ' ', $element->getAttribute('class')));
    }
}
