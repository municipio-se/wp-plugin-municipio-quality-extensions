<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

use MunicipioQualityExtensions\Tests\Support\WordPressState;

WordPressState::reset();

if (!function_exists('get_bloginfo')) {
    function get_bloginfo(string $show = ''): string
    {
        return $show === 'version' ? WordPressState::$wordpressVersion : '';
    }
}

if (!function_exists('add_filter')) {
    function add_filter(string $hook, callable $callback, int $priority = 10, int $acceptedArgs = 1): bool
    {
        WordPressState::$filters[] = compact('hook', 'callback', 'priority', 'acceptedArgs');
        return true;
    }
}

if (!function_exists('wp_is_block_theme')) {
    function wp_is_block_theme(): bool
    {
        return WordPressState::$blockTheme;
    }
}

if (!function_exists('wp_start_template_enhancement_output_buffer')) {
    function wp_start_template_enhancement_output_buffer(): bool
    {
        ++WordPressState::$bufferStarts;
        return true;
    }
}
