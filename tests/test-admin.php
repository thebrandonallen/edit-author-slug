<?php

class BA_EAS_Tests_Admin extends WP_UnitTestCase {
	protected $old_current_user = 0;

	public function setUp() {
		parent::setUp();

		$this->old_current_user = get_current_user_id();
		$this->new_current_user = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $this->new_current_user );

		$this->eas = ba_eas();

		require_once( $this->eas->plugin_dir . 'includes/admin.php' );
	}

	public function tearDown() {
		parent::tearDown();
		wp_set_current_user( $this->old_current_user );
	}

	function test_ba_eas_can_edit_author_slug() {
		$this->assertTrue( ba_eas_can_edit_author_slug() );

		wp_set_current_user( $this->old_current_user );
		$this->assertFalse( ba_eas_can_edit_author_slug() );
		wp_set_current_user( $this->new_current_user );

		add_filter( 'ba_eas_can_edit_author_slug', '__return_false' );
		$this->assertFalse( ba_eas_can_edit_author_slug() );
		remove_filter( 'ba_eas_can_edit_author_slug', '__return_false', 10 );
	}
}
