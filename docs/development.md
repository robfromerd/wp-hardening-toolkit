# Developer guide

## Requirements

- PHP 8.1 or newer
- Composer 2
- A supported WordPress installation for manual and integration verification

Install dependencies and run the same gates as CI:

```bash
composer install
composer check
```

## Adding a module

Implement `WPHardeningToolkit\Module`, keep policy decisions in a pure class,
and add the module through the `wpht_modules` filter or the coordinator's
built-in module list. A module must:

1. remain disabled unless an administrator explicitly enables it;
2. register hooks only through `register()`;
3. send security events through `Logging\Logger`;
4. never execute log SQL directly;
5. include focused policy tests and manual verification steps;
6. document compatibility and rollback implications.

## Persistence

`Logging\Installer` owns the `dbDelta()` schema. `WpdbLogStore` owns runtime
log SQL and uses prepared values. Schema changes must update
`wpht_db_version`, preserve existing data, and be tested against both a fresh
installation and an upgrade.

## Release checklist

1. Update the version in `wp-hardening-toolkit.php` and the stable tag and
   changelog in `readme.txt`.
2. Run `composer check`.
3. Run `composer build` and install the generated zip on a disposable site.
4. Test activation, deactivation, and uninstall on a disposable WordPress site.
5. Complete every item in [manual verification](manual-verification.md).
6. Test the oldest supported PHP and WordPress versions.
7. Confirm no hardening option becomes enabled during upgrade.
8. Commit the release, then create and push the matching version tag (for
   example, `v0.1.0`). GitHub Actions publishes the installable zip and checksum.
