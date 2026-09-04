<?php

declare(strict_types=1);

namespace MunicipioQualityExtensions\WebStandards;

final class LateStyleHoisting
{
    private const MINIMUM_WORDPRESS_VERSION = '6.9';

    /**
     * Register immediately before Municipio 6.x renders from template_include.
     *
     * WordPress normally starts this buffer after template_include has returned.
     * Municipio renders at priority 15 and returns false, bypassing that point.
     * Priority 14 preserves WordPress ownership of collection and HTML rewriting.
     */
    public function register(): void
    {
        if (
            version_compare(get_bloginfo('version'), self::MINIMUM_WORDPRESS_VERSION, '<')
            || !class_exists('Municipio\\Template')
            || !function_exists('wp_start_template_enhancement_output_buffer')
        ) {
            return;
        }

        add_filter('template_include', [$this, 'startBuffer'], 14, 1);
    }

    public function startBuffer(mixed $template): mixed
    {
        if (!wp_is_block_theme()) {
            wp_start_template_enhancement_output_buffer();
        }

        return $template;
    }
}
