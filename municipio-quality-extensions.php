<?php

declare(strict_types=1);

/**
 * Plugin Name:       Municipio Quality Extensions
 * Description:       Adds focused runtime quality improvements to modern Municipio installations.
 * Version:           0.2.1
 * Author:            Whitespace
 * Requires PHP:      8.2
 * License:           MIT
 * License URI:       https://opensource.org/licenses/MIT
 * Text Domain:       municipio-quality-extensions
 */

use MunicipioQualityExtensions\Plugin;

if (!defined('ABSPATH')) {
    exit();
}

$autoload = __DIR__ . '/vendor/autoload.php';
if (is_readable($autoload)) {
    require_once $autoload;
}

if (class_exists(Plugin::class)) {
    (new Plugin())->register();
}
