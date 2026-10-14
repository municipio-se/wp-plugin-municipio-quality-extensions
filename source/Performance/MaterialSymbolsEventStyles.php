<?php

declare(strict_types=1);

namespace MunicipioQualityExtensions\Performance;

final class MaterialSymbolsEventStyles
{
    /**
     * The event React components bypass Component Library. Match their fixed
     * roles rather than their text, so later React renders need no DOM observer.
     * Do not match PHP-rendered SVG icons or unknown input icons.
     */
    private const SELECTORS = [
        'chevron_left' => '.c-pagination__item--previous .c-icon.material-icons',
        'chevron_right' => '.c-pagination__item--next .c-icon.material-icons',
        'more_horiz' => '.c-pagination__item:not(.c-pagination__item--previous):not(.c-pagination__item--next) > .c-icon.material-icons',
        'keyboard_arrow_left' => '.modularity-event-index__mobile-scroll-nav .c-icon[data-material-symbol~="keyboard_arrow_left"]',
        'keyboard_arrow_right' => '.modularity-event-index__mobile-scroll-nav .c-icon[data-material-symbol~="keyboard_arrow_right"]',
        // DropdownList is mounted only while open; the toggle has no state attribute.
        'keyboard_arrow_up' => '.c-dropdown:has(> .c-dropdown__list) > button > .c-button__label-icon > .c-icon.material-icons',
        'keyboard_arrow_down' => '.c-dropdown:not(:has(> .c-dropdown__list)) > button > .c-button__label-icon > .c-icon.material-icons',
        'search' => 'input[id="filter-keyword"] + .c-icon.c-field__suffix.material-icons',
        'date_range' => 'input:is([id="filter-date-from"], [id="filter-date-to"]) + .c-icon.c-field__suffix.material-icons',
    ];

    public static function render(MaterialSymbolsStoreInterface $store): string
    {
        return MaterialSymbolsMaskStyles::render($store, self::SELECTORS, '.modularity-event-index ', false);
    }
}
