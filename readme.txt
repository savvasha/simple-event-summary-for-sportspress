=== Simple Event Summary for SportsPress ===
Contributors: savvasha
Tags: sportspress, events, summary, resume
Requires at least: 5.3
Requires PHP: 7.4
Tested up to: 6.8
Stable tag: 1.2
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl.html

The Simple Event Summary for SportsPress plugin enhances your SportsPress plugin by adding a brief event summary below the main event card.

== Description ==

The Simple Event Summary for SportsPress plugin enhances your SportsPress plugin by adding a brief event summary below the main event card. It includes information such as scorers and referee details for a more comprehensive overview of the event.

== Features ==

- Display performances, including goals, penalties, and other key events.
- Show referee information.
- Customizable display options for different event types and teams.
- Action hooks for custom data

== Installation ==

1. Upload the `simple-event-summary-for-sportspress` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.

== Usage ==

Once activated, the plugin automatically adds the event summary below the main event card for SportsPress events. You can customize what data will be shown at `SportsPress->Settings->Events->Event Summary`.
Action Hooks:
* `esfs_before_inner_event_summary` – Fires before the event summary table is rendered. Allows developers to inject custom table content at the top of the table.
* `esfs_after_inner_event_summary` – Fires after the event summary table is rendered. Useful for appending additional table information at the end of the table.

== Screenshots ==

1. Available options in SportsPress Events Settings page.
2. Event summary with scorers and referee details.

== Changelog ==

= 1.2 =
* Added new css class based on event status.

= 1.1 =
* Added new hooks.

= 1.0 =
* Initial release.

== Upgrade Notice ==

= 1.0 =
* Initial release.

== Frequently Asked Questions ==

= Can I customize the display options? =
Yes, you can customize the display options for performances, officials, and teams through the SportsPress Event Settings.

== Support ==

For support or inquiries, visit [plugin support forum](https://wordpress.org/support/plugin/simple-event-summary-for-sportspress/).

== Donate ==

If you find this plugin helpful, consider [making a donation](https://bit.ly/3NLUtMh) to support further development and maintenance.

== Credits ==

This plugin was developed by Savvas. Visit [author's website](https://savvasha.com) for more information.

== License ==

This plugin is licensed under the GPL v2 or later. See [License](https://www.gnu.org/licenses/gpl.html) for more details.