<?php

declare(strict_types=1);

namespace MunicipioQualityExtensions\Headings;

use DOMDocument;

final class HeadingDocument
{
    public static function parse(string $html): ?DOMDocument
    {
        if ($html === '' || !class_exists(DOMDocument::class) || !class_exists(\WP_HTML_Tag_Processor::class)) {
            return null;
        }
        $previous = libxml_use_internal_errors(true);
        libxml_clear_errors();
        try {
            $document = new DOMDocument();
            // Component Library 5.10.0 emits a stray quote after this exact
            // attribute. Normalize only the parsing copy; source bytes remain
            // untouched and unrelated malformed markup still fails closed.
            $parseHtml = str_replace('data-component="accordion__item"">', 'data-component="accordion__item">', $html);
            $loaded = $document->loadHTML(
                '<?xml encoding="UTF-8"><html><body>' . $parseHtml . '</body></html>',
                LIBXML_NONET,
            );
            $errors = array_filter(libxml_get_errors(), static fn($error) => !in_array($error->code, [23, 801], true));
            return $loaded && $errors === [] ? $document : null;
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }
}
