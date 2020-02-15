=== Gravity Flow Folders Extension ===
Contributors: stevehenty
Tags: gravity forms, approvals, workflow
Requires at least: 4.0
Tested up to: 5.3.2
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Group entries into custom lists.

== Description ==

The Gravity Flow Folders Extension is an extension for Gravity Flow.

Gravity Flow is a commercial Add-On for [Gravity Forms](https://gravityflow.io/gravityforms)

Facebook: [Steven Henty](https://www.facebook.com/gravityflow.io)

= Requirements =

1. [Purchase and install Gravity Forms](https://gravityflow.io/gravityforms)
1. [Purchase and install Gravity Flow](https://gravityflow.io)
1. Wordpress 4.3+
1. The latest versions of Gravity Forms and Gravity Flow


= Support =
If you find any that needs fixing, or if you have any ideas for improvements, please get in touch:
https://gravityflow.io/contact/


== Installation ==

1.  Download the zipped file.
1.  Extract and upload the contents of the folder to /wp-contents/plugins/ folder
1.  Go to the Plugin management page of WordPress admin section and enable the 'Gravity Flow Folders Extension' plugin.

== Frequently Asked Questions ==

= Which license of Gravity Flow do I need? =
The Gravity Flow Folders Extension will work with any license of [Gravity Flow](https://gravityflow.io).


== ChangeLog ==

= 1.4 =
- Added security enhancements.
- Added filter gravityflowfolders_folder_match_add_step to allow control of whether entry should be added to a folder
- Added filter gravityflowfolders_folder_match_remove_step to allow control of whether entry should be removed from a folder
- Added filter gravityflowfolders_display_folder to allow the folder shortcode argument to be customized.
- Updated translations.

= 1.3 =
- Added support for the license key constant GRAVITY_FLOW_FOLDERS_LICENSE_KEY.

= 1.2 =
- Updated Members 2.0 integration to use human readable labels for the capabilities. Requires Gravity Flow 1.8.1 or greater.
- Fixed an issue with the shortcode where the step_status, workflow_info, and sidebar attributes are ignored when the detail page is displayed.
- Fixed an issue with the folder permissions.
- Added CSS RTL improvements.

= 1.1 =
- Added logging statements.
- Added support for Gravity Forms 2.3
- Fixed a PHP notice which could occur when getting the folders.
- Fixed an issue with the shortcode where entries in the folder may not be displayed.
- Fixed an issue with the link to the folders list page from the breadcrumbs where the id parameter is not removed.


= 1.0 =
All new!
