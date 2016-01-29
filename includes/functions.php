<?php
/**
 * Edit Author Slug Core Functions
 *
 * @package Edit_Author_Slug
 * @subpackage Core
 *
 * @author Brandon Allen
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/** Nicename ******************************************************************/

/**
 * Determines if an auto-update should occur
 *
 * @since 0.9.0
 *
 * @return bool True if auto-update enabled.
 */
function ba_eas_do_auto_update() {

	/**
	 * Filters the return of the `do_auto_update` option.
	 *
	 * @since 0.9.0
	 *
	 * @param bool $do_auto_update The `do_auto_update` option.
	 */
	return (bool) apply_filters( 'ba_eas_do_auto_update', ba_eas()->do_auto_update );
}

/**
 * Auto-update the user_nicename for a given user.
 *
 * @since 0.9.0
 *
 * @param int    $user_id   User id.
 * @param bool   $bulk      Bulk upgrade flag. Defaults to false.
 * @param string $structure The nicename structure to use during update.
 *
 * @return bool|int User id on success. False on failure.
 */
function ba_eas_auto_update_user_nicename( $user_id, $bulk = false, $structure = '' ) {

	// Bail if there's no id or object.
	if ( empty( $user_id ) ) {
		return false;
	}

	// Bail if we're not bulk updating and auto-update is disabled.
	if ( false === $bulk && ! ba_eas_do_auto_update() ) {
		return false;
	}

	// Get WP_User object.
	$user = get_userdata( $user_id );

	// Double check we're still good.
	if ( empty( $user->ID ) ) {
		return false;
	}

	// Setup the user_id.
	$user_id = (int) $user->ID;

	if ( empty( $structure ) ) {
		$structure = ba_eas()->default_user_nicename;
	}

	/**
	 * Filters the auto-update user nicename structure.
	 *
	 * @since 0.9.0
	 *
	 * @param string $structure The auto-update structure.
	 * @param int    $user_id   The user id.
	 */
	$structure = apply_filters( 'ba_eas_auto_update_user_nicename_structure', $structure, $user_id );

	// Make sure we have a structure.
	if ( empty( $structure ) ) {
		$structure = 'username';
	}

	// Setup the current nicename.
	$old_nicename = $user->user_login;
	if ( ! empty( $user->user_nicename ) ) {
		$old_nicename = $user->user_nicename;
	}

	// Setup default nicename.
	$nicename = $old_nicename;

	// Setup the new nicename based on the provided structure.
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

		case 'userid':

			$nicename = $user_id;

			break;
	}

	// Sanitize and trim the new user nicename.
	$nicename = ba_eas_trim_nicename( ba_eas_sanitize_nicename( $nicename ) );

	/**
	 * Filters the auto-updated user nicename before being saved.
	 *
	 * @since 0.9.0
	 *
	 * @param string $nicename  The new user nicename.
	 * @param int    $user_id   The user id.
	 * @param string $structure The auto-update structure.
	 */
	$nicename = apply_filters( 'ba_eas_pre_auto_update_user_nicename', $nicename, $user_id, $structure );

	// Bail if nothing changed or the nicename is empty.
	if ( empty( $nicename ) || $nicename === $old_nicename ) {
		return false;
	}

	// Remove the auto-update actions so we don't find ourselves in a loop.
	remove_action( 'profile_update', 'ba_eas_auto_update_user_nicename' );

	// Update if there's a change.
	$user_id = wp_update_user( array( 'ID' => $user_id, 'user_nicename' => $nicename ) );

	// Add it back in case other plugins do some updating.
	add_action( 'profile_update', 'ba_eas_auto_update_user_nicename' );

	/*
	 * Since this is an action taken without the user's knowledge we must fail
	 * silently. Therefore, we only want to update the cache if we're successful.
	 */
	if ( ! empty( $user_id ) && ! is_wp_error( $user_id ) ) {

		// Update the nicename cache.
		ba_eas_update_nicename_cache( $user_id, $user, $nicename );
	}

	return $user_id;
}

/**
 * Auto-update the user_nicename for a given user.
 *
 * Runs on profile updates and registrations
 *
 * @since 0.9.0
 *
 * @deprecated 1.1.0 Use `ba_eas_auto_update_user_nicename()` instead.
 *
 * @param int $user_id The user id.
 *
 * @return bool|int $user_id. False on failure.
 */
function ba_eas_auto_update_user_nicename_single( $user_id = 0 ) {
	_deprecated_function( __FUNCTION__, '1.1.0', 'ba_eas_auto_update_user_nicename' );
	return ba_eas_auto_update_user_nicename( $user_id );
}

/**
 * Auto-update the user_nicename for a given user.
 *
 * Runs during the bulk upgrade process in the Dashboard.
 *
 * @since 0.9.0
 *
 * @param string $value The option value passed to the settings API.
 *
 * @return bool False to prevent the setting from being saved to the db.
 */
function ba_eas_auto_update_user_nicename_bulk( $value = false ) {

	// Nonce check.
	check_admin_referer( 'edit-author-slug-options' );

	// Default the structure to the auto-update structure.
	$structure = ba_eas()->default_user_nicename;

	// If a bulk update structure was passed, use that.
	if ( isset( $_POST['_ba_eas_bulk_update_structure'] ) ) {
		$structure = sanitize_key( $_POST['_ba_eas_bulk_update_structure'] );
	}

	// Sanitize the option value.
	$value = (bool) absint( $value );

	// Bail if the user didn't ask to run the bulk update.
	if ( ! $value ) {
		return false;
	}

	// Get an array of ids of all users.
	$users = get_users( array( 'fields' => 'ID' ) );

	/**
	 * Filters the array of user ids who will have their user nicenames updated.
	 *
	 * @since 1.1.0
	 *
	 * @param array $users The array of user ids to update.
	 */
	$users = (array) apply_filters( 'ba_eas_auto_update_user_nicename_bulk_user_ids', $users );

	// Set the default updated count.
	$updated = 0;

	// Loop through all the users and maybe update their nicenames.
	foreach ( $users as $user_id ) {

		// Maybe update the user nicename.
		$id = ba_eas_auto_update_user_nicename( $user_id, true, $structure );

		// If updating was a success, the bump the updated count.
		if ( ! empty( $id ) && ! is_wp_error( $id ) ) {
			$updated++;
		}
	}

	// Add a message to the settings page denoting user how many users were updated.
	add_settings_error(
		'_ba_eas_bulk_auto_update',
		'bulk_user_nicenames_updated',
		sprintf( __( '%d user author slug(s) updated.', 'edit-author-slug' ), $updated ),
		'updated'
	);

	// Return false to short-circuit the update_option routine, and prevent saving.
	return false;
}

/**
 * Helper function to sanitize a nicename in the same manner as the WP function
 * `wp_insert_user()`.
 *
 * If `$strict` is set to true, this function will result in a nicename that
 * only contains the alphanumeric characters, underscores (_) and dashes (-).
 *
 * @since 1.1.0
 *
 * @param string $nicename The nicename being sanitized.
 * @param bool   $strict   True to return only ASCII characters.
 *
 * @return string The nicename.
 */
function ba_eas_sanitize_nicename( $nicename = '', $strict = true ) {
	return sanitize_title( sanitize_user( $nicename, (bool) $strict ) );
}

/**
 * Sanitize author base and add to database.
 *
 * @since 0.8.0
 * @since 1.2.0 Removed all non-sanitization code.
 *
 * @param string $author_base Author base to be sanitized.
 *
 * @return string The author base.
 */
function ba_eas_sanitize_author_base( $author_base = 'author' ) {

	// Store the author base as passed.
	$original_author_base = $author_base;

	// Only do extra sanitization when needed.
	if ( ! empty( $author_base ) || 'author' !== $author_base ) {

		// Split the author base string on forward slashes.
		$parts = array_filter( explode( '/', $author_base ) );

		// Sanitize the parts, and put them back together.
		$author_base = implode( '/', array_map( 'sanitize_title', $parts ) );
	}

	// Always default to `author`.
	if ( empty( $author_base ) ) {
		$author_base = 'author';
	}

	/**
	 * Filters the sanitized author base.
	 *
	 * @param string $author_base          The sanitized author base.
	 * @param string $original_author_base The unsanitized author base.
	 */
	return apply_filters( 'ba_eas_sanitize_author_base', $author_base, $original_author_base );
}

/**
 * Helper function to escape the nicename in the same manner as other slugs are
 * escaped in WP.
 *
 * @since 1.1.0
 *
 * @param string $nicename The nicename being sanitized.
 *
 * @return string The nicename.
 */
function ba_eas_esc_nicename( $nicename = '' ) {
	return esc_textarea( urldecode( $nicename ) );
}

/**
 * Helper function to trim the nicename to less than 50 characters, and strip
 * off any leading/trailing hyphens or underscores.
 *
 * @since 1.1.0
 *
 * @param string $nicename The nicename being sanitized.
 *
 * @return string The nicename.
 */
function ba_eas_trim_nicename( $nicename = '' ) {
	return trim( mb_substr( $nicename, 0, 50 ), '-_' );
}

/**
 * Helper function to check a nicename for characters that won't be converted
 * to ASCII characters.
 *
 * Before being saved to the db, `wp_insert_user()` converts nicenames by
 * running them through `sanitize_user()` with the `$strict` parameter set to
 * `true`, then through `sanitize_title()`. This results in a user nicename that
 * only contains alphanumeric characters, underscores (_) and dashes (-). Rather
 * than silently strip invalid characters, this function allows us to inform the
 * editing user that their passed user nicename contains characters that won't
 * make it through the `wp_insert_user()` sanitization process.
 *
 * @since 1.1.0
 *
 * @param string $nicename The nicename to check for invalid characters.
 *
 * @return bool True if the nicename contains only ASCII characters, or
 *              characters that can be converted to ASCII.
 */
function ba_eas_nicename_is_ascii( $nicename = '' ) {
	return ba_eas_sanitize_nicename( $nicename ) === ba_eas_sanitize_nicename( $nicename, false );
}

/** Author Base ***************************************************************/

/**
 * Overrides the WP_Rewrite properties, `author_base` and `author_structure`,
 * when appropriate.
 *
 * @since 1.2.0
 *
 * @return void
 */
function ba_eas_wp_rewrite_overrides() {

	// Set default author base.
	$author_base = 'author';

	// Set to our author base if it exists.
	if ( ! empty( ba_eas()->author_base ) ) {
		$author_base = ba_eas()->author_base;
	}

	// If doing role-based, set accordingly.
	if ( ba_eas_do_role_based_author_base() ) {
		$author_base = '%ba_eas_author_role%';
	}

	// Override WP_Rewrite::author_base with our new value.
	$GLOBALS['wp_rewrite']->author_base = $author_base;

	// Override `WP_Rewrite::author_structure` with our new value.
	if ( ba_eas_remove_front() && ba_eas_has_front() ) {
		$GLOBALS['wp_rewrite']->author_structure = '/' . $author_base . '/%author%';
	}
}

/**
 * Determines if we should remove the `front` portion of the author structure.
 *
 * @since 1.2.0
 *
 * @return bool
 */
function ba_eas_remove_front() {

	/**
	 * Filters the return of the `remove_front` option.
	 *
	 * @since 1.2.0
	 *
	 * @param bool $remove_front The `remove_front` option.
	 */
	return (bool) apply_filters( 'ba_eas_remove_front', ba_eas()->remove_front );
}

/**
 * Determines if `WP_Rewrite::front` is anything other than `/`.
 *
 * @since 1.2.0
 *
 * @return bool
 */
function ba_eas_has_front() {

	/**
	 * Filters the return of the `ba_eas_has_front` option.
	 *
	 * @since 1.2.0
	 *
	 * @param bool $has_front The `remove_front` option.
	 */
	return (bool) apply_filters( 'ba_eas_has_front', '/' !== $GLOBALS['wp_rewrite']->front );
}

/**
 * Determines if we should do a role-based author base
 *
 * @since 1.0.0
 *
 * @return bool True if role-based author base enabled.
 */
function ba_eas_do_role_based_author_base() {

	/**
	 * Filters the return of the `do_role_based` option.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $do_role_based The `do_role_based` option.
	 */
	return (bool) apply_filters( 'ba_eas_do_role_based_author_base', ba_eas()->do_role_based );
}

/**
 * Replaces author role rewrite tag with the role of the user.
 *
 * If the user has more than one role, the first role listed in the WP_User::$roles
 * array will be used.
 *
 * @since 1.0.0
 *
 * @param string $link    The author link with user role as author base.
 * @param int    $user_id The user id.
 *
 * @return string Author archive link.
 */
function ba_eas_author_link( $link = '', $user_id = 0 ) {

	// Add a role slug if we're doing role based author bases.
	if ( ba_eas_do_role_based_author_base() && false !== strpos( $link, '%ba_eas_author_role%' ) ) {

		// Setup the user.
		$user = get_userdata( $user_id );

		// Grab the first listed role.
		$role = ba_eas_get_user_role( $user->roles, $user_id );

		// Make sure we have a valid slug.
		$slug = empty( ba_eas()->role_slugs[ $role ]['slug'] ) ? ba_eas()->author_base : ba_eas()->role_slugs[ $role ]['slug'];

		// Add the role slug to the link.
		$link = str_replace( '%ba_eas_author_role%', $slug, $link );
	}

	// Remove front if applicable.
	if ( ba_eas_has_front() && ba_eas_remove_front() ) {
		$link = str_replace( $GLOBALS['wp_rewrite']->front, '/', $link );
	}

	// Return the link.
	return $link;
}

/**
 * Allow author templates to be based on role.
 *
 * Instead of only using author-{user_nicename}.php, author-{ID}.php, and
 * author.php for templates, this allows author-{role}.php or
 * author-{role-slug}.php to be used as well.
 *
 * @since 1.0.0
 *
 * @param string $template Current template according to template hierarchy.
 *
 * @return string Author archive link.
 */
function ba_eas_template_include( $template ) {

	// Bail if we're not doing role-based author bases.
	if ( ! ba_eas_do_role_based_author_base() ) {
		return $template;
	}

	// Get queried object, should be a WP_User object.
	$author = get_queried_object();

	// Make sure we have a WP_User object.
	if ( ! is_a( $author, 'WP_User' ) ) {
		return $template;
	}

	// Nicename and ID templates should take priority, so we need to check for their existence.
	$nicename_template = strpos( $template, "author-{$author->user_nicename}.php" );
	$id_template       = strpos( $template, "author-{$author->ID}.php" );

	// If they don't exist, search for a role based template.
	if ( false === $nicename_template && false === $id_template ) {

		// Grab the first listed role.
		$role = ba_eas_get_user_role( $author->roles, $author->ID );

		// Get the role slug.
		$role_slug = '';
		if ( ! empty( ba_eas()->role_slugs[ $role ]['slug'] ) ) {
			$role_slug = ba_eas()->role_slugs[ $role ]['slug'];
		}

		// Set the templates array.
		$templates = array();

		// Add the role template.
		if ( ! empty( $role ) ) {
			$templates[] = "author-{$role}.php";
		}

		// Add the role_slug template.
		if ( ! empty( $role_slug ) ) {
			$templates[] = "author-{$role_slug}.php";
		}

		// Check for the template.
		$new_template = locate_template( $templates );

		// If we have a role-based template, let's set it to be loaded.
		if ( '' !== $new_template ) {
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
 */
function ba_eas_flush_rewrite_rules() {
	update_option( 'rewrite_rules', '' );
}

/**
 * Filter out unnecessary rewrite rules from the author
 * rules array.
 *
 * @param array $author_rewrite_rules Author rewrite rules.
 *
 * @return array Author rewrite rules.
 */
function ba_eas_author_rewrite_rules( $author_rewrite_rules ) {

	if ( ba_eas_do_role_based_author_base() ) {
		// Filter out the rules without the author_name parameter. We don't need them.
		foreach ( $author_rewrite_rules as $rule => $query ) {
			if ( false === strpos( $query, 'author_name' ) ) {
				unset( $author_rewrite_rules[ $rule ] );
			}
		}
	}

	return $author_rewrite_rules;
}

/**
 * Return the first listed role of a user's role array.
 *
 * @since 1.1.0
 *
 * @param array $roles   An array of user roles.
 * @param int   $user_id The user id.
 *
 * @return string The user's first listed role.
 */
function ba_eas_get_user_role( $roles = array(), $user_id = 0 ) {

	// Set the default role to empty.
	$role = '';

	// Grab the first listed role.
	if ( ! empty( $roles ) && is_array( $roles ) ) {
		$role = array_shift( $roles );

	// If no roles were passed, try using the user id to get them.
	} elseif ( ! empty( $user_id ) ) {

		// Get the WP_User object.
		$user = get_userdata( $user_id );

		// If the roles aren't empty, grab the first listed role.
		if ( ! empty( $user->roles ) ) {
			$role = array_shift( $user->roles );
		}
	}

	/**
	 * Filters the author role.
	 *
	 * @since 1.1.0
	 *
	 * @param string $role    The first listed user role.
	 * @param int    $user_id The user id.
	 */
	return apply_filters( 'ba_eas_get_user_role', $role, $user_id );
}

/**
 * Returns the WP_Roles object.
 *
 * WP 4.3 added the `wp_roles()` function to facilitate the instantiation of the
 * WP_Roles object. This is a wrapper function for `wp_roles()` with a fallback
 * for those on WP < 4.3.
 *
 * @global WP_Roles $wp_roles
 *
 * @return WP_Roles
 */
function ba_eas_get_wp_roles() {

	if ( function_exists( 'wp_roles' ) ) {
		$wp_roles = wp_roles();

	} else {

		global $wp_roles;

		// Make sure the `$wp_roles` global has been set.
		if ( ! isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles();
		}
	}

	return $wp_roles;
}

/**
 * Return an array of WP roles.
 *
 * The capabilities array for each role have been removed.
 *
 * @since 1.2.0
 *
 * @global WP_Roles $wp_roles
 *
 * @return array
 */
function ba_eas_get_roles() {

	// Get the `WP_Roles` object.
	$wp_roles = ba_eas_get_wp_roles();

	// Pull out just the roles array.
	$_wp_roles = $wp_roles->roles;

	// Remove user caps.
	foreach ( $_wp_roles as $role => $details ) {
		unset( $_wp_roles[ $role ]['capabilities'] );
	}

	return $_wp_roles;
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
 * @global WP_Roles $wp_roles The WP_Roles object.
 *
 * @return array $editable_roles List of editable roles.
 */
function ba_eas_get_editable_roles() {

	// Get the `WP_Roles` object.
	$wp_roles = ba_eas_get_wp_roles();

	$roles = array();
	if ( ! empty( $wp_roles->roles ) ) {
		$roles = $wp_roles->roles;
	}

	/**
	 * Filter the list of editable roles.
	 *
	 * @since 1.0.0
	 *
	 * @param array $roles The return of WP_Roles::roles.
	 */
	$editable_roles = apply_filters( 'editable_roles', $roles );

	// Remove user caps.
	if ( ! empty( $editable_roles ) ) {
		foreach ( $editable_roles as $role => $details ) {
			unset( $editable_roles[ $role ]['capabilities'] );
		}
	}

	return $editable_roles;
}

/**
 * Get an list of default role slugs
 *
 * @since 1.0.2
 *
 * @return array Role slugs array.
 */
function ba_eas_get_default_role_slugs() {

	// Get the array of WP roles.
	$roles = ba_eas_get_roles();

	// Convert role names into role slugs.
	foreach ( $roles as $role => $details ) {
		$roles[ $role ]['slug'] = sanitize_title( translate_user_role( $details['name'] ) );
	}

	return $roles;
}

if ( ! function_exists( 'array_replace_recursive' ) ) {
	/**
	 * Add array_replace_recursive() for users of PHP 5.2.x
	 *
	 * http://php.net/manual/en/function.array-replace-recursive.php#109390
	 *
	 * @since 1.0.2
	 *
	 * @codeCoverageIgnore
	 *
	 * @param array $base         The default array.
	 * @param array $replacements The new array.
	 *
	 * @return array Role slugs array.
	 */
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
} // end function exists check.

/**
 * Clean and update the nicename cache.
 *
 * @since 1.0.0
 *
 * @param int    $user_id       The user id.
 * @param object $old_user_data The WP_User object.
 * @param string $new_nicename  The new user nicename.
 */
function ba_eas_update_nicename_cache( $user_id = 0, $old_user_data = '', $new_nicename = '' ) {

	// Bail if there's no user.
	if ( empty( $user_id ) && empty( $old_user_data->ID ) ) {
		return;
	}

	// Get a user_id. This will probably never happen.
	if ( empty( $user_id ) ) {
		$user_id = $old_user_data->ID;
	}

	// We got here via `profile_update`.
	if ( empty( $new_nicename ) ) {

		// Get the new nicename.
		$user = get_userdata( $user_id );
		$new_nicename = $user->user_nicename;
	}

	// Set the old nicename.
	// Note: This check is only for back-compat. You should pass a WP_User object.
	if ( isset( $old_user_data->user_nicename ) ) {
		$old_nicename = $old_user_data->user_nicename;
	} else {
		_doing_it_wrong( __FUNCTION__, ' The function ba_eas_update_nicename_cache() expects $old_user_data to be a WP_User object.', 'Edit Author Slug 1.0.4' );
		$old_nicename = $old_user_data;
	}

	// Delete the old nicename from the cache.
	wp_cache_delete( $old_nicename, 'userslugs' );

	// Add the new nicename to the cache.
	wp_cache_add( $new_nicename, $user_id, 'userslugs' );
}
