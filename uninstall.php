<?

/**
 * Edit Author Slug Uninstall Functions
 *
 * @package Edit Author Slug
 * @subpackage Uninstall
 *
 * @author Brandon Allen
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN') )
	return false;

delete_option( 'ba_edit_author_slug' );

// Final flush for good measure
flush_rewrite_rules( false );

?>