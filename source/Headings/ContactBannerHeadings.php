<?php

declare(strict_types=1);

namespace MunicipioQualityExtensions\Headings;

final class ContactBannerHeadings
{
    private const VERSION = 'qx-contact-banner-headings-v1';

    public function register(): void
    {
        add_filter('Modularity/Display/mod-contact-banner/viewData', [$this, 'viewData'], 30);
        add_filter('Modularity/Display/mod-contact-banner/Markup', [$this, 'filter'], 30);
        add_filter('Modularity/Block/Settings', [$this, 'blockSettings'], 30, 2);
        if (defined('\Modularity\Display::CACHE_CONTEXT_FILTER_VERSION')) {
            add_filter('Modularity/Display/CacheContext', [$this, 'cacheContext'], 30, 2);
        }
    }

    public function viewData(array $data): array
    {
        foreach ($data['ctaList'] ?? [] as $index => $item) {
            if (!is_object($item) || !is_string($item->content ?? null)) {
                continue;
            }
            $content = (new AccordionContent())->prepare($item->content, 'data-qx-contact-depth');
            if ($content !== null) {
                // Modularity reuses module objects between placements and block renders.
                $data['ctaList'][$index] = clone $item;
                $data['ctaList'][$index]->content = $content;
            }
        }
        return $data;
    }

    public function filter(string $markup): string
    {
        return (new ContactBannerMarkup())->transform($markup);
    }

    public function cacheContext(mixed $context, object $module): mixed
    {
        return ($module->post_type ?? '') === 'mod-contact-banner' ? [$context, self::VERSION] : $context;
    }

    public function blockSettings(array $settings, string $slug): array
    {
        if ($slug !== 'contact-banner' || !is_callable($settings['render_callback'] ?? null)) {
            return $settings;
        }
        $callback = $settings['render_callback'];
        $settings['render_callback'] = function (...$arguments) use ($callback) {
            if (array_key_exists('render_callback', $arguments[0] ?? [])) {
                $arguments[0]['render_callback'] = $callback;
            }
            // Block cache skips viewData on a hit. Old fragments lack source depths.
            $arguments[0]['qx_contact_headings_version'] = self::VERSION;
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
            echo $this->filter($markup);
            return $result;
        };
        return $settings;
    }
}
