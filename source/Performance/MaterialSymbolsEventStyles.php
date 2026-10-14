<?php

declare(strict_types=1);

namespace MunicipioQualityExtensions\Performance;

final class MaterialSymbolsEventStyles
{
    /**
     * The event React components bypass Component Library. Match their fixed
     * roles rather than their text, so later React renders need no DOM observer.
     * Do not match PHP-rendered SVG icons or unknown/dynamic input icons.
     */
    private const SELECTORS = [
        'chevron_left' => '.c-pagination__item--previous .c-icon.material-icons',
        'chevron_right' => '.c-pagination__item--next .c-icon.material-icons',
        'more_horiz' => '.c-pagination__item:not(.c-pagination__item--previous):not(.c-pagination__item--next) > .c-icon.material-icons',
        'keyboard_arrow_left' => '.modularity-event-index__mobile-scroll-nav .c-icon[data-material-symbol~="keyboard_arrow_left"]',
        'keyboard_arrow_right' => '.modularity-event-index__mobile-scroll-nav .c-icon[data-material-symbol~="keyboard_arrow_right"]',
    ];

    public static function render(MaterialSymbolsStoreInterface $store): string
    {
        $variant = MaterialSymbolsThemeVariant::fromData(['filled' => false]);
        $rules = '';
        foreach (self::SELECTORS as $name => $selector) {
            $svg = $store->getIcon($name, $variant->style, $variant->weight, $variant->fill);
            if ($svg === null) {
                continue;
            }

            $selector = '.modularity-event-index ' . $selector . ':not([data-mqe-material-symbol-svg])';
            // Only validated local SVGs enter the stylesheet. Encoding also keeps
            // markup delimiters out of WordPress's inline style element.
            $url = 'data:image/svg+xml,' . rawurlencode($svg);
            $rules .=
                $selector
                . '{display:inline-block;flex:none;inline-size:1em;block-size:1em;'
                . 'background-color:currentColor;content-visibility:hidden;'
                . 'mask:url("'
                . $url
                . '") center/contain no-repeat;}';
            $rules .= $selector . '::after{content:none!important;display:none!important;}';
        }

        // Unsupported browsers and missing store entries keep the font path.
        return $rules === ''
            ? ''
            : '@supports (mask-image:linear-gradient(black,black)) and (content-visibility:hidden){' . $rules . '}';
    }
}
