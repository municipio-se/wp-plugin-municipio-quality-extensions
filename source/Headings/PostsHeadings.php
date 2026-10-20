<?php

declare(strict_types=1);

namespace MunicipioQualityExtensions\Headings;

final class PostsHeadings
{
    public function register(): void
    {
        add_filter('Modularity/Display/mod-posts/Markup', [$this, 'filter'], 20, 2);
        add_filter('Modularity/Block/Settings', [$this, 'blockSettings'], 20, 2);
        if (defined('\Modularity\Display::CACHE_CONTEXT_FILTER_VERSION')) {
            add_filter('Modularity/Display/CacheContext', [$this, 'cacheContext'], 20, 2);
        }
    }

    public function filter(string $markup, object $module): string
    {
        if (!$this->supports($module->data['posts_display_as'] ?? 'list')) {
            return $markup;
        }
        return (new PostsMarkup())->transform($markup);
    }

    public function cacheContext(mixed $context, object $module): mixed
    {
        return ($module->post_type ?? '') === 'mod-posts' ? [$context, 'qx-posts-headings-v1'] : $context;
    }

    public function blockSettings(array $settings, string $slug): array
    {
        if ($slug !== 'posts' || !is_callable($settings['render_callback'] ?? null)) {
            return $settings;
        }
        $callback = $settings['render_callback'];
        $settings['render_callback'] = function (...$arguments) use ($callback) {
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
            $display = $arguments[0]['data']['posts_display_as'] ?? 'list';
            echo $this->supports($display) ? (new PostsMarkup())->transform($markup) : $markup;
            return $result;
        };
        return $settings;
    }

    private function supports(mixed $display): bool
    {
        // "items" is an upstream legacy value that currently falls back to list.
        return in_array(
            $display,
            ['', false, 'index', 'grid', 'features-grid', 'news', 'segment', 'collection', 'list', 'mixed', 'items'],
            true,
        );
    }
}
