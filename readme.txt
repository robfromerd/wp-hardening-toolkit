=== WP Hardening Toolkit ===
Contributors: wp-hardening-toolkit
Tags: security, hardening, logging
Requires at least: 6.4
Requires PHP: 8.0
Stable tag: 0.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Modular, opt-in security hardening for WordPress.

== Description ==

WP Hardening Toolkit provides independently configurable hardening rules and
auditable security logging. Behavior-changing rules are disabled by default.

== Installation ==

1. Upload the plugin directory to /wp-content/plugins/.
2. Activate WP Hardening Toolkit.
3. Open Hardening > Settings.
4. Enable and test one option at a time.

== Frequently Asked Questions ==

= Will activation change site behavior? =

No. All behavior-changing hardening options are disabled by default.

= Can this affect Jetpack or the WordPress mobile app? =

Disabling XML-RPC can. REST controls can affect headless and custom REST
clients. Read each option's compatibility guidance before enabling it.

= Where are events stored? =

Events are stored in the prefixed wp_wpht_log-style custom table. The actual
prefix follows the site's WordPress database prefix.

== Changelog ==

= 0.1.0 =

* Initial modular hardening, logging, dashboard, and administration release.

