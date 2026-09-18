=== KitMage FluentCRM Bookings ===
Contributors: kitmage
Tags: fluentbooking, fluentcrm, bookings, merge-tag
Requires at least: 6.0
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Adds a FluentBooking merge tag that links a booking guest to the guest's FluentCRM form submissions.

== Description ==

This integration looks up the FluentCRM contact matching a FluentBooking guest's email address and saves a URL in the booking's custom data.

Use the following merge tag in FluentBooking:

`{{booking.custom.aspen_fluentcrm_form_submissions_url}}`

The resulting value points to the subscriber's Form Submissions tab in the WordPress admin. The URL does not include `http://` or `https://`, because FluentBooking's link editor expects the protocol to be selected separately.

Both FluentBooking and FluentCRM must be installed and active. No configuration is required.

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/kitmage-fluentcrm-bookings`, or install the plugin ZIP through the WordPress Plugins screen.
2. Activate **KitMage FluentCRM Bookings**.
3. Add `{{booking.custom.aspen_fluentcrm_form_submissions_url}}` where needed in FluentBooking.

== Changelog ==

= 1.0.0 =
* Initial release.
