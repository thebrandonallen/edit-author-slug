=== Edit Author Slug ===
Contributors: thebrandonallen
Tags: author, author base, author slug, user nicename, nicename, permalink, permalinks, slug, users, user, role, roles
Requires at least: 4.0.10
Tested up to: 4.4.2
Stable tag: 1.2.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Allows an admin (or capable user) to edit the author slug of a user, and change the author base.

== Description ==

This plugin allows full control of your user permalinks, allowing you to change both the author base (the '/author/' portion of the author URLs), and the author slug (defaults to the username of the author). You can set the author base globally, or you can set it to be user-specific based on a user's role. You now have the power to craft the perfect URL structure for you Author pages.

WordPress default structure *http://example.com/author/username/*.

Edit Author Slug allows for *http://example.com/ninja/master-ninja/*.

Using a role-based author base would allow for *http://example.com/ida/master-splinter/* (for an Administrator Role), or *http://example.com/koga/leonardo/* (for a Subscriber Role).

Development of this plugin takes place on [GitHub](https://github.com/thebrandonallen/edit-author-slug/ "Edit Author Slug on Github"). Pull requests are always welcome!

Translations should be submitted to [Translate WordPress](https://translate.wordpress.org/projects/wp-plugins/edit-author-slug).

== Installation ==

1. Upload `edit-author-slug` folder to your WordPress plugins directory (typically 'wp-content/plugins')
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Users > Your Profile, or Users > All Users > (username), and edit the author slug.
4. Click "Update Profile" or "Update User"
5. Go to Settings > Edit Author Slug to edit settings
6. Click "Save Changes"

== Screenshots ==

1. Settings
2. Individual user author slug

== Frequently Asked Questions ==

= Why can't I edit my Author Slug? =

Make sure you are an admin, or have been assigned the `edit_users` capability.

= Why isn't my new Author Slug working? =

While I've made every attempt to prevent this, I may have missed a spot or two. First things first, go to Settings > Permalinks and click "Save Changes." You don't need to actually need to make any changes for this to work. Hopefully, this should kick your new Author Slug into gear.

If this doesn't work, make sure you don't have any slug conflicts from other posts/pages/plugins/permalink setting/etc. If you're still experiencing the issue, feel free to post a support request in the forums.

== Changelog ==

= 1.2.1 =
* Fixed stupid error where the default user nicename wasn't being properly retrieved from the database. Sorry about that :(
* Unfortunately, some unicorns were lost during the development of this release, but they are a resilient creature.

= 1.2.0 =
* Added the ability to use forward slashes in the author base.
* Improved display on the settings page, and storing, of role slugs.
* Packaged translations are now removed. Anyone interested in translating the plugin should do so at [Translate WordPress](https://translate.wordpress.org/projects/wp-plugins/edit-author-slug).
* EXPERIMENTAL: Added the ability to set the author slug to a user's numeric user id. While I have tested this, I can't be sure that no one's site will implode. If all goes well, the experimental tag will be removed in the next major release (or two).
* Added ability to remove the front portion of author links.
* Accessibility improvements to the settings page.

= 1.1.2 =
* Fix loading of minified JS in the admin. Props nuyensgert.

= 1.1.1 =
* Fix a few minor output escaping issues missed in the 1.1.0 release.

= 1.1.0 =
* Added the ability to update all author slugs at once with the "Bulk Update" tool.
* Greatly improved the checks and error messages when manually updating an author slug for a user.
* Further accessibility improvements to match WP 4.3.
* Improved validation of author slugs to better match that of WP.

= 1.0.6 =
* Fix potential, although unlikely, persistent XSS vulnerability.
* Prevent debug notice in admin. Props chesio.
* Update heading tags to h1 to match WP 4.3.

= 1.0.5.1 =
* Identical to 1.0.5, which failed to commit properly.

= 1.0.5 =
* Add WP_User object as a parameter passed to the `ba_eas_show_user_nicename_options_list` filter
* Add Japanese translation files. Props SmokyJp.
* Fixed possible (although unlikely) cache invalidation issue
* Minor code improvements and optimizations.

= 1.0.4 =
* Improve upgrade routine for older installs
* Improve output escaping
* Various minor fixes and improvements

= 1.0.3 =
I swear I tested this! :(

* Fix custom roles slugs not saving

= 1.0.2 =
* A number of localization fixes and improvements
* Role slug improvements
* Temporary, semi work-around for Co-Authors Plus [https://github.com/Automattic/Co-Authors-Plus/pull/204]

= 1.0.1 =
* Fix possible syntax error when updating a profile (props Christine https://wordpress.org/support/topic/undefined-property-error-1)

= 1.0 =
* Added ability to do role-based author bases
* Added ability to use role-based author templates
* Moderate code refactoring
* Various code fixes and improvements
* Add "nickname" as option for auto-update
* First pass at unit test (only checks if the plugin is installed, for now)

= 0.9.6 =
* Fixed loading of translation files. Looks in wp-content/plugins/edit-author-slug/languages. If you're running 3.7+ (and you are... aren't you?), it will fall back to wp-content/languages/plugins if a proper localization can't be found in the edit-author-slug folder.

= 0.9.5 =
* Fixed instances where the Author Base wouldn't change, or would result in a 404

= 0.9.4 =
* Update readme references to plugin settings
* Fix some copy pasta in settings
* Update screenshots

= 0.9.3 =
* Quickly caught a few things I missed, so this release was skipped. See 0.9.4 for changes

= 0.9.2 =
* Fix issue where any profile information other than the Author Slug could not be updated
* Minor code improvement

= 0.9.1 =
* Add 'Settings' link to plugins list table

= 0.9 =
* Allow Author Slug to be automatically created/updated based on a defined structure
* Switched to using the Settings API, which also means that all options moved to the Settings > Edit Author Slug page
* Various code improvements/optimizations

= 0.8.1 =
* Fix a bug that prevented non-admin users from updating their profile

= 0.8 =
* Drastically improved error handling and feedback for author slug editing.
* Restore duplicate author slug check as old method could alter the slug without any sort of warning.
* Further improve the logic for flushing rewrite rules.
* Introduce ba_eas_can_edit_author_slug() and matching filter to make it even easier to give users the ability to update their own author slug.
* Add message in plugins list warning users of WP less than 3.2 that 0.8 is the last update they'll receive.

= 0.7.2 =
* Remove overzealous cap check.

= 0.7.1 =
* Fix some unfortunate errors I missed before tagging 0.7.

= 0.7 =
* Significant code refactoring.
* Added custom capability to give site admins the ability to add author slug access to other roles.
* Improvements/optimizations to code logic.
* Fixed an incorrect textdomain string.
* Removed filter added in 0.6 as it was messy. It's much easier to achieve the same result without the plugin.
* Got rid of wp_die() statement on duplicate author slugs in favor of WP's built-in duplicate author slug method.

= 0.6.1 =
* Added Dutch translation - props Juliette Reinders Folmer.
* Don't hard code the languages folder path.
* Improve class check/initialization.

= 0.6 =
* Some code cleanup.
* More security hardening.
* Added filter to allow for the complete removal of the Author Base (http://brandonallen.org/2010/11/03/how-to-remove-the-author-base-with-edit-author-slug/).
* Flush rewrite rules only when necessary instead of every page load.

= 0.5 =
* Added 'Author Slug' column to Users > Authors & Users (Users > Users in 3.0) page (props Yonat Sharon for the jumpstart).
* Ended support for the WP 2.8 branch. Most likely still works, but I will not support it.
* Various bug fixes.

= 0.4 =
* Added ability to change the Author Base.
* Updated documentation.
* Added some extra security via WP esc_* functions.
* Added Belorussian translation, props Marcis G.

= 0.3.1 =
* Added Hebrew Translation, props Yonat Sharon.

= 0.3 =
* Now localization friendly.

= 0.2.1 =
* Fixed a bug that prevented updating a user if the author slug did not change.

= 0.2 =
* Added a check to avoid duplicate slugs.
* Properly sanitize slug before comparison and database insertion.
* Updated plugin URI.

= 0.1.4 =
* Update tags to reflect WordPress 2.9.1 compatability.
* Update link to plugin homepage.

= 0.1.3 =
* Update tags to reflect WordPress 2.9 compatability.

= 0.1.2 =
* Fix version number issues.

= 0.1.1 =
* Remove extra debug functions left behind.
* Add screenshot.

= 0.1 =
* Initial release.

== Upgrade Notice ==

= 1.0 =
Role-based author bases are here!

= 0.4 =
Adds ability to change the Author Base (not a required upgrade)

= 0.3 =
Edit Author Slug can now be localized. You can find edit-author-slug.pot in 'edit-author-slug/languages' to get you started.

= 0.2 =
Added a check to avoid duplicate duplicate author slugs, and better sanitization.

= TODO =
* Allow Author Slug editing of users from one centralized location
