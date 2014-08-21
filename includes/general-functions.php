<?php

/**
 * Edit Author Slug Core Functions
 *
 * @package Edit_Author_Slug
 * @subpackage Core
 *
 * @author Brandon Allen
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) { exit; }

/** Nicename ******************************************************************/

/**
 * Determines if an auto-update should occur
 *
 * @since 0.9.0
 *
 * @uses ba_eas() BA_Edit_Author_Slug object
 * @uses apply_filters() To call 'ba_eas_do_auto_update' hook
 *
 * @return bool True if auto-update enabled
 */
function ba_eas_do_auto_update() {

	// Return a bool of the auto-update option value
	return (bool) apply_filters( 'ba_eas_do_auto_update', ba_eas()->do_auto_update );
}

/**
 * Auto-update the user_nicename for a given user.
 *
 * @since 0.9.0
 *
 * @param int $user_id User id
 * @param bool $bulk Bulk upgrade flag. Defaults to false
 *
 * @uses ba_eas_do_auto_update() Do we auto-update?.
 * @uses get_userdata() To get the user object.
 * @uses apply_filters() To call the 'ba_eas_auto_update_user_nicename_structure' hook.
 * @uses ba_eas() BA_Edit_Author_Slug object.
 * @uses sanitize_title() To sanitize the new nicename.
 * @uses apply_filters() To call the 'ba_eas_pre_auto_update_user_nicename' hook.
 * @uses remove_action() To remove the 'ba_eas_auto_update_user_nicename_single' and prevent looping.
 * @uses wp_update_user() Update to new user_nicename.
 * @uses is_wp_error() To make sure update_user was successful before we clear the cache.
 * @uses ba_eas_update_nicename_cache() There’s always money in the banana stand!
 * @uses add_action() To re-add the 'ba_eas_auto_update_user_nicename_single' hook.
 *
 * @return int $user_id. False on failure
 */
function ba_eas_auto_update_user_nicename( $user_id, $bulk = false ) {

	// Bail if there's no id or object
	if ( empty( $user_id ) ) {
		return false;
	}

	// If we're not bulk updating, check if we should auto-update
	if ( false === $bulk ) {

		// Should we auto-update
		if ( ! ba_eas_do_auto_update() ) {
			return false;
		}
	}

	// Get WP_User object
	$user = get_userdata( $user_id );

	// Double check we're still good
	if ( ! is_object( $user ) || empty( $user ) ) {
		return false;
	}

	// Setup the user_id
	if ( ! empty( $user->ID ) ) {
		$user_id  = (int) $user->ID;

	// No user_id so bail
	} else {
		return false;
	}

	// Get the default nicename structure
	$structure = apply_filters( 'ba_eas_auto_update_user_nicename_structure', ba_eas()->default_user_nicename, $user_id );

	// Make sure we have a structure
	if ( empty( $structure ) ) {
		$structure = 'username';
	}

	// Setup the current nicename
	if ( empty( $user->user_nicename ) ) {
		$current_nicename = $user->user_nicename;
	} else {
		$current_nicename = $user->user_login;
	}

	// Setup default nicename
	$nicename = $current_nicename;

	// Setup the new nicename based on the provided structure
	switch ( $structure ) {

		case 'username':

			if ( ! empty( $user->user_login ) ) {
				$nicename = $user->user_login;
			}

			break;

		case 'nickname':

			if ( ! empty( $user->nickname ) ) {
				$nicename = $user->nickname;
			}

			break;

		case 'displayname':

			if ( ! empty( $user->display_name ) ) {
				$nicename = $user->display_name;
			}

			break;

		case 'firstname':

			if ( ! empty( $user->first_name ) ) {
				$nicename = $user->first_name;
			}

			break;

		case 'lastname':

			if ( ! empty( $user->last_name ) ) {
				$nicename = $user->last_name;
			}

			break;

		case 'firstlast':

			if ( ! empty( $user->first_name ) && ! empty( $user->last_name ) ) {
				$nicename = $user->first_name . '-' . $user->last_name;
			}

			break;

		case 'lastfirst':

			if ( ! empty( $user->first_name ) && ! empty( $user->last_name ) ) {
				$nicename = $user->last_name . '-' . $user->first_name;
			}

			break;
	}

	// Sanitize the new nicename
	$nicename = apply_filters( 'ba_eas_pre_auto_update_user_nicename', sanitize_title( $nicename ), $user_id, $structure );

	// Bail if nothing changed
	if ( $nicename == $current_nicename ) {
		return $user_id;
	}

	// Remove the auto-update actions so we don't find ourselves in a loop
	remove_action( 'profile_update', 'ba_eas_auto_update_user_nicename_single' );

	// Update if there's a change
	$user_id = wp_update_user( array( 'ID' => $user_id, 'user_nicename' => $nicename ) );

	/*
	 * Since this is an action taken without the user's knowledge
	 * we must fail silently here. Therefore, we only want to update
	 * the cache if we're successful.
	 */
	if ( ! empty( $user_id ) && ! is_wp_error( $user_id ) ) {

		// Update the nicename cache
		ba_eas_update_nicename_cache( $user_id, $current_nicename, $nicename );
	}

	// Add it back in case other plugins do some updating
	add_action( 'profile_update', 'ba_eas_auto_update_user_nicename_single' );

	return $user_id;
}

/**
 * Auto-update the user_nicename for a given user.
 *
 * Runs on profile updates and registrations
 *
 * @since 0.9.0
 *
 * @param int $user_id User id
 *
 * @uses ba_eas_auto_update_user_nicename() To auto-update the nicename.
 */
function ba_eas_auto_update_user_nicename_single( $user_id = 0 ) {
	ba_eas_auto_update_user_nicename( $user_id );
}

/**
 * Auto-update the user_nicename for a given user.
 *
 * Runs during the bulk upgrade process in the Dashboard
 *
 * @since 0.9.0
 *
 * @param int $user_id User id
 *
 * @uses ba_eas_auto_update_user_nicename() To auto-update the nicename.
 */
function ba_eas_auto_update_user_nicename_bulk( $user_id = 0 ) {
	ba_eas_auto_update_user_nicename( $user_id, true );
}

/** Author Base ***************************************************************/

/**
 * Determines if we should do a role-based author base
 *
 * @since 1.0.0
 *
 * @uses ba_eas() BA_Edit_Author_Slug object
 * @uses apply_filters() To call 'ba_eas_do_role_based_author_base' hook.
 *
 * @return bool True if role-based author base enabled.
 */
function ba_eas_do_role_based_author_base() {

	// Return a bool of the role-based author base option value
	return (bool) apply_filters( 'ba_eas_do_role_based_author_base', ba_eas()->do_role_based );
}

/**
 * Replaces author role rewrite tag with the role of the user.
 *
 * If the user has more than one role, the first role listed in
 * WP_User::$roles will be used.
 *
 * @since 1.0.0
 *
 * @param string $link
 * @param int $user_id
 * @param string $nicename
 *
 * @uses ba_eas_do_role_based_author_base() To determine if we're doing role-based author bases.
 * @uses get_userdata() WP_User object
 * @uses ba_eas() BA_Edit_Author_Slug object
 *
 * @return string Author archive link
 */
function ba_eas_author_link( $link = '', $user_id = 0, $nicename = '' ) {

	// Add a role slug if we're doing role based author bases
	if ( ba_eas_do_role_based_author_base() && false !== strpos( $link, '%ba_eas_author_role%' ) ) {

		// Setup the user
		$user = get_userdata( $user_id );

		// Grab the first listed role
		$role = '';
		if ( ! empty( $user->roles ) && is_array( $user->roles ) ) {
			$role = array_shift( $user->roles );
		}

		// Make sure we have a valid slug
		$slug = empty( ba_eas()->role_slugs[ $role ]['slug'] ) ? ba_eas()->author_base : ba_eas()->role_slugs[ $role ]['slug'];

		// Add the role slug to the link
		$link = str_replace( '%ba_eas_author_role%', $slug, $link );
	}

	// Return the link
	return $link;
}

/**
 * Allow author templates to be based on role.
 *
 * Instead of only using author-{user_nicename}.php, author-{ID}.php, and author.php
 * for templates, this allows author-{role}.php or author-{role-slug}.php to be used as well.
 *
 * @since 1.0.0
 *
 * @param string $template Current template according to template hierarchy
 *
 * @uses get_queried_object() To get the queried object (should be WP_User object).
 * @uses ba_eas() BA_Edit_Author_Slug object
 * @uses locate_template() To see if we have role-based templates.
 *
 * @return string Author archive link
 */
function ba_eas_template_include( $template ) {

	// Bail if we're not doing role-based author bases
	if ( ! ba_eas_do_role_based_author_base() ) {
		return $template;
	}

	// Get queried object, should be a WP_User object
	$author = get_queried_object();

	// Make sure we have a WP_User object
	if ( ! is_a( $author, 'WP_User' ) ) {
		return $template;
	}

	// nicename and ID templates should take priority, so we need to check for their existence
	$nicename_template = strpos( $template, "author-{$author->user_nicename}.php" );
	$id_template       = strpos( $template, "author-{$author->ID}.php"            );

	// If they don't exist, search for a role based template
	if ( false === $nicename_template && false === $id_template ) {

		// Grab the first listed role
		if ( ! empty( $author->roles ) && is_array( $author->roles ) ) {
			$role = array_shift( $author->roles );
		}

		// Get the role slug
		$role_slug = ba_eas()->role_slugs[ $role ]['slug'];

		// Set the templates array
		$templates = array(
			( empty( $role )      ) ? false : "author-{$role}.php",
			( empty( $role_slug ) ) ? false : "author-{$role_slug}.php",
		);

		// Check for the template
		$new_template = locate_template( $templates );

		// If we have a role-based template, let's set it to be loaded
		if ( '' != $new_template ) {
			$template = $new_template;
		}
	}

	return $template;
}

/** Miscellaneous *************************************************************/

/**
 * Delete WP generated rewrite rules from database.
 *
 * Rules will be recreated on next page load.
 *
 * @since 0.9.5
 *
 * @uses delete_option() To auto-update the nicename
 */
function ba_eas_flush_rewrite_rules() {
	delete_option( 'rewrite_rules' );
}

/*
 * Filter out unnecessary rewrite rules from the author
 * rules array.
 *
 * @param array Author rewrite rules
 *
 * @uses ba_eas_do_role_based_author_base() To determine if we're doing role-based author bases.
 *
 * @return array Author rewrite rules
 */
function ba_eas_author_rewrite_rules( $author_rewrite_rules ) {

	if ( ba_eas_do_role_based_author_base() ) {
		// Filter out the rules without the author_name parameter
		// We don't need them
		foreach ( $author_rewrite_rules as $rule => $query ) {
			if ( false === strpos( $query, 'author_name' ) ) {
				unset( $author_rewrite_rules[ $rule ] );
			}
		}
	}

	return $author_rewrite_rules;
}

/**
 * Fetch a filtered list of user roles that the current user is
 * allowed to edit.
 *
 * Simple function who's main purpose is to allow filtering of the
 * list of roles in the $wp_roles object so that plugins can remove
 * inappropriate ones depending on the situation or user making edits.
 * Specifically because without filtering anyone with the edit_users
 * capability can edit others to be administrators, even if they are
 * only editors or authors. This filter allows admins to delegate
 * user management.
 *
 * @since 1.0.0
 *
 * @global object $wp_roles WP_Roles object
 * @uses sanitize_title() To sanitize the role slug
 *
 * @return array WP_Roles::roles object
 */
function ba_eas_get_editable_roles() {
	global $wp_roles;

	// Make sure wp_roles has been set
	if ( empty( $wp_roles ) ) {
		$wp_roles = new WP_Roles();
	}

	/**
	 * Filter the list of editable roles.
	 *
	 * @since 1.0.0
	 *
	 * @param array WP_Roles::roles List of roles.
	 */
	$editable_roles = apply_filters( 'editable_roles', $wp_roles->roles );

	// Remove user caps
	foreach ( $editable_roles as $role => $details ) {
		unset( $editable_roles[ $role ]['capabilities'] );
	}

	return $editable_roles;
}

/**
 * Get an list of default role slugs
 *
 * @since 1.0.2
 *
 * @uses ba_eas_get_editable_roles() To sanitize the role slug
 * @uses translate_user_role() To translate default WP role names
 * @uses sanitize_title() To sanitize the translated role name into a role slug
 *
 * @return array Role slugs array
 */
function ba_eas_get_default_role_slugs() {

	// Get a filtered list of roles
	$roles = ba_eas_get_editable_roles();

	// Convert role names into role slugs
	foreach ( $roles as $role => $details ) {
		$roles[ $role ]['slug'] = sanitize_title( translate_user_role( $details['name'] ) );
	}

	return $roles;
}

/**
 * Add array_replace_recursive() for users of PHP 5.2.x
 *
 * http://php.net/manual/en/function.array-replace-recursive.php#109390
 *
 * @since 1.0.2
 *
 * @return array Role slugs array
 */
if ( ! function_exists( 'array_replace_recursive' ) ) {
	function array_replace_recursive( $base, $replacements ) {
		foreach ( array_slice( func_get_args(), 1 ) as $replacements ) {
			$bref_stack = array( &$base );
			$head_stack = array( $replacements );

			do {
				end( $bref_stack );

				$bref = &$bref_stack[ key( $bref_stack ) ];
				$head = array_pop( $head_stack );

				unset( $bref_stack[ key( $bref_stack ) ] );

				foreach ( array_keys( $head ) as $key ) {
					if ( isset( $key, $bref, $bref[ $key ] ) && is_array( $bref[ $key ] ) && is_array( $head[ $key ] ) ) {
						$bref_stack[] = &$bref[ $key ];
						$head_stack[] = $head[ $key ];
					} else {
						$bref[ $key ] = $head[ $key ];
					}
				}
			} while ( count( $head_stack ) );
		}

		return $base;
	}
} // end function exists check

/**
 * Clean and update the nicename cache.
 *
 * @since 1.0.0
 *
 * @param int $user_id
 * @param object|string $old_user_data WP_User object when coming from hook. Old nicename otherwise.
 * @param string $new_nicename
 *
 * @uses get_userdata() To get a WP_User object.
 * @uses wp_cache_delete() To delete the old nicename from cache.
 * @uses wp_cache_add() To add the new nicename to cache.
 */
function ba_eas_update_nicename_cache( $user_id = 0, $old_user_data = '', $new_nicename = '' ) {

	// Bail if there's no user
	if ( empty( $user_id ) && empty( $old_user_data->ID ) ) {
		return;
	}

	// Get a user_id. This will probably never happen.
	if ( empty( $user_id ) ) {
		$user_id = $old_user_data->ID;
	}

	// We got here via 'profile_update'
	if ( empty( $new_nicename ) ) {

		// Set the old nicename
		$old_nicename = $old_user_data->user_nicename;

		// Get the new nicename
		$user = get_userdata( $user_id );
		$new_nicename = $user->user_nicename;

	// We're passing our own data
	} else {

		// Set the old nicename
		$old_nicename = $old_user_data;
	}

	// Delete the old nicename from the cache
	wp_cache_delete( $old_nicename, 'userslugs' );

	// Add the new nicename to the cache
	wp_cache_add( $new_nicename, $user_id, 'userslugs' );
}
