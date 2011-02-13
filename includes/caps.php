<?php

/**
 * Edit Author Slug Capabilities
 *
 * @package Edit Author Slug
 * @subpackage Capabilities
 *
 * @author Brandon Allen
 */

/**
 * I originally started this file to add a custom capability for
 * later use within the plugin. During development I decided it
 * wasn't safe to rely, solely, on a custom cap. However, it
 * would be beneficial to others looking, for example, to allow
 * subscribers to edit their own slugs. So rather than remove
 * all traces of what almost happened, I've left this file to
 * give others a starting point.
 */

/**
 * Adds capabilities to WordPress user roles.
 *
 * This is called on plugin activation.
 *
 * @since 0.7.0
 *
 * @uses get_role() To get the administrator role
 * @uses WP_Role::add_cap() To add various capabilities
 * @uses do_action() Calls 'ba_eas_add_caps'
 */
function ba_eas_add_caps() {
	// Add cap to admin role
	if ( $admin =& get_role( 'administrator' ) )
		$admin->add_cap( 'edit_author_slug' );

	do_action( 'ba_eas_add_caps' );
}
add_action( 'ba_eas_activation', 'ba_eas_add_caps' );

/**
 * Removes capabilities from WordPress user roles.
 *
 * This is called on plugin deactivation.
 *
 * @since 0.7.0
 *
 * @uses get_role() To get the administrator role
 * @uses WP_Role::remove_cap() To remove various capabilities
 * @uses do_action() Calls 'ba_eas_remove_caps'
 */
function ba_eas_remove_caps() {
	// Add cap to admin role
	if ( $admin =& get_role( 'administrator' ) )
		$admin->remove_cap( 'edit_author_slug' );

	do_action( 'ba_eas_remove_caps' );
}
add_action( 'ba_eas_deactivation', 'ba_eas_remove_caps' );

?>