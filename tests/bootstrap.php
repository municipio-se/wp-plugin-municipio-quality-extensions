<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/vendor/johnpbloch/wordpress-core/wp-includes/compat.php';
require_once dirname(__DIR__) . '/vendor/johnpbloch/wordpress-core/wp-includes/utf8.php';
require_once dirname(__DIR__) . '/vendor/johnpbloch/wordpress-core/wp-includes/kses.php';

foreach (['span', 'attribute-token', 'text-replacement', 'decoder', 'tag-processor'] as $class) {
    require_once
        dirname(__DIR__) . '/vendor/johnpbloch/wordpress-core/wp-includes/html-api/class-wp-html-' . $class . '.php';
}

use MunicipioQualityExtensions\Tests\Support\OptionsState;
use MunicipioQualityExtensions\Tests\Support\WordPressState;

WordPressState::reset();

function apply_filters(string $hook, mixed $value, mixed ...$args): mixed
{
    $filters = array_filter(WordPressState::$filters, static fn($filter) => $filter['hook'] === $hook);
    usort($filters, static fn($a, $b) => $a['priority'] <=> $b['priority']);
    foreach ($filters as $filter) {
        $value = $filter['callback'](...array_slice([$value, ...$args], 0, $filter['acceptedArgs']));
    }
    return $value;
}

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
    if ($name === 'municipio_qx_navigation_index') {
        return OptionsState::$values[$name] ?? $default;
    }
    return $name === 'stylesheet' ? 'municipio' : WordPressState::$themeMods;
}

function update_option(string $name, mixed $value, mixed $autoload = null): bool
{
    if (OptionsState::$writesFail) {
        return false;
    }
    OptionsState::$values[$name] = $value;
    return true;
}

function delete_option(string $name): bool
{
    unset(OptionsState::$values[$name]);
    return true;
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
