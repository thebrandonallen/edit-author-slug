<?php

/**
 * Edit Author Slug Plugin
 *
 * Customize a user's author links
 *
 * @package Edit_Author_Slug
 * @subpackage Main
 *
 * @author Brandon Allen
 */

/**
 * Plugin Name: Edit Author Slug
 * Plugin URI: http://brandonallen.org/wordpress/plugins/edit-author-slug/
 * Description: Allows an Admin (or capable user) to edit the author slug of a user, and change the Author Base. <em>i.e. - (WordPress default structure) http://example.com/author/username/ (Plugin allows) http://example.com/ninja/master-ninja/</em>
 * Version: 0.9.4
 * Tested With: 3.2.1, 3.3.2, 3.4, 3.5.1
 * Author: Brandon Allen
 * Author URI: http://brandonallen.org/
 * License: GPL2
 */

/*
			Copyright 2011  Brandon Allen  (email : wp_plugins ([at]) brandonallen ([dot]) org)

			This program is free software; you can redistribute it and/or modify
			it under the terms of the GNU General Public License as published by
			the Free Software Foundation; either version 2 of the License, or
			(at your option) any later version.

			This program is distributed in the hope that it will be useful,
			but WITHOUT ANY WARRANTY; without even the implied warranty of
			MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
			GNU General Public License for more details.

			You should have received a copy of the GNU General Public License
			along with this program; if not, write to the Free Software
			Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

			http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Main Edit Author Slug class
 */
if ( !class_exists( 'BA_Edit_Author_Slug' ) ) :

final class BA_Edit_Author_Slug {

	/**
	 * Edit Author Slug Version
	 * @access public
	 * @var string Edit Author Slug Version
	 */
	public $version = '0.9.4';

	/**
	 * Edit Author Slug Version
	 * @access public
	 * @var int Edit Author Slug DB Version
	 */
	public $db_version = 132;

	/**
	 * Edit Author Slug Version
	 * @access public
	 * @var int Edit Author Slug Currently Installed DB Version
	 */
	public $current_db_version = 0;

	/**
	 * Edit Author Slug Version
	 * @access public
	 * @var string Result of __FILE__
	 */
	public $file = '';

	/**
	 * Edit Author Slug Version
	 * @access public
	 * @var string Edit Author Slug plugin directory
	 */
	public $plugin_dir = '';

	/**
	 * Edit Author Slug Version
	 * @access public
	 * @var string Edit Author Slug plugin URL
	 */
	public $plugin_url = '';

	/**
	 * Edit Author Slug Version
	 * @access public
	 * @var string Edit Author Slug plugin basename
	 */
	public $plugin_basename = '';

	/**
	 * Edit Author Slug Version
	 * @access public
	 * @var string Author base
	 */
	public $author_base = '';

	/**
	 * PHP5 constructor
	 *
	 * @since 0.7.0
	 */
	public function __construct() {
		$this->setup_globals();
		$this->includes();
		$this->setup_actions();
	}

	/**
	 * Edit Author Slug global variables.
	 *
	 * @since 0.7.0
	 *
	 * @uses plugin_dir_path() To generate Edit Author Slug plugin path
	 * @uses plugin_dir_url() To generate Edit Author Slug plugin url
	 * @uses get_option()  To get the Edit Author Slug options
	 */
	private function setup_globals() {
		// Edit Author Slug root directory
		$this->file            = __FILE__;
		$this->plugin_dir      = plugin_dir_path( $this->file );
		$this->plugin_url      = plugin_dir_url(  $this->file );
		$this->plugin_basename = plugin_basename( $this->file );

		// Options
		if ( $base = get_option( '_ba_eas_author_base' ) ) {
			// Author base
			$this->author_base = 'author';
			if ( 'author' != $base )
				$this->author_base = $base;

			// Current DB version
			$db_version = get_option( '_ba_eas_db_version', 0 );
			if ( !empty( $db_version ) )
				$this->current_db_version = (int) $db_version;

		// Pre-0.9 Back compat
		} elseif ( $options = get_option( 'ba_edit_author_slug' ) ) {
			// Author base
			$this->author_base = 'author';
			if ( 'author' != $options['author_base'] )
				$this->author_base = $options['author_base'];

			// Current DB version
			if ( !empty( $options['db_version'] ) )
				$this->current_db_version = (int) $options['db_version'];

		// Something has gone horribly wrong if this happens
		} else {
			// Author base
			$this->author_base = 'author';
		}
	}

	/**
	 * Include necessary files.
	 *
	 * @since 0.7.0
	 */
	private function includes() {
		if ( is_admin() )
			require_once( $this->plugin_dir . 'includes/admin-functions.php' );

		require_once( $this->plugin_dir . 'includes/general-functions.php' );
		require_once( $this->plugin_dir . 'includes/hooks.php' );
	}

	/**
	 * Display Author slug edit field on User/Profile edit page.
	 *
	 * @since 0.7.0
	 *
	 * @uses register_activation_hook() To register the activation hook
	 * @uses register_deactivation_hook() To register the deactivation hook
	 * @uses add_action() To call BA_Edit_Author_Slug::author_base_rewrite
	 * @uses load_plugin_textdomain()
	 */
	private function setup_actions() {
		// Register Edit Author Slug activation/deactivation sequences
		register_activation_hook(   $this->file, 'ba_eas_activation'   );
		register_deactivation_hook( $this->file, 'ba_eas_deactivation' );

		// Author Base Actions
		add_action( 'init',       array( $this, 'author_base_rewrite'  ) );

		// Localize
		load_plugin_textdomain( 'edit-author-slug', false, dirname( $this->plugin_dir ) . '/languages/' );
	}

	/**
	 * Rewrite Author Base according to user's setting.
	 *
	 * Rewrites Author Base to user's setting from the
	 * Author Base field on Options > Permalinks.
	 *
	 * @since 0.4.0
	 *
	 * @global object $wp_rewrite Adds rewrite tags and permastructs.
	 * @uses do_action() calls 'ba_eas_author_base_rewrite' hook
	 * @uses flush_rewrite_rules() Flush the rules on change
	 */
	public function author_base_rewrite() {
		global $wp_rewrite;

		if ( !empty( $this->author_base ) || 'author' != $this->author_base )
			$wp_rewrite->author_base = $this->author_base;
	}
}

// Places everyone! The show is starting!
$GLOBALS['ba_eas'] = new BA_Edit_Author_Slug();

endif; //end class BA_Edit_Author_Slug

/**
 * Runs on Edit Author Slug activation
 *
 * @since 0.7.0
 *
 * @uses do_action() Calls 'ba_eas_activation' hook
 */
function ba_eas_activation() {
	do_action( 'ba_eas_activation' );
}

/**
 * Runs on Edit Author Slug deactivation
 *
 * @since 0.7.0
 *
 * @uses do_action() Calls 'ba_eas_deactivation' hook
 */
function ba_eas_deactivation() {
	do_action( 'ba_eas_deactivation' );

	// Courtesy flush
	flush_rewrite_rules( false );
}

?>
