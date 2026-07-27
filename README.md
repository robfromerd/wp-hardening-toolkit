# WP Hardening Toolkit

WP Hardening Toolkit is a modular, opt-in WordPress security plugin. No
behavior-changing hardening rule is enabled by default.

## Development

Requires PHP 8.0+ and Composer.

```bash
composer install
composer check
```

See [Architecture](docs/architecture.md) for the design.

## Features

- REST API controls for `/batch/v1` and anonymous `/wp/v2/users` requests.
- Full XML-RPC shutdown, including methods, pingback headers, and direct access.
- Numeric author-enumeration protection.
- Optional file-editor, generator, and asset-version hardening.
- A custom database-backed security log with retention, filters, pagination,
  CSV/JSON export, and secured clearing.
- A security dashboard covering versions, updates, hardening state, debug mode,
  2FA detection, and inactive plugins.

Every behavior-changing control is disabled until an administrator opts in.

## Installation

1. Run `composer install --no-dev --classmap-authoritative` for a production
   package, or include the generated `vendor/` directory in the release zip.
2. Place the plugin directory in `wp-content/plugins/`.
3. Activate **WP Hardening Toolkit**.
4. Review **Hardening → Settings**, enable one option at a time, and follow its
   test guidance.

## Quality gates

The `composer check` command and GitHub Actions both run PHP lint, PHPCS with
WordPress Coding Standards, PHPStan level 8, and PHPUnit.

Further reading:

- [Developer guide](docs/development.md)
- [Compatibility notes](docs/compatibility.md)
- [Manual verification](docs/manual-verification.md)
