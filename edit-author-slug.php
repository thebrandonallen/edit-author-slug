<?php
/**
 * Plugin Name:     Edit Author Slug
 * Plugin URI:      https://github.com/thebrandonallen/edit-author-slug/
 * Description:     Allows an Admin (or capable user) to edit the author slug of a user, and change the Author Base. <em>i.e. - (WordPress default structure) http://example.com/author/username/ (Plugin allows) http://example.com/ninja/master-ninja/</em>
 * Author:          Brandon Allen
 * Author URI:      https://github.com/thebrandonallen/
 * Text Domain:     edit-author-slug
 * Domain Path:     /languages
 * Version:         1.4.1
 *
 * @package Edit_Author_Slug
 * @subpackage Main
 * @author Brandon Allen
 * @version 1.4.1
 */

/*
	Copyright (C) 2009-2017  Brandon Allen  (email : plugins ([at]) brandonallen ([dot]) me)

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

	https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
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
	class BA_Edit_Author_Slug {

		/**
		 * The plugin version.
		 *
		 * @since 1.4.0
		 * @var   string
		 */
		const VERSION = '1.4.1';

		/**
		 * The plugin version.
		 *
		 * @since 1.4.0
		 * @var   int
		 */
		const DB_VERSION = 411;

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
				$instance = new self;
			}

			// Always return the instance.
			return $instance;
		}

		/**
		 * The constructor.
		 *
		 * @since 1.4.0
		 */
		public function __construct() {
			$this->setup_globals();
			$this->includes();
			$this->setup_options();
			$this->setup_actions();
		}

		/** Magic Methods *****************************************************/

		/**
		 * Magic method for accessing custom/deprecated/nonexistent properties.
		 *
		 * @since 1.4.0
		 *
		 * @param string $name The property name.
		 *
		 * @return mixed
		 */
		public function __get( $name ) {

			// Default to null.
			$retval = null;

			if ( 'version' === $name ) {
				$retval = self::VERSION;
				_doing_it_wrong(
					'BA_Edit_Author_Slug::version',
					esc_html__( 'Use class constant, BA_Edit_Author_Slug::VERSION, instead.', 'edit-author-slug' ),
					'1.4.0'
				);
			} elseif ( 'db_version' === $name ) {
				$retval = self::DB_VERSION;
				_doing_it_wrong(
					'BA_Edit_Author_Slug::db_version',
					esc_html__( 'Use class constant, BA_Edit_Author_Slug::DB_VERSION, instead.', 'edit-author-slug' ),
					'1.4.0'
				);
			}

			return $retval;
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
			$remove_front       = (int) get_option( '_ba_eas_remove_front', 0 );
			$this->remove_front = (bool) $remove_front;

			// Load auto-update option.
			$do_auto_update       = (int) get_option( '_ba_eas_do_auto_update', 0 );
			$this->do_auto_update = (bool) $do_auto_update;

			// Load the default nicename structure for auto-update.
			$default_user_nicename = get_option( '_ba_eas_default_user_nicename' );
			$default_user_nicename = sanitize_key( $default_user_nicename );
			if ( ! empty( $default_user_nicename ) ) {
				$this->default_user_nicename = $default_user_nicename;
			}

			// Load role-based author slug option.
			$do_role_based       = (int) get_option( '_ba_eas_do_role_based', 0 );
			$this->do_role_based = (bool) $do_role_based;
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
		 * Get our options from the DB and set their corresponding properties.
		 *
		 * @since 1.4.0
		 *
		 * @return void
		 */
		private function setup_options() {

			// Get the author base option.
			$base = get_option( '_ba_eas_author_base' );

			// Options.
			if ( $base ) {

				// Sanitize the db value.
				$this->author_base = ba_eas_sanitize_author_base( $base );

				// Current DB version.
				$this->current_db_version = (int) get_option( '_ba_eas_db_version', 0 );
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

		/** Public Methods ****************************************************/

		/**
		 * Load the translation file for the current language.
		 *
		 * We only check the default WP language plugins folder.
		 * (ie - wp-content/languages/plugins).
		 *
		 * @since 0.9.6
		 * @since 1.5.0 Only check the default WP languages folder.
		 *
		 * @return bool
		 */
		public function load_textdomain() {
			return load_plugin_textdomain( 'edit-author-slug' );
		}

		/**
		 * Rewrite Author Base according to user's setting.
		 *
		 * Rewrites Author Base to user's setting from the Author Base field
		 * on Options > Permalinks.
		 *
		 * @since 0.4.0
		 * @deprecated 1.2.0
		 *
		 * @return void
		 */
		public function author_base_rewrite() {
			_deprecated_function( __METHOD__, '1.2.0', 'ba_eas_wp_rewrite_overrides' );
			ba_eas_wp_rewrite_overrides();
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

			// Grab the author base.
			$author_base = ba_eas()->author_base;

			// Add a fallback.
			if ( false === strpos( $author_base, '%ba_eas_author_role%' ) && false === strpos( $author_base, '/' ) ) {
				$role_slugs[] = $author_base;
			} else {
				$role_slugs[] = 'author';
			}

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
