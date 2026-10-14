<?php

declare(strict_types=1);

namespace MunicipioQualityExtensions\Performance;

final class MaterialSymbolsMaskStyles
{
    /** @param array<string, string> $selectors Trusted, code-owned selectors keyed by icon name. */
    public static function render(
        MaterialSymbolsStoreInterface $store,
        array $selectors,
        string $scope,
        bool $filled,
    ): string {
        $variant = MaterialSymbolsThemeVariant::fromData(['filled' => $filled]);
        $rules = '';
        foreach ($selectors as $name => $selector) {
            $svg = $store->getIcon($name, $variant->style, $variant->weight, $variant->fill);
            if ($svg === null) {
                continue;
            }

            $selector = $scope . $selector . ':not([data-mqe-material-symbol-svg])';
            // Encoding keeps validated SVG markup out of the inline style element.
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
