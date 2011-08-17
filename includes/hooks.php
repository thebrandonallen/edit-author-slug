<?php

/**
 * Edit Author Slug Filters & Actions
 *
 * @package Edit_Author_Slug
 * @subpackage Hooks
 *
 * @author Brandon Allen
 */

// Admin
if ( is_admin() ) {
	// Author base settings
	add_action( 'admin_init', 'ba_eas_sanitize_author_base'           );
	add_action( 'admin_init', 'ba_eas_add_author_base_settings_field' );

	// Nicename Actions
	add_action( 'edit_user_profile',          'ba_eas_show_user_nicename'          );
	add_action( 'show_user_profile',          'ba_eas_show_user_nicename'          );
	add_action( 'user_profile_update_errors', 'ba_eas_update_user_nicename', 10, 3 );

	// Nicename column filters
	add_filter( 'manage_users_columns',       'ba_eas_author_slug_column'               );
	add_filter( 'manage_users_custom_column', 'ba_eas_author_slug_custom_column', 10, 3 );

}

// Activation/Deactivation Hooks
add_action( 'ba_eas_activation', 'ba_eas_cleanup_options' );

?>