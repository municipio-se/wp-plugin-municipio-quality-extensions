<?php

declare(strict_types=1);

namespace MunicipioQualityExtensions\Headings;

final class AccordionHeadings
{
    public function register(): void
    {
        foreach (['mod-posts', 'mod-manualinput'] as $type) {
            add_filter("Modularity/Display/{$type}/viewData", [$this, 'viewData'], 30);
            add_filter("Modularity/Display/{$type}/Markup", [$this, 'filter'], 30, 2);
        }
        add_filter('Modularity/Block/Settings', [$this, 'blockSettings'], 30, 2);
        if (defined('\Modularity\Display::CACHE_CONTEXT_FILTER_VERSION')) {
            add_filter('Modularity/Display/CacheContext', [$this, 'cacheContext'], 30, 2);
        }
    }

    public function viewData(array $data): array
    {
        return (new AccordionViewData())->prepareData($data);
    }

    public function filter(string $markup, object $module): string
    {
        return (new AccordionMarkup())->transform($markup, $module->post_type ?? '');
    }

    public function cacheContext(mixed $context, object $module): mixed
    {
        return (
            in_array($module->post_type ?? '', ['mod-posts', 'mod-manualinput'], true)
                ? [$context, 'qx-accordion-headings-v1']
                : $context
        );
    }

    public function blockSettings(array $settings, string $slug): array
    {
        if (!in_array($slug, ['posts', 'manualinput'], true) || !is_callable($settings['render_callback'] ?? null)) {
            return $settings;
        }
        $callback = $settings['render_callback'];
        $settings['render_callback'] = static function (...$arguments) use ($callback, $slug) {
            if (array_key_exists('render_callback', $arguments[0] ?? [])) {
                $arguments[0]['render_callback'] = $callback;
            }
            $level = ob_get_level();
            ob_start();
            try {
                $result = $callback(...$arguments);
                $markup = ob_get_contents();
            } finally {
                while (ob_get_level() > $level) {
                    ob_end_clean();
                }
            }
            echo (new AccordionMarkup())->transform($markup, 'mod-' . $slug);
            return $result;
        };
        return $settings;
    }
}
