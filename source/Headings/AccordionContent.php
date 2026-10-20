<?php

declare(strict_types=1);

namespace MunicipioQualityExtensions\Headings;

use DOMElement;
use DOMXPath;

final class AccordionContent
{
    public static function hasClass(DOMElement $element, string $class): bool
    {
        return in_array($class, preg_split('/\s+/', trim($element->getAttribute('class'))), true);
    }

    public function prepare(string $html, string $depthAttribute = 'data-qx-accordion-depth'): ?string
    {
        if ($html === '') {
            return '';
        }
        $document = HeadingDocument::parse($html);
        if ($document === null) {
            return null;
        }
        $levels = [];
        foreach ((new DOMXPath($document))->query('//h1|//h2|//h3|//h4|//h5|//h6') as $index => $heading) {
            if (AccordionContentScope::owned($heading)) {
                $levels[$index] = (int) substr($heading->tagName, 1);
            }
        }
        if ($levels === []) {
            return $html;
        }
        $base = min($levels);
        $tags = new \WP_HTML_Tag_Processor($html);
        $index = 0;
        while ($tags->next_tag()) {
            if (!preg_match('/^H[1-6]$/D', $tags->get_tag() ?? '')) {
                continue;
            }
            if (($levels[$index] ?? null) !== null) {
                // Retain relative depth in rendered cache fragments: H6 clipping
                // must not erase it when the same markup is processed again.
                $tags->set_attribute($depthAttribute, (string) ($levels[$index] - $base));
            }
            $index++;
        }
        return $tags->get_updated_html();
    }

    public function prepareRow(array $row, string $source): array
    {
        $content = $this->prepare($source);
        if ($content !== null) {
            $row['content'] = $content;
            $row['attributeList']['data-qx-accordion'] = '1';
        }
        return $row;
    }
}
