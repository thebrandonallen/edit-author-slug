<?php

/**
 * Edit Author Slug Admin Functions
 *
 * @package Edit_Author_Slug
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
	if ( ba_eas_can_edit_author_slug() ) : ?>

	<h3><?php esc_html_e( 'Edit Author Slug', 'edit-author-slug' ); ?></h3>
	<table class="form-table">
		<tbody><tr>
			<th><label for="ba-edit-author-slug"><?php esc_html_e( 'Author Slug', 'edit-author-slug' ); ?></label></th>
			<td>
				<input type="text" name="ba-edit-author-slug" id="ba-edit-author-slug" value="<?php echo esc_attr( $user->user_nicename ); ?>" class="regular-text" /><br />
				<span class="description"><?php esc_html_e( "ie. - 'user-name', 'firstname-lastname', or 'master-ninja'", 'edit-author-slug' ); ?></span>
			</td>
		</tr></tbody>
	</table>
	<?php endif;
}

/**
 * Can the current user edit the author slug?
 *
 * @since 0.8.0
 *
 * @uses is_super_admin() To check if super admin
 * @uses current_user_can() To check for 'edit_users' and 'edit_author_slug' caps
 * @uses apply_filters() To call 'ba_eas_can_edit_author_slug' hook
 *
 * @return bool True if edit privileges. Defaults to false.
 */
function ba_eas_can_edit_author_slug() {

	// Default to false
	$retval = false;

	if ( is_super_admin() || current_user_can( 'edit_users' ) || current_user_can( 'edit_author_slug' ) )
		$retval = true;

	return apply_filters( 'ba_eas_can_edit_author_slug', $retval );
}

/**
 * Update the user_nicename for a given user.
 *
 * @since 0.1.0
 *
 * @param obj $errors WP_Errors object
 * @param bool $update Are we updating?
 * @param obj WP_User object
 *
 * @global obj $wpdb
 * @uses check_admin_referer() To verify the nonce and check referer
 * @uses current_user_can() To prevent unauthorized users from saving.
 * @uses get_userdata() To get the user data
 * @uses sanitize_title() Used to sanitize user_nicename
 * @uses wp_update_user() Update to new user_nicename
 * @uses wp_cache_delete() To delete the 'userslugs' cache for old nicename
 */
function ba_eas_update_user_nicename( $errors, $update, $user ) {
	global $wpdb;

	// We shouldn't be here if we're not updating
	if ( ! $update )
		return;

	// Bail if we don't have a user
	if ( empty( $user ) )
		return;

	// Setup the user_id
	$user_id = (int) $user->ID;

	// Check the nonce
	check_admin_referer( 'update-user_' . $user_id );

	// User, tell me a little about yourself.
	$userdata	 = get_userdata( $user_id );

	// Setup the author slug
	$author_slug = isset( $_POST['ba-edit-author-slug'] ) ? trim( $_POST['ba-edit-author-slug'] ) : '';

	// Do we have an author slug?
	if ( empty( $author_slug ) ) {
		$errors->add( 'ba_edit_author_slug', __( '<strong>ERROR</strong>: An author slug cannot be blank. Please try again.' ) );
		return;
	}

	// Prepare the author slug
	$author_slug = sanitize_title( $_POST['ba-edit-author-slug'] );

	// Maybe update the author slug?
	if ( $userdata->user_nicename != $author_slug ) {

		// Do we have an author slug?
		if ( empty( $author_slug ) ) {
			$errors->add( 'ba_edit_author_slug', __( '<strong>ERROR</strong>: That author slug appears to be invalid. Please try something different.' ) );
			return;
		}

		// Does this author slug already exist?
		if ( $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->users WHERE user_nicename = %s", $author_slug ) ) ) {
			$errors->add( 'ba_edit_author_slug', __( '<strong>ERROR</strong>: The author slug <strong><em>' . $author_slug . '</em></strong> already exists. Please try something different.' ) );
			return;
		}

		// Looks like we made it, so let's update
		if ( ! $updated_user_id = wp_update_user( array( 'ID' => $user_id, 'user_nicename' => $author_slug ) ) ){
			$errors->add( 'ba_edit_author_slug', __( '<strong>ERROR</strong>: There was an error updating the author slug. Please try again.' ) );
			return;
		}

		// Clear the cache for good measure
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
 * Sanitize author base and add to database.
 *
 * This is a workaround until ticket #9296 is resolved
 * (http://core.trac.wordpress.org/ticket/9296)
 *
 * @since 0.8.0
 *
 * @uses check_admin_referer() To verify the nonce and check referer
 * @uses _wp_filter_taxonomy_base() To remove any manually prepended /index.php/.
 * @uses update_option() To update Edit Author Slug options
 * @uses flush_rewrite_rules() To update Edit Author Slug options
 */
function ba_eas_sanitize_author_base() {
	global $ba_eas, $wp_rewrite;

	if ( isset( $_POST['permalink_structure'] ) || isset( $_POST['category_base'] ) ) {
		check_admin_referer( 'update-permalink' );

		if ( isset( $_POST['ba-eas-author-base'] ) ) {
			$ba_eas->author_base = trim( $_POST['ba-eas-author-base'] );

			// Filer and sanitize the new author_base
			if ( ! empty( $ba_eas->author_base ) ) {
				$ba_eas->author_base = str_replace( '#', '', $ba_eas->author_base     );
				$ba_eas->author_base = _wp_filter_taxonomy_base( $ba_eas->author_base );
			}

			// Do we need to update the author_base
			if ( $ba_eas->author_base != $ba_eas->original_author_base ) {
				// Setup the new author_base
				$ba_eas->options['author_base'] = $ba_eas->author_base;

				// Update options with new author_base
				update_option( 'ba_edit_author_slug', $ba_eas->options );

				// Update the author_base in the WP_Rewrite object
				if ( ! empty( $ba_eas->author_base ) )
					$wp_rewrite->author_base = $ba_eas->author_base;

				// Courtesy flush
				flush_rewrite_rules( false );
			}
		}
	}
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
 * @param string $default Value for column data. Defaults to ''.
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
 * Nothing urgent so no need to worry with constant checks while
 * in the admin. Will run even less frequently for those on 3.1+.
 *
 * @since 0.7.0
 *
 * @global $ba_eas Edit Author Slug object
 * @uses update_option() To update Edit Author Slug options
 */
function ba_eas_cleanup_options() {
	$options = get_option( 'ba_edit_author_slug', array() );

	if ( array_key_exists( 'dont_forget_to_flush', $options ) ) {
		unset( $options['dont_forget_to_flush'] );

		update_option( 'ba_edit_author_slug', $options );
	}
}

?>