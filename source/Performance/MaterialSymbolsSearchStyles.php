<?php

declare(strict_types=1);

namespace MunicipioQualityExtensions\Performance;

final class MaterialSymbolsSearchStyles
{
    public static function render(MaterialSymbolsStoreInterface $store): string
    {
        // The search client substitutes icon names in a shared server template.
        // Match the resulting attributes so subsequent renders need no observer.
        return MaterialSymbolsMaskStyles::render(
            $store,
            [
                'keyboard_arrow_left' => '.c-icon[data-material-symbol="keyboard_arrow_left"]',
                'keyboard_arrow_right' => '.c-icon[data-material-symbol="keyboard_arrow_right"]',
            ],
            '[data-js-search-page-pagination] ',
            true,
        );
    }
}
