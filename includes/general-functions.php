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
if ( !defined( 'ABSPATH' ) ) exit;

/** Nicename ******************************************************************/

/**
 * Determines if an auto-update should occur
 *
 * @since 0.9.0
 *
 * @uses get_option() To get the auto-update option
 * @uses apply_filters() To call 'ba_eas_do_auto_update' hook
 *
 * @return bool True if auto-update enabled
 */
function ba_eas_do_auto_update() {

	$retval = get_option( '_ba_eas_do_auto_update', '0' );

	if ( !is_numeric( $retval ) || 1 !== (int) $retval )
		$retval = false;

	return apply_filters( 'ba_eas_do_auto_update', (bool) $retval );
}

/**
 * Auto-update the user_nicename for a given user.
 *
 * @since 0.9.0
 *
 * @param int $user_id User id
 * @param bool $bulk Bulk upgrade flag. Defaults to false
 *
 * @uses ba_eas_do_auto_update() Do we auto-update?
 * @uses get_userdata() To get the user object
 * @uses apply_filters() To call the 'ba_eas_auto_update_user_nicename_structure' hook
 * @uses get_option() To get the default nicename structure
 * @uses apply_filters() To call the 'ba_eas_pre_auto_update_user_nicename' hook
 * @uses remove_action() To remove the 'ba_eas_auto_update_user_nicename_single' and prevent looping
 * @uses wp_update_user() Update to new user_nicename
 * @uses wp_cache_delete() To delete the 'userslugs' cache for old nicename
 *
 * @return int $user_id. False on failure
 */
function ba_eas_auto_update_user_nicename( $user_id, $bulk = false ) {
	// Bail if there's no id or object
	if ( empty( $user_id ) )
		return false;

	if ( false === $bulk ) {
		// Should we auto-update
		if ( !ba_eas_do_auto_update() )
			return false;
	}

	// Get WP_User object
	$user = get_userdata( $user_id );

	// Double check we're still good
	if ( !is_object( $user ) || empty( $user ) )
		return false;

	// Setup the user_id
	if ( !empty( $user->ID ) )
		$user_id  = (int) $user->ID;

	// No user_id so bail
	else
		return false;

	// Get the default nicename structure
	$structure = apply_filters( 'ba_eas_auto_update_user_nicename_structure', get_option( '_ba_eas_default_user_nicename', 'username' ), $user_id );

	// Make sure we have a structure
	if ( empty( $structure ) )
		$structure = 'username';

	// Setup the current nicename
	if ( empty( $user->user_nicename ) )
		$current_nicename = $user->user_nicename;
	else
		$current_nicename = $user->user_login;

	// Setup default nicename
	$nicename = $current_nicename;

	// Setup the new nicename
	switch( $structure ) {

		case 'username':

			if ( !empty( $user->user_login ) )
				$nicename = $user->user_login;

			break;

		case 'nickname':

			if ( !empty( $user->nickname ) )
				$nicename = $user->nickname;

			break;

		case 'firstname':

			if ( !empty( $user->first_name ) )
				$nicename = $user->first_name;

			break;

		case 'lastname':

			if ( !empty( $user->last_name ) )
				$nicename = $user->last_name;

			break;

		case 'firstlast':

			if ( !empty( $user->first_name ) && !empty( $user->last_name ) )
				$nicename = $user->first_name . '-' . $user->last_name;

			break;

		case 'lastfirst':

			if ( !empty( $user->first_name ) && !empty( $user->last_name ) )
				$nicename = $user->last_name . '-' . $user->first_name;

			break;
	}

	// Sanitize the new nicename
	$nicename = apply_filters( 'ba_eas_pre_auto_update_user_nicename', sanitize_title( $nicename ), $user_id, $structure );

	// Bail if nothing changed
	if ( $nicename == $current_nicename )
		return $user_id;

	// Remove the auto-update actions so we don't find ourselves in a loop
	remove_action( 'profile_update', 'ba_eas_auto_update_user_nicename_single' );

	// Update if there's a change
	$user_id = wp_update_user( array( 'ID' => $user_id, 'user_nicename' => $nicename ) );

	// Clear the cache for good measure
	wp_cache_delete( $current_nicename, 'userslugs' );

	// Add it back in case other plugins do some updating
	remove_action( 'profile_update', 'ba_eas_auto_update_user_nicename_single' );

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
 * @uses ba_eas_auto_update_user_nicename() To auto-update the nicename
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
 * @uses ba_eas_auto_update_user_nicename() To auto-update the nicename
 */
function ba_eas_auto_update_user_nicename_bulk( $user_id = 0 ) {
	ba_eas_auto_update_user_nicename( $user_id, true );
}

?>