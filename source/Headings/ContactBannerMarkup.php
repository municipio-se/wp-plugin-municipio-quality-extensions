<?php

declare(strict_types=1);

namespace MunicipioQualityExtensions\Headings;

use DOMElement;
use DOMXPath;

final class ContactBannerMarkup
{
    public function transform(string $html): string
    {
        $document = HeadingDocument::parse($html);
        if ($document === null) {
            return $html;
        }
        $nodes = iterator_to_array((new DOMXPath($document))->query('//h1|//h2|//h3|//h4|//h5|//h6'));
        $levels = [];
        $remove = [];
        foreach ($nodes as $index => $heading) {
            $root = ContactBannerScope::root($heading);
            if ($root === null || ($level = $this->level($nodes, $root)) === null) {
                continue;
            }
            if (
                AccordionContent::hasClass($heading, 'mod-contact-banner__hours-title')
                && AccordionContentScope::owned($heading, $root)
            ) {
                $levels[$index] = (string) $level;
                continue;
            }
            $replacement = (new ContactBannerCard())->level($heading, $nodes, $root, $level);
            if ($replacement === '') {
                $remove[] = $index;
                continue;
            }
            $levels[$index] = $replacement;
        }
        $html = (new HeadingTags($html))->replace(array_map(static fn($node) => $node->tagName, $nodes), $levels);
        return (new EmptyHeadingTags($html))->remove($remove);
    }

    private function level(array $nodes, DOMElement $root): ?int
    {
        $labels = array_filter(
            $nodes,
            static fn($node) => (
                ContactBannerScope::root($node) === $root
                && AccordionContent::hasClass($node, 'mod-contact-banner__title')
                && AccordionContentScope::owned($node, $root)
            ),
        );
        if (count($labels) > 1 || $labels !== [] && reset($labels)->tagName !== 'h2') {
            return null;
        }
        return $labels === [] ? 2 : 3;
    }
}
