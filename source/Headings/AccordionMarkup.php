<?php

declare(strict_types=1);

namespace MunicipioQualityExtensions\Headings;

use DOMXPath;

final class AccordionMarkup
{
    public function transform(string $html, string $type): string
    {
        $document = HeadingDocument::parse($html);
        if ($document === null || !in_array($type, ['mod-posts', 'mod-manualinput'], true)) {
            return $html;
        }
        $xpath = new DOMXPath($document);
        $scope = new AccordionScope();
        $nodes = iterator_to_array($xpath->query('//h1|//h2|//h3|//h4|//h5|//h6'));
        $labels = array_filter($nodes, fn($heading) => $scope->label($heading, $type));
        if (count($labels) > 1 || $labels !== [] && reset($labels)->tagName !== 'h2') {
            return $html;
        }
        $level = $labels === [] ? 2 : 3;
        $replacements = [];
        foreach ($nodes as $index => $heading) {
            if (!$scope->moduleOwned($heading, $type)) {
                continue;
            }
            $replacements[$index] = (new AccordionRow())->level($heading, $level, $xpath);
        }
        return (new HeadingTags($html))->replace(array_map(static fn($node) => $node->tagName, $nodes), $replacements);
    }
}
