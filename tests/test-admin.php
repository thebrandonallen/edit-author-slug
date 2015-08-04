<?php

class BA_EAS_Tests_Admin extends WP_UnitTestCase {
	protected $old_current_user = 0;

	public function setUp() {
		parent::setUp();

		$this->old_current_user = get_current_user_id();
		$this->new_current_user = $this->factory->user->create( array(
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

		$this->assertContains( '<label title="user-1"><input type="radio" id="ba_eas_author_slug" name="ba_eas_author_slug" value="user-1" checked=\'checked\'> <span>user-1</span></label>', $output );
		$this->assertContains( '<label title="master-splinter"><input type="radio" id="ba_eas_author_slug" name="ba_eas_author_slug" value="master-splinter"> <span>master-splinter</span></label>', $output );
		$this->assertContains( '<label title="master"><input type="radio" id="ba_eas_author_slug" name="ba_eas_author_slug" value="master"> <span>master</span></label>', $output );
		$this->assertContains( '<label title="splinter"><input type="radio" id="ba_eas_author_slug" name="ba_eas_author_slug" value="splinter"> <span>splinter</span></label>', $output );
		$this->assertContains( '<label title="splinter-master"><input type="radio" id="ba_eas_author_slug" name="ba_eas_author_slug" value="splinter-master"> <span>splinter-master</span></label>', $output );
		$this->assertContains( '<label title="user-1"><input type="radio" id="ba_eas_author_slug_custom" name="ba_eas_author_slug" value="\c\u\s\t\o\m"> <span>Custom: </span></label> <input type="text" name="ba_eas_author_slug_custom" id="ba_eas_author_slug_custom" value="user-1" class="regular-text" />', $output );
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

		unset( $errors->errors['ba_edit_author_slug'] );
		unset( $errors->error_data['ba_edit_author_slug'] );

		$_POST = array(
			'ba_eas_author_slug' => '@',
		);

		$this->assertNull( ba_eas_update_user_nicename( $errors, true, $user ) );
		$this->assertEquals( 'assertion-2', $user->user_nicename );
		$this->assertEquals( '<strong>ERROR</strong>: That author slug appears to be invalid. Please try something different.', $errors->get_error_message( 'ba_edit_author_slug' ) );

		unset( $errors->errors['ba_edit_author_slug'] );
		unset( $errors->error_data['ba_edit_author_slug'] );

		$_POST = array(
			'ba_eas_author_slug' => 'admin',
		);

		$this->assertNull( ba_eas_update_user_nicename( $errors, true, $user ) );
		$this->assertEquals( 'assertion-2', $user->user_nicename );
		$this->assertEquals( '<strong>ERROR</strong>: The author slug, <strong><em>admin</em></strong>, already exists. Please try something different.', $errors->get_error_message( 'ba_edit_author_slug' ) );

		unset( $errors->errors['ba_edit_author_slug'] );
		unset( $errors->error_data['ba_edit_author_slug'] );
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
}
