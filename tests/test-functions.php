<?php

class BA_EAS_Tests_Functions extends WP_UnitTestCase  {

	private $single_user = null;
	private $single_user_id = null;

	public function setUp() {
		parent::setUp();

		$this->eas = ba_eas();

		$this->single_user = array(
			'user_login'   => 'mastersplinter',
			'user_pass'    => '1234',
			'user_email'   => 'mastersplinter@example.com',
			'display_name' => 'Master Splinter',
			'nickname'     => 'Sensei',
			'first_name'   => 'Master',
			'last_name'    => 'Splinter',
		);

		$this->single_user_id = $this->factory->user->create_object( $this->single_user );
	}

	public function tearDown() {
		parent::tearDown();

		$this->eas->author_based = 'author';
		$this->eas->role_slugs   = ba_eas_tests_slugs( 'default' );
	}

	/**
	 * Ensure that the plugin has been installed and activated.
	 */
	function test_do_auto_update() {

		// True tests
		add_filter( 'ba_eas_do_auto_update', '__return_true' );
		$this->assertTrue( ba_eas_do_auto_update() );
		remove_filter( 'ba_eas_do_auto_update', '__return_true', 10 );

		add_filter( 'ba_eas_do_auto_update', 'ba_eas_tests_return_null_string' );
		$this->assertTrue( ba_eas_do_auto_update() );
		remove_filter( 'ba_eas_do_auto_update', 'ba_eas_tests_return_null_string', 10 );

		add_filter( 'ba_eas_do_auto_update', 'ba_eas_tests_return_one_int' );
		$this->assertTrue( ba_eas_do_auto_update() );
		remove_filter( 'ba_eas_do_auto_update', 'ba_eas_tests_return_one_int', 10 );

		add_filter( 'ba_eas_do_auto_update', 'ba_eas_tests_return_full_array' );
		$this->assertTrue( ba_eas_do_auto_update() );
		remove_filter( 'ba_eas_do_auto_update', 'ba_eas_tests_return_full_array', 10 );

		// False tests
		add_filter( 'ba_eas_do_auto_update', '__return_false' );
		$this->assertFalse( ba_eas_do_auto_update() );
		remove_filter( 'ba_eas_do_auto_update', '__return_false', 10 );

		add_filter( 'ba_eas_do_auto_update', '__return_zero' );
		$this->assertFalse( ba_eas_do_auto_update() );
		remove_filter( 'ba_eas_do_auto_update', '__return_zero', 10 );

		add_filter( 'ba_eas_do_auto_update', '__return_empty_array' );
		$this->assertFalse( ba_eas_do_auto_update() );
		remove_filter( 'ba_eas_do_auto_update', '__return_empty_array', 10 );

		add_filter( 'ba_eas_do_auto_update', '__return_empty_string' );
		$this->assertFalse( ba_eas_do_auto_update() );
		remove_filter( 'ba_eas_do_auto_update', '__return_empty_string', 10 );
	}

	/**
	 * Ensure that all of our core actions have been added.
	 */
	function test_auto_update_user_nicename() {
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
		add_filter( 'ba_eas_auto_update_user_nicename_structure', '__return_empty_string' );
		$user_id  = ba_eas_auto_update_user_nicename( $this->single_user_id );
		$this->assertTrue( 0 < $user_id );
		$user     = get_userdata( $user_id );
		$this->assertEquals( 'mastersplinter', $user->user_nicename );
		remove_filter( 'ba_eas_auto_update_user_nicename_structure', '__return_empty_string', 10 );

		// Update using username
		add_filter( 'ba_eas_auto_update_user_nicename_structure', 'ba_eas_tests_nicename_return_username' );
		$user_id  = ba_eas_auto_update_user_nicename( $this->single_user_id );
		$this->assertTrue( 0 < $user_id );
		$user     = get_userdata( $user_id );
		$this->assertEquals( 'mastersplinter', $user->user_nicename );
		remove_filter( 'ba_eas_auto_update_user_nicename_structure', 'ba_eas_tests_nicename_return_username', 10 );

		// Update using nickname
		add_filter( 'ba_eas_auto_update_user_nicename_structure', 'ba_eas_tests_nicename_return_nickname' );
		$user_id  = ba_eas_auto_update_user_nicename( $this->single_user_id );
		$this->assertTrue( 0 < $user_id );
		$user     = get_userdata( $user_id );
		$this->assertEquals( 'sensei', $user->user_nicename );
		remove_filter( 'ba_eas_auto_update_user_nicename_structure', 'ba_eas_tests_nicename_return_displayname', 10 );

		// Update using displayname
		add_filter( 'ba_eas_auto_update_user_nicename_structure', 'ba_eas_tests_nicename_return_displayname' );
		$user_id  = ba_eas_auto_update_user_nicename( $this->single_user_id );
		$this->assertTrue( 0 < $user_id );
		$user     = get_userdata( $user_id );
		$this->assertEquals( 'master-splinter', $user->user_nicename );
		remove_filter( 'ba_eas_auto_update_user_nicename_structure', 'ba_eas_tests_nicename_return_displayname', 10 );

		// Update using firstname
		add_filter( 'ba_eas_auto_update_user_nicename_structure', 'ba_eas_tests_nicename_return_firstname' );
		$user_id  = ba_eas_auto_update_user_nicename( $this->single_user_id );
		$this->assertTrue( 0 < $user_id );
		$user     = get_userdata( $user_id );
		$this->assertEquals( 'master', $user->user_nicename );
		remove_filter( 'ba_eas_auto_update_user_nicename_structure', 'ba_eas_tests_nicename_return_firstname', 10 );

		// Update using lastname
		add_filter( 'ba_eas_auto_update_user_nicename_structure', 'ba_eas_tests_nicename_return_lastname' );
		$user_id  = ba_eas_auto_update_user_nicename( $this->single_user_id );
		$this->assertTrue( 0 < $user_id );
		$user     = get_userdata( $user_id );
		$this->assertEquals( 'splinter', $user->user_nicename );
		remove_filter( 'ba_eas_auto_update_user_nicename_structure', 'ba_eas_tests_nicename_return_lastname', 10 );

		// Update using firstlast
		add_filter( 'ba_eas_auto_update_user_nicename_structure', 'ba_eas_tests_nicename_return_firstlast' );
		$user_id  = ba_eas_auto_update_user_nicename( $this->single_user_id );
		$this->assertTrue( 0 < $user_id );
		$user     = get_userdata( $user_id );
		$this->assertEquals( 'master-splinter', $user->user_nicename );
		remove_filter( 'ba_eas_auto_update_user_nicename_structure', 'ba_eas_tests_nicename_return_firstlast', 10 );

		// Update using lastfirst
		add_filter( 'ba_eas_auto_update_user_nicename_structure', 'ba_eas_tests_nicename_return_lastfirst' );
		$user_id  = ba_eas_auto_update_user_nicename( $this->single_user_id );
		$this->assertTrue( 0 < $user_id );
		$user     = get_userdata( $user_id );
		$this->assertEquals( 'splinter-master', $user->user_nicename );
		remove_filter( 'ba_eas_auto_update_user_nicename_structure', 'ba_eas_tests_nicename_return_lastfirst', 10 );

		// Update using random string as structure, shouldn't update, so
		// user_nicename should be same as previous test ('splinter-master')
		add_filter( 'ba_eas_auto_update_user_nicename_structure', 'ba_eas_tests_return_sentence' );
		$user_id  = ba_eas_auto_update_user_nicename( $this->single_user_id );
		$this->assertTrue( 0 < $user_id );
		$user     = get_userdata( $user_id );
		$this->assertEquals( 'splinter-master', $user->user_nicename );
		remove_filter( 'ba_eas_auto_update_user_nicename_structure', 'ba_eas_tests_return_sentence', 10 );

		remove_filter( 'ba_eas_do_auto_update', '__return_true', 10 );
	}

	/**
	 * Ensure that the plugin has been installed and activated.
	 */
	function test_do_role_based_author_base() {

		// True tests
		add_filter( 'ba_eas_do_role_based_author_base', '__return_true' );
		$this->assertTrue( ba_eas_do_role_based_author_base() );
		remove_filter( 'ba_eas_do_role_based_author_base', '__return_true', 10 );

		add_filter( 'ba_eas_do_role_based_author_base', 'ba_eas_tests_return_null_string' );
		$this->assertTrue( ba_eas_do_role_based_author_base() );
		remove_filter( 'ba_eas_do_role_based_author_base', 'ba_eas_tests_return_null_string', 10 );

		add_filter( 'ba_eas_do_role_based_author_base', 'ba_eas_tests_return_one_int' );
		$this->assertTrue( ba_eas_do_role_based_author_base() );
		remove_filter( 'ba_eas_do_role_based_author_base', 'ba_eas_tests_return_one_int', 10 );

		add_filter( 'ba_eas_do_role_based_author_base', 'ba_eas_tests_return_full_array' );
		$this->assertTrue( ba_eas_do_role_based_author_base() );
		remove_filter( 'ba_eas_do_role_based_author_base', 'ba_eas_tests_return_full_array', 10 );

		// False tests
		add_filter( 'ba_eas_do_role_based_author_base', '__return_false' );
		$this->assertFalse( ba_eas_do_role_based_author_base() );
		remove_filter( 'ba_eas_do_role_based_author_base', '__return_false', 10 );

		add_filter( 'ba_eas_do_role_based_author_base', '__return_zero' );
		$this->assertFalse( ba_eas_do_role_based_author_base() );
		remove_filter( 'ba_eas_do_role_based_author_base', '__return_zero', 10 );

		add_filter( 'ba_eas_do_role_based_author_base', '__return_empty_array' );
		$this->assertFalse( ba_eas_do_role_based_author_base() );
		remove_filter( 'ba_eas_do_role_based_author_base', '__return_empty_array', 10 );

		add_filter( 'ba_eas_do_role_based_author_base', '__return_empty_string' );
		$this->assertFalse( ba_eas_do_role_based_author_base() );
		remove_filter( 'ba_eas_do_role_based_author_base', '__return_empty_string', 10 );
	}

	function test_author_link() {
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

	function test_template_include() {

		add_filter( 'ba_eas_do_role_based_author_base', '__return_false' );
		$this->assertEquals( 'no-role-based', ba_eas_template_include( 'no-role-based' ) );
		remove_filter( 'ba_eas_do_role_based_author_base', '__return_false', 10 );

		add_filter( 'ba_eas_do_role_based_author_base', '__return_true' );

		$this->assertEquals( 'no-WP_User', ba_eas_template_include( 'no-WP_User' ) );

		$GLOBALS['wp_query']->queried_object = get_userdata( $this->single_user_id );
		$this->assertEquals( 'author-mastersplinter.php', ba_eas_template_include( 'author-mastersplinter.php' ) );
		$this->assertEquals( "author-{$this->single_user_id}.php", ba_eas_template_include( "author-{$this->single_user_id}.php" ) );
		$this->assertEquals( 'role-test', ba_eas_template_include( 'role-test' ) );
		/**
		 * @todo Figure out how to fake a template file to complete the test
		 */
		$GLOBALS['wp_query']->queried_object = null;

		remove_filter( 'ba_eas_do_role_based_author_base', '__return_true', 10 );
	}
}
