=== SMTP ===

Description:	Simple Mail Transport Protocol (SMTP) plugin.
Version:		1.4.2
Tags:			smtp,email,phpmailer
Author:			azurecurve
Author URI:		https://development.azurecurve.co.uk/
Contributors:	azurecurve,xxsimoxx
Plugin URI:		https://development.azurecurve.co.uk/classicpress-plugins/smtp/
Download link:	https://github.com/azurecurve/azrcrv-smtp/releases/download/v1.4.2/azrcrv-smtp.zip
Donate link:	https://development.azurecurve.co.uk/support-development/
Requires PHP:	5.6
Requires:		1.0.0
Tested:			4.9.99
Text Domain:	azrcrv-smtp
Domain Path:	/languages
License: 		GPLv2 or later
License URI: 	http://www.gnu.org/licenses/gpl-2.0.html

Simple Mail Transport Protocol (SMTP) plugin.

== Description ==

# Description

Simple Mail Transport Protocol (SMTP) plugin will intercept the standard wp_mail and send emails via an SMTP server using PHPMAILER.

When activating for the first time, settings can be imported from Easy WP SMTP.

This plugin is multisite compatible; each site will need settings to be configured in the admin dashboard.

== Installation ==

# Installation Instructions

 * Download the plugin from [GitHub](https://github.com/azurecurve/azrcrv-smtp/releases/latest/).
 * Upload the entire zip file using the Plugins upload function in your ClassicPress admin panel.
 * Activate the plugin.
 * Configure relevant settings via the configuration page in the admin control panel (azurecurve menu).

== Frequently Asked Questions ==

# Frequently Asked Questions

### Can I translate this plugin?
Yes, the .pot file is in the plugins languages folder and can also be downloaded from the plugin page on [azurecurve|Development](https://development.azurecurve.co.uk); if you do translate this plugin, please sent the .po and .mo files to translations@azurecurve.co.uk for inclusion in the next version (full credit will be given).

### Is this plugin compatible with both WordPress and ClassicPress?
This plugin is developed for ClassicPress, but will likely work on WordPress.

== Changelog ==

# Changelog

### [Version 1.4.2](https://github.com/azurecurve/azrcrv-smtp/releases/tag/v1.4.2)
 * Fix bug with php notice when sending test email.
 * Fix missing settings page title.

### [Version 1.4.1](https://github.com/azurecurve/azrcrv-smtp/releases/tag/v1.4.1)
 * Fix bug with azurecurve menu.

### [Version 1.4.0](https://github.com/azurecurve/azrcrv-smtp/releases/tag/v1.4.0)
 * Only replace from email with that from settings when from is set to admin email.
 * Improve sanitization and escaping.
 * Improve security by using wp_safe_redirect.
 * Load Admin Dashboard jQuery in footer.

### [Version 1.3.1](https://github.com/azurecurve/azrcrv-smtp/releases/tag/v1.3.1)
 * Update azurecurve menu and logo.

### [Version 1.3.0](https://github.com/azurecurve/azrcrv-smtp/releases/tag/v1.3.0)
 * Remove debug erroneously included.
 
### [Version 1.2.0](https://github.com/azurecurve/azrcrv-smtp/releases/tag/v1.2.0)
 * Add import of Easy WP SMTP settings on first activation (contributed by xxsimoxx).

### [Version 1.1.0](https://github.com/azurecurve/azrcrv-smtp/releases/tag/v1.1.0)
 * Fix bad TLS cert in plain text auth (contributed by xxsimoxx).
 * Option to allow no authentication when username not set.
 * Add uninstall method.

### [Version 1.0.0](https://github.com/azurecurve/azrcrv-smtp/releases/tag/v1.0.0)
 * Initial release.

== Other Notes ==

# About azurecurve

**azurecurve** was one of the first plugin developers to start developing for Classicpress; all plugins are available from [azurecurve Development](https://development.azurecurve.co.uk/) and are integrated with the [Update Manager plugin](https://codepotent.com/classicpress/plugins/update-manager/) for fully integrated, no hassle, updates.

Some of the other plugins available from **azurecurve** are:
 * [Add Twitter Cards] (https://development.azurecurve.co.uk/classicpress-plugins/add-twitter-cards/) ([download] (https://github.com/azurecurve/azrcrv-add-twitter-cards/releases/latest/))
 * [Avatars] (https://development.azurecurve.co.uk/classicpress-plugins/avatars/) ([download] (https://github.com/azurecurve/azrcrv-avatars/releases/latest/))
 * [Breadcrumbs] (https://development.azurecurve.co.uk/classicpress-plugins/breadcrumbs/) ([download] (https://github.com/azurecurve/azrcrv-breadcrumbs/releases/latest/))
 * [Estimated Read Time] (https://development.azurecurve.co.uk/classicpress-plugins/estimated-read-time/) ([download] (https://github.com/azurecurve/azrcrv-estimated-read-time/releases/latest/))
 * [Maintenance Mode] (https://development.azurecurve.co.uk/classicpress-plugins/maintenance-mode/) ([download] (https://github.com/azurecurve/azrcrv-maintenance-mode/releases/latest/))
 * [Remove Revisions] (https://development.azurecurve.co.uk/classicpress-plugins/remove-revisions/) ([download] (https://github.com/azurecurve/azrcrv-remove-revisions/releases/latest/))
 * [Redirect] (https://development.azurecurve.co.uk/classicpress-plugins/redirect/) ([download] (https://github.com/azurecurve/azrcrv-redirect/releases/latest/))
 * [Toggle Show/Hide] (https://development.azurecurve.co.uk/classicpress-plugins/toggle-showhide/) ([download] (https://github.com/azurecurve/azrcrv-toggle-showhide/releases/latest/))
 * [Update Admin Menu] (https://development.azurecurve.co.uk/classicpress-plugins/update-admin-menu/) ([download] (https://github.com/azurecurve/azrcrv-update-admin-menu/releases/latest/))
 * [URL Shortener] (https://development.azurecurve.co.uk/classicpress-plugins/url-shortener/) ([download] (https://github.com/azurecurve/azrcrv-url-shortener/releases/latest/))