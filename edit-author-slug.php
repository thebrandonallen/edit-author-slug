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
 * Version: 1.0.4
 * Tested With: 3.6.1, 3.7.1, 3.8.1, 3.9.2, 4.0.1, 4.1.1, 4.2
 * Author: Brandon Allen
 * Author URI: http://brandonallen.org/
 * License: GPL2+
 * Text Domain: edit-author-slug
 * Domain Path: /languages
 */

/*
	Copyright 2015  Brandon Allen  (email : wp_plugins ([at]) brandonallen ([dot]) org)

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
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Main Edit Author Slug class
 */
if ( ! class_exists( 'BA_Edit_Author_Slug' ) ) :

	/**
	 * Final BA_Edit_Author_Slug class.
	 *
	 * @since 0.1.0
	 *
	 * @final
	 *
	 * @property string $version
	 * @property int $db_version
	 * @property int $current_db_version
	 * @property string $file
	 * @property string $plugin_dir
	 * @property string $plugin_url
	 * @property string $plugin_basename
	 * @property string $domain
	 * @property string $author_base
	 * @property int $do_auto_update
	 * @property string $default_user_nicename
	 * @property int $do_role_based
	 * @property array $role_slugs
	 */
	final class BA_Edit_Author_Slug {

		/** Magic *****************************************************************/

		/**
		 * Edit Author Slug uses many variables, several of which can be filtered to
		 * customize the way it operates. Most of these variables are stored in a
		 * private array that gets updated with the help of PHP magic methods.
		 *
		 * This is a precautionary measure, to avoid potential errors produced by
		 * unanticipated direct manipulation of Edit Author Slug's run-time data.
		 *
		 * @since 1.0.0
		 *
		 * @see BA_Edit_Author_Slug::setup_globals()
		 * @var object
		 */
		private $data;

		/** Singleton ****************************************************************/

		/**
		 * Main BA_Edit_Author_Slug Instance
		 *
		 * Insures that only one instance of BA_Edit_Author_Slug exists in memory at any one
		 * time. Also prevents needing to define globals all over the place.
		 *
		 * @since 1.0.0
		 *
		 * @staticvar object $instance
		 *
		 * @uses BA_Edit_Author_Slug::setup_globals() Setup the globals needed.
		 * @uses BA_Edit_Author_Slug::includes() Include the required files.
		 * @uses BA_Edit_Author_Slug::setup_actions() Setup the hooks and actions.
		 *
		 * @see ba_eas()
		 *
		 * @return BA_Edit_Author_Slug|null The one true BA_Edit_Author_Slug
		 */
		public static function instance() {

			// Store the instance locally to avoid private static replication
			static $instance = null;

			// Only run these methods if they haven't been ran previously
			if ( null === $instance ) {
				$instance = new BA_Edit_Author_Slug;
				$instance->setup_globals();
				$instance->includes();
				$instance->setup_actions();
			}

			// Always return the instance
			return $instance;
		}

		/** Magic Methods ************************************************************/

		/**
		 * A dummy constructor to prevent BA_Edit_Author_Slug from being loaded more than once.
		 *
		 * @since 0.7.0
		 *
		 * @see BA_Edit_Author_Slug::instance()
		 * @see ba_eas();
		 */
		private function __construct() { /* Do nothing here */ }

		/**
		 * A dummy magic method to prevent BA_Edit_Author_Slug from being cloned
		 *
		 * @since 1.0.0
		 */
		public function __clone() { _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'edit-author-slug' ), '1.0' ); }

		/**
		 * A dummy magic method to prevent BA_Edit_Author_Slug from being unserialized
		 *
		 * @since 1.0.0
		 */
		public function __wakeup() { _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'edit-author-slug' ), '1.0' ); }

		/**
		 * Magic method for checking the existence of a certain custom field
		 *
		 * @since 1.0.0
		 */
		public function __isset( $key ) { return isset( $this->data->$key ); }

		/**
		 * Magic method for getting BA_Edit_Author_Slug variables
		 *
		 * @since 1.0.0
		 */
		public function __get( $key ) { return isset( $this->data->$key ) ? $this->data->$key : null; }

		/**
		 * Magic method for setting BA_Edit_Author_Slug variables
		 *
		 * @since 1.0.0
		 */
		public function __set( $key, $value ) { $this->data->$key = $value; }

		/**
		 * Magic method for unsetting BA_Edit_Author_Slug variables
		 *
		 * @since 1.0.0
		 */
		public function __unset( $key ) { if ( isset( $this->data->$key ) ) { unset( $this->data->$key ); } }

		/**
		 * Magic method to prevent notices and errors from invalid method calls
		 *
		 * @since 1.0.0
		 */
		public function __call( $name = '', $args = array() ) { unset( $name, $args ); return null; }

		/** Private Methods *******************************************************/

		/**
		 * Edit Author Slug global variables.
		 *
		 * @since 0.7.0
		 *
		 * @uses plugin_dir_path() To generate Edit Author Slug plugin path.
		 * @uses plugin_dir_url() To generate Edit Author Slug plugin url.
		 * @uses plugin_basename() To get the plugin basename.
		 * @uses get_option() To get the Edit Author Slug options.
		 * @uses absint() To cast db variables as absolute integers.
		 */
		private function setup_globals() {

			/** Magic *******************************************************************/

			$this->data = new stdClass();

			/** Versions ****************************************************************/

			$this->version            = '1.0.4';
			$this->db_version         = 133;
			$this->current_db_version = 0;

			/** Paths *******************************************************************/

			$this->file            = __FILE__;
			$this->plugin_dir      = plugin_dir_path( $this->file );
			$this->plugin_url      = plugin_dir_url(  $this->file );
			$this->plugin_basename = plugin_basename( $this->file );

			/** Miscellaneous ***********************************************************/

			$this->domain = 'edit-author-slug';

			/** Options *****************************************************************/

			// Setup author base
			$this->author_base = 'author';

			// Options
			if ( $base = get_option( '_ba_eas_author_base' ) ) {
				// Author base
				if ( ! empty( $base ) ) {
					$this->author_base = $base;
				}

				// Current DB version
				$this->current_db_version = absint( get_option( '_ba_eas_db_version' ) );

			// Pre-0.9 Back compat
			} elseif ( $options = get_option( 'ba_edit_author_slug' ) ) {

				// Author base
				if ( ! empty( $options['author_base'] ) ) {
					$this->author_base = $options['author_base'];
				}

				// Current DB version
				if ( ! empty( $options['db_version'] ) ) {
					$this->current_db_version = absint( $options['db_version'] );
				}
			}

			// Load auto-update option
			$this->do_auto_update = absint( get_option( '_ba_eas_do_auto_update', 0 ) );

			// Load the default nicename structure for auto-update
			$default_user_nicename = get_option( '_ba_eas_default_user_nicename', 'username' );
			$this->default_user_nicename = ( ! empty( $default_user_nicename ) ) ? $default_user_nicename : 'username';

			// Load role-based author slug option
			$this->do_role_based = absint( get_option( '_ba_eas_do_role_based', 0 ) );

			// Load role-based author slug option
			$this->role_slugs = array();
		}

		/**
		 * Include necessary files.
		 *
		 * @since 0.7.0
		 *
		 * @uses is_admin() To load admin specific functions.
		 */
		private function includes() {

			// Load the core functions
			require_once( $this->plugin_dir . 'includes/functions.php' );
			require_once( $this->plugin_dir . 'includes/hooks.php'             );

			// Maybe load the admin functions
			if ( is_admin() ) {
				require_once( $this->plugin_dir . 'includes/admin.php' );
			}
		}

		/**
		 * Display Author slug edit field on User/Profile edit page.
		 *
		 * @since 0.7.0
		 *
		 * @uses register_activation_hook() To register the activation hook.
		 * @uses register_deactivation_hook() To register the deactivation hook.
		 * @uses add_action() To call various hooks.
		 */
		private function setup_actions() {
			// Register Edit Author Slug activation/deactivation sequences
			register_activation_hook(   $this->file, 'ba_eas_activation'   );
			register_deactivation_hook( $this->file, 'ba_eas_deactivation' );

			// Author Base Actions
			add_action( 'after_setup_theme', array( $this, 'set_role_slugs' )          );
			add_action( 'init',              array( $this, 'author_base_rewrite' ), 4  );
			add_action( 'init',              array( $this, 'add_rewrite_tags' ),    20 );

			// Localize
			add_action( 'init', array( $this, 'load_textdomain' ), 0 );
		}

		/** Public Methods ***********************************************************/

		/**
		 * Load the translation file for current language. Checks the Edit Author Slug
		 * languages folder first, then inside the default WP language plugins folder.
		 *
		 * Note that custom translation files inside the Edit Author Slug plugin folder
		 * will be removed on edit-author-slug updates. If you're creating custom
		 * translation files, please use the global language folder (ie - wp-content/languages/plugins).
		 *
		 * @since 0.9.6
		 *
		 * @uses load_plugin_textdomain() To load the textdomain inside the 'plugin/languages' folder.
		 */
		public function load_textdomain() {

			// Look in wp-content/plugins/edit-author-slug/languages first
			// fallback to wp-content/languages/plugins
			load_plugin_textdomain( $this->domain, false, dirname( $this->plugin_basename ) . '/languages/' );
		}

		/**
		 * Rewrite Author Base according to user's setting.
		 *
		 * Rewrites Author Base to user's setting from the
		 * Author Base field on Options > Permalinks.
		 *
		 * @since 0.4.0
		 *
		 * @uses ba_eas_do_role_based_author_base() To check if we're doing role-based author bases.
		 */
		public function author_base_rewrite() {

			// Are we doing a role-based author base?
			if ( ba_eas_do_role_based_author_base() ) {

				$GLOBALS['wp_rewrite']->author_base = '%ba_eas_author_role%';

			// Has the author base changed from the default?
			} elseif ( ! empty( $this->author_base ) && 'author' != $this->author_base ) {

				$GLOBALS['wp_rewrite']->author_base = $this->author_base;
			}
		}

		/**
		 * Set the role_slugs global
		 *
		 * @since 1.0.0
		 *
		 * @uses ba_eas_get_default_role_slugs() To get an array of default role slugs.
		 * @uses get_option() To get the custom role slugs array.
		 */
		public function set_role_slugs() {

			// Merge system roles with any customizations we may have
			$this->role_slugs = array_replace_recursive( ba_eas_get_default_role_slugs(), get_option( '_ba_eas_role_slugs', array() ) );
		}

		/** Custom Rewrite Rules *****************************************************/

		/**
		 * Add the Edit Author Slug rewrite tags
		 *
		 * @since 1.0.0
		 *
		 * @uses ba_eas_do_role_based_author_base() To check if we're doing role-based author bases.
		 * @uses wp_list_pluck() To get only the role slugs.
		 * @uses add_rewrite_tag() To add the rewrite tags.
		 */
		public function add_rewrite_tags() {

			// Should we be here?
			if ( ! ba_eas_do_role_based_author_base() ) {
				return;
			}

			// Get the role slugs to add the rewrite tag
			$role_slugs = array_filter( array_values( wp_list_pluck( $this->role_slugs, 'slug' ) ) );

			// Add the author base as a fallback
			$role_slugs[] = ba_eas()->author_base;

			// Add the role-based rewrite tag, and the expected role slugs
			add_rewrite_tag( '%ba_eas_author_role%', '(' . implode( '|', array_unique( $role_slugs ) ) . ')' );
		}
	}

	/**
	 * The main function responsible for returning the one true BA_Edit_Author_Slug Instance
	 * to functions everywhere.
	 *
	 * Use this function like you would a global variable, except without needing
	 * to declare the global.
	 *
	 * Example: <?php $ba_eas = ba_eas(); ?>
	 *
	 * @return BA_Edit_Author_Slug The one true BA_Edit_Author_Slug Instance
	 */
	function ba_eas() {
		return BA_Edit_Author_Slug::instance();
	}

	// Places everyone! The show is starting!
	ba_eas();

endif; //end class BA_Edit_Author_Slug

/**
 * Runs on Edit Author Slug activation
 *
 * @since 0.7.0
 *
 * @uses do_action() Calls 'ba_eas_activation' hook.
 */
function ba_eas_activation() {
	do_action( 'ba_eas_activation' );

	// Pre-emptive courtesy flush in case of existing author base
	ba_eas_flush_rewrite_rules();
}

/**
 * Runs on Edit Author Slug deactivation
 *
 * @since 0.7.0
 *
 * @uses do_action() Calls 'ba_eas_deactivation' hook.
 */
function ba_eas_deactivation() {
	do_action( 'ba_eas_deactivation' );

	// Courtesy flush
	delete_option( 'rewrite_rules' );
}
