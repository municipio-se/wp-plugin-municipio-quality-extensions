<?php

declare(strict_types=1);

namespace MunicipioQualityExtensions\Headings;

use DOMDocument;
use DOMXPath;

final class PostsMarkup
{
    public function transform(string $html): string
    {
        if ($html === '' || !class_exists(DOMDocument::class) || !class_exists(\WP_HTML_Tag_Processor::class)) {
            return $html;
        }
        $document = $this->parse($html);
        if ($document === null) {
            return $html;
        }
        $xpath = new DOMXPath($document);
        $nodes = $xpath->query('//h1|//h2|//h3|//h4|//h5|//h6');
        $headings = [];
        $targets = [];
        $labels = [];
        $scope = new PostsScope();
        foreach ($nodes as $index => $heading) {
            $headings[] = $heading->tagName;
            if (!$scope->isOwned($heading)) {
                continue;
            }
            if (preg_match('/^mod-posts-.+-label$/D', $heading->getAttribute('id'))) {
                $labels[] = $heading;
                continue;
            }
            if ($scope->isPostTitle($heading) && trim($heading->textContent) !== '') {
                $targets[] = $index;
            }
        }
        if (count($labels) > 1 || $labels !== [] && $labels[0]->tagName !== 'h2') {
            return $html;
        }
        $level = $labels === [] ? '2' : '3';
        return (new HeadingTags($html))->replace($headings, array_fill_keys($targets, $level));
    }

    private function parse(string $html): ?DOMDocument
    {
        $previous = libxml_use_internal_errors(true);
        libxml_clear_errors();
        try {
            $document = new DOMDocument();
            $loaded = $document->loadHTML(
                '<?xml encoding="UTF-8"><html><body>' . $html . '</body></html>',
                LIBXML_NONET,
            );
            // HTML4 diagnostics for HTML5 tags and bare URL ampersands do not
            // affect ownership. Source spans preserve those attributes verbatim.
            $errors = array_filter(libxml_get_errors(), static fn($error) => !in_array($error->code, [23, 801], true));
            return $loaded && $errors === [] ? $document : null;
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }
}
