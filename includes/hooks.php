<?php
/**
 * Edit Author Slug Filters & Actions.
 *
 * @package Edit_Author_Slug
 * @subpackage Hooks
 *
 * @author Brandon Allen
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Admin.
if ( is_admin() ) {

	// Activation.
	add_action( 'ba_eas_activation', 'ba_eas_install' );
	add_action( 'ba_eas_activation', 'ba_eas_flush_rewrite_rules' );

	// Deactivation.
	add_action( 'ba_eas_deactivation', 'ba_eas_flush_rewrite_rules' );

	// Upgrade.
	add_action( 'admin_init', 'ba_eas_upgrade', 999 );

	// Nicename Actions.
	add_action( 'edit_user_profile', 'ba_eas_show_user_nicename' );
	add_action( 'show_user_profile', 'ba_eas_show_user_nicename' );
	add_action( 'user_profile_update_errors', 'ba_eas_update_user_nicename', 20, 3 );
	add_action( 'admin_enqueue_scripts', 'ba_eas_show_user_nicename_scripts' );

	// Nicename column filters.
	add_filter( 'manage_users_columns', 'ba_eas_author_slug_column' );
	add_filter( 'manage_users_custom_column', 'ba_eas_author_slug_custom_column', 10, 3 );

	// Settings.
	add_action( 'admin_menu', 'ba_eas_add_settings_menu' );
	add_action( 'admin_init', 'ba_eas_register_admin_settings' );
	add_filter( 'plugin_action_links', 'ba_eas_add_settings_link', 10, 2 );

	// Settings updated.
	add_action( 'admin_action_update', 'ba_eas_settings_updated' );
	add_action( 'ba_eas_settings_updated', 'ba_eas_flush_rewrite_rules' );
}

// Nicename auto-update actions.
add_action( 'profile_update', 'ba_eas_auto_update_user_nicename' );
add_action( 'user_register', 'ba_eas_auto_update_user_nicename' );

// Author permalink filtering for role-based author bases.
add_filter( 'author_link', 'ba_eas_author_link', 20, 2 );

// Filter author rewrite rules.
add_filter( 'author_rewrite_rules', 'ba_eas_author_rewrite_rules' );

// Add role-based author templates.
add_filter( 'author_template', 'ba_eas_template_include' );
