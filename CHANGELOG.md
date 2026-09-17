# Changelog

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
