# Changelog

## 1.2.1 — 2026-10-20

Preserve the original Posts block callback in Modularity's serialized cache
context. The heading wrapper continues to transform cached output without
preventing block fragments from being cached.

## 1.2.0 — 2026-10-20

Give Posts presentation titles H3 beneath a visible module H2, or H2 when the
module heading is absent. Preserve each presentation's typography, links and
other markup. Includes mixed layouts and sliders; expandable lists, content
headings and modal headings retain their own hierarchy.

Apply the same correction to module markup and Posts blocks, including cached
block output. Existing module fragment caches use a new context version.

## 1.1.0 — release candidate

Complete the opt-in local SVG integration with preserved glyph dimensions in
padded fields and state-aware icons for event navigation, search pagination and
interactive maps. Verified installations can disable the frontend Material
Symbols font while retaining its layout rules; the default remains enabled.

Support heading-linked event cards through the existing card settings.

Add an opt-in navigation metadata index managed through per-site WP-CLI
commands. Explicit rollback preserves external indexes, while advisory locking
and verified migration records protect retries and concurrent operations. This
feature requires an explicit command and is not enabled by a package upgrade.

This candidate is not yet published. Its SVG and navigation-index source matches
the patched 1.0.0 stage implementation; event-card eligibility is the additional
runtime change. The isolated suite passes 50 tests and 214 assertions on PHP
8.3.33. Clean tagged installation and deployment verification remain release
gates.

## 1.0.0 — 2026-09-17

First stable release with independent semantic versioning. Includes WordPress
late-style hoisting and the documented heading-linked card behavior, settings
and non-destructive legacy-setting migration.

Runtime behavior is preserved from development commit 666a855. The release
preparation changes the plugin version and documents the accepted scope.
Persisted migration and SQL recovery tests passed; the requested Nora stage
screen-reader check was accepted by Fredrik Johansson on 2026-09-17.

The unfinished Material Symbols SVG integration is excluded from the stable
contract and must remain disabled. Keep the existing icon font stylesheet. See
RELEASING.md for the consumer configuration gate, compatibility and rollback.

Publication and clean installation of the published VCS tag passed on
2026-09-17.

Documentation status corrected after publication. The immutable 1.0.0 archive
retains the original candidate wording; runtime code and the tag are unchanged.
