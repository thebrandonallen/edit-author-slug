<?php

/**
 * Edit Author Slug Admin Functions
 *
 * @package Edit_Author_Slug
 * @subpackage Administration
 *
 * @author Brandon Allen
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

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
 * @uses ba_eas_can_edit_author_slug() To verify current user can edit the author slug
 * @uses sanitize_title() To sanitize userdata into a new nicename
 * @uses apply_filters() To call the 'ba_eas_show_user_nicename_options_list' hook
 * @uses esc_html_e() To make sure we're safe to display
 * @uses esc_attr_e() To make sure we're safe to display
 * @uses checked() To check that box
 */
function ba_eas_show_user_nicename( $user ) {
	if ( !ba_eas_can_edit_author_slug() )
		return false;

	// Setup the nicename
	$nicename = '';
	if ( !empty( $user->user_nicename ) )
		$nicename = $user->user_nicename;

	// Setup options array
	$options = array();
	$options['username'] = sanitize_title( $user->user_login );
	$options['nickname'] = sanitize_title( $user->nickname );

	if ( !empty( $user->first_name ) )
		$options['firstname'] = sanitize_title( $user->first_name );

	if ( !empty( $user->last_name ) )
		$options['lastname'] = sanitize_title( $user->last_name );

	if ( !empty( $user->first_name ) && !empty( $user->last_name ) ) {
		$options['firslast'] = sanitize_title( $user->first_name  . ' ' . $user->last_name );
		$options['lastfirst'] = sanitize_title( $user->last_name . ' ' . $user->first_name );
	}

	// Allow custom options to be added, and prep for display
	$options = (array) apply_filters( 'ba_eas_show_user_nicename_options_list', $options );
	$options = array_map( 'trim', $options );
	$options = array_unique( $options );

	// Set default for checked status
	$checked = true;
	?>

	<h3><?php esc_html_e( 'Edit Author Slug', 'edit-author-slug' ); ?></h3>
	<p><?php _e( 'Choose an Author Slug based on the above profile information, or create your own.', 'edit-author-slug' ); ?> <br /><span class="description"><?php esc_html_e( "ie. - 'user-name', 'firstname-lastname', or 'master-ninja'", 'edit-author-slug' ); ?></span></p>
	<table class="form-table">
		<tbody><tr>
			<th scope="row"><?php esc_html_e( 'Author Slug', 'edit-author-slug' ); ?></th>
			<td>
				<fieldset><legend class="screen-reader-text"><span><?php esc_html_e( 'Author Slug', 'edit-author-slug' ); ?></span></legend>
				<?php foreach ( (array) $options as $id => $item ) {
					$checked_text = '';
					if ( $item === $nicename ) {
						$checked_text = ' checked="checked"';
						$checked = false;
					}
				?>
				<label title="<?php esc_attr_e( $item ); ?>"><input type="radio" id="ba_eas_author_slug" name="ba_eas_author_slug" value="<?php esc_attr_e( $item ); ?>"<?php echo $checked_text; ?>> <span><?php esc_attr_e( $item ); ?></span></label><br>
				<?php } ?>
				<label title="<?php esc_attr_e( $nicename ); ?>"><input type="radio" id="ba_eas_author_slug_custom_radio" name="ba_eas_author_slug" value="\c\u\s\t\o\m"<?php checked( $checked ); ?>> <span><?php esc_html_e( 'Custom:', 'edit-author-slug' ); ?> </span></label> <input type="text" name="ba_eas_author_slug_custom" id="ba_eas_author_slug_custom" value="<?php esc_attr_e( $nicename ); ?>" class="regular-text" />
				</fieldset>
			</td>
		</tr></tbody>
	</table>

	<?php
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
 * @uses ba_eas_can_edit_author_slug() To verify current user can edit the author slug
 * @uses check_admin_referer() To verify the nonce and check referer
 * @uses get_userdata() To get the user data
 * @uses sanitize_title() Used to sanitize user_nicename
 * @uses remove_action() To remove the 'ba_eas_auto_update_user_nicename_single' and prevent looping
 * @uses wp_update_user() Update to new user_nicename
 * @uses wp_cache_delete() To delete the 'userslugs' cache for old nicename
 */
function ba_eas_update_user_nicename( $errors, $update, $user ) {

	// Bail early if user can't edit the slug
	if ( !ba_eas_can_edit_author_slug() )
		return false;

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
	$_user = get_userdata( $user_id );

	// Check for a custom author slug
	if ( !empty( $_POST['ba_eas_author_slug'] ) && isset( $_POST['ba_eas_author_slug_custom'] ) && '\c\u\s\t\o\m' == stripslashes( $_POST['ba_eas_author_slug'] ) )
			$_POST['ba_eas_author_slug'] = $_POST['ba_eas_author_slug_custom'];

	// Setup the author slug
	$author_slug = '';
	if ( isset( $_POST['ba_eas_author_slug'] ) )
		$author_slug = trim( stripslashes( $_POST['ba_eas_author_slug'] ) );

	// Do we have an author slug?
	if ( empty( $author_slug ) ) {
		$errors->add( 'ba_edit_author_slug', __( '<strong>ERROR</strong>: An author slug cannot be blank. Please try again.' ) );
		return;
	}

	// Prepare the author slug
	$author_slug = sanitize_title( $author_slug );

	// Don't run the auto-update when the current user can update nicenames
	remove_action( 'profile_update', 'ba_eas_auto_update_user_nicename_single' );

	// Maybe update the author slug?
	if ( $author_slug != $_user->user_nicename ) {

		// Add the wpdb global only when necessary
		global $wpdb;

		// Do we have an author slug?
		if ( empty( $author_slug ) ) {
			$errors->add( 'ba_edit_author_slug', __( '<strong>ERROR</strong>: That author slug appears to be invalid. Please try something different.' ) );
			return;
		}

		// Does this author slug already exist?
		if ( get_user_by( 'slug', $author_slug ) && (int) get_user_by( 'slug', $author_slug )->ID !== $user_id ) {
			$errors->add( 'ba_edit_author_slug', sprintf( __( '<strong>ERROR</strong>: The author slug, %1$s, already exists. Please try something different.' ), '<strong><em>' . esc_attr( $author_slug ) . '</em></strong>' ) );
			return;
		}

		// Looks like we made it, so let's update
		if ( !$updated_user_id = wp_update_user( array( 'ID' => $user_id, 'user_nicename' => $author_slug ) ) ) {
			$errors->add( 'ba_edit_author_slug', __( '<strong>ERROR</strong>: There was an error updating the author slug. Please try again.' ) );
			return;

		}

		// We're still here, so clear the cache for good measure
		wp_cache_delete( $_user->user_nicename, 'userslugs' );
	}
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
 * Add 'Author Slug' column to Users page.
 *
 * Adds the 'Author Slug' column and column heading
 * to the page Users > Authors & Users.
 *
 * @since 0.5.0
 *
 * @param array $defaults Array of current columns/column headings
 *
 * @uses esc_html__() To sanitize the author slug column title
 *
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
 *
 * @uses get_userdata() To get the user data
 * @uses esc_attr() To sanitize the user_nicename
 *
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

function ba_eas_show_user_nicename_scripts() {
	global $pagenow;

	if ( !in_array( $pagenow, array( 'user-edit.php', 'profile.php' ) ) || !ba_eas_can_edit_author_slug() )
		return;
?>

	<!-- Edit Author Slug nicename edit -->
	<script type="text/javascript">
	//<![CDATA[
		jQuery(document).ready(function($){
			$("input[name='ba_eas_author_slug']").click(function(){
				if ( "ba_eas_author_slug_custom_radio" != $(this).attr("id") )
					$("input[name='ba_eas_author_slug_custom']").val( $(this).val() ).siblings('.example').text( $(this).siblings('span').text() );
			});
			$("input[name='ba_eas_author_slug_custom']").focus(function(){
				$("#ba_eas_author_slug_custom_radio").attr("checked", "checked");
			});

		});
	//]]>
	</script>
	<!-- end Edit Author Slug nicename edit -->

<?php
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
 * @param str $author_base Author base to be sanitized
 *
 * @global obj $ba_eas Edit Author Slug object
 * @global obj $wp_rewrite WP_Rewrite object
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

			<?php submit_button(); ?>
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
	add_settings_section( 'ba_eas_auto_update', __( 'Automatic Author Slug Creation', 'edit-author-slug' ), 'ba_eas_admin_setting_callback_auto_update_section', 'edit-author-slug' );

	// Auto-update on/off
	add_settings_field( '_ba_eas_do_auto_update', __( 'Automatically Update', 'edit-author-slug' ), 'ba_eas_admin_setting_callback_do_auto_update', 'edit-author-slug', 'ba_eas_auto_update' );
	register_setting( 'edit-author-slug', '_ba_eas_do_auto_update' );

	// Default user nicename setting
	add_settings_field( '_ba_eas_default_user_nicename', __( 'Author Slug', 'edit-author-slug' ), 'ba_eas_admin_setting_callback_default_user_nicename', 'edit-author-slug', 'ba_eas_auto_update' );
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
function ba_eas_admin_setting_callback_auto_update_section() {
?>

	<p><?php _e( "Allow Author Slugs to be automatically update, and set the default Author Slug structure for users. Automatic updating will only occur when a user can't edit Author Slugs on their own.", 'edit-author-slug' ); ?> <br /><strong><em><?php _e( 'This could have SEO repercussions if users update their profiles frequently, and it will override any manual editing of the Author Slug you may have previously completed.', 'edit-author-slug' ); ?></em></strong></p>

<?php
}

/**
 * Add Author Base settings field.
 *
 * @since 0.9.0
 *
 * @global obj $ba_eas Edit Author Slug object
 * @uses apply_filters() To call 'editable_slug' hook
 * @uses esc_attr_e() To sanitize the author base
 */
function ba_eas_admin_setting_callback_author_base() {
	global $ba_eas;

	$author_base = apply_filters( 'editable_slug', $ba_eas->author_base );
?>

	<input id="_ba_eas_author_base" name="_ba_eas_author_base" type="text" value="<?php esc_attr_e( $author_base ); ?>" class="regular-text code" />

<?php
}

/**
 * Add auto-update checkbox.
 *
 * @since 0.9.0
 *
 * @uses get_option() To get the auto-update option
 */
function ba_eas_admin_setting_callback_do_auto_update() {
	$do_auto_update = (int) get_option( '_ba_eas_do_auto_update', '0' );
?>

	<input name="_ba_eas_do_auto_update" id="_ba_eas_do_auto_update" value="1"<?php checked( $do_auto_update, '1' ); ?> type="checkbox">
	<label for="_ba_eas_do_auto_update">Automatically update Author Slug when a user updates their profile</label>

<?php
}

/**
 * Add default user nicename options.
 *
 * @since 0.9.0
 *
 * @uses get_option() To get the default user nicename
 * @uses apply_filters() To call 'ba_eas_default_user_nicename_options_list' hook
 * @uses esc_attr_e() To sanitize the nicename options
 */
function ba_eas_admin_setting_callback_default_user_nicename() {
	$structure = get_option( '_ba_eas_default_user_nicename', 'username' );

	if ( empty( $structure ) )
		$structure = 'username';

	$options = apply_filters( 'ba_eas_default_user_nicename_options_list', array(
		'username'  => __( 'username (Default)', 'edit-author-slug' ),
		'nickname'  => __( 'nickname', 'edit-author-slug' ),
		'firstname' => __( 'firstname', 'edit-author-slug' ),
		'lastname'  => __( 'lastname', 'edit-author-slug' ),
		'firstlast' => __( 'firstname-lastname', 'edit-author-slug' ),
		'lastfirst' => __( 'lastname-firstname', 'edit-author-slug' ),
	) );

	$options = (array) apply_filters( 'ba_eas_default_user_nicename_options_list', $options );
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
 * Add settings link to plugin listing.
 *
 * @since 0.9.1
 *
 * @param array $links Links array in which we would prepend our link
 * @param string $file Current plugin basename
 *
 * @global obj $ba_eas Edit Author Slug object
 * @uses plugin_basename() To get the plugin basename
 * @uses add_query_arg() To add the edit-author-slug query arg
 * @uses admin_url() To get the admin url
 */
function ba_eas_add_settings_link( $links, $file ) {
	global $ba_eas;

	if ( plugin_basename( $ba_eas->file ) == $file ) {
		$settings_link = '<a href="' . add_query_arg( array( 'page' => 'edit-author-slug' ), admin_url( 'options-general.php' ) ) . '">' . __( 'Settings', 'edit-author-slug' ) . '</a>';
		array_unshift( $links, $settings_link );
	}

	return $links;
}

/** Upgrade *******************************************************************/

/**
 * Upgrade Edit Author Slug.
 *
 * Just cleans up some options for now.
 *
 * @since 0.8.0
 *
 * @global obj $ba_eas Edit Author Slug object
 * @uses update_option() To update Edit Author Slug options
 */
function ba_eas_upgrade() {
	global $ba_eas, $wpdb;

	// We're up-to-date, so let's move on
	if ( $ba_eas->current_db_version === $ba_eas->db_version )
		return;

	if ( $ba_eas->current_db_version < 132 ) {
		// Add new options
		update_option( '_ba_eas_author_base', $ba_eas->author_base );
		update_option( '_ba_eas_db_version',  $ba_eas->db_version  );

		// Rename the old option for safe keeping
		$wpdb->update( $wpdb->options, array( 'option_name' => '_ba_eas_old_options' ), array( 'option_name' => 'ba_edit_author_slug' ) );
	}
}

?>
