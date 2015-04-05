<?php

class EAS_UnitTestCase extends WP_UnitTestCase  {
	public function setUp() {
		parent::setUp();
		$GLOBALS['ba_eas'] = ba_eas();
	}

	public function tearDown() {
		parent::tearDown();
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
	function test_setup_actions_added() {
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
