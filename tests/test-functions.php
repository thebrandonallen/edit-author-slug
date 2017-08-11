<?php
/**
 * Test the Edit Author Slug functions.
 *
 * @package Edit_Author_Slug
 * @subpackage Tests
 */

/**
 * The Edit Author Slug functions test case.
 */
class BA_EAS_Tests_Functions extends WP_UnitTestCase {

	/**
	 * The single user id.
	 *
	 * @since 1.1.0
	 *
	 * @var int
	 */
	private $single_user_id = null;

	/**
	 * The default roles slugs.
	 *
	 * @since 1.6.0
	 *
	 * @var array
	 */
	protected static $default_role_slugs;

	/**
	 * The `setUp` method.
	 *
	 * @since 1.1.0
	 */
	public function setUp() {
		parent::setUp();

		$this->single_user_id = $this->factory->user->create( array(
			'user_login'   => 'mastersplinter',
			'user_pass'    => '1234',
			'user_email'   => 'mastersplinter@example.com',
			'display_name' => 'Master Splinter',
			'nickname'     => 'Sensei',
			'first_name'   => 'Master',
			'last_name'    => 'Splinter',
		) );

		// Set the default roles slugs, if not already.
		if ( is_null( self::$default_role_slugs ) ) {
			self::$default_role_slugs = ba_eas_get_default_role_slugs();
		}
	}

	/**
	 * The `tearDown` method.
	 *
	 * @since 1.1.0
	 */
	public function tearDown() {
		parent::tearDown();

		// Reset some globals to their defaults.
		ba_eas()->author_base               = 'author';
		ba_eas()->role_slugs                = self::$default_role_slugs;
		ba_eas()->do_auto_update            = false;
		ba_eas()->do_role_based             = false;
		ba_eas()->remove_front              = false;
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
	 * Test for `ba_eas_do_auto_update()`.
	 *
	 * @since 1.1.0
	 *
	 * @covers ::ba_eas_do_auto_update
	 */
	public function test_ba_eas_do_auto_update() {

		// False test.
		$this->assertFalse( ba_eas_do_auto_update() );

		// True test.
		ba_eas()->do_auto_update = true;
		$this->assertTrue( ba_eas_do_auto_update() );
	}

	/**
	 * Test for `ba_eas_do_bulk_update()`.
	 *
	 * @since 1.4.0
	 *
	 * @covers ::ba_eas_do_bulk_update
	 */
	public function test_ba_eas_do_bulk_update() {

		// True tests.
		$this->assertTrue( ba_eas_do_bulk_update( '1' ) );
		$this->assertTrue( ba_eas_do_bulk_update( true ) );

		// False tests.
		$this->assertFalse( ba_eas_do_bulk_update( '0' ) );
		$this->assertFalse( ba_eas_do_bulk_update( false ) );
		$this->assertFalse( ba_eas_do_bulk_update( 'ninja' ) );
	}

	/**
	 * Test for `ba_eas_auto_update_user_nicename()`.
	 *
	 * @since 1.1.0
	 *
	 * @covers ::ba_eas_auto_update_user_nicename
	 */
	public function test_ba_eas_auto_update_user_nicename() {
		// No user id.
		$this->assertFalse( ba_eas_auto_update_user_nicename() );

		// No auto update.
		$this->assertFalse( ba_eas_auto_update_user_nicename( 1 ) );

		// We need below tests to think auto update is on.
		ba_eas()->do_auto_update = true;

		// Invalid user.
		$this->assertFalse( ba_eas_auto_update_user_nicename( 1337 ) );

		// Update using username.
		ba_eas()->default_user_nicename = '';
		$user_id = ba_eas_auto_update_user_nicename( $this->single_user_id );
		$user    = get_userdata( $this->single_user_id );
		$this->assertEquals( 'mastersplinter', $user->user_nicename );

		// Update using username.
		ba_eas()->default_user_nicename = 'username';
		$user_id = ba_eas_auto_update_user_nicename( $this->single_user_id );
		$user    = get_userdata( $this->single_user_id );
		$this->assertEquals( 'mastersplinter', $user->user_nicename );

		// Update using nickname.
		ba_eas()->default_user_nicename = 'nickname';
		$user_id = ba_eas_auto_update_user_nicename( $this->single_user_id );
		$user    = get_userdata( $this->single_user_id );
		$this->assertEquals( 'sensei', $user->user_nicename );

		// Update using displayname.
		ba_eas()->default_user_nicename = 'displayname';
		$user_id = ba_eas_auto_update_user_nicename( $this->single_user_id );
		$user    = get_userdata( $this->single_user_id );
		$this->assertEquals( 'master-splinter', $user->user_nicename );

		// Update using firstname.
		ba_eas()->default_user_nicename = 'firstname';
		$user_id = ba_eas_auto_update_user_nicename( $this->single_user_id );
		$user    = get_userdata( $this->single_user_id );
		$this->assertEquals( 'master', $user->user_nicename );

		// Update using lastname.
		ba_eas()->default_user_nicename = 'lastname';
		$user_id = ba_eas_auto_update_user_nicename( $this->single_user_id );
		$user    = get_userdata( $this->single_user_id );
		$this->assertEquals( 'splinter', $user->user_nicename );

		// Update using firstlast.
		ba_eas()->default_user_nicename = 'firstlast';
		$user_id = ba_eas_auto_update_user_nicename( $this->single_user_id );
		$user    = get_userdata( $this->single_user_id );
		$this->assertEquals( 'master-splinter', $user->user_nicename );

		// Update using lastfirst.
		ba_eas()->default_user_nicename = 'lastfirst';
		$user_id = ba_eas_auto_update_user_nicename( $this->single_user_id );
		$user    = get_userdata( $this->single_user_id );
		$this->assertEquals( 'splinter-master', $user->user_nicename );

		// Update using lastfirst.
		ba_eas()->default_user_nicename = 'userid';
		$user_id = ba_eas_auto_update_user_nicename( $this->single_user_id );
		$user    = get_userdata( $this->single_user_id );
		$this->assertEquals( $this->single_user_id, $user->user_nicename );

		// Update using random string as structure, shouldn't update, so
		// user_nicename should be same as previous test ('splinter-master').
		ba_eas()->default_user_nicename = 'Cowabunga Dude!';
		$user_id = ba_eas_auto_update_user_nicename( $this->single_user_id );
		$user    = get_userdata( $this->single_user_id );
		$this->assertEquals( $this->single_user_id, $user->user_nicename );
	}

	/**
	 * Test for `ba_eas_auto_update_user_nicename_bulk()`.
	 *
	 * @since 1.1.0
	 *
	 * @covers ::ba_eas_auto_update_user_nicename_bulk
	 */
	public function test_ba_eas_auto_update_user_nicename_bulk() {
		$leo_id = $this->factory->user->create( array(
			'user_login' => 'leonardo',
			'user_pass'  => '1234',
			'user_email' => 'leonardo@example.com',
			'nickname'   => 'Leo',
			'first_name' => 'Leonardo',
			'last_name'  => 'Hamato',
		) );
		$raph_id = $this->factory->user->create( array(
			'user_login' => 'raphael',
			'user_pass'  => '1234',
			'user_email' => 'raphael@example.com',
			'nickname'   => 'Raph',
			'first_name' => 'Raphael',
			'last_name'  => 'Hamato',
		) );
		$donnie_id = $this->factory->user->create( array(
			'user_login' => 'donatello',
			'user_pass'  => '1234',
			'user_email' => 'donatello@example.com',
			'nickname'   => 'Donnie',
			'first_name' => 'Donatello',
			'last_name'  => 'Hamato',
		) );
		$mikey_id = $this->factory->user->create( array(
			'user_login' => 'michelangelo',
			'user_pass'  => '1234',
			'user_email' => 'michelangelo@example.com',
			'nickname'   => 'Mikey',
			'first_name' => 'Michelangelo',
			'last_name'  => 'Hamato',
		) );

		$_REQUEST = array(
			'_wpnonce' => wp_create_nonce( 'edit-author-slug-options' ),
		);

		ba_eas()->default_user_nicename = 'firstlast';

		ba_eas_auto_update_user_nicename_bulk( '1' );

		$leo = get_userdata( $leo_id );
		$this->assertEquals( 'leonardo-hamato', $leo->user_nicename );

		$raph = get_userdata( $raph_id );
		$this->assertEquals( 'raphael-hamato', $raph->user_nicename );

		$donnie = get_userdata( $donnie_id );
		$this->assertEquals( 'donatello-hamato', $donnie->user_nicename );

		$mikey = get_userdata( $mikey_id );
		$this->assertEquals( 'michelangelo-hamato', $mikey->user_nicename );

		ba_eas()->default_user_nicename = 'nickname';

		ba_eas_auto_update_user_nicename_bulk( '1' );

		$leo = get_userdata( $leo_id );
		$this->assertEquals( 'leo', $leo->user_nicename );

		$raph = get_userdata( $raph_id );
		$this->assertEquals( 'raph', $raph->user_nicename );

		$donnie = get_userdata( $donnie_id );
		$this->assertEquals( 'donnie', $donnie->user_nicename );

		$mikey = get_userdata( $mikey_id );
		$this->assertEquals( 'mikey', $mikey->user_nicename );

		ba_eas()->default_user_nicename = 'firstlast';

		ba_eas_auto_update_user_nicename_bulk();

		$leo = get_userdata( $leo_id );
		$this->assertEquals( 'leo', $leo->user_nicename );

		$raph = get_userdata( $raph_id );
		$this->assertEquals( 'raph', $raph->user_nicename );

		$donnie = get_userdata( $donnie_id );
		$this->assertEquals( 'donnie', $donnie->user_nicename );

		$mikey = get_userdata( $mikey_id );
		$this->assertEquals( 'mikey', $mikey->user_nicename );

		$_POST = array(
			'_ba_eas_bulk_update_structure' => 'firstlast',
		);

		ba_eas_auto_update_user_nicename_bulk( true );

		$leo = get_userdata( $leo_id );
		$this->assertEquals( 'leonardo-hamato', $leo->user_nicename );

		$raph = get_userdata( $raph_id );
		$this->assertEquals( 'raphael-hamato', $raph->user_nicename );

		$donnie = get_userdata( $donnie_id );
		$this->assertEquals( 'donatello-hamato', $donnie->user_nicename );

		$mikey = get_userdata( $mikey_id );
		$this->assertEquals( 'michelangelo-hamato', $mikey->user_nicename );

		$_POST = array(
			'_ba_eas_bulk_update_structure' => 'nickname',
		);

		add_filter( 'ba_eas_auto_update_user_nicename_bulk_user_ids', '__return_empty_array' );
		ba_eas_auto_update_user_nicename_bulk( true );
		remove_filter( 'ba_eas_auto_update_user_nicename_bulk_user_ids', '__return_empty_array' );

		$leo = get_userdata( $leo_id );
		$this->assertEquals( 'leonardo-hamato', $leo->user_nicename );

		$raph = get_userdata( $raph_id );
		$this->assertEquals( 'raphael-hamato', $raph->user_nicename );

		$donnie = get_userdata( $donnie_id );
		$this->assertEquals( 'donatello-hamato', $donnie->user_nicename );

		$mikey = get_userdata( $mikey_id );
		$this->assertEquals( 'michelangelo-hamato', $mikey->user_nicename );
	}

	/**
	 * Test for `ba_eas_auto_update_user_nicename_bulk()` when there is an
	 * invalid user id.
	 *
	 * @since 1.5.0
	 *
	 * @covers ::ba_eas_auto_update_user_nicename_bulk
	 */
	public function test_ba_eas_auto_update_user_nicename_bulk_with_bad_id() {

		$leo_id = $this->factory->user->create( array(
			'user_login' => 'leonardo',
			'user_pass'  => '1234',
			'user_email' => 'leonardo@example.com',
			'nickname'   => 'Leo',
			'first_name' => 'Leonardo',
			'last_name'  => 'Hamato',
		) );
		$raph_id = $this->factory->user->create( array(
			'user_login' => 'raphael',
			'user_pass'  => '1234',
			'user_email' => 'raphael@example.com',
			'nickname'   => 'Raph',
			'first_name' => 'Raphael',
			'last_name'  => 'Hamato',
		) );

		ba_eas()->default_user_nicename = 'firstlast';

		add_filter( 'ba_eas_auto_update_user_nicename_bulk_user_ids', array( $this, 'return_bad_id' ) );
		ba_eas_auto_update_user_nicename_bulk( true );
		remove_filter( 'ba_eas_auto_update_user_nicename_bulk_user_ids', array( $this, 'return_bad_id' ) );

		$leo = get_user_by( 'id', $leo_id );
		$this->assertEquals( 'leonardo-hamato', $leo->user_nicename );

		$raph = get_user_by( 'id', $raph_id );
		$this->assertEquals( 'raphael-hamato', $raph->user_nicename );
	}

	/**
	 * Return a non-existent user id.
	 *
	 * @since 1.5.0
	 *
	 * @param array $ids The array of user ids to update.
	 *
	 * @return array
	 */
	public function return_bad_id( $ids = array() ) {
		$ids = (array) $ids;
		$ids[] = '1234567890';
		return $ids;
	}

	/**
	 * Test for `ba_eas_sanitize_nicename()`.
	 *
	 * @since 1.1.0
	 *
	 * @covers ::ba_eas_sanitize_nicename
	 */
	public function test_ba_eas_sanitize_nicename() {
		$this->assertEquals( 'leonardo-hamato', ba_eas_sanitize_nicename( 'Leonardo Hamato' ) );
		$this->assertEquals( '', ba_eas_sanitize_nicename( '\ ' ) );
		$this->assertEquals( '', ba_eas_sanitize_nicename( '作者' ) );
		$this->assertEquals( '%e4%bd%9c%e8%80%85', ba_eas_sanitize_nicename( '作者', false ) );
	}

	/**
	 * Test for `ba_eas_sanitize_author_base()`.
	 *
	 * @since 1.1.0
	 *
	 * @covers ::ba_eas_sanitize_author_base
	 */
	public function test_ba_eas_sanitize_author_base() {

		// Test an empty author base.
		$this->assertEquals( 'author', ba_eas_sanitize_author_base( '' ) );

		// Test an author base with double forward slashes.
		$this->assertEquals( 'author/base', ba_eas_sanitize_author_base( 'author//base' ) );

		// Test a single word author base.
		$this->assertEquals( 'ninja', ba_eas_sanitize_author_base( 'ninja' ) );

		// Test that a santized, multi-part, author base with an invalid part,
		// doesn't contain double forward slashes.
		$this->assertEquals( 'author/base', ba_eas_sanitize_author_base( 'author/&^$()<>*@!/base' ) );

		// Test that the role-based tag is retained.
		$this->assertEquals( '%ba_eas_author_role%/base', ba_eas_sanitize_author_base( '%ba_eas_author_role%/base' ) );
	}

	/**
	 * Test for `ba_eas_esc_nicename()`.
	 *
	 * @since 1.1.0
	 *
	 * @covers ::ba_eas_esc_nicename
	 */
	public function test_ba_eas_esc_nicename() {
		$this->assertEquals( 'leonardo-hamato', ba_eas_esc_nicename( 'leonardo-hamato' ) );
		$this->assertEquals( 'leonardo_hamato', ba_eas_esc_nicename( 'leonardo_hamato' ) );

		$nicename = ba_eas_sanitize_nicename( '作者', false );
		$this->assertEquals( '作者', ba_eas_esc_nicename( $nicename ) );
	}

	/**
	 * Test for `ba_eas_trim_nicename()`.
	 *
	 * @since 1.1.0
	 *
	 * @covers ::ba_eas_trim_nicename
	 */
	public function test_ba_eas_trim_nicename() {
		$this->assertEquals( 'leonardo-hamato', ba_eas_trim_nicename( 'leonardo-hamato' ) );

		// A nicename over 50 characters.
		$this->assertEquals(
			'this-is-a-really-really-really-really-long-user-ni',
			ba_eas_trim_nicename( 'this-is-a-really-really-really-really-long-user-nicename' )
		);

		// A nicename over 50 characters that ends with a dash after trimming.
		$this->assertEquals(
			'this-is-a-really-really-really-really-looong-user',
			ba_eas_trim_nicename( 'this-is-a-really-really-really-really-looong-user-nicename' )
		);
	}

	/**
	 * Test for `ba_eas_nicename_is_ascii()`.
	 *
	 * @since 1.1.0
	 *
	 * @covers ::ba_eas_nicename_is_ascii
	 */
	public function test_ba_eas_nicename_is_ascii() {
		$this->assertTrue( ba_eas_nicename_is_ascii( 'leonardo-hamato' ) );
		$this->assertTrue( ba_eas_nicename_is_ascii( 'āēīōūǖĀĒĪŌŪǕ' ) );
		$this->assertFalse( ba_eas_nicename_is_ascii( '作者' ) );
	}

	/**
	 * Test for `ba_eas_get_nicename_by_structure()`.
	 *
	 * @since 1.4.0
	 *
	 * @covers ::ba_eas_get_nicename_by_structure
	 */
	public function test_ba_eas_get_nicename_by_structure() {

		$empty_user_id = ba_eas_get_nicename_by_structure();
		$this->assertEquals( '', $empty_user_id );

		$empty_structure = ba_eas_get_nicename_by_structure( $this->single_user_id );
		$this->assertEquals( '', $empty_structure );

		$username = ba_eas_get_nicename_by_structure( $this->single_user_id, 'username' );
		$this->assertEquals( 'mastersplinter', $username );

		$nickname = ba_eas_get_nicename_by_structure( $this->single_user_id, 'nickname' );
		$this->assertEquals( 'sensei', $nickname );

		$displayname = ba_eas_get_nicename_by_structure( $this->single_user_id, 'displayname' );
		$this->assertEquals( 'master-splinter', $displayname );

		$firstname = ba_eas_get_nicename_by_structure( $this->single_user_id, 'firstname' );
		$this->assertEquals( 'master', $firstname );

		$lastname = ba_eas_get_nicename_by_structure( $this->single_user_id, 'lastname' );
		$this->assertEquals( 'splinter', $lastname );

		$firstlast = ba_eas_get_nicename_by_structure( $this->single_user_id, 'firstlast' );
		$this->assertEquals( 'master-splinter', $firstlast );

		$lastfirst = ba_eas_get_nicename_by_structure( $this->single_user_id, 'lastfirst' );
		$this->assertEquals( 'splinter-master', $lastfirst );

		$userid = ba_eas_get_nicename_by_structure( $this->single_user_id, 'userid' );
		$this->assertEquals( $this->single_user_id, $userid );
	}

	/**
	 * Test `ba_eas_nicename_exists()` under normal circumstances.
	 *
	 * @since 1.5.0
	 *
	 * @covers ::ba_eas_nicename_exists
	 */
	public function test_ba_eas_nicename_exists() {

		$this->factory->user->create( array(
			'user_nicename' => 'leonardo-hamato',
		) );

		$exists = ba_eas_nicename_exists( 'leonardo-hamato', $this->single_user_id );
		$this->assertInstanceOf( 'WP_User', $exists );
	}

	/**
	 * Test `ba_eas_nicename_exists()` when the passed nicename doesn't exist.
	 *
	 * @since 1.5.0
	 *
	 * @covers ::ba_eas_nicename_exists
	 */
	public function test_ba_eas_nicename_exists_not_exists() {
		$this->assertFalse( ba_eas_nicename_exists( 'test' ) );
	}

	/**
	 * Test `ba_eas_nicename_exists()` when the nicename exists, but it belongs
	 * to the passed user.
	 *
	 * @since 1.5.0
	 *
	 * @covers ::ba_eas_nicename_exists
	 */
	public function test_ba_eas_nicename_exists_same_as_passed_user() {
		$this->assertFalse( ba_eas_nicename_exists( 'mastersplinter', $this->single_user_id ) );
	}

	/**
	 * Test for `ba_eas_wp_rewrite_overrides()`.
	 *
	 * @since 1.2.0
	 *
	 * @covers ::ba_eas_wp_rewrite_overrides
	 */
	public function test_ba_eas_wp_rewrite_overrides() {
		$this->assertEquals( $GLOBALS['wp_rewrite']->author_base, 'author' );

		ba_eas_wp_rewrite_overrides();
		$this->assertEquals( $GLOBALS['wp_rewrite']->author_base, 'author' );

		add_filter( 'ba_eas_do_role_based_author_base', '__return_true' );
		ba_eas_wp_rewrite_overrides();
		$this->assertEquals( $GLOBALS['wp_rewrite']->author_base, '%ba_eas_author_role%' );
		remove_filter( 'ba_eas_do_role_based_author_base', '__return_true' );

		ba_eas()->author_base = 'ninja';
		ba_eas_wp_rewrite_overrides();
		$this->assertEquals( $GLOBALS['wp_rewrite']->author_base, 'ninja' );

		$this->set_permalink_structure( '/archives/%post_id%/' );
		$GLOBALS['wp_rewrite']->get_author_permastruct();
		$this->assertEquals( '/archives/ninja/%author%', $GLOBALS['wp_rewrite']->author_structure );

		ba_eas()->remove_front = true;
		ba_eas_wp_rewrite_overrides();
		$this->assertEquals( '/ninja/%author%', $GLOBALS['wp_rewrite']->author_structure );

		ba_eas()->author_base = 'author';
		$this->set_permalink_structure( '/archives/%post_id%/' );
		ba_eas_wp_rewrite_overrides();
		$this->assertEquals( '/author/%author%', $GLOBALS['wp_rewrite']->author_structure );
	}

	/**
	 * Test for `ba_eas_remove_front()`.
	 *
	 * @since 1.2.0
	 *
	 * @covers ::ba_eas_remove_front
	 */
	public function test_ba_eas_remove_front() {
		$this->assertFalse( ba_eas_remove_front() );

		add_filter( 'ba_eas_has_front', '__return_true' );
		ba_eas()->remove_front = true;
		$this->assertTrue( ba_eas_remove_front() );
		remove_filter( 'ba_eas_has_front', '__return_true' );
	}

	/**
	 * Test for `ba_eas_has_front()`.
	 *
	 * @since 1.2.0
	 *
	 * @covers ::ba_eas_has_front
	 */
	public function test_ba_eas_has_front() {
		$this->assertFalse( ba_eas_has_front() );

		$GLOBALS['wp_rewrite']->front = '/test/';
		$this->assertTrue( ba_eas_has_front() );
	}

	/**
	 * Test for `ba_eas_do_role_based_author_base()`.
	 *
	 * @since 1.1.0
	 *
	 * @covers ::ba_eas_do_role_based_author_base
	 */
	public function test_ba_eas_do_role_based_author_base() {

		// False tests.
		$this->assertFalse( ba_eas_do_role_based_author_base() );

		// True tests.
		ba_eas()->do_role_based = true;
		$this->assertTrue( ba_eas_do_role_based_author_base() );
	}

	/**
	 * Test for `ba_eas_author_link()`.
	 *
	 * @since 1.1.0
	 *
	 * @covers ::ba_eas_author_link
	 */
	public function test_ba_eas_author_link() {
		$author_link            = 'http://example.com/author/mastersplinter/';
		$role_based_author_link = 'http://example.com/%ba_eas_author_role%/mastersplinter/';
		$author_link_author     = 'http://example.com/author/mastersplinter/';
		$author_link_ninja      = 'http://example.com/ninja/mastersplinter/';
		$author_link_subscriber = 'http://example.com/subscriber/mastersplinter/';

		// Test role-based author base disabled.
		$link = ba_eas_author_link( $author_link, $this->single_user_id );
		$this->assertEquals( $author_link, $link );

		ba_eas()->do_role_based = true;

		// Test role-based author based enabled, but no EAS author base.
		$link = ba_eas_author_link( $author_link, $this->single_user_id );
		$this->assertEquals( $author_link, $link );

		// Test role-based author based enabled, user is subscriber.
		$link = ba_eas_author_link( $role_based_author_link, $this->single_user_id );
		$this->assertEquals( $author_link_subscriber, $link );

		// Test role-based author based enabled, role slug doesn't exist.
		ba_eas()->role_slugs = array();
		$link = ba_eas_author_link( $role_based_author_link, $this->single_user_id );
		$this->assertEquals( $author_link_author, $link );

		// Test role-based author based enabled, role slug doesn't exist, custom author base.
		ba_eas()->author_base = 'ninja';
		$link = ba_eas_author_link( $role_based_author_link, $this->single_user_id );
		$this->assertEquals( $author_link_ninja, $link );

		ba_eas()->remove_front = true;

		$this->set_permalink_structure( '/archives/%post_id%/' );
		$link = ba_eas_author_link( 'http://example.com/archives/author/mastersplinter/', $this->single_user_id );
		$this->assertEquals( 'http://example.com/author/mastersplinter/', $link );
	}

	/**
	 * Test for `ba_eas_template_include()`.
	 *
	 * @since 1.1.0
	 *
	 * @covers ::ba_eas_template_include
	 */
	public function test_ba_eas_template_include() {

		$this->assertEquals( 'no-role-based', ba_eas_template_include( 'no-role-based' ) );

		ba_eas()->do_role_based = true;

		$this->assertEquals( 'no-WP_User', ba_eas_template_include( 'no-WP_User' ) );

		$GLOBALS['wp_query']->queried_object = get_userdata( $this->single_user_id );
		$this->assertEquals( 'author-mastersplinter.php', ba_eas_template_include( 'author-mastersplinter.php' ) );
		$this->assertEquals( "author-{$this->single_user_id}.php", ba_eas_template_include( "author-{$this->single_user_id}.php" ) );

		$role_template         = TEMPLATEPATH . '/author-subscriber.php';
		$role_slug_template    = TEMPLATEPATH . '/author-deshi.php';
		ba_eas()->role_slugs = ba_eas_tests_slugs_custom();

		file_put_contents( $role_template, '<?php' );
		$this->assertEquals( $role_template, ba_eas_template_include( 'author-subscriber.php' ) );
		@unlink( $role_template );

		/*
		 * Creating and loading both files fails. Individually they work, but
		 * for some reason you can't test for both instances. Need to investigate.
		 */
		/*
		file_put_contents( $role_slug_template, '<?php' );
		$this->assertEquals( $role_slug_template, ba_eas_template_include( 'author-deshi.php' ) );
		@unlink( $role_slug_template );
		*/

		$GLOBALS['wp_query']->queried_object = null;
	}

	/**
	 * Test for `ba_eas_flush_rewrite_rules()`.
	 *
	 * @since 1.1.0
	 *
	 * @covers ::ba_eas_flush_rewrite_rules
	 */
	public function test_ba_eas_flush_rewrite_rules() {
		update_option( 'rewrite_rules', 'test' );
		$this->assertEquals( 'test', get_option( 'rewrite_rules' ) );

		ba_eas_flush_rewrite_rules();
		$this->assertEmpty( get_option( 'rewrite_rules' ) );
	}

	/**
	 * Test for `ba_eas_author_rewrite_rules()`.
	 *
	 * @since 1.1.0
	 *
	 * @covers ::ba_eas_author_rewrite_rules
	 */
	public function test_ba_eas_author_rewrite_rules() {

		$test = array(
			'with_name_1'    => 'index.php?ba_eas_author_role=$matches[1]&author_name=$matches[2]&feed=$matches[3]',
			'without_name_1' => 'index.php?ba_eas_author_role=$matches[1]&feed=$matches[2]',
			'with_name_2'    => 'index.php?ba_eas_author_role=$matches[1]&author_name=$matches[2]',
			'with_name_3'    => 'index.php?ba_eas_author_role=$matches[1]&author_name=$matches[2]&paged=$matches[3]',
			'without_name_2' => 'index.php?ba_eas_author_role=$matches[1]&paged=$matches[2]',
		);

		$expected = array(
			'with_name_1'    => 'index.php?ba_eas_author_role=$matches[1]&author_name=$matches[2]&feed=$matches[3]',
			'with_name_2'    => 'index.php?ba_eas_author_role=$matches[1]&author_name=$matches[2]',
			'with_name_3'    => 'index.php?ba_eas_author_role=$matches[1]&author_name=$matches[2]&paged=$matches[3]',
		);

		$this->assertEquals( $test, ba_eas_author_rewrite_rules( $test ) );

		ba_eas()->do_role_based = true;
		$this->assertEquals( $expected, ba_eas_author_rewrite_rules( $test ) );
	}

	/**
	 * Test for `ba_eas_get_user_role()`.
	 *
	 * @since 1.2.0
	 *
	 * @covers ::ba_eas_get_user_role
	 */
	public function test_ba_eas_get_user_role() {

		// Passed roles array.
		$role = ba_eas_get_user_role( array( 'administrator', 'foot-soldier' ), $this->single_user_id );
		$this->assertEquals( 'administrator', $role );

		// No passed roles array.
		$role = ba_eas_get_user_role( array(), $this->single_user_id );
		$this->assertEquals( 'subscriber', $role );

		// No passed roles array.
		add_filter( 'ba_eas_get_user_role', array( $this, 'user_role_filter' ) );
		$role = ba_eas_get_user_role( array(), $this->single_user_id );
		$this->assertEquals( 'test', $role );
		remove_filter( 'ba_eas_get_user_role', array( $this, 'user_role_filter' ) );
	}

	/**
	 * Test for `ba_eas_get_roles()`.
	 *
	 * @since 1.2.0
	 *
	 * @covers ::ba_eas_get_roles
	 */
	public function test_ba_eas_get_roles() {
		$this->assertEquals( ba_eas_tests_roles_default(), ba_eas_get_roles() );
	}

	/**
	 * Test for `ba_eas_get_default_role_slugs()`.
	 *
	 * @since 1.1.0
	 *
	 * @covers ::ba_eas_get_default_role_slugs
	 */
	public function test_ba_eas_get_default_role_slugs() {
		$this->assertEquals( ba_eas_tests_slugs_default(), ba_eas_get_default_role_slugs() );
	}

	/**
	 * Test for `array_replace_recursive()`.
	 *
	 * @since 1.1.0
	 *
	 * @covers ::array_replace_recursive
	 */
	public function test_array_replace_recursive() {
		$base = array(
			'test-1' => array(
				'test-1a' => 'test-1a',
			),
			'test-2' => 'test-2',
		);

		$replacements = array(
			'test-1' => array(
				'test-1a' => 'new-test-1a',
				'test-1b' => 'test-1b',
			),
			'test-3' => 'test-3',
		);

		$expected = array(
			'test-1' => array(
				'test-1a' => 'new-test-1a',
				'test-1b' => 'test-1b',
			),
			'test-2' => 'test-2',
			'test-3' => 'test-3',
		);

		$this->assertEquals( $expected, array_replace_recursive( $base, $replacements ) );
	}
}
