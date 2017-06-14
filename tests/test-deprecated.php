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
	 * @var int
	 */
	protected static $user_id;

	/**
	 * The old user id.
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
		self::$user_id = $f->user->create( array(
			'user_login' => 'mastersplinter',
			'role'       => 'administrator',
			'first_name' => 'Master',
			'last_name'  => 'Splinter',
		) );

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
	 * The `tearDown` method.
	 */
	public function tearDown() {
		parent::tearDown();

		// Reset the user data.
		wp_update_user( array(
			'ID'            => self::$user_id,
			'user_nicename' => 'mastersplinter',
			'role'          => 'administrator',
			'first_name'    => 'Master',
			'last_name'     => 'Splinter',
		) );
	}

	/**
	 * Test for `ba_eas_auto_update_user_nicename_single()`.
	 *
	 * @covers ::ba_eas_auto_update_user_nicename_single
	 * @expectedDeprecated ba_eas_auto_update_user_nicename_single
	 */
	public function test_ba_eas_auto_update_user_nicename_single() {
		$this->assertFalse( ba_eas_auto_update_user_nicename_single() );
	}

	/**
	 * Test for `ba_eas_get_wp_roles()`.
	 *
	 * @covers ::ba_eas_get_wp_roles
	 * @expectedDeprecated ba_eas_get_wp_roles
	 */
	public function test_ba_eas_get_wp_roles() {
		$this->assertInstanceOf( 'WP_Roles', ba_eas_get_wp_roles() );
	}

	/**
	 * Test for `ba_eas_get_editable_roles()`.
	 *
	 * @covers ::ba_eas_get_editable_roles
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
	 * @covers ::ba_eas_update_nicename_cache
	 *
	 * @expectedDeprecated ba_eas_update_nicename_cache
	 */
	public function test_ba_eas_update_nicename_cache() {

		// Make sure the user is cached.
		$user = get_userdata( self::$user_id );

		$this->assertEquals( self::$user_id, wp_cache_get( 'mastersplinter', 'userslugs' ) );

		wp_update_user( array(
			'ID'            => self::$user_id,
			'user_nicename' => 'master-splinter',
		) );

		ba_eas_update_nicename_cache( self::$user_id, $user );
		$this->assertNotEquals( self::$user_id, wp_cache_get( 'mastersplinter', 'userslugs' ) );
		$this->assertEquals( self::$user_id, wp_cache_get( 'master-splinter', 'userslugs' ) );
	}

	/**
	 * Test for `ba_eas_update_nicename_cache()` when no user id is passed.
	 *
	 * @covers ::ba_eas_update_nicename_cache
	 *
	 * @expectedDeprecated ba_eas_update_nicename_cache
	 */
	public function test_ba_eas_update_nicename_cache_no_user_id() {
		// Make sure the user is cached.
		$user = get_userdata( self::$user_id );
		$this->assertNull( ba_eas_update_nicename_cache( 0, new WP_User ) );
	}

	/**
	 * Test for `ba_eas_update_nicename_cache()` when no user id is passed.
	 *
	 * @covers ::ba_eas_update_nicename_cache
	 *
	 * @expectedDeprecated ba_eas_update_nicename_cache
	 */
	public function test_ba_eas_update_nicename_cache_old_userdata_only() {

		// Make sure the user is cached.
		$user = get_userdata( self::$user_id );

		wp_update_user( array(
			'ID'            => self::$user_id,
			'user_nicename' => 'master-splinter',
		) );

		ba_eas_update_nicename_cache( false, $user );
		$this->assertNotEquals( self::$user_id, (int) wp_cache_get( 'mastersplinter', 'userslugs' ) );
		$this->assertEquals( self::$user_id, (int) wp_cache_get( 'master-splinter', 'userslugs' ) );
	}

	/**
	 * Test for `ba_eas_update_nicename_cache()` when a nicename is passed to
	 * the old user data parameter.
	 *
	 * @covers ::ba_eas_update_nicename_cache
	 *
	 * @expectedDeprecated ba_eas_update_nicename_cache
	 */
	public function test_ba_eas_update_nicename_cache_nicename_new_nicename_passed() {

		// Make sure the user is cached.
		$user = get_userdata( self::$user_id );

		ba_eas_update_nicename_cache( self::$user_id, $user, 'splinter-master' );
		$this->assertNotEquals( self::$user_id, wp_cache_get( 'mastersplinter', 'userslugs' ) );
		$this->assertEquals( self::$user_id, wp_cache_get( 'splinter-master', 'userslugs' ) );
	}

	/**
	 * Test for `ba_eas_update_nicename_cache()` when a nicename is passed to
	 * the old user data parameter.
	 *
	 * @covers ::ba_eas_update_nicename_cache
	 *
	 * @expectedDeprecated ba_eas_update_nicename_cache
	 * @expectedIncorrectUsage ba_eas_update_nicename_cache
	 */
	public function test_ba_eas_update_nicename_cache_nicename_as_old_user_data() {

		// Make sure the user is cached.
		$user = get_userdata( self::$user_id );

		wp_update_user( array(
			'ID'            => self::$user_id,
			'user_nicename' => 'master-splinter',
		) );

		ba_eas_update_nicename_cache( self::$user_id, 'mastersplinter' );
		$this->assertNotEquals( self::$user_id, wp_cache_get( 'mastersplinter', 'userslugs' ) );
		$this->assertEquals( self::$user_id, wp_cache_get( 'master-splinter', 'userslugs' ) );
	}
}
