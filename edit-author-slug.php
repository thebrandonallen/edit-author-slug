<?php
/**
 * Edit Author Slug Plugin
 *
 * Customize a user's author links.
 *
 * @package Edit_Author_Slug
 * @subpackage Main
 *
 * @author Brandon Allen
 */

/**
 * Plugin Name: Edit Author Slug
 * Plugin URI: https://github.com/thebrandonallen/edit-author-slug/
 * Description: Allows an Admin (or capable user) to edit the author slug of a user, and change the Author Base. <em>i.e. - (WordPress default structure) http://example.com/author/username/ (Plugin allows) http://example.com/ninja/master-ninja/</em>
 * Version: 1.2.1
 * Tested With: 4.0.10, 4.1.10, 4.2.7, 4.3.3, 4.4.2
 * Author: Brandon Allen
 * Author URI: https://github.com/thebrandonallen/
 * License: GPLv2 or later
 * Text Domain: edit-author-slug
 * Domain Path: /languages
 */

/*
	Copyright 2009-2016  Brandon Allen  (email : plugins ([at]) brandonallen ([dot]) me)

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

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Main Edit Author Slug class.
 */
if ( ! class_exists( 'BA_Edit_Author_Slug' ) ) :

	/**
	 * Final BA_Edit_Author_Slug class.
	 *
	 * @since 0.1.0
	 *
	 * @final
	 */
	final class BA_Edit_Author_Slug {

		/**
		 * The plugin version.
		 *
		 * @since  0.8.0
		 * @access public
		 * @var    string
		 */
		public $version = '1.2.1';

		/**
		 * The database version.
		 *
		 * @since  0.8.0
		 * @access public
		 * @var    int
		 */
		public $db_version = 411;

		/**
		 * The current installed database version.
		 *
		 * @since  0.8.0
		 * @access public
		 * @var    int
		 */
		public $current_db_version = 0;

		/**
		 * The path to this file.
		 *
		 * @since  0.7.0
		 * @access public
		 * @var    string
		 */
		public $file = __FILE__;

		/**
		 * The path to the Edit AUthor Slug directory.
		 *
		 * @since  0.7.0
		 * @access public
		 * @var    string
		 */
		public $plugin_dir = '';

		/**
		 * The URL for the Edit Author Slug directory.
		 *
		 * @since  0.7.0
		 * @access public
		 * @var    string
		 */
		public $plugin_url = '';

		/**
		 * The basename for the Edit Author Slug directory.
		 *
		 * @since  0.8.0
		 * @access public
		 * @var    string
		 */
		public $plugin_basename = '';

		/**
		 * The text domain for Edit Author Slug.
		 *
		 * @since  0.9.6
		 * @access public
		 * @var    string
		 */
		public $domain = 'edit-author-slug';

		/**
		 * The author base.
		 *
		 * @since  0.7.0
		 * @access public
		 * @var    string
		 */
		public $author_base = 'author';

		/**
		 * The remove front option.
		 *
		 * @since  1.2.0
		 * @access public
		 * @var    bool
		 */
		public $remove_front = false;

		/**
		 * The auto update option.
		 *
		 * @since  1.0.0
		 * @access public
		 * @var    bool
		 */
		public $do_auto_update = false;

		/**
		 * The default user nicename option.
		 *
		 * @since  1.0.0
		 * @access public
		 * @var    string
		 */
		public $default_user_nicename = 'username';

		/**
		 * The role-based option.
		 *
		 * @since  1.0.0
		 * @access public
		 * @var    bool
		 */
		public $do_role_based = false;

		/**
		 * The role slugs array.
		 *
		 * @since  1.0.0
		 * @access public
		 * @var    array
		 */
		public $role_slugs = array();

		/** Singleton *********************************************************/

		/**
		 * Main BA_Edit_Author_Slug Instance
		 *
		 * Insures that only one instance of BA_Edit_Author_Slug exists in memory
		 * at any one time. Also prevents needing to define globals all over the
		 * place.
		 *
		 * @since 1.0.0
		 *
		 * @staticvar object $instance
		 *
		 * @see ba_eas()
		 *
		 * @return BA_Edit_Author_Slug|null The one true BA_Edit_Author_Slug.
		 */
		public static function instance() {

			// Store the instance locally to avoid private static replication.
			static $instance = null;

			// Only run these methods if they haven't been ran previously.
			if ( null === $instance ) {
				$instance = new BA_Edit_Author_Slug;
				$instance->setup_globals();
				$instance->includes();
				$instance->options_back_compat();
				$instance->setup_actions();
			}

			// Always return the instance.
			return $instance;
		}

		/**
		 * Magic method to prevent notices and errors from invalid method calls.
		 *
		 * @since 1.0.0
		 *
		 * @param string $name The method name being called.
		 * @param array  $args The method arguments.
		 *
		 * @return null
		 */
		public function __call( $name = '', $args = array() ) {

			if ( 'author_base_rewrite' === $name ) {
				_deprecated_function( 'BA_Edit_Author_Slug::author_base_rewrite', '1.2.0', 'ba_eas_wp_rewrite_overrides' );
				ba_eas_wp_rewrite_overrides();
			} else {
				_doing_it_wrong( "BA_Edit_Author_Slug::{$name}", esc_html__( 'Method does not exist.', 'edit-author-slug' ), '1.0.0' );
			}

			unset( $name, $args );
			return null;
		}

		/** Magic Methods *****************************************************/

		/**
		 * A dummy magic method to prevent BA_Edit_Author_Slug from being cloned.
		 *
		 * @since 1.0.0
		 *
		 * @codeCoverageIgnore
		 *
		 * @return void
		 */
		private function __clone() {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'edit-author-slug' ), '1.0' );
		}

		/**
		 * A dummy magic method to prevent BA_Edit_Author_Slug from being unserialized.
		 *
		 * @since 1.0.0
		 *
		 * @codeCoverageIgnore
		 *
		 * @return void
		 */
		private function __wakeup() {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'edit-author-slug' ), '1.0' );
		}

		/* Private Methods ****************************************************/

		/**
		 * Edit Author Slug global variables.
		 *
		 * @since 0.7.0
		 *
		 * @return void
		 */
		private function setup_globals() {

			/* Paths **********************************************************/

			$this->plugin_dir      = plugin_dir_path( $this->file );
			$this->plugin_url      = plugin_dir_url( $this->file );
			$this->plugin_basename = plugin_basename( $this->file );

			/* Options ********************************************************/

			// Load the remove front option.
			$this->remove_front = (bool) absint( get_option( '_ba_eas_remove_front', 0 ) );

			// Load auto-update option.
			$this->do_auto_update = (bool) absint( get_option( '_ba_eas_do_auto_update', 0 ) );

			// Load the default nicename structure for auto-update.
			$default_user_nicename = get_option( '_ba_eas_default_user_nicename' );
			$default_user_nicename = sanitize_key( $default_user_nicename );
			if ( ! empty( $default_user_nicename ) ) {
				$this->default_user_nicename = $default_user_nicename;
			}

			// Load role-based author slug option.
			$this->do_role_based = (bool) absint( get_option( '_ba_eas_do_role_based', 0 ) );
		}

		/**
		 * Include necessary files.
		 *
		 * @since 0.7.0
		 *
		 * @return void
		 */
		private function includes() {

			// Load the core functions.
			require_once( $this->plugin_dir . 'includes/functions.php' );
			require_once( $this->plugin_dir . 'includes/hooks.php' );

			// Maybe load the admin functions.
			if ( is_admin() ) {
				require_once( $this->plugin_dir . 'includes/admin.php' );
			}
		}

		/**
		 * Display Author slug edit field on User/Profile edit page.
		 *
		 * @since 0.7.0
		 *
		 * @return void
		 */
		private function setup_actions() {
			// Register Edit Author Slug activation/deactivation sequences.
			register_activation_hook( $this->file, 'ba_eas_activation' );
			register_deactivation_hook( $this->file, 'ba_eas_deactivation' );

			// Author Base Actions.
			add_action( 'after_setup_theme', array( $this, 'set_role_slugs' ) );
			add_action( 'init',              'ba_eas_wp_rewrite_overrides', 4 );
			add_action( 'init',              array( $this, 'add_rewrite_tags' ), 20 );

			// Localize.
			add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		}

		/**
		 * Sets the author base and db version with support for previous
		 * versions of the plugin.
		 *
		 * @since 1.2.0
		 *
		 * @return void
		 */
		private function options_back_compat() {

			// Options.
			if ( $base = get_option( '_ba_eas_author_base' ) ) {

				// Sanitize the db value.
				$base = ba_eas_sanitize_author_base( $base );

				// Author base.
				if ( ! empty( $base ) ) {
					$this->author_base = $base;
				}

				// Current DB version.
				$this->current_db_version = absint( get_option( '_ba_eas_db_version' ) );

			// Pre-0.9 Back compat.
			} elseif ( $options = get_option( 'ba_edit_author_slug' ) ) {

				// Sanitize the db value.
				if ( ! empty( $options['author_base'] ) ) {
					$base = ba_eas_sanitize_author_base( $options['author_base'] );
				}

				// Author base.
				if ( ! empty( $base ) ) {
					$this->author_base = $base;
				}

				// Current DB version.
				if ( ! empty( $options['db_version'] ) ) {
					$this->current_db_version = absint( $options['db_version'] );
				}
			}
		}

		/** Public Methods ****************************************************/

		/**
		 * Load the translation file for current language. Checks the Edit Author
		 * Slug languages folder first, then inside the default WP language
		 * plugins folder.
		 *
		 * Note that custom translation files inside the Edit Author Slug plugin
		 * folder will be removed on edit-author-slug updates. If you're creating
		 * custom translation files, please use the global language folder
		 * (ie - wp-content/languages/plugins).
		 *
		 * @since 0.9.6
		 *
		 * @return void
		 */
		public function load_textdomain() {

			// Look in wp-content/plugins/edit-author-slug/languages first.
			// Fallback to wp-content/languages/plugins.
			load_plugin_textdomain( $this->domain, false, dirname( $this->plugin_basename ) . '/languages/' );
		}

		/**
		 * Set the role_slugs global
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function set_role_slugs() {

			// Get the default role slugs.
			$defaults = ba_eas_get_default_role_slugs();

			// Merge system roles with any customizations we may have.
			$role_slugs = array_replace_recursive(
				$defaults,
				get_option( '_ba_eas_role_slugs', array() )
			);

			foreach ( $role_slugs as $role => $details ) {

				if ( empty( $defaults[ $role ] ) ) {
					unset( $role_slugs[ $role ] );
				}
			}

			$this->role_slugs = $role_slugs;
		}

		/** Custom Rewrite Rules **********************************************/

		/**
		 * Add the Edit Author Slug rewrite tags
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function add_rewrite_tags() {

			// Should we be here?
			if ( ! ba_eas_do_role_based_author_base() ) {
				return;
			}

			// Get the role slugs to add the rewrite tag.
			$role_slugs = wp_list_pluck( $this->role_slugs, 'slug' );
			$role_slugs = array_filter( array_values( $role_slugs ) );

			// Add the author base as a fallback.
			$role_slugs[] = ba_eas()->author_base;

			// Add the role-based rewrite tag, and the expected role slugs.
			add_rewrite_tag( '%ba_eas_author_role%', '(' . implode( '|', array_unique( $role_slugs ) ) . ')' );
		}
	}

	/**
	 * The main function responsible for returning the one true BA_Edit_Author_Slug
	 * Instance to functions everywhere.
	 *
	 * Use this function like you would a global variable, except without needing
	 * to declare the global.
	 *
	 * Example: <?php $ba_eas = ba_eas(); ?>
	 *
	 * @return BA_Edit_Author_Slug|null The one true BA_Edit_Author_Slug Instance.
	 */
	function ba_eas() {
		return BA_Edit_Author_Slug::instance();
	}

	// Places everyone! The show is starting!
	ba_eas();

endif; // End class BA_Edit_Author_Slug.

/**
 * Runs on Edit Author Slug activation.
 *
 * @since 0.7.0
 *
 * @return void
 */
function ba_eas_activation() {

	/**
	 * Fires on Edit Author Slug activation.
	 *
	 * @since 0.7.0
	 */
	do_action( 'ba_eas_activation' );
}

/**
 * Runs on Edit Author Slug deactivation.
 *
 * @since 0.7.0
 *
 * @return void
 */
function ba_eas_deactivation() {

	/**
	 * Fires on Edit Author Slug deactivation.
	 *
	 * @since 0.7.0
	 */
	do_action( 'ba_eas_deactivation' );
}
