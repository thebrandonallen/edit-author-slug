<?php

/**
 * Tests to test that that testing framework is testing tests. Meta, huh?
 *
 * @package wordpress-plugins-tests
 */
class BA_EAS_Functions_Tests extends WP_UnitTestCase  {

	/**
	 * Ensure that the plugin has been installed and activated.
	 */
	function test_plugin_activated() {
		$this->assertTrue( is_plugin_active( 'edit-author-slug/edit-author-slug.php' ) );
	}
}
