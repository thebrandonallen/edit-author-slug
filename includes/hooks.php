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

	// Cleanup options array
	add_action( 'admin_init', 'ba_eas_upgrade', 999 );

	// Nicename Actions
	add_action( 'edit_user_profile',          'ba_eas_show_user_nicename'          );
	add_action( 'show_user_profile',          'ba_eas_show_user_nicename'          );
	add_action( 'user_profile_update_errors', 'ba_eas_update_user_nicename', 10, 3 );

	// Nicename column filters
	add_filter( 'manage_users_columns',       'ba_eas_author_slug_column'               );
	add_filter( 'manage_users_custom_column', 'ba_eas_author_slug_custom_column', 10, 3 );

	// WP < 3.2 EOL message
	add_action( 'after_plugin_row_' . $this->plugin_basename, 'ba_eas_eol_for_less_than_wp_3_2_message' );

}

?>