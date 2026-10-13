# Municipio Quality Extensions

Municipio Quality Extensions provides focused runtime improvements for modern
Municipio installations. Features belong here when they improve accessibility,
performance, security, or web standards through stable WordPress or Municipio
extension points and are too broad or version-specific for a small upstream fix.

The plugin is independent of Municipio Cloud and contains no customer-specific
behavior. Municipio LTS is not a supported runtime.

## Installation

See [RELEASING.md](RELEASING.md) for release gates, compatibility evidence,
publication and rollback.

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

## Server-local Material Symbols SVG

This integration is unfinished and excluded from the stable 1.0.0 contract. Keep
`MUNICIPIO_QUALITY_EXTENSIONS_MATERIAL_SYMBOLS_SVG_ENABLED` undefined or `false`
when adopting 1.0.0, including on a site that previously enabled the development
experiment. Retain the icon font and its stylesheet. The opt-in instructions
below are for continued development, not stable-release use.

The optional Material Symbols integration reads prebuilt SVGs from a local,
root-owned store and injects them through Component Library's documented icon
data and attribute filters. It performs one indexed seek for each distinct icon
variant in a request. Missing, unreadable or invalid data preserves Component
Library's original font rendering.

The integration is disabled unless both constants are configured:

```php
define('MUNICIPIO_QUALITY_EXTENSIONS_MATERIAL_SYMBOLS_SVG_ENABLED', true);
define(
    'MUNICIPIO_QUALITY_EXTENSIONS_MATERIAL_SYMBOLS_STORE_PATH',
    '/path/to/material-symbols',
);
```

The store must contain a `current` symlink to a release under `releases/`, with
one JSON offset index and uncompressed SVG pack per supported style, weight and
fill combination. The plugin validates names, byte ranges and content hashes
before rendering. It does not download, generate or write icons during a web
request.

The Material Symbols font remains a fallback in this phase. Remove its
stylesheet only after every icon-rendering path used by the installation has
been verified with the SVG integration enabled.

## Heading links for cards

Under **Customize → Component appearance → Card**, enable **Use heading links
for cards** for the current site. The feature is off by default, including when
the plugin is network activated. **Underline card heading links** defaults to on
and can be disabled separately. Both settings use a full preview refresh.

Supported callers are Posts cards, Manual Input's `card` appearance and
Navigation's `cards` appearance with the `module.navigation.cards` context.
Segment cards, custom slots, collapsible cards and unknown callers retain their
existing rendering. The integration uses `ComponentLibrary/Component/Card/Data`
before the Card controller initializes. It requires the Municipio 6.x Card
markup with a paint container and the Customizer section registration hook.

A linked card gets a heading link inside a div wrapper. A single navigation
button is replaced only when its rendered text is empty or matches Municipio's
translated “Read more” label after whitespace normalization. Custom labels and
action controls are preserved. Link attributes move to the heading link; the
wrapper no longer has a link name. Heading text is rendered as escaped text.

CSS extends the heading link across the card only when there are no secondary
links or interactive controls. With secondary controls, each link keeps its own
click and focus area. Without `:has()` support, the heading remains an ordinary
link. Existing paint containment, shadows and container queries remain intact.

Settings are theme mods on the current blog:

- `municipio_qx_linked_cards_enabled`: default `false`.
- `municipio_qx_linked_cards_underline`: default `true`.
- `municipio_qx_linked_cards_migration_version`: migration bookkeeping.

On `init` and `switch_blog`, an existing `card_mxui_enabled` value is copied
only if the QX activation setting is absent. Explicit QX values and the legacy
source are preserved. The migration accepts boolean values and their strict
`0`/`1`/`false`/`true` representations. Missing or invalid source values are
rechecked on later requests so an import after plugin activation still works.

## Optional navigation database index

This unreleased feature is an explicit, per-site optimization. Plugin
activation, network activation and ordinary requests never create or inspect
indexes. Test the benefit on a representative copy of the site before enabling
it.

With the plugin active, use WP-CLI to inspect, enable or undo the optimization:

```console
wp --url=https://example.org municipio-quality navigation-index status
wp --url=https://example.org municipio-quality navigation-index enable
wp --url=https://example.org municipio-quality navigation-index disable
```

On multisite, `--url` selects exactly one site's metadata table. Other sites and
new sites remain unchanged. Commands return JSON and report failures through
WP-CLI's nonzero exit status. The database user needs permission to alter that
table; index creation can briefly block concurrent writes.

An existing active index with the same columns and prefix lengths is reused.
`disable` removes only an unchanged index whose creation the plugin recorded
successfully. External indexes and indexes left by an interrupted creation are
preserved. A changed or disabled owned index requires manual inspection before
rollback. Plugin deactivation also preserves the index.

After a failed command, inspect `status` and retry the command after resolving
the cause. Migration state is versioned; future migrations must also be invoked
explicitly with `enable`, never as part of a page request or plugin update.

## Development

```console
composer install
composer test
composer lint
```
