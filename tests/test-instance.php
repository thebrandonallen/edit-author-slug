<?php

require_once( dirname( __FILE__ ) . '/functions.php' );

class EAS_UnitTestCase extends WP_UnitTestCase  {

	public function setUp() {
		parent::setUp();

		$this->eas = ba_eas();
	}

	public function tearDown() {
		parent::tearDown();
		$this->eas->role_slugs = ba_eas_tests_slugs( 'default' );
	}

	/**
	 * Ensure that the plugin has been installed and activated.
	 */
	function test_plugin_activated() {
		$this->assertTrue( class_exists( 'BA_Edit_Author_Slug' ) );
	}

	/**
	 * Ensure that all of our core actions have been added.
	 */
	function test_setup_actions() {
		$this->assertEquals( 10, has_action( 'activate_' . $this->eas->plugin_basename, 'ba_eas_activation' ) );
		$this->assertEquals( 10, has_action( 'deactivate_' . $this->eas->plugin_basename, 'ba_eas_deactivation' ) );
		$this->assertEquals( 10, has_action( 'after_setup_theme', array( $this->eas, 'set_role_slugs' ) ) );
		$this->assertEquals( 10, has_action( 'init', array( $this->eas, 'author_base_rewrite' ) ) );
		$this->assertEquals( 20, has_action( 'init', array( $this->eas, 'add_rewrite_tags' ) ) );
		$this->assertEquals( 0,  has_action( 'init', array( $this->eas, 'load_textdomain' ) ) );
	}

	/**
	 * Test that our activation hook is fired.
	 */
	function test_add_rewrite_tag() {
		// Check for return when role-based author base is disabled
		add_filter( 'ba_eas_do_role_based_author_base', '__return_false' );

		$this->assertNull( $this->eas->add_rewrite_tags() );

		remove_filter( 'ba_eas_do_role_based_author_base', '__return_false', 10 );

		// Check that rewrite tags have been added when role-based author base is on
		$wp_rewrite = $GLOBALS['wp_rewrite'];

		add_filter( 'ba_eas_do_role_based_author_base', '__return_true' );

		// Test for WP default roles/role slugs
		$this->eas->add_rewrite_tags();
		$slugs = '(administrator|editor|author|contributor|subscriber)';

		$this->assertTrue( in_array( '%ba_eas_author_role%', $wp_rewrite->rewritecode ) );
		$this->assertTrue( in_array( $slugs, $wp_rewrite->rewritereplace ) );

		// Test for WP custom roles/role slugs
		$this->eas->role_slugs = ba_eas_tests_slugs( 'custom' );
		$this->eas->add_rewrite_tags();
		$slugs = '(jonin|chunin|mystic|junior-genin|deshi|author)';

		$this->assertTrue( in_array( $slugs, $wp_rewrite->rewritereplace ) );

		// Test for WP custom roles/role slugs
		$this->eas->role_slugs = ba_eas_tests_slugs( 'extra' );
		$this->eas->add_rewrite_tags();
		$slugs = '(administrator|editor|author|contributor|subscriber|foot-soldier)';

		$this->assertTrue( in_array( $slugs, $wp_rewrite->rewritereplace ) );

		remove_filter( 'ba_eas_do_role_based_author_base', '__return_true', 10 );
	}

	/**
	 * Test that our activation hook is fired.
	 */
	function test_ba_eas_activation() {
		ba_eas_activation();
		$this->assertTrue( (bool) did_action( 'ba_eas_activation' ) );
	}

	/**
	 * Test that our deactivation hook is fired.
	 */
	function test_ba_eas_deactivation() {
		ba_eas_deactivation();
		$this->assertTrue( (bool) did_action( 'ba_eas_deactivation' ) );
	}
}
