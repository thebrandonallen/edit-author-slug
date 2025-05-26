<?php
/**
 * Test the main class functionality.
 *
 * @package Edit_Author_Slug
 * @subpackage Tests
 */

/**
 * The Edit Author Slug unit test case.
 */
class BA_EAS_Tests_BA_Edit_Author_Slug extends WP_UnitTestCase {

	/**
	 * The default roles slugs.
	 *
	 * @since 1.6.0
	 *
	 * @var array
	 */
	protected static $default_role_slugs;

	/**
	 * Set up the main class fixture.
	 *
	 * @since 1.6.0
	 */
	public static function setUpBeforeClass(): void {
		// Load the ITSEC_Modules mock.
		require_once 'mocks/class-itsec-modules-mock.php';
	}

	/**
	 * The admin `setUp` method.
	 *
	 * @since 1.1.0
	 */
	public function setUp(): void {
		parent::setUp();

		// Set the default roles slugs, if not already.
		if ( is_null( self::$default_role_slugs ) ) {
			self::$default_role_slugs = ba_eas_get_default_role_slugs();
		}
	}

	/**
	 * The admin `tearDown` method.
	 *
	 * @since 1.1.0
	 *
	 * Resets the current user and globals.
	 */
	public function tearDown(): void {
		parent::tearDown();

		// Reset properties used in testing.
		ba_eas()->author_base   = 'author';
		ba_eas()->role_slugs    = self::$default_role_slugs;
		ba_eas()->do_role_based = false;
	}

	/**
	 * Test for `BA_Edit_Author_Slug::setup_globals()`.
	 *
	 * @since 1.2.0
	 *
	 * @covers BA_Edit_Author_Slug::setup_globals
	 */
	public function test_setup_globals() {
		$file = dirname( dirname( __FILE__ ) ) . '/edit-author-slug.php';
		$this->assertEquals( $file, ba_eas()->file );
		$this->assertEquals( plugin_dir_path( $file ), ba_eas()->plugin_dir );
		$this->assertEquals( plugin_dir_url( $file ), ba_eas()->plugin_url );
		$this->assertEquals( plugin_basename( $file ), ba_eas()->plugin_basename );
		$this->assertEquals( 'author', ba_eas()->author_base );
		$this->assertEquals( 'username', ba_eas()->default_user_nicename );
		$this->assertEquals( false, ba_eas()->remove_front );
		$this->assertEquals( false, ba_eas()->do_auto_update );
		$this->assertEquals( false, ba_eas()->do_role_based );
		// $this->assertEquals( array(), ba_eas()->role_slugs );
	}

	/**
	 * Test for `BA_Edit_Author_Slug::setup_actions()`.
	 *
	 * @since 1.1.0
	 *
	 * @covers BA_Edit_Author_Slug::setup_actions
	 */
	public function test_setup_actions() {
		$this->assertEquals( 10, has_action( 'activate_' . ba_eas()->plugin_basename, 'ba_eas_activation' ) );
		$this->assertEquals( 10, has_action( 'deactivate_' . ba_eas()->plugin_basename, 'ba_eas_deactivation' ) );
		$this->assertEquals( 10, has_action( 'after_setup_theme', array( ba_eas(), 'set_role_slugs' ) ) );
		$this->assertEquals( 4, has_action( 'init', 'ba_eas_wp_rewrite_overrides' ) );
		$this->assertEquals( 20, has_action( 'init', array( ba_eas(), 'add_rewrite_tags' ) ) );
	}

	/**
	 * Test the `BA_Edit_Author_Slug::__get()`.
	 *
	 * @since 1.5.0
	 *
	 * @covers BA_Edit_Author_Slug::__get
	 * @expectedIncorrectUsage BA_Edit_Author_Slug::version
	 * @expectedIncorrectUsage BA_Edit_Author_Slug::db_version
	 */
	public function test__get() {

		$this->assertNull( ba_eas()->__get( 'fake_property' ) );

		$actual = ba_eas()->__get( 'version' );
		$this->assertSame( BA_Edit_Author_Slug::VERSION, $actual );

		$actual = ba_eas()->__get( 'db_version' );
		$this->assertSame( BA_Edit_Author_Slug::DB_VERSION, $actual );
	}

	/**
	 * Test for `BA_Edit_Author_Slug::author_base_rewrite()`.
	 *
	 * @since 1.1.0
	 *
	 * @covers BA_Edit_Author_Slug::author_base_rewrite
	 *
	 * @expectedDeprecated BA_Edit_Author_Slug::author_base_rewrite
	 */
	public function test_author_base_rewrite() {
		$this->assertNull( ba_eas()->author_base_rewrite() );
	}

	/**
	 * Test for `BA_Edit_Author_Slug::set_role_slugs()`.
	 *
	 * @since 1.1.0
	 *
	 * @covers BA_Edit_Author_Slug::set_role_slugs
	 */
	public function test_set_role_slugs() {

		$role_slugs                       = self::$default_role_slugs;
		$role_slugs['subscriber']['slug'] = 'test';

		update_option( '_ba_eas_role_slugs', $role_slugs );
		ba_eas()->set_role_slugs();
		$this->assertEquals( ba_eas()->role_slugs, $role_slugs );
		update_option( '_ba_eas_role_slugs', self::$default_role_slugs );
	}

	/**
	 * Test for `BA_Edit_Author_Slug::set_role_slugs()` when a custom role exists.
	 *
	 * @since 1.6.0
	 *
	 * @covers BA_Edit_Author_Slug::set_role_slugs
	 */
	public function test_set_role_slugs_custom_role() {

		$role_slugs = self::$default_role_slugs + array(
			'foot-soldier' => array(
				'name' => 'Foot Soldier',
				'slug' => 'foot-soldier',
			),
		);

		add_role( 'foot-soldier', 'Foot Soldier' );
		ba_eas()->set_role_slugs();
		$this->assertEquals( ba_eas()->role_slugs, $role_slugs );
		remove_role( 'foot-soldier' );
	}

	/**
	 * Test for `BA_Edit_Author_Slug::add_rewrite_tags()`.
	 *
	 * @since 1.1.0
	 *
	 * @covers BA_Edit_Author_Slug::add_rewrite_tags
	 */
	public function test_add_rewrite_tags() {

		// Make sure the rewrite tag doesn't already exist.
		remove_rewrite_tag( '%ba_eas_author_role%' );

		// // Test for WP default roles/role slugs.
		add_filter( 'ba_eas_do_role_based_author_base', '__return_true' );
		ba_eas()->add_rewrite_tags();
		remove_filter( 'ba_eas_do_role_based_author_base', '__return_true' );

		$slugs = '(administrator|editor|author|contributor|subscriber)';

		$this->assertContains( '%ba_eas_author_role%', $GLOBALS['wp_rewrite']->rewritecode );
		$this->assertContains( $slugs, $GLOBALS['wp_rewrite']->rewritereplace );
	}

	/**
	 * Test for `BA_Edit_Author_Slug::add_rewrite_tags()` when role based author
	 * bases are disabled.
	 *
	 * @since 1.6.0
	 *
	 * @covers BA_Edit_Author_Slug::add_rewrite_tags
	 */
	public function test_add_rewrite_tags_not_role_based() {
		$this->assertNull( ba_eas()->add_rewrite_tags() );
	}

	/**
	 * Test for `BA_Edit_Author_Slug::add_rewrite_tags()` when the author base
	 * contains the rewrite tag, and that `author` is appended when appropriate.
	 *
	 * @since 1.6.0
	 *
	 * @covers BA_Edit_Author_Slug::add_rewrite_tags
	 */
	public function test_add_rewrite_tags_rewrite_tag_in_author_base() {

		$role_slugs                   = self::$default_role_slugs;
		$role_slugs['author']['slug'] = 'test';

		ba_eas()->role_slugs  = $role_slugs;
		ba_eas()->author_base = '%ba_eas_author_role%';

		add_filter( 'ba_eas_do_role_based_author_base', '__return_true' );
		ba_eas()->add_rewrite_tags();
		remove_filter( 'ba_eas_do_role_based_author_base', '__return_true' );

		$slugs = '(administrator|editor|test|contributor|subscriber|author)';

		$this->assertContains( $slugs, $GLOBALS['wp_rewrite']->rewritereplace );

	}

	/**
	 * Test for `BA_Edit_Author_Slug::add_rewrite_tags()` when the author base
	 * contains a forward slash, and that `author` is appended when appropriate.
	 *
	 * @since 1.6.0
	 *
	 * @covers BA_Edit_Author_Slug::add_rewrite_tags
	 */
	public function test_add_rewrite_tags_slash_in_author_base() {

		$role_slugs                   = self::$default_role_slugs;
		$role_slugs['author']['slug'] = 'test';

		ba_eas()->role_slugs  = $role_slugs;
		ba_eas()->author_base = 'author/base';

		add_filter( 'ba_eas_do_role_based_author_base', '__return_true' );
		ba_eas()->add_rewrite_tags();
		remove_filter( 'ba_eas_do_role_based_author_base', '__return_true' );

		$slugs = '(administrator|editor|test|contributor|subscriber|author)';

		$this->assertContains( $slugs, $GLOBALS['wp_rewrite']->rewritereplace );

	}

	/**
	 * Test for `BA_Edit_Author_Slug::add_rewrite_tags()` when a custom author
	 * base has been set.
	 *
	 * @since 1.6.0
	 *
	 * @covers BA_Edit_Author_Slug::add_rewrite_tags
	 */
	public function test_add_rewrite_tags_custom_author_base() {

		// // Test for WP default roles/role slugs.
		ba_eas()->author_base = 'test';

		add_filter( 'ba_eas_do_role_based_author_base', '__return_true' );
		ba_eas()->add_rewrite_tags();
		remove_filter( 'ba_eas_do_role_based_author_base', '__return_true' );

		$slugs = '(administrator|editor|author|contributor|subscriber|test)';

		$this->assertContains( $slugs, $GLOBALS['wp_rewrite']->rewritereplace );
	}

	/**
	 * Test for `BA_Edit_Author_Slug::add_rewrite_tags()` when role slugs have
	 * been customized, and that the author base is added when appropriate.
	 *
	 * @since 1.6.0
	 *
	 * @covers BA_Edit_Author_Slug::add_rewrite_tags
	 */
	public function test_add_rewrite_tags_custom_role_slugs() {

		$role_slugs                   = self::$default_role_slugs;
		$role_slugs['author']['slug'] = 'test';

		ba_eas()->role_slugs = $role_slugs;

		add_filter( 'ba_eas_do_role_based_author_base', '__return_true' );
		ba_eas()->add_rewrite_tags();
		remove_filter( 'ba_eas_do_role_based_author_base', '__return_true' );

		$slugs = '(administrator|editor|test|contributor|subscriber|author)';

		$this->assertContains( $slugs, $GLOBALS['wp_rewrite']->rewritereplace );

	}

	/**
	 * Test for `BA_Edit_Author_Slug::is_itsec_force_unique_nickname()`.
	 *
	 * @since 1.6.0
	 *
	 * @covers BA_Edit_Author_Slug::is_itsec_force_unique_nickname
	 */
	public function test_is_itsec_force_unique_nickname() {
		ITSEC_Modules::$force_unique_nicename = true;
		$this->assertTrue( ba_eas()->is_itsec_force_unique_nickname() );
	}

	/**
	 * Test for `ba_eas_activation()`.
	 *
	 * @since 1.1.0
	 *
	 * @covers ::ba_eas_activation
	 */
	public function test_ba_eas_activation() {
		ba_eas_activation();
		$this->assertTrue( (bool) did_action( 'ba_eas_activation' ) );
	}

	/**
	 * Test for `ba_eas_deactivation()`.
	 *
	 * @since 1.1.0
	 *
	 * @covers ::ba_eas_deactivation
	 */
	public function test_ba_eas_deactivation() {
		ba_eas_deactivation();
		$this->assertTrue( (bool) did_action( 'ba_eas_deactivation' ) );
	}
}
