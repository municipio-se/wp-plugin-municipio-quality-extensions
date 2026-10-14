<?php

declare(strict_types=1);

namespace MunicipioQualityExtensions\Performance;

final class MaterialSymbolsSvg
{
    private const MARKER_ATTRIBUTE = 'data-mqe-material-symbol-svg';
    // Field icons have padding outside the glyph; it must not consume the SVG's 1em.
    private const INLINE_STYLE = <<<'CSS'
        .c-icon[data-mqe-material-symbol-svg]{box-sizing:content-box;flex:none;inline-size:1em;block-size:1em;line-height:1}
        .c-icon[data-mqe-material-symbol-svg]::after{content:none!important;display:none!important}
        .c-icon[data-mqe-material-symbol-svg]>svg{display:block;inline-size:100%!important;block-size:100%!important;fill:currentColor}
        .c-icon[data-mqe-material-symbol-svg]>svg path{fill:currentColor;stroke:none}
        .c-icon[data-mqe-material-symbol-dynamic]>svg{display:none}
        .c-icon[data-mqe-material-symbol-dynamic][data-material-symbol="check_box"]>svg[data-mqe-icon="check_box"],
        .c-icon[data-mqe-material-symbol-dynamic][data-material-symbol="check_box_outline_blank"]>svg[data-mqe-icon="check_box_outline_blank"],
        .c-icon[data-mqe-material-symbol-dynamic][data-material-symbol="tune"]>svg[data-mqe-icon="tune"],
        .c-icon[data-mqe-material-symbol-dynamic][data-material-symbol="close"]>svg[data-mqe-icon="close"]{display:block}
        CSS;

    public function __construct(
        private readonly bool $enabled,
        private readonly MaterialSymbolsStoreInterface $store,
    ) {}

    public function register(): void
    {
        if (!$this->enabled) {
            return;
        }

        add_filter('ComponentLibrary/Component/Icon/Data', [$this, 'replaceIcon'], 100, 1);
        add_filter('ComponentLibrary/Component/Icon/Attribute', [$this, 'filterAttributes'], 100, 1);
        add_action('wp_enqueue_scripts', [$this, 'enqueueStyles'], 100);
        add_action('admin_enqueue_scripts', [$this, 'enqueueStyles'], 100);
    }

    /** @param array<string, mixed> $data
     *  @return array<string, mixed>
     */
    public function replaceIcon(array $data): array
    {
        $originalData = $data;
        $name = $data['icon'] ?? null;
        if (!is_string($name)) {
            return $data;
        }

        $variant = MaterialSymbolsThemeVariant::fromData($data);
        $svg = $this->store->getIcon($name, $variant->style, $variant->weight, $variant->fill);
        if ($svg === null) {
            return $data;
        }

        $data['svgElementFromFile'] = '<svg aria-hidden="true" focusable="false" ' . substr($svg, 5);
        $data['attributeList'] = is_array($data['attributeList'] ?? null) ? $data['attributeList'] : [];
        $data['attributeList'][self::MARKER_ATTRIBUTE] = '';

        // Map layer buttons locate icons by this attribute before registering
        // listeners. Their Icon component receives no parent-button context,
        // so both checkbox states must support the same contract everywhere.
        $alternate = match ($name) {
            'check_box' => 'check_box_outline_blank',
            'check_box_outline_blank' => 'check_box',
            'tune' => array_key_exists('data-js-interactive-map-filter-icon', $data['attributeList']) ? 'close' : null,
            'close' => array_key_exists('data-js-interactive-map-filter-icon', $data['attributeList']) ? 'tune' : null,
            default => null,
        };
        if ($alternate !== null) {
            $otherSvg = $this->store->getIcon($alternate, $variant->style, $variant->weight, $variant->fill);
            if ($otherSvg === null) {
                return $originalData;
            }
            $data['svgElementFromFile'] =
                '<svg aria-hidden="true" focusable="false" data-mqe-icon="'
                . $name
                . '" '
                . substr($svg, 5)
                . '<svg aria-hidden="true" focusable="false" data-mqe-icon="'
                . $alternate
                . '" '
                . substr($otherSvg, 5);
            $data['attributeList']['data-mqe-material-symbol-dynamic'] = '';
            $data['attributeList']['data-material-symbol'] = $name;
        }

        return $data;
    }

    /**
     * Component Library normally filters the attribute array while building a
     * component, but some nested Blade render paths reuse the hook after the
     * attributes have already been serialized. Preserve that string unchanged.
     *
     * @param array<string, mixed>|string $attributes
     * @return array<string, mixed>|string
     */
    public function filterAttributes(array|string $attributes): array|string
    {
        if (is_string($attributes)) {
            return $attributes;
        }

        if (
            array_key_exists(self::MARKER_ATTRIBUTE, $attributes)
            && !array_key_exists('data-mqe-material-symbol-dynamic', $attributes)
        ) {
            unset($attributes['data-material-symbol']);
        }

        return $attributes;
    }

    public function enqueueStyles(): void
    {
        wp_register_style('municipio-quality-extensions-material-symbols-svg', false, [], '1');
        wp_enqueue_style('municipio-quality-extensions-material-symbols-svg');
        wp_add_inline_style(
            'municipio-quality-extensions-material-symbols-svg',
            self::INLINE_STYLE . MaterialSymbolsEventStyles::render($this->store)
                . MaterialSymbolsSearchStyles::render($this->store),
        );
    }
}
