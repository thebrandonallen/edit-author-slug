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
	 * The old user id.
	 *
	 * @var int
	 */
	protected $old_current_user = 0;

	/**
	 * The admin `setUp` method.
	 *
	 * Sets up up some users, and loads the admin.
	 */
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

	/**
	 * The admin `tearDown` method.
	 *
	 * Resets the current user and globals.
	 */
	public function tearDown() {
		parent::tearDown();
		wp_set_current_user( $this->old_current_user );
		$GLOBALS['wp_rewrite']->author_base = 'author';
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
	 * @covers ::ba_eas_show_user_nicename
	 */
	public function test_ba_eas_show_user_nicename() {
		add_filter( 'ba_eas_can_edit_author_slug', '__return_false' );
		$this->assertEquals( '', ba_eas_show_user_nicename( wp_get_current_user() ) );
		remove_filter( 'ba_eas_can_edit_author_slug', '__return_false' );

		ob_start();
		ba_eas_show_user_nicename( wp_get_current_user() );
		$output = ob_get_clean();

		// Test for `masterplinter`.
		$this->assertContains( '<label title="mastersplinter">', $output );
		$this->assertContains( '<input type="radio" id="ba_eas_author_slug" name="ba_eas_author_slug" value="mastersplinter" autocapitalize="none" autocorrect="off" maxlength="50" checked=\'checked\'>', $output );
		$this->assertContains( '<span>mastersplinter</span>', $output );

		// Test for `master-splinter`.
		$this->assertContains( '<label title="master-splinter">', $output );
		$this->assertContains( '<input type="radio" id="ba_eas_author_slug" name="ba_eas_author_slug" value="master-splinter" autocapitalize="none" autocorrect="off" maxlength="50">', $output );
		$this->assertContains( '<span>master-splinter</span>', $output );

		// Test for `master`.
		$this->assertContains( '<label title="master">', $output );
		$this->assertContains( '<input type="radio" id="ba_eas_author_slug" name="ba_eas_author_slug" value="master" autocapitalize="none" autocorrect="off" maxlength="50">', $output );
		$this->assertContains( '<span>master</span>', $output );

		// Test for `splinter`.
		$this->assertContains( '<label title="splinter">', $output );
		$this->assertContains( '<input type="radio" id="ba_eas_author_slug" name="ba_eas_author_slug" value="splinter" autocapitalize="none" autocorrect="off" maxlength="50">', $output );
		$this->assertContains( '<span>splinter</span>', $output );

		// Test for `splinter-master`.
		$this->assertContains( '<label title="splinter-master">', $output );
		$this->assertContains( '<input type="radio" id="ba_eas_author_slug" name="ba_eas_author_slug" value="splinter-master" autocapitalize="none" autocorrect="off" maxlength="50">', $output );
		$this->assertContains( '<span>splinter-master</span>', $output );

		// Test for `userid`.
		$this->assertContains( '<label title="' . $this->new_current_user . '">', $output );
		$this->assertContains( '<input type="radio" id="ba_eas_author_slug" name="ba_eas_author_slug" value="' . $this->new_current_user . '" autocapitalize="none" autocorrect="off" maxlength="50">', $output );
		$this->assertContains( '<span>' . $this->new_current_user . '</span>', $output );

		// Test custom author slug.
		$this->assertContains( '<label for="ba_eas_author_slug_custom_radio">', $output );
		$this->assertContains( '<input type="radio" id="ba_eas_author_slug_custom_radio" name="ba_eas_author_slug" value="\c\u\s\t\o\m" autocapitalize="none" autocorrect="off" maxlength="50">', $output );
		$this->assertContains( '<span class="screen-reader-text">Enter a custom author slug in the following field</span>', $output );
		$this->assertContains( '<label for="ba_eas_author_slug_custom" class="screen-reader-text">Custom author slug:</label>', $output );
		$this->assertContains( '<input type="text" name="ba_eas_author_slug_custom" id="ba_eas_author_slug_custom" value="mastersplinter" class="regular-text" />', $output );
	}

	/**
	 * Test `ba_eas_update_user_nicename()`.
	 *
	 * @covers ::ba_eas_update_user_nicename
	 */
	public function test_ba_eas_update_user_nicename() {
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
		$this->assertEquals( '<strong>ERROR</strong>: An author slug cannot be blank. Please try again.', $errors->get_error_message( 'user_nicename_empty' ) );

		unset( $errors );
		$errors = new WP_Error;

		$_POST = array(
			'ba_eas_author_slug' => '作者',
		);

		$this->assertNull( ba_eas_update_user_nicename( $errors, true, $user ) );
		$this->assertEquals( 'assertion-2', $user->user_nicename );
		$this->assertEquals( '<strong>ERROR</strong>: An author slug can only contain alphanumeric characters, underscores (_) and dashes (-).', $errors->get_error_message( 'user_nicename_invalid_characters' ) );

		unset( $errors );
		$errors = new WP_Error;

		$_POST = array(
			'ba_eas_author_slug' => '@',
		);

		$this->assertNull( ba_eas_update_user_nicename( $errors, true, $user ) );
		$this->assertEquals( 'assertion-2', $user->user_nicename );
		$this->assertEquals( '<strong>ERROR</strong>: That author slug appears to be invalid. Please try something different.', $errors->get_error_message( 'user_nicename_invalid' ) );

		unset( $errors );
		$errors = new WP_Error;

		$_POST = array(
			'ba_eas_author_slug' => 'this-is-a-really-really-really-really-long-user-nicename',
		);

		$this->assertNull( ba_eas_update_user_nicename( $errors, true, $user ) );
		$this->assertEquals( 'assertion-2', $user->user_nicename );
		$this->assertEquals( '<strong>ERROR</strong>: An author slug may not be longer than 50 characters.', $errors->get_error_message( 'user_nicename_too_long' ) );

		unset( $errors );
		$errors = new WP_Error;

		$_POST = array(
			'ba_eas_author_slug' => 'admin',
		);

		$this->assertNull( ba_eas_update_user_nicename( $errors, true, $user ) );
		$this->assertEquals( 'assertion-2', $user->user_nicename );
		$this->assertEquals( '<strong>ERROR</strong>: The author slug, <strong><em>admin</em></strong>, already exists. Please try something different.', $errors->get_error_message( 'user_nicename_exists' ) );

		unset( $errors );
		$errors = new WP_Error;

		$_POST = array(
			'ba_eas_author_slug' => 'admin',
		);

		add_filter( 'ba_eas_pre_update_user_nicename', array( $this, 'user_nicename_filter' ) );
		$this->assertNull( ba_eas_update_user_nicename( $errors, true, $user ) );
		$this->assertEquals( 'test', $user->user_nicename );
		remove_filter( 'ba_eas_pre_update_user_nicename', array( $this, 'user_nicename_filter' ) );

		unset( $errors );
	}

	/**
	 * Test `ba_eas_can_edit_author_slug()`.
	 *
	 * @covers ::ba_eas_can_edit_author_slug
	 */
	public function test_ba_eas_can_edit_author_slug() {
		$this->assertTrue( ba_eas_can_edit_author_slug() );

		wp_set_current_user( $this->old_current_user );
		$this->assertFalse( ba_eas_can_edit_author_slug() );
		wp_set_current_user( $this->new_current_user );

		add_filter( 'ba_eas_can_edit_author_slug', '__return_false' );
		$this->assertFalse( ba_eas_can_edit_author_slug() );
		remove_filter( 'ba_eas_can_edit_author_slug', '__return_false' );
	}

	/**
	 * Test `ba_eas_author_slug_column()`.
	 *
	 * @covers ::ba_eas_author_slug_column
	 */
	public function test_ba_eas_author_slug_column() {
		$this->assertArrayHasKey( 'ba-eas-author-slug', ba_eas_author_slug_column( array() ) );
	}

	/**
	 * Test `ba_eas_author_slug_custom_column()`.
	 *
	 * @covers ::ba_eas_author_slug_custom_column
	 */
	public function test_ba_eas_author_slug_custom_column() {
		$default = ba_eas_author_slug_custom_column( 'ninja', 'ninjas', $this->new_current_user );
		$this->assertEquals( 'ninja', $default );

		$default = ba_eas_author_slug_custom_column( 'ninja', 'ba-eas-author-slug', $this->new_current_user );
		$this->assertEquals( 'mastersplinter', $default );
	}

	/**
	 * Test `ba_eas_show_user_nicename_scripts()`.
	 *
	 * @covers ::ba_eas_show_user_nicename_scripts
	 */
	public function test_ba_eas_show_user_nicename_scripts() {

		// Valid page.
		ba_eas_show_user_nicename_scripts( 'profile.php' );
		$this->assertNotEmpty( $GLOBALS['wp_scripts']->registered['edit-author-slug'] );

		// Invalid page.
		ba_eas_show_user_nicename_scripts( 'admin.php' );
		$this->assertNotEmpty( $GLOBALS['wp_scripts']->registered['edit-author-slug'] );
	}

	/**
	 * Test `ba_eas_add_settings_menu()`.
	 *
	 * @covers ::ba_eas_add_settings_menu
	 */
	public function test_ba_eas_add_settings_menu() {
		$user_id = $this->factory->user->create( array(
			'role' => 'administrator',
		) );
		wp_set_current_user( $user_id );
		update_option( 'siteurl', 'http://example.com' );

		ba_eas_add_settings_menu();

		$expected = 'http://example.com/wp-admin/options-general.php?page=edit-author-slug';

		$this->assertEquals( $expected, menu_page_url( 'edit-author-slug', false ) );
	}

	/**
	 * Test `ba_eas_settings_page_html()`.
	 *
	 * @covers ::ba_eas_settings_page_html
	 */
	public function test_ba_eas_settings_page_html() {
		ob_start();
		ba_eas_settings_page_html();
		$output = ob_get_clean();

		$this->assertContains( '<h1>Edit Author Slug Settings</h1>', $output );
	}

	/**
	 * Test `ba_eas_register_admin_settings()`.
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
	 * @covers ::ba_eas_admin_setting_callback_author_base
	 */
	public function test_ba_eas_admin_setting_callback_author_base() {
		ob_start();
		ba_eas_admin_setting_callback_author_base();
		$output = ob_get_clean();

		$input = '<input id="_ba_eas_author_base" name="_ba_eas_author_base" type="text" value="author" class="regular-text code" />';
		$label = '<em>Defaults to &#039;author&#039;</em>';
		$front = '<span class="eas-demo-author-base-front"></span>';

		$this->assertContains( $input, $output );
		$this->assertContains( $label, $output );
		$this->assertContains( $front, $output );
	}

	/**
	 * Test `ba_eas_admin_setting_callback_author_base()`.
	 *
	 * @covers ::ba_eas_admin_setting_callback_author_base
	 */
	public function test_ba_eas_admin_setting_callback_author_base_with_front() {

		$GLOBALS['wp_rewrite']->front = '/test/';
		ob_start();
		ba_eas_admin_setting_callback_author_base();
		$output = ob_get_clean();

		$input = '<input id="_ba_eas_author_base" name="_ba_eas_author_base" type="text" value="author" class="regular-text code" />';
		$label = '<em>Defaults to &#039;author&#039;</em>';
		$front = '<span class="eas-demo-author-base-front">test/</span>';

		$this->assertContains( $input, $output );
		$this->assertContains( $label, $output );
		$this->assertContains( $front, $output );
	}

	/**
	 * Test `ba_eas_admin_setting_callback_remove_front()`.
	 *
	 * @covers ::ba_eas_admin_setting_callback_remove_front
	 */
	public function test_ba_eas_admin_setting_callback_remove_front() {
		ob_start();
		ba_eas_admin_setting_callback_remove_front();
		$output = ob_get_clean();

		$this->assertContains( '<input name="_ba_eas_remove_front" id="_ba_eas_remove_front" value="1" type="checkbox" />', $output );
	}

	/**
	 * Test `ba_eas_admin_setting_callback_auto_update_section()`.
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
	 * @covers ::ba_eas_admin_setting_callback_do_role_based
	 */
	public function test_ba_eas_admin_setting_callback_do_role_based() {
		ob_start();
		ba_eas_admin_setting_callback_do_role_based();
		$output = ob_get_clean();

		$input = '<input class="eas-checkbox" name="_ba_eas_do_role_based" id="_ba_eas_do_role_based" value="1" type="checkbox" />';
		$label = 'Set user&#039;s Author Base according to their role.';

		$this->assertContains( $input, $output );
		$this->assertContains( $label, $output );
	}

	/**
	 * Test `ba_eas_admin_setting_callback_role_slugs()`.
	 *
	 * @covers ::ba_eas_admin_setting_callback_role_slugs
	 */
	public function test_ba_eas_admin_setting_callback_role_slugs() {

		// Add the ninja role.
		add_role( 'ninja', 'Ninja' );

		ob_start();
		ba_eas_admin_setting_callback_role_slugs();
		$output = ob_get_clean();

		$input = '<input name="_ba_eas_role_slugs[administrator][slug]" id="_ba_eas_role_slugs[administrator][slug]" type="text" value="administrator" class="regular-text code" />';
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

		$this->eas->role_slugs = array(
			'administrator' => array(
				'name' => 'Administrator',
				'slug' => 'administrator',
			),
			'editor' => array(
				'name' => 'Editor',
				'slug' => 'editor',
			),
			'contributor' => array(
				'name' => 'Contributor',
				'slug' => 'contributor',
			),
			'author' => array(
				'name' => 'Author',
				'slug' => 'author',
			),
			'subscriber' => array(
				'name' => 'Subscriber',
				'slug' => 'subscriber',
			),
			'ninja' => array(
				'name' => 'Ninja',
				'slug' => 'ninja',
			),
		);

		// Remove the ninja role.
		remove_role( 'ninja' );

		ob_start();
		ba_eas_admin_setting_callback_role_slugs();
		$output = ob_get_clean();

		$input = 'name="_ba_eas_role_slugs[ninja][slug]"';
		$label = 'Ninja';
		$this->assertNotContains( $input, $output );
		$this->assertNotContains( $label, $output );

		// Empty name.
		$role_slugs = $this->eas->role_slugs;
		$role_slugs['administrator']['name'] = '';
		$this->eas->role_slugs = $role_slugs;
		ob_start();
		ba_eas_admin_setting_callback_role_slugs();
		$output = ob_get_clean();
		$role_slugs['administrator']['name'] = 'Administrator';
		$this->eas->role_slugs = $role_slugs;

		$input = 'name="_ba_eas_role_slugs[administrator][slug]"';
		$label = 'Administrator';
		$this->assertNotContains( $input, $output );
		$this->assertNotContains( $label, $output );

		// Empty slug.
		$role_slugs = $this->eas->role_slugs;
		$role_slugs['administrator']['slug'] = '';
		$this->eas->role_slugs = $role_slugs;
		ob_start();
		ba_eas_admin_setting_callback_role_slugs();
		$output = ob_get_clean();
		$role_slugs['administrator']['slug'] = 'Administrator';
		$this->eas->role_slugs = $role_slugs;

		$input = 'name="_ba_eas_role_slugs[administrator][slug]"';
		$label = 'Administrator';
		$this->assertContains( $input, $output );
		$this->assertContains( $label, $output );
	}

	/**
	 * Test `ba_eas_admin_setting_sanitize_callback_role_slugs()`.
	 *
	 * @covers ::ba_eas_admin_setting_sanitize_callback_role_slugs
	 */
	public function test_ba_eas_admin_setting_sanitize_callback_role_slugs() {
		$this->markTestIncomplete();
	}

	/**
	 * Test `ba_eas_admin_setting_callback_do_auto_update()`.
	 *
	 * @covers ::ba_eas_admin_setting_callback_do_auto_update
	 */
	public function test_ba_eas_admin_setting_callback_do_auto_update() {
		ob_start();
		ba_eas_admin_setting_callback_do_auto_update();
		$output = ob_get_clean();

		$input = '<input class="eas-checkbox" name="_ba_eas_do_auto_update" id="_ba_eas_do_auto_update" value="1" type="checkbox" />';
		$label = 'Automatically update Author Slug when a user updates their profile.';

		$this->assertContains( $input, $output );
		$this->assertContains( $label, $output );
	}

	/**
	 * Test `ba_eas_admin_setting_callback_default_user_nicename()`.
	 *
	 * @covers ::ba_eas_admin_setting_callback_default_user_nicename
	 */
	public function test_ba_eas_admin_setting_callback_default_user_nicename() {
		ob_start();
		ba_eas_admin_setting_callback_default_user_nicename();
		$output = ob_get_clean();

		$this->assertContains( '<select id="_ba_eas_default_user_nicename" name="_ba_eas_default_user_nicename">', $output );
	}

	/**
	 * Test `ba_eas_admin_setting_callback_bulk_update_section()`.
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
	 * @covers ::ba_eas_admin_setting_callback_bulk_update
	 */
	public function test_ba_eas_admin_setting_callback_bulk_update() {
		ob_start();
		ba_eas_admin_setting_callback_bulk_update();
		$output = ob_get_clean();

		$input = '<input class="eas-checkbox" name="_ba_eas_bulk_update" id="_ba_eas_bulk_update" value="1" type="checkbox" />';
		$label = 'Update all users according to the below Author Slug setting. This will only be run after clicking &quot;Save Changes&quot;.';

		$this->assertContains( $input, $output );
		$this->assertContains( $label, $output );
	}

	/**
	 * Test `ba_eas_admin_setting_callback_bulk_update_structure()`.
	 *
	 * @covers ::ba_eas_admin_setting_callback_bulk_update_structure
	 */
	public function test_ba_eas_admin_setting_callback_bulk_update_structure() {
		ob_start();
		ba_eas_admin_setting_callback_bulk_update_structure();
		$output = ob_get_clean();

		$this->assertContains( '<select id="_ba_eas_bulk_update_structure" name="_ba_eas_bulk_update_structure">', $output );
	}

	/**
	 * Test `ba_eas_add_settings_link()`.
	 *
	 * @covers ::ba_eas_add_settings_link
	 */
	public function test_ba_eas_add_settings_link() {
		$links = ba_eas_add_settings_link( array(), $this->eas->plugin_basename );
		$this->assertEquals( '<a href="http://example.org/wp-admin/options-general.php?page=edit-author-slug">Settings</a>', $links[0] );
	}

	/**
	 * Test `ba_eas_default_user_nicename_options_list()`.
	 *
	 * @covers ::ba_eas_default_user_nicename_options_list
	 */
	public function test_ba_eas_default_user_nicename_options_list() {
		$options = array(
			'username'    => __( 'username (Default)',    'edit-author-slug' ),
			'nickname'    => __( 'nickname',              'edit-author-slug' ),
			'displayname' => __( 'displayname',           'edit-author-slug' ),
			'firstname'   => __( 'firstname',             'edit-author-slug' ),
			'lastname'    => __( 'lastname',              'edit-author-slug' ),
			'firstlast'   => __( 'firstname-lastname',    'edit-author-slug' ),
			'lastfirst'   => __( 'lastname-firstname',    'edit-author-slug' ),
			'userid'      => __( 'userid (Experimental)', 'edit-author-slug' ),
		);

		$this->assertSame( $options, ba_eas_default_user_nicename_options_list() );
	}

	/**
	 * Test `ba_eas_settings_updated()`.
	 *
	 * @covers ::ba_eas_settings_updated
	 */
	public function test_ba_eas_settings_updated() {

		// Legitimate request.
		$_REQUEST = array(
			'_wpnonce' => wp_create_nonce( 'edit-author-slug-options' ),
			'option_page' => 'edit-author-slug',
		);

		ba_eas_settings_updated();
		$this->assertSame( 1, did_action( 'ba_eas_settings_updated' ) );

		// Bad nonce.
		$_REQUEST = array(
			'_wpnonce' => wp_create_nonce( 'edit-author-slug-settings' ),
			'option_page' => 'edit-author-slug',
		);

		ba_eas_settings_updated();
		$this->assertSame( 1, did_action( 'ba_eas_settings_updated' ) );

		// Wrong page.
		$_REQUEST = array(
			'_wpnonce' => wp_create_nonce( 'edit-author-slug-options' ),
			'option_page' => 'test',
		);

		ba_eas_settings_updated();
		$this->assertSame( 1, did_action( 'ba_eas_settings_updated' ) );

	}

	/**
	 * Test `ba_eas_install()`.
	 *
	 * @covers ::ba_eas_install
	 */
	public function test_ba_eas_install() {
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
	 * Test `ba_eas_upgrade()`.
	 *
	 * @covers ::ba_eas_upgrade
	 */
	public function test_ba_eas_upgrade() {
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
