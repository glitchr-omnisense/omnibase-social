# Changelog

All notable changes to omnibase/social. The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
versions [Semantic Versioning](https://semver.org/).

## [Unreleased]

## [1.0.1] — 2026-11-14

### Changed
- The bundle's classes extend omnibridge/symfony's `AbstractBaseBundle`, `AbstractBaseExtension` and
  `AbstractBaseConfiguration` by their names (`Omnibridge\Symfony\Omnibase\Bundle\*`), no longer by the former
  `Base\Bundle\*` ones, which omnisense/omnibase keeps only as aliases until 6.0. The same classes: nothing changes for
  an application.
- `CrudFormsTest` names the form types `Omnibridge\Symfony\Omnibase\Field\Type\AssociationType` and
  `CollectionType`, not their former names `Base\Field\Type\*`, and compares a field's form type with `is_a()`:
  omnibridge/symfony 1.1's kinds still give the collection's by its former name, an alias of the same class.
- docs/installation.md: `config/bundles.php` registers `Omnibridge\Symfony\Omnibase\BaseBundle`, the core's
  bundle under its name since omnisense/omnibase 5.0 (`Base\BaseBundle` is its former name).

## [1.0.0] — 2026-11-13

### Changed
- First stable release: installs with the default minimum-stability. Requires omnisense/omnibase `^5.1` (no longer
  the 3.0, 4.0 or 5.0 development branches), omnibridge/symfony `^1.1`, omnibase/admin `^1.0`, omnisense/omnipost `^1.0`, without `@dev`; `minimum-stability: dev` removed;
  branch alias `dev-main` = `1.x-dev`.
- CI: glitchr-studio/ci's package workflow (validate, PHPUnit, PHPStan level 5 with a baseline, a consumer install)
  and release workflow on a `v*` tag.
- README: the install line, how to enable the bundle under Symfony, a first use.

### Fixed
- Requires doctrine/doctrine-bundle and symfony/doctrine-bridge, whose classes `src/` uses: they came only with a host
  application (omnibridge/symfony suggests them), so the bundle installed alone failed to load its repositories.
- phpunit/phpunit in require-dev: the suite could not run from the checkout.
- Neutral example data in docs and tests.
