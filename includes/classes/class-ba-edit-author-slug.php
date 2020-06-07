<?php
/**
 * The main Edit Author Slug class.
 *
 * @package Edit_Author_Slug
 * @subpackage Classes
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Main Edit Author Slug class.
 */
if ( ! class_exists( 'BA_Edit_Author_Slug' ) ) :

	/**
	 * BA_Edit_Author_Slug class.
	 *
	 * @since 0.1.0
	 */
	class BA_Edit_Author_Slug {

		/**
		 * The plugin version.
		 *
		 * @since 1.4.0
		 * @var   string
		 */
		const VERSION = '1.5.2';

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
		public $file = '';

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
				$instance = new self();
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

			$this->file            = dirname( dirname( dirname( __FILE__ ) ) ) . '/edit-author-slug.php';
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
			require_once $this->plugin_dir . 'includes/deprecated.php';
			require_once $this->plugin_dir . 'includes/functions.php';
			require_once $this->plugin_dir . 'includes/hooks.php';

			// Maybe load the admin functions.
			if ( is_admin() ) {
				require_once $this->plugin_dir . 'includes/admin.php';
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
		public function setup_actions() {

			// Author Base Actions.
			add_action( 'after_setup_theme', array( $this, 'set_role_slugs' ) );
			add_action( 'init', 'ba_eas_wp_rewrite_overrides', 4 );
			add_action( 'init', array( $this, 'add_rewrite_tags' ), 20 );

			// Localize.
			add_action( 'ba_eas_loaded', array( $this, 'load_textdomain' ) );
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

			// Add a fallback.
			if ( false === strpos( $this->author_base, '%ba_eas_author_role%' ) && false === strpos( $this->author_base, '/' ) ) {
				$role_slugs[] = $this->author_base;
			} else {
				$role_slugs[] = 'author';
			}

			// Add the role-based rewrite tag, and the expected role slugs.
			add_rewrite_tag( '%ba_eas_author_role%', '(' . implode( '|', array_unique( $role_slugs ) ) . ')' );
		}

		/**
		 * Checks if iThemes Security is enabled, and if the Force Unique
		 * Nickname WordPress Tweak is turned on.
		 *
		 * @since 1.6.0
		 *
		 * @return bool
		 */
		public function is_itsec_force_unique_nickname() {
			$retval = false;

			if ( method_exists( 'ITSEC_Modules', 'get_settings' ) ) {
				$tweaks = ITSEC_Modules::get_settings( 'wordpress-tweaks' );
				$retval = $tweaks['force_unique_nicename'];
			}

			return (bool) $retval;
		}
	}
endif; // End class BA_Edit_Author_Slug.
