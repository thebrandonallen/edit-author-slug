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
 * Determines if a bulk update should occur.
 *
 * @since 1.4.0
 *
 * @param int|bool $do_bulk Whether or not to perform a bulk update.
 *
 * @return bool True if bulk update should occur.
 */
function ba_eas_do_bulk_update( $do_bulk = false ) {

	// Sanitize the option value.
	$retval = ( is_numeric( $do_bulk ) || is_bool( $do_bulk ) )
		? (bool) $do_bulk
		: false;

	/**
	 * Filters the return of the `ba_eas_do_bulk_update()`.
	 *
	 * @since 1.4.0
	 *
	 * @param bool $retval The `do_auto_update` option.
	 */
	return (bool) apply_filters( 'ba_eas_do_bulk_update', $retval );
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
function ba_eas_auto_update_user_nicename( $user_id = 0, $bulk = false, $structure = '' ) {

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

	// Setup the current nicename.
	$old_nicename = $user->user_nicename;

	// Sanitize and trim the new user nicename.
	$nicename = ba_eas_get_nicename_by_structure( $user_id, $structure );

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
	$user_id = wp_update_user(
		array(
			'ID'            => $user_id,
			'user_nicename' => $nicename,
		)
	);

	// Add it back in case other plugins do some updating.
	add_action( 'profile_update', 'ba_eas_auto_update_user_nicename' );

	return $user_id;
}

/**
 * Auto-update the user_nicename for a given user.
 *
 * Runs during the bulk upgrade process in the Dashboard.
 *
 * @since 0.9.0
 *
 * @param string|bool $do_bulk The option value passed to the settings API.
 *
 * @return bool False to prevent the setting from being saved to the db.
 */
function ba_eas_auto_update_user_nicename_bulk( $do_bulk = false ) {

	// Bail if the user didn't ask to run the bulk update.
	if ( ! ba_eas_do_bulk_update( $do_bulk ) ) {
		return false;
	}

	// Nonce check.
	check_admin_referer( 'edit-author-slug-options' );

	global $wpdb;

	// Default to the auto-update nicename structure.
	$structure = ba_eas()->default_user_nicename;

	// If a bulk update structure was passed, use that.
	if ( isset( $_POST['_ba_eas_bulk_update_structure'] ) ) {
		$structure = sanitize_key( $_POST['_ba_eas_bulk_update_structure'] );
	}

	// Get an array of ids of all users.
	$users = get_users(
		array(
			'fields' => 'ID',
		)
	);

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
	$when    = array();
	$where   = array();

	// Loop through all the users and maybe update their nicenames.
	foreach ( $users as $key => $user_id ) {

		$user = get_user_by( 'id', $user_id );
		if ( empty( $user->ID ) ) {
			continue;
		}

		// Reset the max execution time.
		set_time_limit( 30 );

		$user     = get_user_by( 'id', $user_id );
		$nicename = ba_eas_get_nicename_by_structure( $user->ID, $structure );
		$exists   = ba_eas_nicename_exists( $nicename, $user );

		if ( ! $exists && $nicename && $user->nicename !== $nicename ) {
			$when[]  = $wpdb->prepare( 'WHEN %d THEN %s', $user->ID, $nicename );
			$where[] = $wpdb->prepare( '%d', $user->ID );
		}

		// Remove the processed user from the users array and clean the cache.
		unset( $users[ $key ] );
		clean_user_cache( $user );
		if ( $exists ) {
			clean_user_cache( $exists );
		}
	}

	// If we have some when statements, then update the nicenames.
	if ( ! empty( $when ) ) {

		// Setup our when and where statements.
		$when_sql  = implode( ' ', $when );
		$where_sql = '';
		if ( ! empty( $where ) ) {
			$where_sql = 'WHERE ID IN ( ' . implode( ',', $where ) . ' )';
		}

		// Run the update.
		$sql     = "
			UPDATE $wpdb->users
			SET user_nicename = CASE ID
			{$when_sql}
			ELSE user_nicename
			END
			{$where_sql}
		";
		$updated = $wpdb->query( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- This is actually prepared above.
	}

	// Unset some vars to help with memory.
	unset( $users, $when, $when_sql, $where, $where_sql );

	// Add a message to the settings page denoting user how many users were updated.
	add_settings_error(
		'_ba_eas_bulk_auto_update',
		'bulk_user_nicenames_updated',
		sprintf(
			/* translators: Updated author slugs count. */
			_n(
				'%d user author slug updated.',
				'%d user author slugs updated.',
				$updated,
				'edit-author-slug'
			),
			$updated
		),
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
 * @since 1.3.0 Allow `%ba_eas_author_role%` rewrite tag in author base.
 *
 * @param string $author_base Author base to be sanitized.
 *
 * @return string The author base.
 */
function ba_eas_sanitize_author_base( $author_base = 'author' ) {

	// Store the author base as passed.
	$original_author_base = $author_base;

	// Only do extra sanitization when needed.
	if ( ! empty( $author_base ) && 'author' !== $author_base ) {

		// Split the author base string on forward slashes.
		$parts = explode( '/', $author_base );
		$parts = array_filter( array_map( 'trim', $parts ) );

		// Sanitize all parts except our rewrite tag, `%ba_eas_author_role%`.
		foreach ( $parts as $key => $part ) {

			if ( '%ba_eas_author_role%' !== $part ) {
				$parts[ $key ] = sanitize_title( $part );
			}
		}

		// Sanitize the parts, and put them back together.
		$author_base = implode( '/', array_filter( $parts ) );
	}

	// Always default to `author`.
	if ( empty( $author_base ) ) {
		$author_base = 'author';
	}

	/**
	 * Filters the sanitized author base.
	 *
	 * @since 1.2.0
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
	$sanitize_nicename        = ba_eas_sanitize_nicename( $nicename );
	$sanitize_nicename_strict = ba_eas_sanitize_nicename( $nicename, false );
	return ( $sanitize_nicename === $sanitize_nicename_strict );
}

/**
 * Returns a nicename built according to the passed structure.
 *
 * @since 1.4.0
 *
 * @param int    $user_id   The user id.
 * @param string $structure The structure to build the nicename against.
 *
 * @return string Defaults to empty.
 */
function ba_eas_get_nicename_by_structure( $user_id = 0, $structure = '' ) {

	// Validate the user id.
	$user = get_userdata( $user_id );

	// Bail if we don't have a valid user id.
	if ( empty( $user->ID ) ) {
		return '';
	}

	// Set the default nicename.
	$nicename = '';

	// Setup the new nicename based on the provided structure.
	switch ( $structure ) {

		case 'username':
			$nicename = $user->user_login;
			break;

		case 'nickname':
			$nicename = $user->nickname;
			break;

		case 'displayname':
			$nicename = $user->display_name;
			break;

		case 'firstname':
			$nicename = $user->first_name;
			break;

		case 'lastname':
			$nicename = $user->last_name;
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
			$nicename = $user->ID;
			break;

		case 'hash':
			$nicename = hash( 'sha1', $user->ID . '-' . $user->user_login );
			break;
	} // End switch.

	// Sanitize and trim the new user nicename.
	$nicename = ba_eas_trim_nicename( ba_eas_sanitize_nicename( $nicename ) );

	/**
	 * Filters the return of `ba_eas_get_nicename_by_structure`.
	 *
	 * @since 1.4.0
	 *
	 * @param string $nicename  The nicename.
	 * @param int    $user_id   The user id.
	 * @param string $structure The passed nicename structure.
	 */
	return apply_filters( 'ba_eas_get_nicename_by_structure', $nicename, $user_id, $structure );
}

/**
 * Check if a nicename exists.
 *
 * @since 1.5.0
 *
 * @param string      $nicename   The nicename to check.
 * @param int|WP_User $user_or_id The user id or user object.
 *
 * @return bool|WP_User The WP_User object. False on failure.
 */
function ba_eas_nicename_exists( $nicename = '', $user_or_id = 0 ) {

	// Default to false.
	$retval = false;

	// Get the user objects if they exist.
	$user     = new WP_User( $user_or_id );
	$existing = get_user_by( 'slug', $nicename );

	// Return the existing user object if it exists.
	if ( ! empty( $existing->ID ) ) {
		$retval = $existing;
	}

	// Check if a user was passed and if it matches the existing user.
	if ( $retval && ! empty( $user->ID ) && $existing->ID === $user->ID ) {
		$retval = false;
	}

	/**
	 * Filters the return of `ba_eas_nicename_exists()`.
	 *
	 * @since 1.5.0
	 *
	 * @param bool|WP_User $retval     The WP_User object. False on failure.
	 * @param string       $nicename   The user nicename.
	 * @param int|WP_User  $user_or_id The user id or user object.
	 */
	return apply_filters( 'ba_eas_nicename_exists', $retval, $nicename, $user_or_id );
}

/** Author Base ***************************************************************/

/**
 * Overrides the WP_Rewrite properties, `author_base` and `author_structure`,
 * when appropriate.
 *
 * @since 1.2.0
 * @since 1.3.0 Allow `%ba_eas_author_role%` rewrite tag in author base.
 *
 * @return void
 */
function ba_eas_wp_rewrite_overrides() {

	// Set to our author base if it exists.
	$author_base = ba_eas()->author_base;

	// Get the role-based option.
	$role_based = ba_eas_do_role_based_author_base();

	// Override `WP_Rewrite::author_structure` with our new value.
	if ( ba_eas_remove_front() ) {
		$GLOBALS['wp_rewrite']->author_structure = '/' . $author_base . '/%author%';
	}

	// If we have the default author base and not doing role-based.
	if ( 'author' === $author_base && ! $role_based ) {
		return;
	}

	// If doing role-based, set accordingly.
	if ( $role_based && false === strpos( $author_base, '%ba_eas_author_role%' ) ) {
		$author_base = '%ba_eas_author_role%';
	}

	// Override WP_Rewrite::author_base with our new value.
	$GLOBALS['wp_rewrite']->author_base = $author_base;
}

/**
 * Determines if we should remove the `front` portion of the author structure.
 *
 * @since 1.2.0
 *
 * @return bool
 */
function ba_eas_remove_front() {

	$reval = ( ba_eas_has_front() && ba_eas()->remove_front );

	/**
	 * Filters the return of the `remove_front` option.
	 *
	 * @since 1.2.0
	 *
	 * @param bool $remove_front The `remove_front` option.
	 */
	return (bool) apply_filters( 'ba_eas_remove_front', $reval );
}

/**
 * Determines if `WP_Rewrite::front` is anything other than `/`.
 *
 * @since 1.2.0
 *
 * @return bool
 */
function ba_eas_has_front() {

	$retval = ( '/' !== $GLOBALS['wp_rewrite']->front );

	/**
	 * Filters the return of the `ba_eas_has_front` option.
	 *
	 * @since 1.2.0
	 *
	 * @param bool $has_front The `remove_front` option.
	 */
	return (bool) apply_filters( 'ba_eas_has_front', $retval );
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
		$slug = isset( ba_eas()->role_slugs[ $role ]['slug'] ) ? ba_eas()->role_slugs[ $role ]['slug'] : '';
		$slug = empty( $slug ) ? ba_eas()->author_base : $slug;

		// Add the role slug to the link.
		$link = str_replace( '%ba_eas_author_role%', $slug, $link );
	}

	// Remove front if applicable.
	if ( ba_eas_remove_front() ) {
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
	if ( ! $author instanceof WP_User ) {
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
		$slug      = isset( ba_eas()->role_slugs[ $role ]['slug'] ) ? ba_eas()->role_slugs[ $role ]['slug'] : '';
		$role_slug = '';
		if ( ! empty( $slug ) ) {
			$role_slug = $slug;
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

	// Attempt to get the user role. Grab the first role in the array.
	if ( ! empty( $roles ) && is_array( $roles ) ) {
		$role = array_shift( $roles );
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

	// Pull out just the roles array.
	$_wp_roles = array();

	// Remove user caps.
	foreach ( wp_roles()->roles as $role => $details ) {
		$_wp_roles[ $role ] = $details;
		unset( $_wp_roles[ $role ]['capabilities'] );
	}

	return $_wp_roles;
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
	foreach ( (array) $roles as $role => $details ) {
		$roles[ $role ]['slug'] = sanitize_title( translate_user_role( $details['name'] ) );
	}

	return $roles;
}

if ( ! function_exists( 'array_replace_recursive' ) ) {
	/**
	 * Add array_replace_recursive() for users of PHP 5.2.x
	 *
	 * @see http://php.net/manual/en/function.array-replace-recursive.php#109390
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
			} while ( count( $head_stack ) ); // phpcs:ignore Squiz.PHP.DisallowSizeFunctionsInLoops
		}

		return $base;
	}
} // End if.
