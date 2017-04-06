<?php
/**
 * Test the Edit Author Slug hooks.
 *
 * @package Edit_Author_Slug
 * @subpackage Tests
 */

/**
 * The Edit Author Slug hooks test case.
 */
class BA_EAS_Tests_Hooks extends WP_UnitTestCase {

	/**
	 * The `setUp` method.
	 */
	public function setUp() {
		parent::setUp();

		$this->eas = ba_eas();
	}

	/**
	 * Test that actions are added.
	 */
	public function test_actions_added() {
		$this->assertEquals( 10, has_action( 'profile_update', 'ba_eas_auto_update_user_nicename' ) );
		$this->assertEquals( 10, has_action( 'user_register', 'ba_eas_auto_update_user_nicename' ) );
		$this->assertEquals( 20, has_filter( 'author_link', 'ba_eas_author_link' ) );
		$this->assertEquals( 10, has_filter( 'author_rewrite_rules', 'ba_eas_author_rewrite_rules' ) );
		$this->assertEquals( 10, has_filter( 'author_template', 'ba_eas_template_include' ) );
	}

	/**
	 * Test that admin actions are added.
	 */
	public function test_admin_actions_added() {
		set_current_screen( 'admin.php' );
		require( $this->eas->plugin_dir . 'includes/hooks.php' );

		$this->assertEquals( 10, has_action( 'ba_eas_activation', 'ba_eas_install' ) );
		$this->assertEquals( 999, has_action( 'admin_init', 'ba_eas_upgrade' ) );
		$this->assertEquals( 10, has_action( 'edit_user_profile', 'ba_eas_show_user_nicename' ) );
		$this->assertEquals( 10, has_action( 'show_user_profile', 'ba_eas_show_user_nicename' ) );
		$this->assertEquals( 10, has_action( 'user_profile_update_errors', 'ba_eas_update_user_nicename' ) );
		$this->assertEquals( 10, has_action( 'admin_enqueue_scripts', 'ba_eas_show_user_nicename_scripts' ) );
		$this->assertEquals( 10, has_action( 'manage_users_columns', 'ba_eas_author_slug_column' ) );
		$this->assertEquals( 10, has_action( 'manage_users_custom_column', 'ba_eas_author_slug_custom_column' ) );
		$this->assertEquals( 10, has_action( 'admin_menu', 'ba_eas_add_settings_menu' ) );
		$this->assertEquals( 10, has_action( 'admin_init', 'ba_eas_register_admin_settings' ) );
		$this->assertEquals( 10, has_action( 'plugin_action_links', 'ba_eas_add_settings_link' ) );
	}
}
