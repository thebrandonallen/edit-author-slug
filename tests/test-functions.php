<?php

class BA_EAS_Tests_Functions extends WP_UnitTestCase {

	private $single_user_id = null;

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

	public function tearDown() {
		parent::tearDown();

		$this->eas->author_base = 'author';
		$this->eas->role_slugs  = ba_eas_tests_slugs_default();
	}

	/**
	 * @covers ::ba_eas_do_auto_update
	 */
	function test_ba_eas_do_auto_update() {

		// True tests
		add_filter( 'ba_eas_do_auto_update', '__return_true' );
		$this->assertTrue( ba_eas_do_auto_update() );
		remove_filter( 'ba_eas_do_auto_update', '__return_true', 10 );

		// False tests
		add_filter( 'ba_eas_do_auto_update', '__return_false' );
		$this->assertFalse( ba_eas_do_auto_update() );
		remove_filter( 'ba_eas_do_auto_update', '__return_false', 10 );
	}

	/**
	 * @covers ::ba_eas_auto_update_user_nicename
	 */
	function test_ba_eas_auto_update_user_nicename() {
		// No user id
		$this->assertFalse( ba_eas_auto_update_user_nicename( false ) );

		// No auto update
		add_filter( 'ba_eas_do_auto_update', '__return_false' );
		$this->assertFalse( ba_eas_auto_update_user_nicename( 1 ) );
		remove_filter( 'ba_eas_do_auto_update', '__return_false', 10 );

		// We need below tests to think auto update is on
		add_filter( 'ba_eas_do_auto_update', '__return_true' );

		// Invalid user
		$this->assertFalse( ba_eas_auto_update_user_nicename( 1337 ) );

		// Update using username
		$this->eas->default_user_nicename = '';
		$user_id = ba_eas_auto_update_user_nicename( $this->single_user_id );
		$user    = get_userdata( $this->single_user_id );
		$this->assertEquals( 'mastersplinter', $user->user_nicename );

		// Update using username
		$this->eas->default_user_nicename = 'username';
		$user_id = ba_eas_auto_update_user_nicename( $this->single_user_id );
		$user    = get_userdata( $this->single_user_id );
		$this->assertEquals( 'mastersplinter', $user->user_nicename );

		// Update using nickname
		$this->eas->default_user_nicename = 'nickname';
		$user_id = ba_eas_auto_update_user_nicename( $this->single_user_id );
		$user    = get_userdata( $this->single_user_id );
		$this->assertEquals( 'sensei', $user->user_nicename );

		// Update using displayname
		$this->eas->default_user_nicename = 'displayname';
		$user_id = ba_eas_auto_update_user_nicename( $this->single_user_id );
		$user    = get_userdata( $this->single_user_id );
		$this->assertEquals( 'master-splinter', $user->user_nicename );

		// Update using firstname
		$this->eas->default_user_nicename = 'firstname';
		$user_id = ba_eas_auto_update_user_nicename( $this->single_user_id );
		$user    = get_userdata( $this->single_user_id );
		$this->assertEquals( 'master', $user->user_nicename );

		// Update using lastname
		$this->eas->default_user_nicename = 'lastname';
		$user_id = ba_eas_auto_update_user_nicename( $this->single_user_id );
		$user    = get_userdata( $this->single_user_id );
		$this->assertEquals( 'splinter', $user->user_nicename );

		// Update using firstlast
		$this->eas->default_user_nicename = 'firstlast';
		$user_id = ba_eas_auto_update_user_nicename( $this->single_user_id );
		$user    = get_userdata( $this->single_user_id );
		$this->assertEquals( 'master-splinter', $user->user_nicename );

		// Update using lastfirst
		$this->eas->default_user_nicename = 'lastfirst';
		$user_id = ba_eas_auto_update_user_nicename( $this->single_user_id );
		$user    = get_userdata( $this->single_user_id );
		$this->assertEquals( 'splinter-master', $user->user_nicename );

		// Update using random string as structure, shouldn't update, so
		// user_nicename should be same as previous test ('splinter-master')
		$this->eas->default_user_nicename = 'Cowabunga Dude!';
		$user_id = ba_eas_auto_update_user_nicename( $this->single_user_id );
		$user    = get_userdata( $this->single_user_id );
		$this->assertEquals( 'splinter-master', $user->user_nicename );

		remove_filter( 'ba_eas_do_auto_update', '__return_true', 10 );
	}

	/**
	 * @covers ::ba_eas_auto_update_user_nicename_single
	 */
	function test_ba_eas_auto_update_user_nicename_single() {
		$this->markTestIncomplete();
	}

	/**
	 * @covers ::ba_eas_auto_update_user_nicename_bulk
	 */
	function test_ba_eas_auto_update_user_nicename_bulk() {
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

		$_POST = array( '_ba_eas_bulk_update_structure' => 'firstlast' );

		ba_eas_auto_update_user_nicename_bulk( true );

		$leo = get_userdata( $leo_id );
		$this->assertEquals( 'leonardo-hamato', $leo->user_nicename );

		$raph = get_userdata( $raph_id );
		$this->assertEquals( 'raphael-hamato', $raph->user_nicename );

		$donnie = get_userdata( $donnie_id );
		$this->assertEquals( 'donatello-hamato', $donnie->user_nicename );

		$mikey = get_userdata( $mikey_id );
		$this->assertEquals( 'michelangelo-hamato', $mikey->user_nicename );

		$_POST = array( '_ba_eas_bulk_update_structure' => 'nickname' );

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
	 * @covers ::ba_eas_sanitize_nicename
	 */
	function test_ba_eas_sanitize_nicename() {
		$this->assertEquals( 'leonardo-hamato', ba_eas_sanitize_nicename( 'Leonardo Hamato' ) );
		$this->assertEquals( '', ba_eas_sanitize_nicename( '\ ' ) );
		$this->assertEquals( '', ba_eas_sanitize_nicename( '作者' ) );
		$this->assertEquals( '%e4%bd%9c%e8%80%85', ba_eas_sanitize_nicename( '作者', false ) );
	}

	/**
	 * @covers ::ba_eas_sanitize_author_base
	 */
	function test_ba_eas_sanitize_author_base() {

		$this->assertEquals( 'author', ba_eas_sanitize_author_base( '' ) );

		$this->assertEquals( 'author/base', ba_eas_sanitize_author_base( 'author//base' ) );

		$this->assertEquals( 'ninja', ba_eas_sanitize_author_base( 'ninja' ) );
	}

	/**
	 * @covers ::ba_eas_esc_nicename
	 */
	function test_ba_eas_esc_nicename() {
		$this->assertEquals( 'leonardo-hamato', ba_eas_esc_nicename( 'leonardo-hamato' ) );
		$this->assertEquals( 'leonardo_hamato', ba_eas_esc_nicename( 'leonardo_hamato' ) );

		$nicename = ba_eas_sanitize_nicename( '作者', false );
		$this->assertEquals( '作者', ba_eas_esc_nicename( $nicename ) );
	}

	/**
	 * @covers ::ba_eas_trim_nicename
	 */
	function test_ba_eas_trim_nicename() {
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
	 * @covers ::ba_eas_nicename_is_ascii
	 */
	function test_ba_eas_nicename_is_ascii() {
		$this->assertTrue( ba_eas_nicename_is_ascii( 'leonardo-hamato' ) );
		$this->assertTrue( ba_eas_nicename_is_ascii( 'āēīōūǖĀĒĪŌŪǕ' ) );
		$this->assertFalse( ba_eas_nicename_is_ascii( '作者' ) );
	}

	/**
	 * @covers ::ba_eas_do_role_based_author_base
	 */
	function test_ba_eas_do_role_based_author_base() {

		// True tests
		add_filter( 'ba_eas_do_role_based_author_base', '__return_true' );
		$this->assertTrue( ba_eas_do_role_based_author_base() );
		remove_filter( 'ba_eas_do_role_based_author_base', '__return_true', 10 );

		// False tests
		add_filter( 'ba_eas_do_role_based_author_base', '__return_false' );
		$this->assertFalse( ba_eas_do_role_based_author_base() );
		remove_filter( 'ba_eas_do_role_based_author_base', '__return_false', 10 );
	}

	/**
	 * @covers ::ba_eas_author_link
	 */
	function test_ba_eas_author_link() {
		$author_link            = 'http://example.com/author/mastersplinter/';
		$role_based_author_link = 'http://example.com/%ba_eas_author_role%/mastersplinter/';
		$author_link_author     = 'http://example.com/author/mastersplinter/';
		$author_link_ninja      = 'http://example.com/ninja/mastersplinter/';
		$author_link_subscriber = 'http://example.com/subscriber/mastersplinter/';

		add_filter( 'ba_eas_do_role_based_author_base', '__return_false' );

		// Test role-based author base disabled
		$link = ba_eas_author_link( $author_link, $this->single_user_id );
		$this->assertEquals( $author_link, $link );

		remove_filter( 'ba_eas_do_role_based_author_base', '__return_false', 10 );

		add_filter( 'ba_eas_do_role_based_author_base', '__return_true' );

		// Test role-based author based enabled, but no EAS author base
		$link = ba_eas_author_link( $author_link, $this->single_user_id );
		$this->assertEquals( $author_link, $link );

		// Test role-based author based enabled, user is subscriber
		$link = ba_eas_author_link( $role_based_author_link, $this->single_user_id );
		$this->assertEquals( $author_link_subscriber, $link );

		// Test role-based author based enabled, role slug doesn't exist
		$this->eas->role_slugs = array();
		$link = ba_eas_author_link( $role_based_author_link, $this->single_user_id );
		$this->assertEquals( $author_link_author, $link );

		// Test role-based author based enabled, role slug doesn't exist, custom author base
		$this->eas->author_base = 'ninja';
		$link = ba_eas_author_link( $role_based_author_link, $this->single_user_id );
		$this->assertEquals( $author_link_ninja, $link );

		remove_filter( 'ba_eas_do_role_based_author_base', '__return_true', 10 );
	}

	/**
	 * @covers ::ba_eas_template_include
	 */
	function test_ba_eas_template_include() {

		add_filter( 'ba_eas_do_role_based_author_base', '__return_false' );
		$this->assertEquals( 'no-role-based', ba_eas_template_include( 'no-role-based' ) );
		remove_filter( 'ba_eas_do_role_based_author_base', '__return_false', 10 );

		add_filter( 'ba_eas_do_role_based_author_base', '__return_true' );

		$this->assertEquals( 'no-WP_User', ba_eas_template_include( 'no-WP_User' ) );

		$GLOBALS['wp_query']->queried_object = get_userdata( $this->single_user_id );
		$this->assertEquals( 'author-mastersplinter.php', ba_eas_template_include( 'author-mastersplinter.php' ) );
		$this->assertEquals( "author-{$this->single_user_id}.php", ba_eas_template_include( "author-{$this->single_user_id}.php" ) );

		$role_template         = TEMPLATEPATH . '/' . 'author-subscriber.php';
		$role_slug_template    = TEMPLATEPATH . '/' . 'author-deshi.php';
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

		remove_filter( 'ba_eas_do_role_based_author_base', '__return_true', 10 );
	}

	/**
	 * @covers ::ba_eas_flush_rewrite_rules
	 */
	function test_ba_eas_flush_rewrite_rules() {
		update_option( 'rewrite_rules', 'test' );
		$this->assertEquals( 'test', get_option( 'rewrite_rules' ) );

		ba_eas_flush_rewrite_rules();
		$this->assertFalse( get_option( 'rewrite_rules' ) );
	}

	/**
	 * @covers ::ba_eas_author_rewrite_rules
	 */
	function test_ba_eas_author_rewrite_rules() {

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

		add_filter( 'ba_eas_do_role_based_author_base', '__return_false' );
		$this->assertEquals( $test, ba_eas_author_rewrite_rules( $test ) );
		remove_filter( 'ba_eas_do_role_based_author_base', '__return_false', 10 );

		add_filter( 'ba_eas_do_role_based_author_base', '__return_true' );
		$this->assertEquals( $expected, ba_eas_author_rewrite_rules( $test ) );
		remove_filter( 'ba_eas_do_role_based_author_base', '__return_true', 10 );
	}

	/**
	 * @covers ::ba_eas_get_wp_roles
	 */
	function test_ba_eas_get_wp_roles() {
		$this->markTestIncomplete();
	}

	/**
	 * @covers ::ba_eas_get_roles
	 */
	function test_ba_eas_get_roles() {
		$this->markTestIncomplete();
	}

	/**
	 * @covers ::ba_eas_get_editable_roles
	 */
	function test_ba_eas_get_editable_roles() {

		// Test with empty $wp_roles global
		global $wp_roles;
		unset( $wp_roles );
		$this->assertEquals( ba_eas_tests_roles_default(), ba_eas_get_editable_roles() );

		// Test default WP roles
		$this->assertEquals( ba_eas_tests_roles_default(), ba_eas_get_editable_roles() );

		// Test with extra role
		add_filter( 'editable_roles', 'ba_eas_tests_roles_extra' );
		$this->assertEquals( ba_eas_tests_roles_extra(), ba_eas_get_editable_roles() );
		remove_filter( 'editable_roles', 'ba_eas_tests_roles_extra', 10 );
	}

	/**
	 * @covers ::ba_eas_get_default_role_slugs
	 */
	function test_ba_eas_get_default_role_slugs() {

		// Test with empty $wp_roles global
		global $wp_roles;
		unset( $wp_roles );
		$this->assertEquals( ba_eas_tests_slugs_default(), ba_eas_get_default_role_slugs() );

		// Test default WP roles
		$this->assertEquals( ba_eas_tests_slugs_default(), ba_eas_get_default_role_slugs() );
	}

	/**
	 * @covers ::array_replace_recursive
	 */
	function test_array_replace_recursive() {
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
	 * @covers ::ba_eas_update_nicename_cache
	 *
	 * @expectedIncorrectUsage ba_eas_update_nicename_cache
	 */
	function test_ba_eas_update_nicename_cache() {

		$this->assertEquals( $this->single_user_id, wp_cache_get( 'mastersplinter', 'userslugs' ) );

		$this->assertNull( ba_eas_update_nicename_cache( null ) );

		$user = get_userdata( $this->single_user_id );
		wp_update_user( array( 'ID' => $this->single_user_id, 'user_nicename' => 'master-splinter' ) );
		ba_eas_update_nicename_cache( $this->single_user_id, $user );
		$this->assertNotEquals( $this->single_user_id, wp_cache_get( 'mastersplinter', 'userslugs' ) );
		$this->assertEquals( $this->single_user_id, wp_cache_get( 'master-splinter', 'userslugs' ) );

		$user = get_userdata( $this->single_user_id );
		wp_update_user( array( 'ID' => $this->single_user_id, 'user_nicename' => 'mastersplinter' ) );
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
