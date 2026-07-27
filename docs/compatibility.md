# Compatibility notes

These controls are intentionally opt-in because security policy must reflect
how each WordPress site is used.

## REST API

WordPress core uses REST for the Block Editor and many administration screens.
The users rule blocks only anonymous `/wp/v2/users` collection and item
requests; authenticated editors and administrators remain unaffected. The
batch rule blocks the exact normalized `/batch/v1` route.

Headless front ends, custom REST clients, and plugins that anonymously query
users can be affected. Test application authentication, editor publishing,
media workflows, and front-end requests after enabling either rule.

## XML-RPC

Disabling XML-RPC affects Jetpack features that still require XML-RPC,
WordPress mobile applications configured through XML-RPC, legacy desktop
publishers, pingbacks, and remote-management services. Confirm those workflows
are unused or have REST-based replacements before enabling the option.

## Author enumeration

Only positive numeric `?author=` probes are converted to 404 responses. Normal
author slug archives are preserved. Sites intentionally linking to numeric
author query URLs should update those links before enabling the rule.

## File editor

The `DISALLOW_FILE_EDIT` constant removes dashboard theme and plugin editors.
It does not prevent changes through deployments, SFTP/SSH, hosting tools, or
vulnerable code with filesystem access.

## Generator and asset versions

Generator hiding can affect inventory scanners. Removing asset `ver` query
arguments may change CDN and browser cache invalidation. Purge caches and
verify CSS and JavaScript after releases if version hiding is enabled.

