=== Edit Author Slug ===
Contributors: thebrandonallen
Tags: author, author base, author slug, user nicename, nicename, permalink, permalinks, slug, users, user, role, roles
Requires at least: 4.9
Tested up to: 6.2
Requires PHP: 5.6.20
Stable tag: 1.8.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.html

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

= What is an author slug? =

On standard WordPress installs, it's the final part of an author permalink.
e.g. - https://example.com/author/author-slug/

In relation to WordPress internals, the author slug is the same as the `user_nicename` field found in a `WP_User` object, or the users table in the database.

= Will my changes persist if I deactivate or delete the Edit Author Slug plugin? =

It depends.

Changing a user's author slug is permanent, as this changes the user's `user_nicename` field in the database.

If you've changed the author base, deactivating or deleting the plugin will revert your author base back to `author`.

= Why can't I edit my Author Slug? =

Make sure you are an admin, or have been assigned the `edit_users` capability.

= Why isn't my new Author Slug working? =

While I've made every attempt to prevent this, I may have missed a spot or two. First things first, go to Settings > Permalinks and click "Save Changes." You don't need to actually need to make any changes for this to work. Hopefully, this should kick your new Author Slug into gear.

If this doesn't work, make sure you don't have any slug conflicts from other posts/pages/plugins/permalink setting/etc. If you're still experiencing the issue, feel free to post a support request in the forums.

== Changelog ==

= 1.8.4 =
* Release date: 2022-02-13
* Bumps "Tested up to" version to 5.9

= 1.8.3 =
* Release date: 2021-08-07
* Bumps "Tested up to" version to 5.8
* Improves string translations and adds some missing strings that couldn't, previously be translated. Props @alexclassroom

= 1.8.2 =
* Release date: 2021-06-01
* Bumps "Tested up to" version to 5.7

= 1.8.1 =
* Release date: 2020-12-14
* Corrects settings page notice link to actually link to the current user's profile page.

= 1.8.0 =
* Release date: 2020-12-14
* Bumps "Tested up to" version to 5.6
* Adds a notice to the settings page reminding you that you can edit your author slug on your profile page.
* Drops support for IE 10 on the settings page.

= 1.7.0 =
* Release date: 2020-06-07
* Bumps the minimum required version to WordPress 4.9.
* Bumps "Tested up to" version to 5.4.1
* Fixes an error that may occur with some install of iThemes Security

= 1.6.1 =
* Release date: 2019-09-05
* Removes pre-WP 4.5 cache busting. The minimum version has been 4.7 for quite some time, and, as of WP 4.5, the cache busting was redundant.
* Don't show the options page if `ba_eas_can_edit_author_slug()` returns `false`.
* Bumps "Tested up to" version to 5.2.3

= 1.6.0 =
* Release date: 2018-10-11
* Minimum required WordPress version is now 4.7
* Settings page JS no longer depends on jQuery (switched to plain js)
* Add compatibility for iThemes Force Unique Nickname WordPress Tweak
* Add a hash string as an author slug option
* Minimum PHP version has been bumped to 5.3. This is a soft bump, meaning, the plugin should still run on PHP 5.2. However, PHP 5.2 is no longer, officially, supported.

= 1.5.2 =
* Release date: 2017-06-21
* Fixed a regression where those using the default author based couldn't remove front unless they were also using role-based author bases. Props @thatherton.

= 1.5.1 =
* Release date: 2017-06-02
* Fix PHP notice when manually updating a user profile. Props @mydigitalsauce.

= 1.5.0 =
* Release date: 2017-05-30
* Bumped minimum required WordPress version to 4.4.
* Refactored bulk upgrading again. The original fix made things better, but not as good as it could be. This new refactoring drastically improves performance and memory usage.
* This release is primarily an under-the-hood release with a number of optimizations and performance improvements.

= 1.4.1 =
* Release date: 2017-04-24
* Fix failing string replacement in bulk update message.

= 1.4.0 =
* Release date: 2017-04-04
* Lots of code cleanup to better adhere to WordPress Coding Standards.
* Improved performance of `ba_eas_sanitize_author_base()` by preventing unnecessary processing.
* Fixed an issue where the demo author permalink URL could have a double slash.
* Improvements to bulk update for sites with a large user base.

= 1.3.0 =
* Release date: 2017-01-25
* Fix a potential bug where a sanitized author base could end up with double forward slashes.
* Introduce the `%ba_eas_author_role%` permalink structure tag. This can be used to customize role-based author bases.
* Bonus: All alternative facts are now free!

= 1.2.1 =
* Release date: 2016-02-29
* Fixed stupid error where the default user nicename wasn't being properly retrieved from the database. Sorry about that :(
* Unfortunately, some unicorns were lost during the development of this release, but they are a resilient creature.

= 1.2.0 =
* Release date: 2016-02-01
* Added the ability to use forward slashes in the author base.
* Improved display on the settings page, and storing, of role slugs.
* Packaged translations are now removed. Anyone interested in translating the plugin should do so at [Translate WordPress](https://translate.wordpress.org/projects/wp-plugins/edit-author-slug).
* EXPERIMENTAL: Added the ability to set the author slug to a user's numeric user id. While I have tested this, I can't be sure that no one's site will implode. If all goes well, the experimental tag will be removed in the next major release (or two).
* Added ability to remove the front portion of author links.
* Accessibility improvements to the settings page.

= Full Changelog =
* https://github.com/thebrandonallen/edit-author-slug/blob/master/CHANGELOG.md
