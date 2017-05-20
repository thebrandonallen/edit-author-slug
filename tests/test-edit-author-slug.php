<?php
/**
 * Test the main class functionality.
 *
 * @package Edit_Author_Slug
 * @subpackage Tests
 */

/**
 * The Edit Author Slug unit test case.
 */
class EAS_UnitTestCase extends WP_UnitTestCase {

	/**
	 * The `setUp` method.
	 */
	public function setUp() {
		parent::setUp();

		$this->eas = ba_eas();
	}

	/**
	 * The admin `tearDown` method.
	 *
	 * Resets the current user and globals.
	 */
	public function tearDown() {
		parent::tearDown();
		$this->eas->author_base   = 'author';
		$this->eas->role_slugs    = ba_eas_tests_slugs_default();
		$this->eas->do_role_based = false;
	}

	/**
	 * Filter the text domain, so that something is loaded for testing.
	 *
	 * @since 1.5.0
	 *
	 * @param bool   $override Whether to override the .mo file loading. Default false.
	 * @param string $domain   Text domain. Unique identifier for retrieving translated strings.
	 * @param string $file     Path to the MO file.
	 *
	 * @return bool
	 */
	function _override_load_textdomain_filter( $override, $domain, $file ) {
		global $l10n;

		$file = WP_LANG_DIR . '/plugins/internationalized-plugin-de_DE.mo';

		if ( ! is_readable( $file ) ) {
			return false;
		}

		$mo = new MO();

		if ( ! $mo->import_from_file( $file ) ) {
			return false;
		}

		if ( isset( $l10n[ $domain ] ) ) {
			$mo->merge_with( $l10n[ $domain ] );
		}

		$l10n[ $domain ] = &$mo;

		return true;
	}

	/**
	 * Test for `BA_Edit_Author_Slug::__call()`.
	 *
	 * @covers BA_Edit_Author_Slug::__call
	 *
	 * @expectedDeprecated     BA_Edit_Author_Slug::author_base_rewrite
	 * @expectedIncorrectUsage BA_Edit_Author_Slug::fake_method
	 */
	public function test__call() {

		$this->eas->__call( 'author_base_rewrite' );

		$this->assertNull( $this->eas->__call( 'fake_method' ) );
	}

	/**
	 * Test for `BA_Edit_Author_Slug::setup_globals()`.
	 *
	 * @covers BA_Edit_Author_Slug::setup_globals
	 */
	public function test_setup_globals() {
		$this->markTestIncomplete();
	}

	/**
	 * Test for `BA_Edit_Author_Slug::setup_actions()`.
	 *
	 * @covers BA_Edit_Author_Slug::setup_actions
	 */
	public function test_setup_actions() {
		$this->assertEquals( 10, has_action( 'activate_' . $this->eas->plugin_basename, 'ba_eas_activation' ) );
		$this->assertEquals( 10, has_action( 'deactivate_' . $this->eas->plugin_basename, 'ba_eas_deactivation' ) );
		$this->assertEquals( 10, has_action( 'after_setup_theme', array( $this->eas, 'set_role_slugs' ) ) );
		$this->assertEquals( 4,  has_action( 'init', 'ba_eas_wp_rewrite_overrides' ) );
		$this->assertEquals( 20, has_action( 'init', array( $this->eas, 'add_rewrite_tags' ) ) );
		$this->assertEquals( 10, has_action( 'plugins_loaded', array( $this->eas, 'load_textdomain' ) ) );
	}

	/**
	 * Test for `BA_Edit_Author_Slug::options_back_compat()`.
	 *
	 * @covers BA_Edit_Author_Slug::options_back_compat
	 */
	public function test_options_back_compat() {
		$this->markTestIncomplete();
	}

	/**
	 * Test for `BA_Edit_Author_Slug::load_textdomain()`.
	 *
	 * @covers BA_Edit_Author_Slug::load_textdomain
	 */
	public function test_load_textdomain() {

		// Make sure the text domain isn't already loaded.
		unload_textdomain( 'edit-author-slug' );
		$this->assertFalse( is_textdomain_loaded( 'edit-author-slug' ) );

		add_filter( 'override_load_textdomain', array( $this, '_override_load_textdomain_filter' ), 10, 3 );
		$this->eas->load_textdomain();
		remove_filter( 'override_load_textdomain', array( $this, '_override_load_textdomain_filter' ) );

		$this->assertTrue( is_textdomain_loaded( 'edit-author-slug' ) );

		unload_textdomain( 'edit-author-slug' );
	}

	/**
	 * Test for `BA_Edit_Author_Slug::set_role_slugs()`.
	 *
	 * @covers BA_Edit_Author_Slug::set_role_slugs
	 */
	public function test_set_role_slugs() {
		$this->assertEquals( $this->eas->role_slugs, ba_eas_tests_slugs_default() );

		update_option( '_ba_eas_role_slugs', ba_eas_tests_slugs_custom() );
		$this->eas->set_role_slugs();
		$this->assertEquals( $this->eas->role_slugs, ba_eas_tests_slugs_custom() );

		add_role( 'foot-soldier', 'Foot Soldier' );
		update_option( '_ba_eas_role_slugs', ba_eas_tests_slugs_extra() );
		$this->eas->set_role_slugs();
		$this->assertEquals( $this->eas->role_slugs, ba_eas_tests_slugs_extra() );
		remove_role( 'foot-soldier' );

		$this->eas->set_role_slugs();
		$this->assertEquals( $this->eas->role_slugs, ba_eas_tests_slugs_default() );
	}

	/**
	 * Test for `BA_Edit_Author_Slug::add_rewrite_tags()`.
	 *
	 * @covers BA_Edit_Author_Slug::add_rewrite_tags
	 */
	public function test_add_rewrite_tags() {

		// Check for return when role-based author base is disabled.
		$this->assertNull( $this->eas->add_rewrite_tags() );

		// Check that rewrite tags have been added when role-based author base is on.
		$wp_rewrite = $GLOBALS['wp_rewrite'];

		add_filter( 'ba_eas_do_role_based_author_base', '__return_true' );

		// Test for WP default roles/role slugs.
		$this->eas->add_rewrite_tags();
		$slugs = '(administrator|editor|author|contributor|subscriber)';

		$this->assertTrue( in_array( '%ba_eas_author_role%', $wp_rewrite->rewritecode, true ) );
		$this->assertTrue( in_array( $slugs, $wp_rewrite->rewritereplace, true ) );

		$old_author_base = 'test/base';
		$this->eas->author_base = '%ba_eas_author_role%';
		$this->assertTrue( in_array( '%ba_eas_author_role%', $wp_rewrite->rewritecode, true ) );
		$this->assertTrue( in_array( $slugs, $wp_rewrite->rewritereplace, true ) );
		$this->eas->author_base = $old_author_base;

		// Test for WP custom roles/role slugs.
		$this->eas->role_slugs = ba_eas_tests_slugs_custom();
		$this->eas->add_rewrite_tags();
		$slugs = '(jonin|chunin|mystic|junior-genin|deshi|author)';

		$this->assertTrue( in_array( $slugs, $wp_rewrite->rewritereplace, true ) );

		// Test for WP custom roles/role slugs.
		$this->eas->role_slugs = ba_eas_tests_slugs_extra();
		$this->eas->add_rewrite_tags();
		$slugs = '(administrator|editor|author|contributor|subscriber|foot-soldier)';

		$this->assertTrue( in_array( $slugs, $wp_rewrite->rewritereplace, true ) );

		remove_filter( 'ba_eas_do_role_based_author_base', '__return_true' );
	}

	/**
	 * Test for `ba_eas_activation()`.
	 *
	 * @covers ::ba_eas_activation
	 */
	public function test_ba_eas_activation() {
		ba_eas_activation();
		$this->assertTrue( (bool) did_action( 'ba_eas_activation' ) );
	}

	/**
	 * Test for `ba_eas_deactivation()`.
	 *
	 * @covers ::ba_eas_deactivation
	 */
	public function test_ba_eas_deactivation() {
		ba_eas_deactivation();
		$this->assertTrue( (bool) did_action( 'ba_eas_deactivation' ) );
	}
}
