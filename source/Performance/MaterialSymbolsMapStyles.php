<?php

declare(strict_types=1);

namespace MunicipioQualityExtensions\Performance;

final class MaterialSymbolsMapStyles
{
    public function __construct(
        private readonly MaterialSymbolsStoreInterface $store,
    ) {}

    public function filterMarkup(string $markup): string
    {
        $tags = new \WP_HTML_Tag_Processor($markup);
        $styles = '';
        while ($tags->next_tag()) {
            $id = $tags->get_attribute('data-js-interactive-map');
            $json = $tags->get_attribute('data-js-interactive-map-data');
            if (is_string($id) && is_string($json)) {
                $styles .= $this->render($id, $json);
            }
        }

        // Modules can render after wp_head. Keep their styles with their markup
        // instead of enqueueing into a stylesheet that has already been printed.
        return $styles === '' ? $markup : '<style>' . $styles . '</style>' . $markup;
    }

    public function render(string $id, string $json): string
    {
        $map = json_decode($json, true);
        if (!preg_match('/\A[a-zA-Z0-9_-]+\z/', $id) || !is_array($map)) {
            return '';
        }

        $names = array_filter(
            array_column((array) ($map['layerGroups'] ?? []), 'icon'),
            static fn(mixed $name): bool => is_string($name) && preg_match('/\A[a-z0-9_]+\z/', $name) === 1,
        );
        array_unshift($names, 'location_on');

        $variant = MaterialSymbolsThemeVariant::fromData(['filled' => true]);
        $css = '';
        foreach (array_unique($names) as $name) {
            $svg = $this->store->getIcon($name, $variant->style, $variant->weight, $variant->fill);
            if ($svg === null) {
                continue;
            }
            $selector =
                '[data-js-interactive-map="'
                . $id
                . '"] .interactive-map__marker > [data-material-symbol="'
                . $name
                . '"]';
            // Mask only the glyph: the parent owns the normal/highlighted circle,
            // border and colors, and Leaflet replaces it when popups open/close.
            $css .= $selector . '{display:inline-flex;align-items:center;justify-content:center;}';
            $css .=
                $selector
                . '::after{content:""!important;display:block;inline-size:1em;block-size:1em;'
                . 'background-color:currentColor;mask:url("data:image/svg+xml,'
                . rawurlencode($svg)
                . '") center/contain no-repeat;}';
        }

        return $css === '' ? '' : '@supports (mask-image:linear-gradient(black,black)){' . $css . '}';
    }
}
