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
	 * Copy of WP's function from 4.4.0. Can be removed when 4.4.0 is the
	 * minimum version.
	 *
	 * @since 1.2.0
	 *
	 * @todo Remove.
	 *
	 * @global WP_Rewrite $wp_rewrite
	 *
	 * @param string $structure Optional. Permalink structure to set. Default empty.
	 */
	public function set_permalink_structure( $structure = '' ) {
		global $wp_rewrite;

		$wp_rewrite->init();
		$wp_rewrite->set_permalink_structure( $structure );
		$wp_rewrite->flush_rules();
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
	 * @covers ::ba_eas_do_auto_update
	 */
	public function test_ba_eas_do_auto_update() {

		// False test.
		$this->assertFalse( ba_eas_do_auto_update() );

		// True test.
		$this->eas->do_auto_update = true;
		$this->assertTrue( ba_eas_do_auto_update() );
	}

	/**
	 * Test for `ba_eas_do_bulk_update()`.
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
	 * @covers ::ba_eas_auto_update_user_nicename
	 */
	public function test_ba_eas_auto_update_user_nicename() {
		// No user id.
		$this->assertFalse( ba_eas_auto_update_user_nicename() );

		// No auto update.
		$this->assertFalse( ba_eas_auto_update_user_nicename( 1 ) );

		// We need below tests to think auto update is on.
		$this->eas->do_auto_update = true;

		// Invalid user.
		$this->assertFalse( ba_eas_auto_update_user_nicename( 1337 ) );

		// Update using username.
		$this->eas->default_user_nicename = '';
		$user_id = ba_eas_auto_update_user_nicename( $this->single_user_id );
		$user    = get_userdata( $this->single_user_id );
		$this->assertEquals( 'mastersplinter', $user->user_nicename );

		// Update using username.
		$this->eas->default_user_nicename = 'username';
		$user_id = ba_eas_auto_update_user_nicename( $this->single_user_id );
		$user    = get_userdata( $this->single_user_id );
		$this->assertEquals( 'mastersplinter', $user->user_nicename );

		// Update using nickname.
		$this->eas->default_user_nicename = 'nickname';
		$user_id = ba_eas_auto_update_user_nicename( $this->single_user_id );
		$user    = get_userdata( $this->single_user_id );
		$this->assertEquals( 'sensei', $user->user_nicename );

		// Update using displayname.
		$this->eas->default_user_nicename = 'displayname';
		$user_id = ba_eas_auto_update_user_nicename( $this->single_user_id );
		$user    = get_userdata( $this->single_user_id );
		$this->assertEquals( 'master-splinter', $user->user_nicename );

		// Update using firstname.
		$this->eas->default_user_nicename = 'firstname';
		$user_id = ba_eas_auto_update_user_nicename( $this->single_user_id );
		$user    = get_userdata( $this->single_user_id );
		$this->assertEquals( 'master', $user->user_nicename );

		// Update using lastname.
		$this->eas->default_user_nicename = 'lastname';
		$user_id = ba_eas_auto_update_user_nicename( $this->single_user_id );
		$user    = get_userdata( $this->single_user_id );
		$this->assertEquals( 'splinter', $user->user_nicename );

		// Update using firstlast.
		$this->eas->default_user_nicename = 'firstlast';
		$user_id = ba_eas_auto_update_user_nicename( $this->single_user_id );
		$user    = get_userdata( $this->single_user_id );
		$this->assertEquals( 'master-splinter', $user->user_nicename );

		// Update using lastfirst.
		$this->eas->default_user_nicename = 'lastfirst';
		$user_id = ba_eas_auto_update_user_nicename( $this->single_user_id );
		$user    = get_userdata( $this->single_user_id );
		$this->assertEquals( 'splinter-master', $user->user_nicename );

		// Update using lastfirst.
		$this->eas->default_user_nicename = 'userid';
		$user_id = ba_eas_auto_update_user_nicename( $this->single_user_id );
		$user    = get_userdata( $this->single_user_id );
		$this->assertEquals( $this->single_user_id, $user->user_nicename );

		// Update using random string as structure, shouldn't update, so
		// user_nicename should be same as previous test ('splinter-master').
		$this->eas->default_user_nicename = 'Cowabunga Dude!';
		$user_id = ba_eas_auto_update_user_nicename( $this->single_user_id );
		$user    = get_userdata( $this->single_user_id );
		$this->assertEquals( $this->single_user_id, $user->user_nicename );
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
	 * Test for `ba_eas_auto_update_user_nicename_bulk()`.
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

		$this->eas->default_user_nicename = 'firstlast';

		ba_eas_auto_update_user_nicename_bulk( '1' );

		$leo = get_userdata( $leo_id );
		$this->assertEquals( 'leonardo-hamato', $leo->user_nicename );

		$raph = get_userdata( $raph_id );
		$this->assertEquals( 'raphael-hamato', $raph->user_nicename );

		$donnie = get_userdata( $donnie_id );
		$this->assertEquals( 'donatello-hamato', $donnie->user_nicename );

		$mikey = get_userdata( $mikey_id );
		$this->assertEquals( 'michelangelo-hamato', $mikey->user_nicename );

		$this->eas->default_user_nicename = 'nickname';

		ba_eas_auto_update_user_nicename_bulk( '1' );

		$leo = get_userdata( $leo_id );
		$this->assertEquals( 'leo', $leo->user_nicename );

		$raph = get_userdata( $raph_id );
		$this->assertEquals( 'raph', $raph->user_nicename );

		$donnie = get_userdata( $donnie_id );
		$this->assertEquals( 'donnie', $donnie->user_nicename );

		$mikey = get_userdata( $mikey_id );
		$this->assertEquals( 'mikey', $mikey->user_nicename );

		$this->eas->default_user_nicename = 'firstlast';

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
	 * Test for `ba_eas_sanitize_nicename()`.
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
	 * Test for `ba_eas_wp_rewrite_overrides()`.
	 *
	 * @covers ::ba_eas_wp_rewrite_overrides
	 */
	public function test_ba_eas_wp_rewrite_overrides() {
		$this->assertEquals( $GLOBALS['wp_rewrite']->author_base, 'author' );

		$this->eas->do_role_based = true;
		ba_eas_wp_rewrite_overrides();
		$this->assertEquals( $GLOBALS['wp_rewrite']->author_base, '%ba_eas_author_role%' );
		$this->eas->do_role_based = false;

		$this->eas->author_base = 'ninja';
		ba_eas_wp_rewrite_overrides();
		$this->assertEquals( $GLOBALS['wp_rewrite']->author_base, 'ninja' );

		$this->set_permalink_structure( '/archives/%post_id%/' );
		$GLOBALS['wp_rewrite']->get_author_permastruct();
		$this->assertEquals( '/archives/ninja/%author%', $GLOBALS['wp_rewrite']->author_structure );

		$this->eas->remove_front = true;
		ba_eas_wp_rewrite_overrides();
		$this->assertEquals( '/ninja/%author%', $GLOBALS['wp_rewrite']->author_structure );
	}

	/**
	 * Test for `ba_eas_remove_front()`.
	 *
	 * @covers ::ba_eas_remove_front
	 */
	public function test_ba_eas_remove_front() {
		$this->assertFalse( ba_eas_remove_front() );

		$this->eas->remove_front = true;
		$this->assertTrue( ba_eas_remove_front() );
	}

	/**
	 * Test for `ba_eas_has_front()`.
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
	 * @covers ::ba_eas_do_role_based_author_base
	 */
	public function test_ba_eas_do_role_based_author_base() {

		// False tests.
		$this->assertFalse( ba_eas_do_role_based_author_base() );

		// True tests.
		$this->eas->do_role_based = true;
		$this->assertTrue( ba_eas_do_role_based_author_base() );
	}

	/**
	 * Test for `ba_eas_author_link()`.
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

		$this->eas->do_role_based = true;

		// Test role-based author based enabled, but no EAS author base.
		$link = ba_eas_author_link( $author_link, $this->single_user_id );
		$this->assertEquals( $author_link, $link );

		// Test role-based author based enabled, user is subscriber.
		$link = ba_eas_author_link( $role_based_author_link, $this->single_user_id );
		$this->assertEquals( $author_link_subscriber, $link );

		// Test role-based author based enabled, role slug doesn't exist.
		$this->eas->role_slugs = array();
		$link = ba_eas_author_link( $role_based_author_link, $this->single_user_id );
		$this->assertEquals( $author_link_author, $link );

		// Test role-based author based enabled, role slug doesn't exist, custom author base.
		$this->eas->author_base = 'ninja';
		$link = ba_eas_author_link( $role_based_author_link, $this->single_user_id );
		$this->assertEquals( $author_link_ninja, $link );

		$this->eas->remove_front = true;

		$this->set_permalink_structure( '/archives/%post_id%/' );
		$link = ba_eas_author_link( 'http://example.com/archives/author/mastersplinter/', $this->single_user_id );
		$this->assertEquals( 'http://example.com/author/mastersplinter/', $link );
	}

	/**
	 * Test for `ba_eas_template_include()`.
	 *
	 * @covers ::ba_eas_template_include
	 */
	public function test_ba_eas_template_include() {

		$this->assertEquals( 'no-role-based', ba_eas_template_include( 'no-role-based' ) );

		$this->eas->do_role_based = true;

		$this->assertEquals( 'no-WP_User', ba_eas_template_include( 'no-WP_User' ) );

		$GLOBALS['wp_query']->queried_object = get_userdata( $this->single_user_id );
		$this->assertEquals( 'author-mastersplinter.php', ba_eas_template_include( 'author-mastersplinter.php' ) );
		$this->assertEquals( "author-{$this->single_user_id}.php", ba_eas_template_include( "author-{$this->single_user_id}.php" ) );

		$role_template         = TEMPLATEPATH . '/author-subscriber.php';
		$role_slug_template    = TEMPLATEPATH . '/author-deshi.php';
		$this->eas->role_slugs = ba_eas_tests_slugs_custom();

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

		$this->eas->do_role_based = true;
		$this->assertEquals( $expected, ba_eas_author_rewrite_rules( $test ) );
	}

	/**
	 * Test for `ba_eas_get_user_role()`.
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
	 * Test for `ba_eas_get_wp_roles()`.
	 *
	 * @covers ::ba_eas_get_wp_roles
	 * @expectedDeprecated ba_eas_get_wp_roles
	 */
	public function test_ba_eas_get_wp_roles() {
		$this->assertInstanceOf( 'WP_Roles', ba_eas_get_wp_roles() );
	}

	/**
	 * Test for `ba_eas_get_roles()`.
	 *
	 * @covers ::ba_eas_get_roles
	 */
	public function test_ba_eas_get_roles() {
		$this->assertEquals( ba_eas_tests_roles_default(), ba_eas_get_roles() );
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
	 * Test for `ba_eas_get_default_role_slugs()`.
	 *
	 * @covers ::ba_eas_get_default_role_slugs
	 */
	public function test_ba_eas_get_default_role_slugs() {

		// Test with empty $wp_roles global.
		global $wp_roles;
		unset( $wp_roles );
		$this->assertEquals( ba_eas_tests_slugs_default(), ba_eas_get_default_role_slugs() );

		// Test default WP roles.
		$this->assertEquals( ba_eas_tests_slugs_default(), ba_eas_get_default_role_slugs() );
	}

	/**
	 * Test for `array_replace_recursive()`.
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
