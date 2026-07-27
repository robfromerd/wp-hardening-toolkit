# Manual verification

Use a disposable staging site with logging enabled. Enable only the rule under
test and restore it afterward.

## REST batch

1. Request `/wp-json/batch/v1` and `/wp-json//batch/v1/`.
2. Confirm the configured 403 or 404 response.
3. Confirm a `rest_batch` event appears in the security log.
4. Disable the rule and confirm WordPress handles the route normally.

## REST users

1. Logged out, request `/wp-json/wp/v2/users` and a user item route.
2. Confirm the configured response and `rest_users` log event.
3. Sign in as an administrator and confirm both requests are not blocked by the
   toolkit.
4. Open and publish with the Block Editor.

## XML-RPC

1. POST an XML-RPC request to `/xmlrpc.php` and expect 403.
2. Confirm the `X-Pingback` header is absent from normal responses.
3. If applicable, verify Jetpack, mobile apps, and remote management before
   leaving the rule enabled.

## Author enumeration

1. Request `/?author=1` and confirm a 404 response.
2. Confirm an `author_enumeration` log event.
3. Confirm a real author slug archive remains available.

## WordPress hardening

1. Enable the file-editor option and confirm editors disappear from Appearance
   and Plugins.
2. Enable generator hiding and inspect HTML and feed source.
3. Enable asset-version hiding and confirm enqueued CSS/JS URLs have no `ver`
   argument while the site still renders and behaves correctly.

## Logging and administration

1. Verify pagination, each filter, search, and ascending/descending sort.
2. Export filtered CSV and JSON and inspect their contents.
3. Set each retention period and run
   `wp cron event run wpht_daily_log_cleanup`.
4. Verify clear-log requires confirmation credentials and removes rows.
5. Sign in as a user without `manage_options` and confirm every toolkit screen
   and action is denied.

## Lifecycle

1. Activate on a clean site and confirm the prefixed `wpht_log` table exists.
2. Deactivate and reactivate; settings and logs should remain.
3. Uninstall on a disposable site; the table, options, and scheduled event
   should be removed.
