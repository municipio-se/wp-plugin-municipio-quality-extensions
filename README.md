# Municipio Quality Extensions

Municipio Quality Extensions provides focused runtime improvements for modern
Municipio installations. Features belong here when they improve accessibility,
performance, security, or web standards through stable WordPress or Municipio
extension points and are too broad or version-specific for a small upstream
fix.

The plugin is independent of Municipio Cloud and contains no customer-specific
behavior. Municipio LTS is not a supported runtime.

## Installation

Install the package with Composer:

```console
composer require municipio/wp-plugin-municipio-quality-extensions
```

Activate **Municipio Quality Extensions** as a network plugin for a Municipio
multisite installation.

## WordPress 6.9 late-style hoisting

WordPress 6.9 introduced a template enhancement buffer that moves styles
discovered while rendering a classic theme from `body` to `head`. Municipio
6.43.3 renders its Blade response inside the `template_include` filter and
returns `false`, so WordPress never reaches the point where it normally starts
that buffer.

For WordPress 6.9 and later, the plugin starts the public WordPress buffer at
priority 14, immediately before Municipios renderer at priority 15. WordPress
continues to own style collection, ordering, HTML processing, and final output.
The compatibility layer does nothing for block themes, non-Municipio themes,
older WordPress versions, or runtimes without the public buffer API.

The feature can be removed once supported Municipio versions let WordPress run
`wp_before_include_template` before rendering their response.

## Development

```console
composer install
composer test
composer lint
```
