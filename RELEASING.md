# Release process

## First stable release

The planned first tag is 1.0.0 with independent SemVer. Keep the development
header until reference and migration acceptance is complete. Documented
settings, preserved data, hooks and intentional runtime behavior form the stable
contract. Incompatible changes require a major release; compatible additions and
fixes use minor and patch releases.

Distribution remains Composer VCS with an only restriction for
municipio/wp-plugin-municipio-quality-extensions. Packagist registration is not
required. Composer derives versions from immutable tags; do not add a version
field to composer.json or claim Packagist verification for this package.

## Compatibility and acceptance

The primary target is WordPress 6.9.4, PHP 8.3, Municipio theme 6.43.3 and
Component Library 5.10.0. Record required hook patches and the optional local
icon-store configuration. The PHP requirement and feature guards do not prove
all permitted combinations have been tested.

On 2026-09-14, the isolated suite passed on PHP 8.3.33: 25 tests and 123
assertions. Composer validation and lint passed with existing help diagnostics.
Reference acceptance remains required for changes beyond the consumer's lock.

On 2026-09-17, persisted migrations passed in a disposable WordPress 6.9.4
multisite on PHP 8.3.33. Fresh/absent and malformed sources stayed unchanged;
legacy booleans migrated, explicit false targets survived, later imports were
handled and repeated execution across blog switches remained idempotent. A real
SQL export/import restored all eight Theme/Quality fixtures exactly, including
absent migration markers, and migrations passed again afterwards. The reusable
runner is scripts/verify-package-migrations.py in Municipio Cloud Tooling. This
is data recovery evidence, not an end-to-end accessibility test or a customer
disaster recovery drill. Runtime code remains identical to 666a855, now deployed
to both Nora environments; the former production-code gap is closed.

- Verify late-style hoisting on the reference site and the documented no-op
  paths. Keep core WordPress responsible for buffering and output.
- Verify optional SVG rendering, fallback and icon geometry before enabling it
  in a consumer. Do not remove the font stylesheet based solely on unit tests.
- Complete visual and assistive-technology checks for heading-linked cards,
  hover effects, secondary controls and underline choices. Verify Navigation
  cards both with and without the module.navigation.cards context.
- Verify the linked-card migration with fresh data, absent and malformed
  sources, legacy values, explicit target values, repeated requests and
  multisite blog switches. Preserve source values and document code/data
  rollback.

## Publication and rollback

After acceptance, align the changelog, plugin header and any asset-version
constants. Run composer validate --strict, composer format, composer test and
composer lint. Verify clean installation through the restricted VCS source,
including autoloading and shipped assets.

Obtain approval for the exact commit and compatibility evidence before
publishing an annotated 1.0.0 tag and matching GitHub release. Verify Composer
source and dist references against the peeled tag commit. Never move a published
tag. Approved consumers require ^1.0 through the restricted VCS source, with a
reviewed and committed lockfile. Deployment requires its own approval and
reference tests.

Retain previous code, lockfile, feature configuration and a tested recovery
point for migrated theme mods. A lockfile rollback alone does not reverse data
changes. Do not enable optional features or delete legacy values as a side
effect of a version-only upgrade.
