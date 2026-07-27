# WP Hardening Toolkit

WP Hardening Toolkit is a modular, opt-in WordPress security plugin. No
behavior-changing hardening rule is enabled by default.

## Development

Requires PHP 8.1+ and Composer.

```bash
composer install
composer check
composer build
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

1. Download the versioned plugin zip from the repository's Releases page.
2. In WordPress, open **Plugins → Add New → Upload Plugin** and select the zip.
3. Activate **WP Hardening Toolkit**.
4. Review **Hardening → Settings**, enable one option at a time, and follow its
   test guidance.

GitHub's automatically generated "Source code" archives are not installable
plugin packages because they do not contain the production Composer autoloader.
Use the attached `wp-hardening-toolkit-<version>.zip` release asset instead.

## Quality gates

The `composer check` command and GitHub Actions both run PHP lint, PHPCS with
WordPress Coding Standards, PHPStan level 8, and PHPUnit.

`composer build` creates the installable plugin zip and SHA-256 checksum in
`dist/`. Pushing a tag that matches the plugin version, such as `v0.1.0`,
automatically runs the quality gates and publishes those files in a GitHub
Release.

Further reading:

- [Developer guide](docs/development.md)
- [Compatibility notes](docs/compatibility.md)
- [Manual verification](docs/manual-verification.md)
