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
	 * The single user id.
	 *
	 * @var int
	 */
	private $single_user_id = null;

	/**
	 * The `setUp` method.
	 */
	public function setUp() {
		parent::setUp();

		$this->eas = ba_eas();

		$this->single_user_id = $this->factory->user->create( array(
			'user_login'   => 'mastersplinter',
			'user_pass'    => '1234',
			'user_email'   => 'mastersplinter@example.com',
			'display_name' => 'Master Splinter',
			'nickname'     => 'Sensei',
			'first_name'   => 'Master',
			'last_name'    => 'Splinter',
		) );
	}

	/**
	 * The `tearDown` method.
	 */
	public function tearDown() {
		parent::tearDown();

		// Reset some globals to their defaults.
		$this->eas->author_base             = 'author';
		$this->eas->role_slugs              = ba_eas_tests_slugs_default();
		$this->eas->do_auto_update          = false;
		$this->eas->do_role_based           = false;
		$this->eas->remove_front            = false;
		$GLOBALS['wp_rewrite']->author_base = 'author';
		$GLOBALS['wp_rewrite']->front       = '/';
	}

	/**
	 * Filters the return of user role filters.
	 *
	 * @since 1.2.0
	 *
	 * @param string $role The user role.
	 *
	 * @return string
	 */
	public function user_role_filter( $role = '' ) {
		return 'test';
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
	 * @expectedIncorrectUsage ba_eas_update_nicename_cache
	 */
	public function test_ba_eas_update_nicename_cache() {

		$this->assertEquals( $this->single_user_id, wp_cache_get( 'mastersplinter', 'userslugs' ) );

		$this->assertNull( ba_eas_update_nicename_cache( null ) );

		$user = get_userdata( $this->single_user_id );
		wp_update_user( array(
			'ID' => $this->single_user_id,
			'user_nicename' => 'master-splinter',
		) );
		ba_eas_update_nicename_cache( $this->single_user_id, $user );
		$this->assertNotEquals( $this->single_user_id, wp_cache_get( 'mastersplinter', 'userslugs' ) );
		$this->assertEquals( $this->single_user_id, wp_cache_get( 'master-splinter', 'userslugs' ) );

		$user = get_userdata( $this->single_user_id );
		wp_update_user( array(
			'ID' => $this->single_user_id,
			'user_nicename' => 'mastersplinter',
		) );
		ba_eas_update_nicename_cache( false, $user );
		$this->assertNotEquals( $this->single_user_id, (int) wp_cache_get( 'master-splinter', 'userslugs' ) );
		$this->assertEquals( $this->single_user_id, (int) wp_cache_get( 'mastersplinter', 'userslugs' ) );

		$user = get_userdata( $this->single_user_id );
		ba_eas_update_nicename_cache( $this->single_user_id, $user, 'splintermaster' );
		$this->assertNotEquals( $this->single_user_id, wp_cache_get( 'mastersplinter', 'userslugs' ) );
		$this->assertEquals( $this->single_user_id, wp_cache_get( 'splintermaster', 'userslugs' ) );

		$user = get_userdata( $this->single_user_id );
		ba_eas_update_nicename_cache( $this->single_user_id, 'splintermaster', 'splinter-master' );
		$this->assertNotEquals( $this->single_user_id, wp_cache_get( 'splintermaster', 'userslugs' ) );
		$this->assertEquals( $this->single_user_id, wp_cache_get( 'splinter-master', 'userslugs' ) );
	}
}
