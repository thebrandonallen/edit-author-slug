<?php
/**
 * Test admin functionality.
 *
 * @package Edit_Author_Slug
 * @subpackage Tests
 */

/**
 * The Edit Author Slug admin test class.
 */
class BA_EAS_Tests_Admin extends WP_UnitTestCase {

	/**
	 * The new user id.
	 *
	 * @since 1.6.0
	 *
	 * @var int
	 */
	protected static $user_id;

	/**
	 * The old user id.
	 *
	 * @since 1.6.0
	 *
	 * @var int
	 */
	protected static $old_user_id;

	/**
	 * The default roles slugs.
	 *
	 * @since 1.6.0
	 *
	 * @var array
	 */
	protected static $default_role_slugs;

	/**
	 * Set up the admin fixture.
	 *
	 * @since 1.6.0
	 */
	public static function setUpBeforeClass(): void {
		$f = new WP_UnitTest_Factory();

		// Set up the new user.
		self::$user_id = $f->user->create(
			array(
				'user_login' => 'mastersplinter',
				'role'       => 'administrator',
				'first_name' => 'Master',
				'last_name'  => 'Splinter',
				'nickname'   => 'Sensei',
			)
		);

		self::commit_transaction();

		// Set the old user id.
		self::$old_user_id = get_current_user_id();

		// Load the admin.
		require_once ba_eas()->plugin_dir . 'includes/admin.php';

		// Load the ITSEC_Modules mock.
		require_once 'mocks/class-itsec-modules-mock.php';
	}

	/**
	 * Tear down the admin fixture.
	 *
	 * @since 1.6.0
	 */
	public static function tearDownAfterClass(): void {
		wp_delete_user( self::$user_id );
		self::commit_transaction();
	}

	/**
	 * The admin `setUp` method.
	 *
	 * @since 1.1.0
	 *
	 * Sets up up some users, and loads the admin.
	 */
	public function setUp(): void {
		parent::setUp();
		wp_set_current_user( self::$user_id );

		// Set the default roles slugs, if not already.
		if ( is_null( self::$default_role_slugs ) ) {
			self::$default_role_slugs = ba_eas_get_default_role_slugs();
		}
	}

	/**
	 * The admin `tearDown` method.
	 *
	 * @since 1.1.0
	 *
	 * Resets the current user and globals.
	 */
	public function tearDown(): void {
		parent::tearDown();

		wp_set_current_user( self::$old_user_id );

		// Reset the wp_rewrite global.
		$GLOBALS['wp_rewrite']->author_base = 'author';

		// Unset any test REQUEST and POST keys we've set.
		unset( $_REQUEST['_wpnonce'] );
		unset( $_POST['ba_eas_author_slug'] );
		unset( $_POST['ba_eas_author_slug_custom'] );

		// Reset the role slugs.
		ba_eas()->role_slugs = self::$default_role_slugs;

		// Remove any added roles.
		remove_role( 'ninja' );

		ITSEC_Modules::$force_unique_nicename = false;
	}

	/**
	 * Helper function to filter the return of nicename filters.
	 *
	 * @since 1.2.0
	 *
	 * @param string $nicename The user nicename.
	 *
	 * @return string
	 */
	public function user_nicename_filter( $nicename = '' ) {
		return 'test';
	}

	/**
	 * Test `ba_eas_show_user_nicename()`.
	 *
	 * @since 1.1.0
	 *
	 * @covers ::ba_eas_show_user_nicename
	 */
	public function test_ba_eas_show_user_nicename() {
		add_filter( 'ba_eas_can_edit_author_slug', '__return_false' );
		$this->assertEquals( '', ba_eas_show_user_nicename( wp_get_current_user() ) );
		remove_filter( 'ba_eas_can_edit_author_slug', '__return_false' );

		ob_start();
		ba_eas_show_user_nicename( wp_get_current_user() );
		$output = ob_get_clean();

		// Test for username (`masterplinter`).
		$this->assertContains( '<label title="mastersplinter">', $output );
		$this->assertContains( '<input type="radio" class="eas-author-slug" name="ba_eas_author_slug" value="mastersplinter" autocapitalize="none" autocorrect="off" maxlength="50" checked=\'checked\'>', $output );
		$this->assertContains( '<span>mastersplinter</span>', $output );

		// Test for nickname (`sensei`).
		$this->assertContains( '<label title="sensei">', $output );
		$this->assertContains( '<input type="radio" class="eas-author-slug" name="ba_eas_author_slug" value="sensei" autocapitalize="none" autocorrect="off" maxlength="50">', $output );
		$this->assertContains( '<span>sensei</span>', $output );

		// Test for firstname-lastname (`master-splinter`).
		$this->assertContains( '<label title="master-splinter">', $output );
		$this->assertContains( '<input type="radio" class="eas-author-slug" name="ba_eas_author_slug" value="master-splinter" autocapitalize="none" autocorrect="off" maxlength="50">', $output );
		$this->assertContains( '<span>master-splinter</span>', $output );

		// Test for firstname (`master`).
		$this->assertContains( '<label title="master">', $output );
		$this->assertContains( '<input type="radio" class="eas-author-slug" name="ba_eas_author_slug" value="master" autocapitalize="none" autocorrect="off" maxlength="50">', $output );
		$this->assertContains( '<span>master</span>', $output );

		// Test for lastname (`splinter`).
		$this->assertContains( '<label title="splinter">', $output );
		$this->assertContains( '<input type="radio" class="eas-author-slug" name="ba_eas_author_slug" value="splinter" autocapitalize="none" autocorrect="off" maxlength="50">', $output );
		$this->assertContains( '<span>splinter</span>', $output );

		// Test for lastname-firstname (`splinter-master`).
		$this->assertContains( '<label title="splinter-master">', $output );
		$this->assertContains( '<input type="radio" class="eas-author-slug" name="ba_eas_author_slug" value="splinter-master" autocapitalize="none" autocorrect="off" maxlength="50">', $output );
		$this->assertContains( '<span>splinter-master</span>', $output );

		// Test for userid (`userid`).
		$this->assertContains( '<label title="' . self::$user_id . '">', $output );
		$this->assertContains( '<input type="radio" class="eas-author-slug" name="ba_eas_author_slug" value="' . self::$user_id . '" autocapitalize="none" autocorrect="off" maxlength="50">', $output );
		$this->assertContains( '<span>' . self::$user_id . '</span>', $output );

		// Test custom author slug.
		$this->assertContains( '<label for="ba_eas_author_slug_custom_radio">', $output );
		$this->assertContains( '<input type="radio" class="eas-author-slug-custom-radio" name="ba_eas_author_slug" value="\c\u\s\t\o\m" autocapitalize="none" autocorrect="off" maxlength="50">', $output );
		$this->assertContains( '<span class="screen-reader-text">Enter a custom author slug in the following field</span>', $output );
		$this->assertContains( '<label for="ba_eas_author_slug_custom" class="screen-reader-text">Custom author slug:</label>', $output );
		$this->assertContains( '<input type="text" name="ba_eas_author_slug_custom" class="eas-author-slug-custom" value="mastersplinter" class="regular-text" />', $output );
	}

	/**
	 * Test `ba_eas_show_user_nicename()` when iThemes force unique nicename is
	 * active.
	 *
	 * @since 1.6.0
	 *
	 * @covers ::ba_eas_show_user_nicename
	 */
	public function test_ba_eas_show_user_nicename_itsec_force_unique_nicename() {
		ITSEC_Modules::$force_unique_nicename = true;

		add_filter( 'ba_eas_can_edit_author_slug', '__return_false' );
		$this->assertEquals( '', ba_eas_show_user_nicename( wp_get_current_user() ) );
		remove_filter( 'ba_eas_can_edit_author_slug', '__return_false' );

		ob_start();
		ba_eas_show_user_nicename( wp_get_current_user() );
		$output = ob_get_clean();

		// Test for username (`masterplinter`).
		$this->assertNotContains( '<label title="mastersplinter">', $output );
		$this->assertNotContains( '<input type="radio" class="eas-author-slug" name="ba_eas_author_slug" value="mastersplinter" autocapitalize="none" autocorrect="off" maxlength="50" checked=\'checked\'>', $output );
		$this->assertNotContains( '<span>mastersplinter</span>', $output );
	}

	/**
	 * Test `ba_eas_update_user_nicename()`.
	 *
	 * @since 1.1.0
	 *
	 * @covers ::ba_eas_update_user_nicename
	 */
	public function test_ba_eas_update_user_nicename() {
		$errors = new WP_Error();
		$user   = wp_get_current_user();

		$_REQUEST = array(
			'_wpnonce' => wp_create_nonce( 'update-user_' . $user->ID ),
		);

		// Test a standard change scenario.
		$_POST = array(
			'ba_eas_author_slug' => 'assertion-2',
		);

		ba_eas_update_user_nicename( $errors, true, $user );
		$this->assertEquals( 'assertion-2', $user->user_nicename );
	}

	/**
	 * Test `ba_eas_update_user_nicename()` when the nicename remains unchanged.
	 *
	 * @since 1.6.0
	 *
	 * @covers ::ba_eas_update_user_nicename
	 */
	public function test_ba_eas_update_user_nicename_nicename_unchanged() {

		$errors = new WP_Error();
		$user   = wp_get_current_user();

		$_REQUEST = array(
			'_wpnonce' => wp_create_nonce( 'update-user_' . $user->ID ),
		);

		// Test that the nicename remains unchanged.
		$_POST = array(
			'ba_eas_author_slug' => 'mastersplinter',
		);

		$this->assertNull( ba_eas_update_user_nicename( $errors, true, $user ) );
		$this->assertEquals( 'mastersplinter', $user->user_nicename );
	}

	/**
	 * Test `ba_eas_update_user_nicename()` when the nicename remains unchanged,
	 * and iThemes force unique nicename is active.
	 *
	 * @since 1.6.0
	 *
	 * @covers ::ba_eas_update_user_nicename
	 */
	public function test_ba_eas_update_user_nicename_nicename_unchanged_itsec_force_unique_nicename() {
		ITSEC_Modules::$force_unique_nicename = true;

		$errors = new WP_Error();
		$user   = wp_get_current_user();

		$_REQUEST = array(
			'_wpnonce' => wp_create_nonce( 'update-user_' . $user->ID ),
		);

		// Test that the nicename remains unchanged.
		$_POST = array(
			'ba_eas_author_slug' => 'mastersplinter',
		);

		$this->assertNull( ba_eas_update_user_nicename( $errors, true, $user ) );
		$this->assertEquals( 'mastersplinter', $user->user_nicename );
	}

	/**
	 * Test `ba_eas_update_user_nicename()` when the nicename remains unchanged.
	 *
	 * @since 1.6.0
	 *
	 * @covers ::ba_eas_update_user_nicename
	 */
	public function test_ba_eas_update_user_nicename_custom() {

		$errors = new WP_Error();
		$user   = wp_get_current_user();

		$_REQUEST = array(
			'_wpnonce' => wp_create_nonce( 'update-user_' . $user->ID ),
		);

		// Test custom fields.
		$_POST = array(
			'ba_eas_author_slug'        => addslashes( '\c\u\s\t\o\m' ),
			'ba_eas_author_slug_custom' => 'assertion-1',
		);

		ba_eas_update_user_nicename( $errors, true, $user );
		$this->assertEquals( 'assertion-1', $user->user_nicename );
	}

	/**
	 * Test `ba_eas_update_user_nicename()` when we're not updating.
	 *
	 * @since 1.6.0
	 *
	 * @covers ::ba_eas_update_user_nicename
	 */
	public function test_ba_eas_update_user_nicename_not_updating() {
		$this->assertNull( ba_eas_update_user_nicename( '', false, '' ) );
	}

	/**
	 * Test `ba_eas_update_user_nicename()` with no valid user.
	 *
	 * @since 1.6.0
	 *
	 * @covers ::ba_eas_update_user_nicename
	 */
	public function test_ba_eas_update_user_nicename_no_valid_user() {
		$this->assertNull( ba_eas_update_user_nicename( '', true, '' ) );
	}

	/**
	 * Test `ba_eas_update_user_nicename()` when the current user can't update
	 * author slugs.
	 *
	 * @since 1.6.0
	 *
	 * @covers ::ba_eas_update_user_nicename
	 */
	public function test_ba_eas_update_user_nicename_when_user_cant_update() {
		$user = wp_get_current_user();
		add_filter( 'ba_eas_can_edit_author_slug', '__return_false' );
		$this->assertNull( ba_eas_update_user_nicename( '', true, $user ) );
		remove_filter( 'ba_eas_can_edit_author_slug', '__return_false' );
	}

	/**
	 * Test `ba_eas_update_user_nicename()` when the nonce is invalid.
	 *
	 * @since 1.6.0
	 *
	 * @covers ::ba_eas_update_user_nicename
	 * @expectedException WPDieException
	 */
	public function test_ba_eas_update_user_nicename_invalid_nonce() {
		$user = wp_get_current_user();
		$this->assertNull( ba_eas_update_user_nicename( '', true, $user ) );
	}

	/**
	 * Test `ba_eas_update_user_nicename()` when the passed nicename is blank.
	 *
	 * @since 1.6.0
	 *
	 * @covers ::ba_eas_update_user_nicename
	 */
	public function test_ba_eas_update_user_nicename_blank_author_slug() {

		$errors = new WP_Error();
		$user   = wp_get_current_user();

		// Set the nonce.
		$_REQUEST = array(
			'_wpnonce' => wp_create_nonce( 'update-user_' . $user->ID ),
		);

		// Set the nicename to empty.
		$_POST = array(
			'ba_eas_author_slug' => '',
		);

		$this->assertNull( ba_eas_update_user_nicename( $errors, true, $user ) );
		$this->assertEquals( 'mastersplinter', $user->user_nicename );
		$this->assertEquals( '<strong>ERROR</strong>: An author slug cannot be blank. Please try again.', $errors->get_error_message( 'user_nicename_empty' ) );
	}

	/**
	 * Test `ba_eas_update_user_nicename()` when the nicename contains
	 * characters other than alphanumeric, underscores, and hyphens.
	 *
	 * @since 1.6.0
	 *
	 * @covers ::ba_eas_update_user_nicename
	 */
	public function test_ba_eas_update_user_nicename_not_ascii() {

		$errors = new WP_Error();
		$user   = wp_get_current_user();

		// Set the nonce.
		$_REQUEST = array(
			'_wpnonce' => wp_create_nonce( 'update-user_' . $user->ID ),
		);

		// Set the nicename to something with invalid characters.
		$_POST = array(
			'ba_eas_author_slug' => '作者',
		);

		$this->assertNull( ba_eas_update_user_nicename( $errors, true, $user ) );
		$this->assertEquals( 'mastersplinter', $user->user_nicename );
		$this->assertEquals( '<strong>ERROR</strong>: An author slug can only contain alphanumeric characters, underscores (_) and dashes (-).', $errors->get_error_message( 'user_nicename_invalid_characters' ) );
	}

	/**
	 * Test `ba_eas_update_user_nicename()` when the nicename passes earlier
	 * checks, but has sanitized to empty. This is most likely to happen if the
	 * nicename is filtered.
	 *
	 * @since 1.6.0
	 *
	 * @covers ::ba_eas_update_user_nicename
	 */
	public function test_ba_eas_update_user_nicename_invalid_characters() {

		$errors = new WP_Error();
		$user   = wp_get_current_user();

		// Set the nonce.
		$_REQUEST = array(
			'_wpnonce' => wp_create_nonce( 'update-user_' . $user->ID ),
		);

		// Set the nicename to something with invalid characters.
		$_POST = array(
			'ba_eas_author_slug' => '@',
		);

		$this->assertNull( ba_eas_update_user_nicename( $errors, true, $user ) );
		$this->assertEquals( 'mastersplinter', $user->user_nicename );
		$this->assertEquals( '<strong>ERROR</strong>: That author slug appears to be invalid. Please try something different.', $errors->get_error_message( 'user_nicename_invalid' ) );
	}

	/**
	 * Test `ba_eas_update_user_nicename()` with a long author slug.
	 *
	 * @since 1.6.0
	 *
	 * @covers ::ba_eas_update_user_nicename
	 */
	public function test_ba_eas_update_user_nicename_long_nicename() {

		$errors = new WP_Error();
		$user   = wp_get_current_user();

		// Set the nonce.
		$_REQUEST = array(
			'_wpnonce' => wp_create_nonce( 'update-user_' . $user->ID ),
		);

		// Set the nicename to something with invalid characters.
		$_POST = array(
			'ba_eas_author_slug' => 'this-is-a-really-really-really-really-long-user-nicename',
		);

		$this->assertNull( ba_eas_update_user_nicename( $errors, true, $user ) );
		$this->assertEquals( 'mastersplinter', $user->user_nicename );
		$this->assertEquals( '<strong>ERROR</strong>: An author slug may not be longer than 50 characters.', $errors->get_error_message( 'user_nicename_too_long' ) );
	}

	/**
	 * Test `ba_eas_update_user_nicename()` when the nicename already exists.
	 *
	 * @since 1.6.0
	 *
	 * @covers ::ba_eas_update_user_nicename
	 */
	public function test_ba_eas_update_user_nicename_existing_nicename() {

		$errors = new WP_Error();
		$user   = wp_get_current_user();

		// Set the nonce.
		$_REQUEST = array(
			'_wpnonce' => wp_create_nonce( 'update-user_' . $user->ID ),
		);

		// Set the nicename to something existing.
		$_POST = array(
			'ba_eas_author_slug' => 'admin',
		);

		$this->assertNull( ba_eas_update_user_nicename( $errors, true, $user ) );
		$this->assertEquals( 'mastersplinter', $user->user_nicename );
		$this->assertEquals( '<strong>ERROR</strong>: The author slug, <strong><em>admin</em></strong>, already exists. Please try something different.', $errors->get_error_message( 'user_nicename_exists' ) );
	}

	/**
	 * Test `ba_eas_update_user_nicename()` when the nicename is filtered.
	 *
	 * @since 1.6.0
	 *
	 * @covers ::ba_eas_update_user_nicename
	 */
	public function test_ba_eas_update_user_nicename_filtered_nicename() {

		$errors = new WP_Error();
		$user   = wp_get_current_user();

		// Set the nonce.
		$_REQUEST = array(
			'_wpnonce' => wp_create_nonce( 'update-user_' . $user->ID ),
		);

		// Set the nicename to something existing.
		$_POST = array(
			'ba_eas_author_slug' => 'admin',
		);

		add_filter( 'ba_eas_pre_update_user_nicename', array( $this, 'user_nicename_filter' ) );
		$this->assertNull( ba_eas_update_user_nicename( $errors, true, $user ) );
		$this->assertEquals( 'test', $user->user_nicename );
		remove_filter( 'ba_eas_pre_update_user_nicename', array( $this, 'user_nicename_filter' ) );
	}

	/**
	 * Test `ba_eas_can_edit_author_slug()`.
	 *
	 * @since 1.1.0
	 *
	 * @covers ::ba_eas_can_edit_author_slug
	 */
	public function test_ba_eas_can_edit_author_slug() {
		$this->assertTrue( ba_eas_can_edit_author_slug() );

		wp_set_current_user( self::$old_user_id );
		$this->assertFalse( ba_eas_can_edit_author_slug() );
		wp_set_current_user( self::$user_id );

		add_filter( 'ba_eas_can_edit_author_slug', '__return_false' );
		$this->assertFalse( ba_eas_can_edit_author_slug() );
		remove_filter( 'ba_eas_can_edit_author_slug', '__return_false' );
	}

	/**
	 * Test `ba_eas_author_slug_column()`.
	 *
	 * @since 1.1.0
	 *
	 * @covers ::ba_eas_author_slug_column
	 */
	public function test_ba_eas_author_slug_column() {
		$this->assertArrayHasKey( 'ba-eas-author-slug', ba_eas_author_slug_column( array() ) );
	}

	/**
	 * Test `ba_eas_author_slug_custom_column()`.
	 *
	 * @since 1.1.0
	 *
	 * @covers ::ba_eas_author_slug_custom_column
	 */
	public function test_ba_eas_author_slug_custom_column() {
		$default = ba_eas_author_slug_custom_column( 'ninja', 'ninjas', self::$user_id );
		$this->assertEquals( 'ninja', $default );

		$default = ba_eas_author_slug_custom_column( 'ninja', 'ba-eas-author-slug', self::$user_id );
		$this->assertEquals( 'mastersplinter', $default );
	}

	/**
	 * Test `ba_eas_show_user_nicename_scripts()`.
	 *
	 * @since 1.1.0
	 *
	 * @covers ::ba_eas_show_user_nicename_scripts
	 */
	public function test_ba_eas_show_user_nicename_scripts() {

		// Invalid page.
		ba_eas_show_user_nicename_scripts( 'admin.php' );
		$this->assertFalse( isset( $GLOBALS['wp_scripts']->registered['edit-author-slug'] ) );

		// Valid page.
		ba_eas_show_user_nicename_scripts( 'profile.php' );
		$this->assertTrue( isset( $GLOBALS['wp_scripts']->registered['edit-author-slug'] ) );
	}

	/**
	 * Test `ba_eas_add_settings_menu()`.
	 *
	 * @since 1.1.0
	 *
	 * @covers ::ba_eas_add_settings_menu
	 */
	public function test_ba_eas_add_settings_menu() {
		ba_eas_add_settings_menu();

		$expected = get_option( 'siteurl' ) . '/wp-admin/options-general.php?page=edit-author-slug';

		$this->assertEquals( $expected, menu_page_url( 'edit-author-slug', false ) );
	}

	/**
	 * Test `ba_eas_settings_page_html()`.
	 *
	 * @since 1.1.0
	 *
	 * @covers ::ba_eas_settings_page_html
	 */
	public function test_ba_eas_settings_page_html() {
		ob_start();
		ba_eas_settings_page_html();
		$output = ob_get_clean();

		$this->assertContains( '<h1 id="edit-author-slug">Edit Author Slug Settings</h1>', $output );
	}

	/**
	 * Test `ba_eas_register_admin_settings()`.
	 *
	 * @since 1.1.0
	 *
	 * @covers ::ba_eas_register_admin_settings
	 */
	public function test_ba_eas_register_admin_settings() {
		global $wp_settings_sections, $wp_settings_fields;

		ba_eas_register_admin_settings();

		// Sections _ba_eas_bulk_auto_update.
		$this->assertEquals( 'ba_eas_author_base', $wp_settings_sections['edit-author-slug']['ba_eas_author_base']['id'] );
		$this->assertEquals( 'ba_eas_auto_update', $wp_settings_sections['edit-author-slug']['ba_eas_auto_update']['id'] );
		$this->assertEquals( 'ba_eas_bulk_update', $wp_settings_sections['edit-author-slug']['ba_eas_bulk_update']['id'] );

		// Fields.
		$this->assertEquals( '_ba_eas_author_base', $wp_settings_fields['edit-author-slug']['ba_eas_author_base']['_ba_eas_author_base']['id'] );
		$this->assertEquals( '_ba_eas_do_role_based', $wp_settings_fields['edit-author-slug']['ba_eas_author_base']['_ba_eas_do_role_based']['id'] );
		$this->assertEquals( '_ba_eas_role_slugs', $wp_settings_fields['edit-author-slug']['ba_eas_author_base']['_ba_eas_role_slugs']['id'] );
		$this->assertEquals( '_ba_eas_do_auto_update', $wp_settings_fields['edit-author-slug']['ba_eas_auto_update']['_ba_eas_do_auto_update']['id'] );
		$this->assertEquals( '_ba_eas_default_user_nicename', $wp_settings_fields['edit-author-slug']['ba_eas_auto_update']['_ba_eas_default_user_nicename']['id'] );
		$this->assertEquals( '_ba_eas_bulk_update', $wp_settings_fields['edit-author-slug']['ba_eas_bulk_update']['_ba_eas_bulk_update']['id'] );
		$this->assertEquals( '_ba_eas_bulk_update_structure', $wp_settings_fields['edit-author-slug']['ba_eas_bulk_update']['_ba_eas_bulk_update_structure']['id'] );
	}

	/**
	 * Test `ba_eas_admin_setting_callback_author_base_section()`.
	 *
	 * @since 1.1.0
	 *
	 * @covers ::ba_eas_admin_setting_callback_author_base_section
	 */
	public function test_ba_eas_admin_setting_callback_author_base_section() {
		ob_start();
		ba_eas_admin_setting_callback_author_base_section();
		$output = ob_get_clean();

		$this->assertContains( 'Change your author base to something more fun!', $output );
	}

	/**
	 * Test `ba_eas_admin_setting_callback_author_base()`.
	 *
	 * @since 1.1.0
	 *
	 * @covers ::ba_eas_admin_setting_callback_author_base
	 */
	public function test_ba_eas_admin_setting_callback_author_base() {
		ob_start();
		ba_eas_admin_setting_callback_author_base();
		$output = ob_get_clean();

		$input = '<input id="eas-author-base" name="_ba_eas_author_base" type="text" value="author" class="regular-text code" />';
		$label = '<em>Defaults to &#039;author&#039;</em>';
		$front = '<span class="eas-demo-author-base-front"></span>';

		$this->assertContains( $input, $output );
		$this->assertContains( $label, $output );
		$this->assertContains( $front, $output );
	}

	/**
	 * Test `ba_eas_admin_setting_callback_author_base()`.
	 *
	 * @since 1.5.0
	 *
	 * @covers ::ba_eas_admin_setting_callback_author_base
	 */
	public function test_ba_eas_admin_setting_callback_author_base_with_front() {

		$GLOBALS['wp_rewrite']->front = '/test/';
		ob_start();
		ba_eas_admin_setting_callback_author_base();
		$output = ob_get_clean();

		$input = '<input id="eas-author-base" name="_ba_eas_author_base" type="text" value="author" class="regular-text code" />';
		$label = '<em>Defaults to &#039;author&#039;</em>';
		$front = '<span class="eas-demo-author-base-front">test/</span>';

		$this->assertContains( $input, $output );
		$this->assertContains( $label, $output );
		$this->assertContains( $front, $output );
	}

	/**
	 * Test `ba_eas_admin_setting_callback_remove_front()`.
	 *
	 * @since 1.2.0
	 *
	 * @covers ::ba_eas_admin_setting_callback_remove_front
	 */
	public function test_ba_eas_admin_setting_callback_remove_front() {
		ob_start();
		ba_eas_admin_setting_callback_remove_front();
		$output = ob_get_clean();

		$this->assertContains( '<input name="_ba_eas_remove_front" id="eas-remove-front" value="1" type="checkbox" />', $output );
	}

	/**
	 * Test `ba_eas_admin_setting_callback_auto_update_section()`.
	 *
	 * @since 1.1.0
	 *
	 * @covers ::ba_eas_admin_setting_callback_auto_update_section
	 */
	public function test_ba_eas_admin_setting_callback_auto_update_section() {
		ob_start();
		ba_eas_admin_setting_callback_auto_update_section();
		$output = ob_get_clean();

		$this->assertContains( 'Allow Author Slugs to be automatically updated, and set the default Author Slug structure for users. Automatic updating will only occur when a user can&#039;t edit Author Slugs on their own.', $output );
	}

	/**
	 * Test `ba_eas_admin_setting_callback_do_role_based()`.
	 *
	 * @since 1.1.0
	 *
	 * @covers ::ba_eas_admin_setting_callback_do_role_based
	 */
	public function test_ba_eas_admin_setting_callback_do_role_based() {
		ob_start();
		ba_eas_admin_setting_callback_do_role_based();
		$output = ob_get_clean();

		$input = '<input class="eas-checkbox" name="_ba_eas_do_role_based" id="eas-do-role-based" value="1" type="checkbox" />';
		$label = 'Set user&#039;s Author Base according to their role.';

		$this->assertContains( $input, $output );
		$this->assertContains( $label, $output );
	}

	/**
	 * Test `ba_eas_admin_setting_callback_role_slugs()`.
	 *
	 * @since 1.1.0
	 *
	 * @covers ::ba_eas_admin_setting_callback_role_slugs
	 */
	public function test_ba_eas_admin_setting_callback_role_slugs() {

		// Add the ninja role.
		add_role( 'ninja', 'Ninja' );

		ob_start();
		ba_eas_admin_setting_callback_role_slugs();
		$output = ob_get_clean();

		$input = '<input name="_ba_eas_role_slugs[administrator][slug]" id="eas-role-slugs-administrator-slug" type="text" value="administrator" class="regular-text code" />';
		$label = '>Administrator</label';
		$this->assertContains( $input, $output );
		$this->assertContains( $label, $output );

		$input = 'name="_ba_eas_role_slugs[editor][slug]"';
		$label = '>Editor</label';
		$this->assertContains( $input, $output );
		$this->assertContains( $label, $output );

		$input = 'name="_ba_eas_role_slugs[contributor][slug]"';
		$label = '>Contributor</label';
		$this->assertContains( $input, $output );
		$this->assertContains( $label, $output );

		$input = 'name="_ba_eas_role_slugs[author][slug]"';
		$label = '>Author</label';
		$this->assertContains( $input, $output );
		$this->assertContains( $label, $output );

		$input = 'name="_ba_eas_role_slugs[subscriber][slug]"';
		$label = '>Subscriber</label';
		$this->assertContains( $input, $output );
		$this->assertContains( $label, $output );

		$input = 'name="_ba_eas_role_slugs[ninja][slug]"';
		$label = '>Ninja</label';
		$this->assertContains( $input, $output );
		$this->assertContains( $label, $output );
	}

	/**
	 * Test `ba_eas_admin_setting_callback_role_slugs()` when a role name is empty.
	 *
	 * @since 1.6.0
	 *
	 * @covers ::ba_eas_admin_setting_callback_role_slugs
	 */
	public function test_ba_eas_admin_setting_callback_role_slugs_role_doesnt_exist() {

		// Fill the role slugs property with a role that doesn't exist.
		ba_eas()->role_slugs = self::$default_role_slugs + array(
			'ninja' => array(
				'name' => 'Ninja',
				'slug' => 'ninja',
			),
		);

		ob_start();
		ba_eas_admin_setting_callback_role_slugs();
		$output = ob_get_clean();

		$input = 'name="_ba_eas_role_slugs[ninja][slug]"';
		$label = 'Ninja';
		$this->assertNotContains( $input, $output );
		$this->assertNotContains( $label, $output );
	}

	/**
	 * Test `ba_eas_admin_setting_callback_role_slugs()` when a role name is empty.
	 *
	 * @since 1.6.0
	 *
	 * @covers ::ba_eas_admin_setting_callback_role_slugs
	 */
	public function test_ba_eas_admin_setting_callback_role_slugs_empty_name() {

		// Empty the adminstrator role slug for testing.
		$role_slugs                          = self::$default_role_slugs;
		$role_slugs['administrator']['name'] = '';
		ba_eas()->role_slugs                 = $role_slugs;

		// Capture the output.
		ob_start();
		ba_eas_admin_setting_callback_role_slugs();
		$output = ob_get_clean();

		$input = 'name="_ba_eas_role_slugs[administrator][slug]"';
		$label = 'Administrator';
		$this->assertNotContains( $input, $output );
		$this->assertNotContains( $label, $output );
	}

	/**
	 * Test `ba_eas_admin_setting_callback_role_slugs()` when a role slug is empty.
	 *
	 * @since 1.6.0
	 *
	 * @covers ::ba_eas_admin_setting_callback_role_slugs
	 */
	public function test_ba_eas_admin_setting_callback_role_slugs_empty_slug() {

		// Empty the adminstrator role slug for testing.
		$role_slugs                          = self::$default_role_slugs;
		$role_slugs['administrator']['slug'] = '';
		ba_eas()->role_slugs                 = $role_slugs;

		// Capture the output.
		ob_start();
		ba_eas_admin_setting_callback_role_slugs();
		$output = ob_get_clean();

		$input = 'name="_ba_eas_role_slugs[administrator][slug]"';
		$label = 'Administrator';
		$this->assertContains( $input, $output );
		$this->assertContains( $label, $output );
	}

	/**
	 * Test `ba_eas_admin_setting_sanitize_callback_role_slugs()`.
	 *
	 * @since 1.1.0
	 *
	 * @covers ::ba_eas_admin_setting_sanitize_callback_role_slugs
	 */
	public function test_ba_eas_admin_setting_sanitize_callback_role_slugs() {

		// Get the sanitized role slugs.
		$actual = ba_eas_admin_setting_sanitize_callback_role_slugs( self::$default_role_slugs );

		$this->assertEqualSets( self::$default_role_slugs, $actual );
	}

	/**
	 * Test `ba_eas_admin_setting_sanitize_callback_role_slugs()` when a passed
	 * role doesn't exist.
	 *
	 * @since 1.5.0
	 *
	 * @covers ::ba_eas_admin_setting_sanitize_callback_role_slugs
	 */
	public function test_ba_eas_admin_setting_sanitize_callback_role_slugs_no_role() {

		// Add the `fake-role` role to the role slugs.
		$role_slugs                      = self::$default_role_slugs;
		$role_slugs['fake-role']['slug'] = 'fake-role';

		$actual = ba_eas_admin_setting_sanitize_callback_role_slugs( $role_slugs );

		$this->assertEqualSets( self::$default_role_slugs, $actual );
	}

	/**
	 * Test `ba_eas_admin_setting_sanitize_callback_role_slugs()` when the
	 * passed role slug is empty.
	 *
	 * @since 1.5.0
	 *
	 * @covers ::ba_eas_admin_setting_sanitize_callback_role_slugs
	 */
	public function test_ba_eas_admin_setting_sanitize_callback_role_slugs_empty_slug_passed() {

		// Empty the `administrator` role slug.
		$role_slugs                          = self::$default_role_slugs;
		$role_slugs['administrator']['slug'] = '';

		$actual = ba_eas_admin_setting_sanitize_callback_role_slugs( $role_slugs );

		$this->assertEqualSets( self::$default_role_slugs, $actual );
	}

	/**
	 * Test `ba_eas_admin_setting_sanitize_callback_role_slugs()` when no role
	 * slug can be found.
	 *
	 * @since 1.5.0
	 *
	 * @covers ::ba_eas_admin_setting_sanitize_callback_role_slugs
	 */
	public function test_ba_eas_admin_setting_sanitize_callback_role_slugs_no_role_slug_exists() {

		// Add a `ninja` role with no display name.
		add_role( 'ninja', '' );

		// Get the default role slugs.
		$expected = ba_eas_get_default_role_slugs();

		$actual = ba_eas_admin_setting_sanitize_callback_role_slugs( $expected );

		$this->assertEqualSets( $expected, $actual );
	}

	/**
	 * Test `ba_eas_admin_setting_callback_do_auto_update()`.
	 *
	 * @since 1.1.0
	 *
	 * @covers ::ba_eas_admin_setting_callback_do_auto_update
	 */
	public function test_ba_eas_admin_setting_callback_do_auto_update() {
		ob_start();
		ba_eas_admin_setting_callback_do_auto_update();
		$output = ob_get_clean();

		$input = '<input class="eas-checkbox" name="_ba_eas_do_auto_update" id="eas-do-auto-update" value="1" type="checkbox" />';
		$label = 'Automatically update Author Slug when a user updates their profile.';

		$this->assertContains( $input, $output );
		$this->assertContains( $label, $output );
	}

	/**
	 * Test `ba_eas_admin_setting_callback_default_user_nicename()`.
	 *
	 * @since 1.1.0
	 *
	 * @covers ::ba_eas_admin_setting_callback_default_user_nicename
	 */
	public function test_ba_eas_admin_setting_callback_default_user_nicename() {
		ob_start();
		ba_eas_admin_setting_callback_default_user_nicename();
		$output = ob_get_clean();

		$this->assertContains( '<select id="eas-default-user-nicename" name="_ba_eas_default_user_nicename">', $output );
	}

	/**
	 * Test `ba_eas_admin_setting_callback_default_user_nicename()`.
	 *
	 * @since 1.5.0
	 *
	 * @covers ::ba_eas_admin_setting_callback_default_user_nicename
	 */
	public function test_ba_eas_admin_setting_callback_default_user_nicename_no_default() {

		$old_default_user_nicename      = ba_eas()->default_user_nicename;
		ba_eas()->default_user_nicename = '';
		ob_start();
		ba_eas_admin_setting_callback_default_user_nicename();
		$output = ob_get_clean();

		$this->assertContains( '<select id="eas-default-user-nicename" name="_ba_eas_default_user_nicename">', $output );
		ba_eas()->default_user_nicename = $old_default_user_nicename;
	}

	/**
	 * Test `ba_eas_admin_setting_callback_bulk_update_section()`.
	 *
	 * @since 1.1.0
	 *
	 * @covers ::ba_eas_admin_setting_callback_bulk_update_section
	 */
	public function test_ba_eas_admin_setting_callback_bulk_update_section() {
		ob_start();
		ba_eas_admin_setting_callback_bulk_update_section();
		$output = ob_get_clean();

		$this->assertContains( 'Update all users at once based on the specified Author Slug structure.', $output );
	}

	/**
	 * Test `ba_eas_admin_setting_callback_bulk_update()`.
	 *
	 * @since 1.1.0
	 *
	 * @covers ::ba_eas_admin_setting_callback_bulk_update
	 */
	public function test_ba_eas_admin_setting_callback_bulk_update() {
		ob_start();
		ba_eas_admin_setting_callback_bulk_update();
		$output = ob_get_clean();

		$input = '<input class="eas-checkbox" name="_ba_eas_bulk_update" id="eas-bulk-update" value="1" type="checkbox" />';
		$label = 'Update all users according to the below Author Slug setting. This will only be run after clicking &quot;Save Changes&quot;.';

		$this->assertContains( $input, $output );
		$this->assertContains( $label, $output );
	}

	/**
	 * Test `ba_eas_admin_setting_callback_bulk_update_structure()`.
	 *
	 * @since 1.1.0
	 *
	 * @covers ::ba_eas_admin_setting_callback_bulk_update_structure
	 */
	public function test_ba_eas_admin_setting_callback_bulk_update_structure() {
		ob_start();
		ba_eas_admin_setting_callback_bulk_update_structure();
		$output = ob_get_clean();

		$this->assertContains( '<select id="eas-bulk-update-structure" name="_ba_eas_bulk_update_structure">', $output );
	}

	/**
	 * Test `ba_eas_admin_setting_callback_bulk_update_structure()` with no
	 * default nicename structure.
	 *
	 * @since 1.5.0
	 *
	 * @covers ::ba_eas_admin_setting_callback_bulk_update_structure
	 */
	public function test_ba_eas_admin_setting_callback_bulk_update_structure_no_default() {

		$old_default_user_nicename      = ba_eas()->default_user_nicename;
		ba_eas()->default_user_nicename = '';
		ob_start();
		ba_eas_admin_setting_callback_bulk_update_structure();
		$output = ob_get_clean();

		$this->assertContains( '<select id="eas-bulk-update-structure" name="_ba_eas_bulk_update_structure">', $output );
		ba_eas()->default_user_nicename = $old_default_user_nicename;
	}

	/**
	 * Test `ba_eas_add_settings_link()`.
	 *
	 * @since 1.1.0
	 *
	 * @covers ::ba_eas_add_settings_link
	 */
	public function test_ba_eas_add_settings_link() {
		$links = ba_eas_add_settings_link( array(), ba_eas()->plugin_basename );
		$this->assertEquals( '<a href="http://example.org/wp-admin/options-general.php?page=edit-author-slug">Settings</a>', $links[0] );
	}

	/**
	 * Test `ba_eas_default_user_nicename_options_list()`.
	 *
	 * @since 1.2.0
	 *
	 * @covers ::ba_eas_default_user_nicename_options_list
	 */
	public function test_ba_eas_default_user_nicename_options_list() {
		$options = array(
			'username'    => __( 'username (Default)', 'edit-author-slug' ),
			'nickname'    => __( 'nickname', 'edit-author-slug' ),
			'displayname' => __( 'displayname', 'edit-author-slug' ),
			'firstname'   => __( 'firstname', 'edit-author-slug' ),
			'lastname'    => __( 'lastname', 'edit-author-slug' ),
			'firstlast'   => __( 'firstname-lastname', 'edit-author-slug' ),
			'lastfirst'   => __( 'lastname-firstname', 'edit-author-slug' ),
			'userid'      => __( 'userid', 'edit-author-slug' ),
			'hash'        => __( 'hash', 'edit-author-slug' ),
		);

		$this->assertSame( $options, ba_eas_default_user_nicename_options_list() );
	}

	/**
	 * Test `ba_eas_settings_updated()`.
	 *
	 * @since 1.2.0
	 *
	 * @covers ::ba_eas_settings_updated
	 */
	public function test_ba_eas_settings_updated() {

		// Legitimate request.
		$_REQUEST = array(
			'_wpnonce'    => wp_create_nonce( 'edit-author-slug-options' ),
			'option_page' => 'edit-author-slug',
		);

		ba_eas_settings_updated();
		$this->assertSame( 1, did_action( 'ba_eas_settings_updated' ) );

		// Bad nonce.
		$_REQUEST = array(
			'_wpnonce'    => wp_create_nonce( 'edit-author-slug-settings' ),
			'option_page' => 'edit-author-slug',
		);

		ba_eas_settings_updated();
		$this->assertSame( 1, did_action( 'ba_eas_settings_updated' ) );

		// Wrong page.
		$_REQUEST = array(
			'_wpnonce'    => wp_create_nonce( 'edit-author-slug-options' ),
			'option_page' => 'test',
		);

		ba_eas_settings_updated();
		$this->assertSame( 1, did_action( 'ba_eas_settings_updated' ) );

	}

	/**
	 * Test `ba_eas_install()`.
	 *
	 * @since 1.1.0
	 *
	 * @covers ::ba_eas_install
	 */
	public function test_ba_eas_install() {
		$old_db_version              = ba_eas()->current_db_version;
		ba_eas()->current_db_version = 0;
		ba_eas_install();

		$this->assertEquals( ba_eas()->author_base, get_option( '_ba_eas_author_base' ) );
		$this->assertEquals( BA_Edit_Author_Slug::DB_VERSION, get_option( '_ba_eas_db_version' ) );
		$this->assertEquals( ba_eas()->do_auto_update, get_option( '_ba_eas_do_auto_update' ) );
		$this->assertEquals( ba_eas()->default_user_nicename, get_option( '_ba_eas_default_user_nicename' ) );
		$this->assertEquals( ba_eas()->do_role_based, get_option( '_ba_eas_do_role_based' ) );
		$this->assertEquals( ba_eas()->role_slugs, get_option( '_ba_eas_role_slugs' ) );

		ba_eas()->current_db_version = BA_Edit_Author_Slug::DB_VERSION;
		$this->assertNull( ba_eas_install() );
	}

	/**
	 * Test `ba_eas_upgrade()`.
	 *
	 * @since 1.1.0
	 *
	 * @covers ::ba_eas_upgrade
	 */
	public function test_ba_eas_upgrade() {
		$old_db_version              = ba_eas()->current_db_version;
		ba_eas()->current_db_version = 30;

		update_option( 'ba_edit_author_slug', 'test' );

		ba_eas_upgrade();

		$this->assertEquals( ba_eas()->author_base, get_option( '_ba_eas_author_base' ) );
		$this->assertEquals( BA_Edit_Author_Slug::DB_VERSION, get_option( '_ba_eas_db_version' ) );
		$this->assertEquals( ba_eas()->do_auto_update, get_option( '_ba_eas_do_auto_update' ) );
		$this->assertEquals( ba_eas()->default_user_nicename, get_option( '_ba_eas_default_user_nicename' ) );
		$this->assertEquals( ba_eas()->do_role_based, get_option( '_ba_eas_do_role_based' ) );
		$this->assertEquals( ba_eas()->role_slugs, get_option( '_ba_eas_role_slugs' ) );
		$this->assertEquals( 'test', get_option( '_ba_eas_old_options' ) );
		$this->assertEquals( false, get_option( 'ba_edit_author_slug' ) );
		$this->assertEquals( false, get_option( 'rewrite_rules' ) );

		ba_eas()->current_db_version = BA_Edit_Author_Slug::DB_VERSION;
		$this->assertNull( ba_eas_upgrade() );
	}
}
