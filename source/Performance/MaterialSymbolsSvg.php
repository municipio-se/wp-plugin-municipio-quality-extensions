<?php

declare(strict_types=1);

namespace MunicipioQualityExtensions\Performance;

final class MaterialSymbolsSvg
{
    private const MARKER_ATTRIBUTE = 'data-mqe-material-symbol-svg';

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
        $data['classList'] = is_array($data['classList'] ?? null) ? $data['classList'] : [];
        $data['classList'][] = 'c-icon--svg-path';
        $data['attributeList'] = is_array($data['attributeList'] ?? null) ? $data['attributeList'] : [];
        $data['attributeList'][self::MARKER_ATTRIBUTE] = '';

        return $data;
    }

    /** @param array<string, mixed> $attributes
     *  @return array<string, mixed>
     */
    public function filterAttributes(array $attributes): array
    {
        if (array_key_exists(self::MARKER_ATTRIBUTE, $attributes)) {
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
            '.c-icon[data-mqe-material-symbol-svg]>svg{display:block;width:1em;height:1em;fill:currentColor}',
        );
    }
}
