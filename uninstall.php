<?

if ( !defined( 'WP_UNINSTALL_PLUGIN') )
	return false;

delete_option( 'ba_edit_author_slug' );

?>