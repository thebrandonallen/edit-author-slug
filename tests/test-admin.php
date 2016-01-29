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

		// masterplinter
		$this->assertContains( '<label title="mastersplinter">', $output );
		$this->assertContains( '<input type="radio" id="ba_eas_author_slug" name="ba_eas_author_slug" value="mastersplinter" autocapitalize="none" autocorrect="off" maxlength="50" checked=\'checked\'>', $output );
		$this->assertContains( '<span>mastersplinter</span>', $output );

		// master-splinter
		$this->assertContains( '<label title="master-splinter">', $output );
		$this->assertContains( '<input type="radio" id="ba_eas_author_slug" name="ba_eas_author_slug" value="master-splinter" autocapitalize="none" autocorrect="off" maxlength="50">', $output );
		$this->assertContains( '<span>master-splinter</span>', $output );

		// master
		$this->assertContains( '<label title="master">', $output );
		$this->assertContains( '<input type="radio" id="ba_eas_author_slug" name="ba_eas_author_slug" value="master" autocapitalize="none" autocorrect="off" maxlength="50">', $output );
		$this->assertContains( '<span>master</span>', $output );

		// splinter
		$this->assertContains( '<label title="splinter">', $output );
		$this->assertContains( '<input type="radio" id="ba_eas_author_slug" name="ba_eas_author_slug" value="splinter" autocapitalize="none" autocorrect="off" maxlength="50">', $output );
		$this->assertContains( '<span>splinter</span>', $output );

		// splinter-master
		$this->assertContains( '<label title="splinter-master">', $output );
		$this->assertContains( '<input type="radio" id="ba_eas_author_slug" name="ba_eas_author_slug" value="splinter-master" autocapitalize="none" autocorrect="off" maxlength="50">', $output );
		$this->assertContains( '<span>splinter-master</span>', $output );

		// userid
		$this->assertContains( '<label title="' . $this->new_current_user . '">', $output );
		$this->assertContains( '<input type="radio" id="ba_eas_author_slug" name="ba_eas_author_slug" value="' . $this->new_current_user . '" autocapitalize="none" autocorrect="off" maxlength="50">', $output );
		$this->assertContains( '<span>' . $this->new_current_user . '</span>', $output );

		// Custom
		$this->assertContains( '<label for="ba_eas_author_slug_custom_radio">', $output );
		$this->assertContains( '<input type="radio" id="ba_eas_author_slug_custom_radio" name="ba_eas_author_slug" value="\c\u\s\t\o\m" autocapitalize="none" autocorrect="off" maxlength="50">', $output );
		$this->assertContains( '<span class="screen-reader-text">Enter a custom author slug in the following field</span>', $output );
		$this->assertContains( '<label for="ba_eas_author_slug_custom" class="screen-reader-text">Custom author slug:</label>', $output );
		$this->assertContains( '<input type="text" name="ba_eas_author_slug_custom" id="ba_eas_author_slug_custom" value="mastersplinter" class="regular-text" />', $output );
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
	 * @covers ::ba_eas_show_user_nicename_scripts
	 */
	function test_ba_eas_show_user_nicename_scripts() {
		$this->markTestIncomplete();
	}

	/**
	 * @covers ::ba_eas_add_settings_menu
	 */
	function test_ba_eas_add_settings_menu() {
		$current_user = get_current_user_id();
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		update_option( 'siteurl', 'http://example.com' );

		ba_eas_add_settings_menu();

		$expected = 'http://example.com/wp-admin/options-general.php?page=edit-author-slug';

		$this->assertEquals( $expected, menu_page_url( 'edit-author-slug', false ) );

		wp_set_current_user( $current_user );
	}

	/**
	 * @covers ::ba_eas_settings_page_html
	 */
	function test_ba_eas_settings_page_html() {
		$this->markTestIncomplete();
	}

	/**
	 * @covers ::ba_eas_register_admin_settings
	 */
	function test_ba_eas_register_admin_settings() {
		global $wp_settings_sections, $wp_settings_fields;

		ba_eas_register_admin_settings();

		// Sections _ba_eas_bulk_auto_update
		$this->assertEquals( 'ba_eas_author_base', $wp_settings_sections['edit-author-slug']['ba_eas_author_base']['id'] );
		$this->assertEquals( 'ba_eas_auto_update', $wp_settings_sections['edit-author-slug']['ba_eas_auto_update']['id'] );
		$this->assertEquals( 'ba_eas_bulk_update', $wp_settings_sections['edit-author-slug']['ba_eas_bulk_update']['id'] );

		// Fields
		$this->assertEquals( '_ba_eas_author_base', $wp_settings_fields['edit-author-slug']['ba_eas_author_base']['_ba_eas_author_base']['id'] );
		$this->assertEquals( '_ba_eas_do_role_based', $wp_settings_fields['edit-author-slug']['ba_eas_author_base']['_ba_eas_do_role_based']['id'] );
		$this->assertEquals( '_ba_eas_role_slugs', $wp_settings_fields['edit-author-slug']['ba_eas_author_base']['_ba_eas_role_slugs']['id'] );
		$this->assertEquals( '_ba_eas_do_auto_update', $wp_settings_fields['edit-author-slug']['ba_eas_auto_update']['_ba_eas_do_auto_update']['id'] );
		$this->assertEquals( '_ba_eas_default_user_nicename', $wp_settings_fields['edit-author-slug']['ba_eas_auto_update']['_ba_eas_default_user_nicename']['id'] );
		$this->assertEquals( '_ba_eas_bulk_update', $wp_settings_fields['edit-author-slug']['ba_eas_bulk_update']['_ba_eas_bulk_update']['id'] );
		$this->assertEquals( '_ba_eas_bulk_update_structure', $wp_settings_fields['edit-author-slug']['ba_eas_bulk_update']['_ba_eas_bulk_update_structure']['id'] );
	}

	/**
	 * @covers ::ba_eas_admin_setting_callback_author_base_section
	 */
	function test_ba_eas_admin_setting_callback_author_base_section() {
		ob_start();
		ba_eas_admin_setting_callback_author_base_section();
		$output = ob_get_clean();

		$this->assertContains( 'Change your author base to something more fun!', $output );
	}

	/**
	 * @covers ::ba_eas_admin_setting_callback_auto_update_section
	 */
	function test_ba_eas_admin_setting_callback_auto_update_section() {
		ob_start();
		ba_eas_admin_setting_callback_auto_update_section();
		$output = ob_get_clean();

		$this->assertContains( "Allow Author Slugs to be automatically updated, and set the default Author Slug structure for users. Automatic updating will only occur when a user can&#039;t edit Author Slugs on their own.", $output );
	}

	/**
	 * @covers ::ba_eas_admin_setting_callback_author_base
	 */
	function test_ba_eas_admin_setting_callback_author_base() {
		ob_start();
		ba_eas_admin_setting_callback_author_base();
		$output = ob_get_clean();

		$input = '<input id="_ba_eas_author_base" name="_ba_eas_author_base" type="text" value="author" class="regular-text code" />';
		$label = "<em>Defaults to &#039;author&#039;</em>";

		$this->assertContains( $input, $output );
		$this->assertContains( $label, $output );
	}

	/**
	 * @covers ::ba_eas_admin_setting_sanitize_callback_author_base
	 *
	 * @expectedDeprecated ba_eas_admin_setting_sanitize_callback_author_base
	 */
	function test_ba_eas_admin_setting_sanitize_callback_author_base() {
		ba_eas_admin_setting_sanitize_callback_author_base();
	}

	/**
	 * @covers ::ba_eas_admin_setting_callback_do_role_based
	 */
	function test_ba_eas_admin_setting_callback_do_role_based() {
		ob_start();
		ba_eas_admin_setting_callback_do_role_based();
		$output = ob_get_clean();

		$input = '<input class="eas-checkbox" name="_ba_eas_do_role_based" id="_ba_eas_do_role_based" value="1" type="checkbox" />';
		$label = 'Set user&#039;s Author Base according to their role. (The above &quot;Author Base&quot; setting will be used as a fallback.)';

		$this->assertContains( $input, $output );
		$this->assertContains( $label, $output );
	}

	/**
	 * @covers ::ba_eas_admin_setting_callback_role_slugs
	 */
	function test_ba_eas_admin_setting_callback_role_slugs() {

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

		ba_eas()->role_slugs = array(
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
	}

	/**
	 * @covers ::ba_eas_admin_setting_sanitize_callback_role_slugs
	 */
	function test_ba_eas_admin_setting_sanitize_callback_role_slugs() {
		$this->markTestIncomplete();
	}

	/**
	 * @covers ::ba_eas_admin_setting_callback_do_auto_update
	 */
	function test_ba_eas_admin_setting_callback_do_auto_update() {
		ob_start();
		ba_eas_admin_setting_callback_do_auto_update();
		$output = ob_get_clean();

		$input = '<input class="eas-checkbox" name="_ba_eas_do_auto_update" id="_ba_eas_do_auto_update" value="1" type="checkbox" />';
		$label = 'Automatically update Author Slug when a user updates their profile.';

		$this->assertContains( $input, $output );
		$this->assertContains( $label, $output );
	}

	/**
	 * @covers ::ba_eas_admin_setting_callback_default_user_nicename
	 */
	function test_ba_eas_admin_setting_callback_default_user_nicename() {
		$this->markTestIncomplete();
	}

	/**
	 * @covers ::ba_eas_admin_setting_callback_bulk_update_section
	 */
	function test_ba_eas_admin_setting_callback_bulk_update_section() {
		ob_start();
		ba_eas_admin_setting_callback_bulk_update_section();
		$output = ob_get_clean();

		$this->assertContains( 'Update all users at once based on the specified Author Slug structure.', $output );
	}

	/**
	 * @covers ::ba_eas_admin_setting_callback_bulk_update
	 */
	function test_ba_eas_admin_setting_callback_bulk_update() {
		ob_start();
		ba_eas_admin_setting_callback_bulk_update();
		$output = ob_get_clean();

		$input = '<input class="eas-checkbox" name="_ba_eas_bulk_update" id="_ba_eas_bulk_update" value="1" type="checkbox" />';
		$label = 'Update all users according to the below Author Slug setting. This will only be run after clicking &quot;Save Changes&quot;.';

		$this->assertContains( $input, $output );
		$this->assertContains( $label, $output );
	}

	/**
	 * @covers ::ba_eas_admin_setting_callback_bulk_update_structure
	 */
	function test_ba_eas_admin_setting_callback_bulk_update_structure() {
		$this->markTestIncomplete();
	}

	/**
	 * @covers ::ba_eas_add_settings_link
	 */
	function test_ba_eas_add_settings_link() {
		$this->markTestIncomplete();
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
