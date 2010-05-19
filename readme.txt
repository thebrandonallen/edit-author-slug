=== Edit Author Slug ===
Contributors: cnorris23
Tags: admin, author, permalink, permalinks, slug, users, user
Requires at least: 2.8.6
Tested up to: 3.0-beta2
Stable tag: 0.4

Allows an Admin to edit the author slug of any blog user, and change the Author Base.

== Description ==

This plugin allows an Admin to change the Author slug, without having to actually enter the database. You can also change the Author Base. Two new fields will be added to your Dashboard. The "Edit Author Slug" field can be found under Users > Your Profile or Users > Authors & Users. The "Author Base" field can be found under Settings > Permalinks. This plugin not only allows for greater security, as it helps mask usernames, but it also allows you to craft the perfect URL structure for you Author pages. 

WordPress default structure
http://example.com/author/username/

Plugin allows
http://example.com/ninja/master-ninja/

#### Translations Available
* Hebrew (he_IL) - Yonat Sharon
* Belorussian (be_BY) - [Marcis G.](http://pc.de/ "Marcis G.")

== Installation ==

1. Upload `edit-author-slug` folder to your WordPress plugins directory (typically 'wp-content/plugins')
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Go to Users > Your Profile, or Users > Authors & Users > (username), and edit the Author slug.
1. Click "Update Profile"
1. Go to Settings > Permalinks, and edit the Author Base
1. Click "Save Changes"

== Screenshots ==

1. Edit Author Slug screenshot
2. Author Base screenshot

== Frequently Asked Questions ==

= Why can't I edit my Author Slug? =

Make sure you are an admin, or have been assigned the `edit_users` capability.

== Changelog ==

= 0.4 =
* added ability to change the Author Base 
* updated documentation
* added some extra security via WP esc_* functions
* added Belorussian translation, props Marcis G.

= 0.3.1 =
* added Hebrew Translation, props Yonat Sharon

= 0.3 =
* now localization friendly

= 0.2.1 =
* fixed a bug that prevented updating a user if the author slug did not change

= 0.2 =
* added a check to avoid duplicate slugs
* properly sanitize slug before comparison and database insertion
* updated plugin URI

= 0.1.4 =
* update tags to reflect WordPress 2.9.1 compatability
* update link to plugin homepage

= 0.1.3 =
* update tags to reflect WordPress 2.9 compatability

= 0.1.2 =
* fix version number issues

= 0.1.1 =
* Remove extra debug functions left behind
* Add screenshot

= 0.1 =
* Initial release

== Upgrade Notice ==

= 0.4 =
Adds ability to change the Author Base (not a required upgrade)

= 0.3 =
Edit Author Slug can now be localized. You can find edit-author-slug.pot in 'edit-author-slug/languages' to get you started.

= 0.2 =
Added a check to avoid duplicate duplicate author slugs, and better sanitization.

= TODO =
* Allow Author Slug editing of users from one centralized location