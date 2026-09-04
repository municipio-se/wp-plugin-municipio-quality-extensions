# Repository instructions

## Scope

Keep this plugin independent of Municipio Cloud and customer-specific code.
Each feature must be a general runtime quality improvement for modern Municipio
and must use a documented WordPress or Municipio extension point.

## Verification

Run these commands after changing PHP behavior:

```console
composer format
composer test
composer lint
```
