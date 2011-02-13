<?php

/**
 * Edit Author Slug Admin Functions
 *
 * @package Edit Author Slug
 * @subpackage Administration
 *
 * @author Brandon Allen
 */

/**
 * Display Author slug edit field on User/Profile edit page.
 *
 * Displays the Author slug edit field on User/Profile edit page.
 * Runs with the 'show_user_profile' and 'edit_user_profile' actions.
 *
 * @since 0.1.0
 *
 * @param object $user User data object
 * @uses current_user_can() To hide from unauthorized users.
 * @uses esc_html_e() To make sure we're safe to display
 */
function ba_eas_show_user_nicename( $user ) {
	if ( current_user_can( 'edit_users' ) || ( current_user_can( 'edit_author_slug' ) && IS_PROFILE_PAGE ) ) : ?>

	<h3><?php esc_html_e( 'Edit Author Slug', 'edit-author-slug' ) ?></h3>
	<table class="form-table">
		<tbody><tr>
			<th><label for="ba-edit-author-slug"><?php esc_html_e( 'Author Slug', 'edit-author-slug' ) ?></label></th>
			<td>
				<input type="text" name="ba-edit-author-slug" id="ba-edit-author-slug" value="<?php echo esc_attr( $user->user_nicename ); ?>" class="regular-text" /><br />
				<span class="description"><?php esc_html_e( "ie. - 'user-name', 'firstname-lastname', or 'master-ninja'", 'edit-author-slug' ) ?></span>
			</td>
		</tr></tbody>
	</table>
	<?php endif;
}

/**
 * Update user_nicename for a given user.
 *
 * Runs with 'personal_options_update' and 'edit_user_profile_update' actions,
 * and updates the user_nicename.
 *
 * @since 0.1.0
 *
 * @global $wpdb WordPress database object for queries
 * @uses check_admin_referer() To verify the nonce and check referer
 * @uses current_user_can() To prevent unauthorized users from saving.
 * @uses get_userdata() To get the user data
 * @uses sanitize_title() Used to sanitize user_nicename
 * @uses wp_update_user() Update to new user_nicename
 * @uses wp_cache_delete() To delete the 'userslugs' cache for old nicename
 */
function ba_eas_update_user_nicename( $user_id = 0 ) {
	global $wpdb;

	$user_id = (int) $user_id;

	// Check the nonce
	check_admin_referer( 'update-user_' . $user_id );

	// Make sure the user is allowed to do this
	if ( ! current_user_can( 'edit_users' ) || ! current_user_can( 'edit_author_slug' ) )
		return false;

	// User, tell me a little about yourself.
	$userdata	 = get_userdata( $user_id );

	// Do we have an author slug?
	$author_slug = isset( $_POST['ba-edit-author-slug'] ) ? sanitize_title( trim( $_POST['ba-edit-author-slug'] ) ) : '';

	if ( ! empty( $author_slug ) && ( $userdata->user_nicename != $author_slug ) ) {
		/*
		 * Legacy nicename verification code
		 *
		 * May make a return in a later release, but discovered that wp_insert_user(),
		 * which is called by wp_update_user() handles duplicate nicenames with a bit
		 * more grace. It does, however, come at the expense of appending a '-2' at the
		 * end without warning. Colliding nicenames should be a rare/fringe case, so no
		 * issues should arise.
		 *
		// Get array of existing user_nicenames to compare against
		$user_nicenames = $wpdb->get_col( $wpdb->prepare( "SELECT user_nicename FROM $wpdb->users" ) );

		// Bail if we the nicename already exists
		if ( in_array( $author_slug, $user_nicenames ) ) {
			$author_slug_html = '<em><strong>' . esc_attr( $author_slug ) . '</strong></em>';
			$message          = sprintf( esc_html__( "The author slug, '%s', is already in use. Please go back, and try again.", 'edit-author-slug' ), $author_slug_html );

			wp_die( $message );
		}
		*/

		// Looks like we made it, so let's update
		$new_userdata = array(
			'ID'            => $user_id,
			'user_nicename' => $author_slug
		);
		$updated_user_id = wp_update_user( $new_userdata );

		// Clear the cache for good measure
		if ( $updated_user_id )
			wp_cache_delete( $userdata->user_nicename, 'userslugs' );
	}
}

/**
 * Add Author Base settings field to 'Permalink' options page.
 *
 * Adds a settings field for Author Base in the 'Optional' settings
 * section along with Category Base and Tag Base.
 *
 * @since 0.4.0
 *
 * @uses add_settings_field() To add the settings field
 */
function ba_eas_add_author_base_settings_field() {
	/**
	 * Register setting doesn't work on options-permalink.php
	 * see Trac ticket #9296 (http://core.trac.wordpress.org/ticket/9296)
	 * register_setting( 'ba_edit_author_slug', 'ba_edit_author_slug', array( $this, 'sanitize_author_base' ) );
	 */
	add_settings_field( 'ba-eas-author-base', __( 'Author Base', 'edit-author-slug' ), 'ba_eas_author_base_settings_html', 'permalink', 'optional' );
}

/**
 * Add Author Base settings html to Options > Permalinks.
 *
 * Adds a settings field for Author Base in the 'Optional'
 * settings section along with Category Base and Tag Base.
 *
 * @since 0.4.0
 *
 * @uses esc_attr() To sanitize the author base
 */
function ba_eas_author_base_settings_html() {
	global $ba_eas;

	echo '<input id="ba-eas-author-base" name="ba-eas-author-base" type="text" value="' . esc_attr( $ba_eas->author_base ) . '" class="regular-text code" />';
}

/**
 * Add 'Author Slug' column to Users page.
 *
 * Adds the 'Author Slug' column and column heading
 * to the page Users > Authors & Users.
 *
 * @since 0.5.0
 *
 * @param array $defaults Array of current columns/column headings
 * @uses esc_html__() To sanitize the author slug column title
 * @return array $defaults Array of current columns/column headings
 */
function ba_eas_author_slug_column( $defaults ) {
	$defaults['ba-eas-author-slug'] = esc_html__( 'Author Slug', 'edit-author-slug' );

	return $defaults;
}

/**
 * Fill in user_nicename for 'Author Slug' column.
 *
 * Adds the user's corresponding user_nicename to the
 * 'Author Slug' column.
 *
 * @since 0.5.0
 *
 * @param string $default Value for column data, defaults to ''
 * @param string $column_name Column name currently being filtered
 * @param int $user_id User ID
 * @uses get_userdata() To get the user data
 * @uses esc_attr() To sanitize the user_nicename
 * @return string $default Value for column data. Defaults to ''.
 */
function ba_eas_author_slug_custom_column( $default, $column_name, $user_id ) {
	$user_id = (int) $user_id;

	if ( $column_name == 'ba-eas-author-slug') {
		$userdata = get_userdata( $user_id );

		return esc_attr( $userdata->user_nicename );
	}

	return $default;
}

/**
 * Cleanup old options.
 *
 * Nothing urgent so no need to worry constant checks while
 * in the admin. We'll run even less frequently for those on 3.1+.
 *
 * @since 0.7.0
 *
 * @global $ba_eas Edit Author Slug object
 * @uses update_option()
 */
function ba_eas_cleanup_options() {
	global $ba_eas;

	if ( array_key_exists( $ba_eas->options['dont_forget_to_flush'] ) ) {
		unset( $ba_eas->options['dont_forget_to_flush'] );

		update_option( 'ba_edit_author_slug', $ba_eas->options );
	}
}

/**
 * Flush rewrite rules.
 *
 * Flush rewrite rules so author links aren't busted when
 * the user deactivates the plugin.
 *
 * @since 0.7.0
 *
 * @uses flush_rewrite_rules() To flush the rewrite rules
 */
function ba_eas_courtesy_flush() {
	flush_rewrite_rules( false );
}

?>