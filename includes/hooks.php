<?php

/**
 * Edit Author Slug Filters & Actions
 *
 * @package Edit Author Slug
 * @subpackage Hooks
 *
 * @author Brandon Allen
 */

// Admin
if ( is_admin() ) {
	// Author base settings
	add_action( 'admin_init',                 'ba_eas_add_author_base_settings_field'   );

	// Nicename column filters
	add_filter( 'manage_users_columns',       'ba_eas_author_slug_column'               );
	add_filter( 'manage_users_custom_column', 'ba_eas_author_slug_custom_column', 10, 3 );

	// Nicename Actions
	add_action( 'edit_user_profile',          'ba_eas_show_user_nicename'               );
	add_action( 'show_user_profile',          'ba_eas_show_user_nicename'               );
	add_action( 'edit_user_profile_update',   'ba_eas_update_user_nicename'             );
	add_action( 'personal_options_update',    'ba_eas_update_user_nicename'             );
}

// Capabilities
add_action( 'ba_eas_activation',   'ba_eas_add_caps'        );
add_action( 'ba_eas_deactivation', 'ba_eas_remove_caps'     );

// Activation/Deactivation Hooks
add_action( 'ba_eas_activation',   'ba_eas_cleanup_options' );
add_action( 'ba_eas_deactivation', 'ba_eas_courtesy_flush'  );

?>