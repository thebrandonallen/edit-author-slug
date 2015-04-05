<?php

class EAS_UnitTestCase extends WP_UnitTestCase  {

	private $default_user_slugs;
	private $custom_user_slugs;
	private $user_slugs_with_extra_role;

	public function setUp() {
		parent::setUp();

		$GLOBALS['ba_eas'] = ba_eas();

		$this->default_user_slugs = array(
			'administrator' => array(
				'name' => 'Administrator',
				'slug' => 'administrator',
			),
			'editor' => array(
				'name' => 'Editor',
				'slug' => 'editor',
			),
			'author' => array(
				'name' => 'Author',
				'slug' => 'author',
			),
			'contributor' => array(
				'name' => 'Contributor',
				'slug' => 'contributor',
			),
			'subscriber' => array(
				'name' => 'Subscriber',
				'slug' => 'subscriber',
			),
		);

		$this->custom_user_slugs = array(
			'administrator' => array(
				'name' => 'Administrator',
				'slug' => 'jonin',
			),
			'editor' => array(
				'name' => 'Editor',
				'slug' => 'chunin',
			),
			'author' => array(
				'name' => 'Author',
				'slug' => 'mystic',
			),
			'contributor' => array(
				'name' => 'Contributor',
				'slug' => 'junior-genin',
			),
			'subscriber' => array(
				'name' => 'Subscriber',
				'slug' => 'deshi',
			),
		);

		$extra_role = array(
			'foot-soldier' => array(
				'name' => 'Foot Soldier',
				'slug' => 'foot-soldier',
			)
		);

		$this->user_slugs_with_extra_role = $this->default_user_slugs + $extra_role;
	}

	public function tearDown() {
		parent::tearDown();

		ba_eas()->role_slugs = $this->default_user_slugs;
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
		$ba_eas = $GLOBALS['ba_eas'];

		$this->assertEquals( 10, has_action( 'activate_' . $ba_eas->plugin_basename, 'ba_eas_activation' ) );
		$this->assertEquals( 10, has_action( 'deactivate_' . $ba_eas->plugin_basename, 'ba_eas_deactivation' ) );
		$this->assertEquals( 10, has_action( 'after_setup_theme', array( $ba_eas, 'set_role_slugs' ) ) );
		$this->assertEquals( 10, has_action( 'init', array( $ba_eas, 'author_base_rewrite' ) ) );
		$this->assertEquals( 20, has_action( 'init', array( $ba_eas, 'add_rewrite_tags' ) ) );
		$this->assertEquals( 0,  has_action( 'init', array( $ba_eas, 'load_textdomain' ) ) );
	}

	/**
	 * Test that our activation hook is fired.
	 */
	function test_add_rewrite_tag() {
		// Check for return when role-based author base is disabled
		add_filter( 'ba_eas_do_role_based_author_base', '__return_false' );

		$this->assertFalse( (bool) ba_eas()->add_rewrite_tags() );

		remove_filter( 'ba_eas_do_role_based_author_base', '__return_false', 10 );

		// Check that rewrite tags have been added when role-based author base is on
		$wp_rewrite = $GLOBALS['wp_rewrite'];

		add_filter( 'ba_eas_do_role_based_author_base', '__return_true' );

		// Test for WP default roles/role slugs
		ba_eas()->add_rewrite_tags();
		$slugs = '(administrator|editor|author|contributor|subscriber)';

		$this->assertTrue( in_array( '%ba_eas_author_role%', $wp_rewrite->rewritecode ) );
		$this->assertTrue( in_array( $slugs, $wp_rewrite->rewritereplace ) );

		// Test for WP custom roles/role slugs
		ba_eas()->role_slugs = $this->custom_user_slugs;
		ba_eas()->add_rewrite_tags();
		$slugs = '(jonin|chunin|mystic|junior-genin|deshi|author)';

		$this->assertTrue( in_array( $slugs, $wp_rewrite->rewritereplace ) );

		// Test for WP custom roles/role slugs
		ba_eas()->role_slugs = $this->user_slugs_with_extra_role;
		ba_eas()->add_rewrite_tags();
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
