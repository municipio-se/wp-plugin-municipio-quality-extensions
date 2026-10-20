<?php

declare(strict_types=1);

namespace MunicipioQualityExtensions\Headings;

use DOMElement;
use DOMXPath;

final class AccordionRow
{
    public function level(DOMElement $heading, int $level, DOMXPath $xpath): ?string
    {
        $scope = new AccordionScope();
        $row = $scope->ancestor($heading, 'c-accordion__section');
        if ($row === null || $row->getAttribute('data-qx-accordion') !== '1') {
            return null;
        }
        // A nested accordion is an independent component.
        if ($scope->ancestor($row, 'c-accordion__section') !== null) {
            return null;
        }
        $titles = $xpath->query(
            './/*[contains(concat(" ", normalize-space(@class), " "), " c-accordion__button-column ")]',
            $row,
        );
        $titles = array_filter(
            iterator_to_array($titles),
            fn($node) => (
                preg_match('/^h[1-6]$/D', $node->tagName)
                && $scope->ancestor($node, 'c-accordion__section') === $row
                && $scope->ancestor($node, 'c-accordion__content') === null
                && trim($node->textContent) !== ''
            ),
        );
        if (count($titles) !== 1) {
            return null;
        }
        if ($heading === reset($titles)) {
            return (string) $level;
        }
        $content = $scope->ancestor($heading, 'c-accordion__content');
        $depth = $heading->getAttribute('data-qx-accordion-depth');
        if ($content !== null && AccordionContentScope::owned($heading, $content) && preg_match('/^[0-5]$/D', $depth)) {
            return (string) min(6, $level + 1 + (int) $depth);
        }
        return null;
    }
}
