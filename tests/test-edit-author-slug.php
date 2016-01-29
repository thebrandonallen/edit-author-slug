<?php

class EAS_UnitTestCase extends WP_UnitTestCase  {

	public function setUp() {
		parent::setUp();

		$this->eas = ba_eas();
	}

	public function tearDown() {
		parent::tearDown();
		$this->eas->author_base = 'author';
		$this->eas->role_slugs  = ba_eas_tests_slugs_default();
	}

	/**
	 * @covers BA_Edit_Author_Slug::setup_actions
	 */
	function test_setup_actions() {
		$this->assertEquals( 10, has_action( 'activate_' . $this->eas->plugin_basename, 'ba_eas_activation' ) );
		$this->assertEquals( 10, has_action( 'deactivate_' . $this->eas->plugin_basename, 'ba_eas_deactivation' ) );
		$this->assertEquals( 10, has_action( 'after_setup_theme', array( $this->eas, 'set_role_slugs' ) ) );
		$this->assertEquals( 4,  has_action( 'init', 'ba_eas_wp_rewrite_overrides' ) );
		$this->assertEquals( 20, has_action( 'init', array( $this->eas, 'add_rewrite_tags' ) ) );
		$this->assertEquals( 10, has_action( 'plugins_loaded', array( $this->eas, 'load_textdomain' ) ) );
	}

	/**
	 * @covers BA_Edit_Author_Slug::author_base_rewrite
	 */
	function test_author_base_rewrite() {
		$this->assertEquals( $GLOBALS['wp_rewrite']->author_base, 'author' );

		add_filter( 'ba_eas_do_role_based_author_base', '__return_true' );

		$this->eas->author_base_rewrite();
		$this->assertEquals( $GLOBALS['wp_rewrite']->author_base, '%ba_eas_author_role%' );

		remove_filter( 'ba_eas_do_role_based_author_base', '__return_true', 10 );

		$this->eas->author_base = 'ninja';
		$this->eas->author_base_rewrite();
		$this->assertEquals( $GLOBALS['wp_rewrite']->author_base, 'ninja' );
	}

	/**
	 * @covers BA_Edit_Author_Slug::set_role_slugs
	 */
	function test_set_role_slugs() {
		$this->assertEquals( $this->eas->role_slugs, ba_eas_tests_slugs_default() );

		update_option( '_ba_eas_role_slugs', ba_eas_tests_slugs_custom() );
		$this->eas->set_role_slugs();
		$this->assertEquals( $this->eas->role_slugs, ba_eas_tests_slugs_custom() );

		add_role( 'foot-soldier', 'Foot Soldier' );
		update_option( '_ba_eas_role_slugs', ba_eas_tests_slugs_extra() );
		$this->eas->set_role_slugs();
		$this->assertEquals( $this->eas->role_slugs, ba_eas_tests_slugs_extra() );
		remove_role( 'foot-soldier' );
	}

	/**
	 * @covers BA_Edit_Author_Slug::add_rewrite_tags
	 */
	function test_add_rewrite_tags() {
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
		$this->eas->role_slugs = ba_eas_tests_slugs_custom();
		$this->eas->add_rewrite_tags();
		$slugs = '(jonin|chunin|mystic|junior-genin|deshi|author)';

		$this->assertTrue( in_array( $slugs, $wp_rewrite->rewritereplace ) );

		// Test for WP custom roles/role slugs
		$this->eas->role_slugs = ba_eas_tests_slugs_extra();
		$this->eas->add_rewrite_tags();
		$slugs = '(administrator|editor|author|contributor|subscriber|foot-soldier)';

		$this->assertTrue( in_array( $slugs, $wp_rewrite->rewritereplace ) );

		remove_filter( 'ba_eas_do_role_based_author_base', '__return_true', 10 );
	}

	/**
	 * @covers ::ba_eas_activation
	 */
	function test_ba_eas_activation() {
		ba_eas_activation();
		$this->assertTrue( (bool) did_action( 'ba_eas_activation' ) );
	}

	/**
	 * @covers ::ba_eas_deactivation
	 */
	function test_ba_eas_deactivation() {
		ba_eas_deactivation();
		$this->assertTrue( (bool) did_action( 'ba_eas_deactivation' ) );
	}
}
