<?php

class BA_EAS_Tests_Admin extends WP_UnitTestCase {
	protected $old_current_user = 0;

	public function setUp() {
		parent::setUp();

		$this->old_current_user = get_current_user_id();
		$this->new_current_user = $this->factory->user->create( array(
			'user_login' => 'mastersplinter',
			'role' => 'administrator',
			'first_name' => 'Master',
			'last_name' => 'Splinter',
		) );
		wp_set_current_user( $this->new_current_user );

		$this->eas = ba_eas();

		require_once( $this->eas->plugin_dir . 'includes/admin.php' );
	}

	public function tearDown() {
		parent::tearDown();
		wp_set_current_user( $this->old_current_user );
		$GLOBALS['wp_rewrite']->author_base = 'author';
	}

	/**
	 * @covers ::ba_eas_show_user_nicename
	 */
	function test_ba_eas_show_user_nicename() {
		add_filter( 'ba_eas_can_edit_author_slug', '__return_false' );
		$this->assertEquals( '', ba_eas_show_user_nicename( wp_get_current_user() ) );
		remove_filter( 'ba_eas_can_edit_author_slug', '__return_false' );

		ob_start();
		ba_eas_show_user_nicename( wp_get_current_user() );
		$output = ob_get_clean();

		$this->assertContains( '<label title="mastersplinter"><input type="radio" id="ba_eas_author_slug" name="ba_eas_author_slug" value="mastersplinter" checked=\'checked\'> <span>mastersplinter</span></label>', $output );
		$this->assertContains( '<label title="master-splinter"><input type="radio" id="ba_eas_author_slug" name="ba_eas_author_slug" value="master-splinter"> <span>master-splinter</span></label>', $output );
		$this->assertContains( '<label title="master"><input type="radio" id="ba_eas_author_slug" name="ba_eas_author_slug" value="master"> <span>master</span></label>', $output );
		$this->assertContains( '<label title="splinter"><input type="radio" id="ba_eas_author_slug" name="ba_eas_author_slug" value="splinter"> <span>splinter</span></label>', $output );
		$this->assertContains( '<label title="splinter-master"><input type="radio" id="ba_eas_author_slug" name="ba_eas_author_slug" value="splinter-master"> <span>splinter-master</span></label>', $output );
		$this->assertContains( '<label title="mastersplinter"><input type="radio" id="ba_eas_author_slug_custom" name="ba_eas_author_slug" value="\c\u\s\t\o\m"> <span>Custom: </span></label> <input type="text" name="ba_eas_author_slug_custom" id="ba_eas_author_slug_custom" value="mastersplinter" class="regular-text" />', $output );
	}

	/**
	 * @covers ::ba_eas_update_user_nicename
	 */
	function test_ba_eas_update_user_nicename() {
		$user = new WP_User;
		$errors = new WP_Error;

		$this->assertNull( ba_eas_update_user_nicename( $errors, false, $user ) );

		$this->assertNull( ba_eas_update_user_nicename( $errors, true, $user ) );

		$user = wp_get_current_user();

		$_REQUEST = array(
			'_wpnonce' => wp_create_nonce( 'update-user_' . $user->ID ),
		);

		add_filter( 'ba_eas_can_edit_author_slug', '__return_false' );
		$this->assertNull( ba_eas_update_user_nicename( $errors, true, $user ) );
		remove_filter( 'ba_eas_can_edit_author_slug', '__return_false' );

		$_POST = array(
			'ba_eas_author_slug' => addslashes( '\c\u\s\t\o\m' ),
			'ba_eas_author_slug_custom' => 'assertion-1',
		);

		ba_eas_update_user_nicename( $errors, true, $user );
		$this->assertEquals( 'assertion-1', $user->user_nicename );

		$_POST = array(
			'ba_eas_author_slug' => 'assertion-2',
		);

		ba_eas_update_user_nicename( $errors, true, $user );
		$this->assertEquals( 'assertion-2', $user->user_nicename );

		$_POST = array(
			'ba_eas_author_slug' => '\ ',
		);

		$this->assertNull( ba_eas_update_user_nicename( $errors, true, $user ) );
		$this->assertEquals( 'assertion-2', $user->user_nicename );
		$this->assertEquals( '<strong>ERROR</strong>: An author slug cannot be blank. Please try again.', $errors->get_error_message( 'ba_edit_author_slug' ) );

		unset( $errors );
		$errors = new WP_Error;

		$_POST = array(
			'ba_eas_author_slug' => '@',
		);

		$this->assertNull( ba_eas_update_user_nicename( $errors, true, $user ) );
		$this->assertEquals( 'assertion-2', $user->user_nicename );
		$this->assertEquals( '<strong>ERROR</strong>: That author slug appears to be invalid. Please try something different.', $errors->get_error_message( 'ba_edit_author_slug' ) );

		unset( $errors );
		$errors = new WP_Error;

		$_POST = array(
			'ba_eas_author_slug' => 'admin',
		);

		$this->assertNull( ba_eas_update_user_nicename( $errors, true, $user ) );
		$this->assertEquals( 'assertion-2', $user->user_nicename );
		$this->assertEquals( '<strong>ERROR</strong>: The author slug, <strong><em>admin</em></strong>, already exists. Please try something different.', $errors->get_error_message( 'ba_edit_author_slug' ) );

		unset( $errors );
	}

	/**
	 * @covers ::ba_eas_author_slug_column
	 */
	function test_ba_eas_author_slug_column() {
		$this->assertArrayHasKey( 'ba-eas-author-slug', ba_eas_author_slug_column( array() ) );
	}

	/**
	 * @covers ::ba_eas_author_slug_custom_column
	 */
	function test_ba_eas_author_slug_custom_column() {
		$default = ba_eas_author_slug_custom_column( 'ninja', 'ninjas', $this->new_current_user );
		$this->assertEquals( 'ninja', $default );

		$default = ba_eas_author_slug_custom_column( 'ninja', 'ba-eas-author-slug', $this->new_current_user );
		$this->assertEquals( 'mastersplinter', $default );
	}

	/**
	 * @covers ::ba_eas_can_edit_author_slug
	 */
	function test_ba_eas_can_edit_author_slug() {
		$this->assertTrue( ba_eas_can_edit_author_slug() );

		wp_set_current_user( $this->old_current_user );
		$this->assertFalse( ba_eas_can_edit_author_slug() );
		wp_set_current_user( $this->new_current_user );

		add_filter( 'ba_eas_can_edit_author_slug', '__return_false' );
		$this->assertFalse( ba_eas_can_edit_author_slug() );
		remove_filter( 'ba_eas_can_edit_author_slug', '__return_false' );
	}

	/**
	 * @covers ::ba_eas_sanitize_author_base
	 */
	function test_ba_eas_sanitize_author_base() {

		$this->assertEquals( 'author', $GLOBALS['wp_rewrite']->author_base );

		$this->assertEquals( 'author', ba_eas_sanitize_author_base( '' ) );

		$this->assertEquals( 'author', ba_eas_sanitize_author_base( '@' ) );

		$this->assertEquals( 'ninja', ba_eas_sanitize_author_base( 'ninja' ) );
		$this->assertEquals( 'ninja', $GLOBALS['wp_rewrite']->author_base );
		$this->assertEquals( false, get_option( 'rewrite_rules' ) );
	}

	/**
	 * @covers ::ba_eas_install
	 */
	function test_ba_eas_install() {
		$old_db_version = $this->eas->current_db_version;
		$this->eas->current_db_version = 0;
		ba_eas_install();

		$this->assertEquals( $this->eas->author_base, get_option( '_ba_eas_author_base' ) );
		$this->assertEquals( $this->eas->db_version, get_option( '_ba_eas_db_version' ) );
		$this->assertEquals( $this->eas->do_auto_update, get_option( '_ba_eas_do_auto_update' ) );
		$this->assertEquals( $this->eas->default_user_nicename, get_option( '_ba_eas_default_user_nicename' ) );
		$this->assertEquals( $this->eas->do_role_based, get_option( '_ba_eas_do_role_based' ) );
		$this->assertEquals( $this->eas->role_slugs, get_option( '_ba_eas_role_slugs' ) );

		$this->eas->current_db_version = $this->eas->db_version;
		$this->assertNull( ba_eas_install() );
	}

	/**
	 * @covers ::ba_eas_upgrade
	 */
	function test_ba_eas_upgrade() {
		$old_db_version = $this->eas->current_db_version;
		$this->eas->current_db_version = 30;

		update_option( 'ba_edit_author_slug', 'test' );

		ba_eas_upgrade();

		$this->assertEquals( $this->eas->author_base, get_option( '_ba_eas_author_base' ) );
		$this->assertEquals( $this->eas->db_version, get_option( '_ba_eas_db_version' ) );
		$this->assertEquals( $this->eas->do_auto_update, get_option( '_ba_eas_do_auto_update' ) );
		$this->assertEquals( $this->eas->default_user_nicename, get_option( '_ba_eas_default_user_nicename' ) );
		$this->assertEquals( $this->eas->do_role_based, get_option( '_ba_eas_do_role_based' ) );
		$this->assertEquals( $this->eas->role_slugs, get_option( '_ba_eas_role_slugs' ) );
		$this->assertEquals( 'test', get_option( '_ba_eas_old_options' ) );
		$this->assertEquals( false, get_option( 'ba_edit_author_slug' ) );
		$this->assertEquals( false, get_option( 'rewrite_rules' ) );

		$this->eas->current_db_version = $this->eas->db_version;
		$this->assertNull( ba_eas_upgrade() );
	}
}
