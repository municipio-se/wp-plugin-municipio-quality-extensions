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

if (!function_exists('add_action')) {
    function add_action(string $hook, callable $callback, int $priority = 10, int $acceptedArgs = 1): bool
    {
        WordPressState::$actions[] = compact('hook', 'callback', 'priority', 'acceptedArgs');
        return true;
    }
}

if (!function_exists('get_theme_mod')) {
    function get_theme_mod(string $name, mixed $default = false): mixed
    {
        return WordPressState::$themeMods[$name] ?? $default;
    }
}

if (!function_exists('wp_register_style')) {
    function wp_register_style(string $handle, string|false $src, array $deps = [], string|bool|null $ver = false): bool
    {
        return true;
    }
}

if (!function_exists('wp_enqueue_style')) {
    function wp_enqueue_style(string $handle, string $src = '', array $deps = [], string|bool|null $ver = false): void
    {
        WordPressState::$styles[$handle] = $src;
    }
}

function __(string $text, string $domain = ''): string
{
    return $domain === 'municipio' && $text === 'Read more' ? 'Läs mer' : $text;
}

function get_option(string $name, mixed $default = false): mixed
{
    return $name === 'stylesheet' ? 'municipio' : WordPressState::$themeMods;
}

function set_theme_mod(string $name, mixed $value): void
{
    WordPressState::$themeMods[$name] = $value;
    ++WordPressState::$themeModWrites;
}

function plugins_url(string $path, string $plugin): string
{
    return '/plugins/qx/' . $path;
}

if (!function_exists('wp_add_inline_style')) {
    function wp_add_inline_style(string $handle, string $data): bool
    {
        WordPressState::$inlineStyles[$handle] = $data;
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
