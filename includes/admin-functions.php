<?php

/**
 * Edit Author Slug Admin Functions
 *
 * @package Edit_Author_Slug
 * @subpackage Administration
 *
 * @author Brandon Allen
 */

/** Nicename ******************************************************************/

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
	if ( !ba_eas_can_edit_author_slug() )
		return false;
	?>

	<h3><?php esc_html_e( 'Edit Author Slug', 'edit-author-slug' ); ?></h3>
	<table class="form-table">
		<tbody><tr>
			<th><label for="ba-edit-author-slug"><?php esc_html_e( 'Author Slug', 'edit-author-slug' ); ?></label></th>
			<td>
				<input type="text" name="ba-edit-author-slug" id="ba-edit-author-slug" value="<?php ( isset( $user->user_nicename ) ) ? esc_attr_e( $user->user_nicename ) : ''; ?>" class="regular-text" /><br />
				<span class="description"><?php esc_html_e( "ie. - 'user-name', 'firstname-lastname', or 'master-ninja'", 'edit-author-slug' ); ?></span>
			</td>
		</tr></tbody>
	</table>
	<?php
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

	// Bail early if user can't edit the slug
	if ( !ba_eas_can_edit_author_slug() )
		return false;

	global $wpdb;

	// We shouldn't be here if we're not updating
	if ( !$update )
		return;

	// Bail if we don't have a user
	if ( empty( $user ) || !is_object( $user ) )
		return;

	// Setup the user_id
	$user_id = (int) $user->ID;

	// Check the nonce
	check_admin_referer( 'update-user_' . $user_id );

	// Validate the user object
	$user = get_userdata( $user_id );

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
	if ( $user->user_nicename != $author_slug ) {

		// Do we have an author slug?
		if ( empty( $author_slug ) ) {
			$errors->add( 'ba_edit_author_slug', __( '<strong>ERROR</strong>: That author slug appears to be invalid. Please try something different.' ) );
			return;
		}

		// Does this author slug already exist?
		if ( $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->users WHERE user_nicename = %s", $author_slug ) ) ) {
			$errors->add( 'ba_edit_author_slug', sprintf( __( '<strong>ERROR</strong>: The author slug, %1$s, already exists. Please try something different.' ), '<strong><em>' . esc_attr( $author_slug ) . '</em></strong>' ) );
			return;
		}

		// Looks like we made it, so let's update
		if ( !$updated_user_id = wp_update_user( array( 'ID' => $user_id, 'user_nicename' => $author_slug ) ) ){
			$errors->add( 'ba_edit_author_slug', __( '<strong>ERROR</strong>: There was an error updating the author slug. Please try again.' ) );
			return;
		}

		// Clear the cache for good measure
		wp_cache_delete( $user->user_nicename, 'userslugs' );
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

	if ( 'ba-eas-author-slug' == $column_name ) {
		$user = get_userdata( $user_id );

		if ( !empty( $user->user_nicename ) )
			$default = esc_attr( $user->user_nicename );
	}

	return $default;
}

/** Author Base **************************************************************/

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
function ba_eas_sanitize_author_base( $author_base ) {
	global $ba_eas, $wp_rewrite;

	// Sanitize the author base
	$author_base = sanitize_title( $author_base );

	// Make sure we have something
	if ( empty( $author_base ) )
		$author_base = 'author';


	// Do we need to update the author_base
	if ( $author_base != $ba_eas->author_base ) {
		// Setup the new author_base global
		$ba_eas->author_base = $author_base;

		// Update options with new author_base
		update_option( '_ba_eas_author_base', $ba_eas->author_base );

		// Update the author_base in the WP_Rewrite object
		if ( !empty( $ba_eas->author_base ) )
			$wp_rewrite->author_base = $ba_eas->author_base;

		// Courtesy flush
		flush_rewrite_rules( false );
	}

	return $author_base;
}

/** Settings *****************************************************************/

/**
 * Add the Edit Author Slug Settings Menu.
 *
 * @since 0.9.0
 *
 * @uses add_submenu_page() To add the Edit Author Slug submenu
 */
function ba_eas_add_settings_menu() {
	add_options_page( __( 'Edit Author Slug Settings', 'edit-author-slug' ), __( 'Edit Author Slug', 'edit-author-slug' ), 'edit_users', 'edit-author-slug', 'ba_eas_settings_page_html' );
}

/**
 * Output HTML for settings page.
 *
 * @since 0.9.0
 *
 * @uses add_submenu_page() To add the Edit Author Slug submenu
 */
function ba_eas_settings_page_html() {
?>

	<div class="wrap">

		<?php screen_icon(); ?>

		<h2><?php _e( 'Edit Author Slug Settings', 'edit-author-slug' ); ?></h2>

		<form action="options.php" method="post">

			<?php settings_fields( 'edit-author-slug' ); ?>

			<?php do_settings_sections( 'edit-author-slug' ); ?>

			<p class="submit">
				<input type="submit" name="submit" class="button-primary" value="<?php _e( 'Save Changes', 'edit-author-slug' ); ?>" />
			</p>
		</form>
	</div>

<?php
}

/**
 * Add Author Base settings field to 'Permalink' options page.
 *
 * Adds a settings field for Author Base in the 'Optional' settings
 * section along with Category Base and Tag Base.
 *
 * @since 0.9.0
 *
 * @uses add_settings_field() To add the settings field
 */
function ba_eas_register_admin_settings() {
	// Add the Author Base section
	add_settings_section( 'ba_eas_author_base', __( 'Author Base', 'edit-author-slug' ), 'ba_eas_admin_setting_callback_author_base_section', 'edit-author-slug' );

	// Author Base setting
	add_settings_field( '_ba_eas_author_base', __( 'Author Base', 'edit-author-slug' ), 'ba_eas_admin_setting_callback_author_base', 'edit-author-slug', 'ba_eas_author_base' );
	register_setting( 'edit-author-slug', '_ba_eas_author_base', 'ba_eas_sanitize_author_base' );

	// Add the default user nicename section
	add_settings_section( 'ba_eas_default_user_nicename', __( 'Author Slug Creation', 'edit-author-slug' ), 'ba_eas_admin_setting_callback_default_user_nicename_section', 'edit-author-slug' );

	// Default user nicename setting
	add_settings_field( '_ba_eas_default_user_nicename', __( 'Author Base', 'edit-author-slug' ), 'ba_eas_admin_setting_callback_default_user_nicename', 'edit-author-slug', 'ba_eas_default_user_nicename' );
	register_setting( 'edit-author-slug', '_ba_eas_default_user_nicename' );
}

/**
 * Add Author Base settings section.
 *
 * @since 0.9.0
 */
function ba_eas_admin_setting_callback_author_base_section() {
?>

	<p><?php _e( "Change your author base to something more fun. <em>Defaults to 'author'</em>.", 'edit-author-slug' ); ?></p>

<?php
}

/**
 * Add default user nicename settings section.
 *
 * @since 0.9.0
 */
function ba_eas_admin_setting_callback_default_user_nicename_section() {
?>

	<p><?php _e( 'Set the default Author Slug structure for new users', 'edit-author-slug' ); ?></p>

<?php
}

/**
 * Add Author Base settings field.
 *
 * @since 0.9.0
 *
 * @uses esc_attr_e() To sanitize the author base
 */
function ba_eas_admin_setting_callback_author_base() {
	global $ba_eas;
?>

	<input id="_ba_eas_author_base" name="_ba_eas_author_base" type="text" value="<?php esc_attr_e( $ba_eas->author_base ); ?>" class="regular-text code" />

<?php
}

/**
 * Add default user nicename options.
 *
 * @since 0.9.0
 *
 * @uses get_option() To get the default user nicename
 * @uses apply_filters() To call 'ba_eas_admin_setting_callback_default_user_nicename_list' hook
 * @uses esc_attr_e() To sanitize the nicename options
 */
function ba_eas_admin_setting_callback_default_user_nicename() {
	global $ba_eas;

	$structure = get_option( '_ba_eas_default_user_nicename', 'username' );

	$options = apply_filters( 'ba_eas_admin_setting_callback_default_user_nicename_list', array(
		'username'  => 'Default (Username)',
		'nickname'  => 'Nickname',
		'firstname' => 'First Name',
		'lastname'  => 'Last Name',
		'firstlast' => 'First Name + Last Name',
		'lastfirst' => 'Last Name + First Name',
	) );

	$options = (array) $options;
	$options = array_map( 'trim', $options );
	$options = array_unique( $options );
?>

	<select id="_ba_eas_default_user_nicename" name="_ba_eas_default_user_nicename">
	<?php foreach ( (array) $options as $id => $item ) { ?>
		<option id="<?php esc_attr_e( $id ); ?>" value="<?php esc_attr_e( $id ); ?>"<?php selected( $structure, $id ); ?>><?php esc_attr_e( $item ); ?></option>
	<?php } ?>
	</select>

<?php
}

/**
 * Output settings API option
 *
 * @since 0.9.0
 *
 * @uses ba_eas_get_form_option()
 *
 * @param string $option
 * @param string $default
 * @param bool $slug
 */
function ba_eas_form_option( $option, $default = '' , $slug = false ) {
	echo ba_eas_get_form_option( $option, $default, $slug );
}

/**
 * Return settings API option
 *
 * @since 0.9.0
 *
 * @uses get_option()
 * @uses esc_attr()
 * @uses apply_filters()
 *
 * @param string $option
 * @param string $default
 * @param bool $slug
 */
function ba_eas_get_form_option( $option, $default = '', $slug = false ) {

	// Get the option and sanitize it
	$value = get_option( $option, $default );

	// Slug?
	if ( true === $slug )
		$value = esc_attr( apply_filters( 'editable_slug', $value ) );

	// Not a slug
	else
		$value = esc_attr( $value );

	// Fallback to default
	if ( empty( $value ) )
		$value = $default;

	// Allow plugins to further filter the output
	return apply_filters( 'ba_eas_get_form_option', $value, $option );
}

/** Upgrade *******************************************************************/

/**
 * Upgrade Edit Author Slug.
 *
 * Just cleans up some options for now.
 *
 * @since 0.8.0
 *
 * @global $ba_eas Edit Author Slug object
 * @uses update_option() To update Edit Author Slug options
 */
function ba_eas_upgrade() {
	global $ba_eas;

	// We're up-to-date, so let's move on
	if ( $ba_eas->current_db_version === $ba_eas->db_version )
		return;

	if ( $ba_eas->current_db_version < 100 ) {
		$ba_eas->options['author_base'] = empty( $ba_eas->author_base ) ? '' : $ba_eas->author_base;
		$ba_eas->options['db_version']  = $ba_eas->db_version;

		if ( array_key_exists( 'dont_forget_to_flush', $ba_eas->options ) )
			unset( $ba_eas->options['dont_forget_to_flush'] );
	}

	// Update the option
	update_option( 'ba_edit_author_slug', $ba_eas->options );
}

?>