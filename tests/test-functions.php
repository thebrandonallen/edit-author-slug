<?php

class BA_EAS_Functions_Tests extends WP_UnitTestCase  {
	public function setUp() {
		$GLOBALS['ba_eas'] = ba_eas();
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
		$this->assertEquals( 10, has_action( 'after_setup_theme', array( $GLOBALS['ba_eas'], 'set_role_slugs' ) ) );
		$this->assertEquals( 10, has_action( 'init', array( $GLOBALS['ba_eas'], 'author_base_rewrite' ) ) );
		$this->assertEquals( 20, has_action( 'init', array( $GLOBALS['ba_eas'], 'add_rewrite_tags' ) ) );
		$this->assertEquals( 0, has_action( 'init', array( $GLOBALS['ba_eas'], 'load_textdomain' ) ) );
	}
}
