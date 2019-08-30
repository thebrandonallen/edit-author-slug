<?php
/**
 * Test the Edit Author Slug deprecated functions.
 *
 * @package Edit_Author_Slug
 * @subpackage Tests
 */

/**
 * The Edit Author Slug deprecated functions test case.
 */
class BA_EAS_Tests_Deprecated extends WP_UnitTestCase {

	/**
	 * The new user id.
	 *
	 * @since 1.6.0
	 *
	 * @var int
	 */
	protected static $user_id;

	/**
	 * The old user id.
	 *
	 * @since 1.6.0
	 *
	 * @var int
	 */
	protected static $old_user_id;

	/**
	 * Set up the admin fixture.
	 *
	 * @since 1.6.0
	 */
	public static function setUpBeforeClass() {
		$f = new WP_UnitTest_Factory();

		// Set up the new user.
		self::$user_id = $f->user->create(
			array(
				'user_login' => 'mastersplinter',
				'role'       => 'administrator',
				'first_name' => 'Master',
				'last_name'  => 'Splinter',
			)
		);

		self::commit_transaction();

		// Set the old user id.
		self::$old_user_id = get_current_user_id();
	}

	/**
	 * Tear down the admin fixture.
	 *
	 * @since 1.6.0
	 */
	public static function tearDownAfterClass() {
		wp_delete_user( self::$user_id );
		self::commit_transaction();
	}

	/**
	 * Test for `ba_eas_auto_update_user_nicename_single()`.
	 *
	 * @since 1.1.0
	 *
	 * @covers ::ba_eas_auto_update_user_nicename_single
	 *
	 * @expectedDeprecated ba_eas_auto_update_user_nicename_single
	 */
	public function test_ba_eas_auto_update_user_nicename_single() {
		$this->assertFalse( ba_eas_auto_update_user_nicename_single() );
	}

	/**
	 * Test for `ba_eas_get_wp_roles()`.
	 *
	 * @since 1.2.0
	 *
	 * @covers ::ba_eas_get_wp_roles
	 *
	 * @expectedDeprecated ba_eas_get_wp_roles
	 */
	public function test_ba_eas_get_wp_roles() {
		$this->assertInstanceOf( 'WP_Roles', ba_eas_get_wp_roles() );
	}

	/**
	 * Test for `ba_eas_get_editable_roles()`.
	 *
	 * @since 1.1.0
	 *
	 * @covers ::ba_eas_get_editable_roles
	 *
	 * @expectedDeprecated ba_eas_get_editable_roles
	 */
	public function test_ba_eas_get_editable_roles() {

		// Test with empty $wp_roles global.
		global $wp_roles;
		unset( $wp_roles );
		$this->assertEquals( ba_eas_tests_roles_default(), ba_eas_get_editable_roles() );

		// Test default WP roles.
		$this->assertEquals( ba_eas_tests_roles_default(), ba_eas_get_editable_roles() );

		// Test with extra role.
		add_filter( 'editable_roles', 'ba_eas_tests_roles_extra' );
		$this->assertEquals( ba_eas_tests_roles_extra(), ba_eas_get_editable_roles() );
		remove_filter( 'editable_roles', 'ba_eas_tests_roles_extra', 10 );
	}

	/**
	 * Test for `ba_eas_update_nicename_cache()`.
	 *
	 * @since 1.1.0
	 * @since 1.6.1 Main function converted to noop, so only test for `null` value.
	 *
	 * @covers ::ba_eas_update_nicename_cache
	 *
	 * @expectedDeprecated ba_eas_update_nicename_cache
	 */
	public function test_ba_eas_update_nicename_cache() {
		$this->assertNull( ba_eas_update_nicename_cache() );
	}
}
