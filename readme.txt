=== Edit Author Slug ===
Contributors: cnorris23
Tags: slug, author, permalink, users, user, admin
Requires at least: 2.8
Tested up to: 2.8.6
Stable tag: 0.1.2

This plugin allows you to change the Author slug, without having to actually enter the database.

== Description ==

This plugin allows you to change the Author slug, without having to actually enter the database. Assuming you are an admin or a user with the "edit_users" capability, you will see an extra field on your user edit/profile edit page. This allows for greater security, as your user name isn't revealed through your URL structure. It also allows you to make your URLs look a bit cleaner. (i.e. - http://example.com/author/username/ could become http://example.com/author/user-name/)

== Installation ==

1. Upload `edit-author-slug` folder to your WordPress plugins directory (typically 'wp-content/plugins')
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Go to your Profile page, or the profile page of another user and edit their Author slug.
1. Click the update button, and you're done

== Screenshots ==
1. Edit Author Slug screenshot

== Frequently Asked Questions ==

= Nothing shows up when I go to my profile page? =

Make sure you are an admin, or someone with the `edit_users` capability.

= What if I'm not an admin? =

You will not be able to change your author slug. Changing the author slug too frequently, or to something non-descriptive is bad SEO, so we'll leave this ability to a chosen few. If this doesn't work for you, consider Justin Tadlock's [Members] (http://wordpress.org/extend/plugins/members/ "Justin Tadlock's Members plugin") plugin.

== Changelog ==

= 0.1.2 =
* fix version number issues

= 0.1.1 =
* Remove extra debug functions left behind
* Add screenshot

= 0.1 =
* Initial release