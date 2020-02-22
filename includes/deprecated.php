<?php
/**
 * Edit Author Slug Deprecated Functions.
 *
 * @package Edit_Author_Slug
 * @subpackage Deprecated
 *
 * @author Brandon Allen
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/** Nicename ******************************************************************/

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

/** Miscellaneous *************************************************************/

/**
 * Returns the WP_Roles object.
 *
 * WP 4.3 added the `wp_roles()` function to facilitate the instantiation of the
 * WP_Roles object. This is a wrapper function for `wp_roles()` with a fallback
 * for those on WP < 4.3.
 *
 * @since 1.2.0
 * @deprecated 1.5.0
 *
 * @return WP_Roles
 */
function ba_eas_get_wp_roles() {
	_deprecated_function( __FUNCTION__, '1.5.0', 'wp_roles' );
	return wp_roles();
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
 * @deprecated 1.2.0
 *
 * @global WP_Roles $wp_roles The WP_Roles object.
 *
 * @return array $editable_roles List of editable roles.
 */
function ba_eas_get_editable_roles() {

	_deprecated_function( __FUNCTION__, '1.2.0' );

	/**
	 * Filter the list of editable roles.
	 *
	 * @since 1.0.0
	 *
	 * @param array $roles The `WP_Roles::roles` array.
	 */
	$editable_roles = apply_filters( 'editable_roles', wp_roles()->roles );

	// Remove user caps.
	foreach ( (array) $editable_roles as $role => $details ) {
		unset( $editable_roles[ $role ]['capabilities'] );
	}

	return $editable_roles;
}

/**
 * Clean and update the nicename cache.
 *
 * @since 1.0.0
 * @since 1.6.1 Converted to a no-op.
 * @deprecated 1.5.0
 *
 * @param int    $user_id       The user id.
 * @param object $old_user_data The WP_User object.
 * @param string $new_nicename  The new user nicename.
 */
function ba_eas_update_nicename_cache( $user_id = 0, $old_user_data = '', $new_nicename = '' ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
	_deprecated_function( __FUNCTION__, '1.5.0', 'wp_cache_delete( $old_nicename, \'userslugs\' );' );
}
