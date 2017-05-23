<?php
/**
 * Edit Author Slug Uninstall Functions.
 *
 * @package Edit_Author_Slug
 * @subpackage Uninstall
 *
 * @author Brandon Allen
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Make sure we're uninstalling.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Delete all the options.
delete_option( 'ba_edit_author_slug' );
delete_option( '_ba_eas_author_base' );
delete_option( '_ba_eas_db_version' );
delete_option( '_ba_eas_default_user_nicename' );
delete_option( '_ba_eas_do_auto_update' );
delete_option( '_ba_eas_do_role_based' );
delete_option( '_ba_eas_old_options' );
delete_option( '_ba_eas_role_slugs' );
delete_option( '_ba_eas_remove_front' );

// Final flush for good measure.
update_option( 'rewrite_rules', '' );
