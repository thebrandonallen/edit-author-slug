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
if ( ! defined( 'ABSPATH' ) ) { exit; }

/** Nicename ******************************************************************/

/**
 * Display Author slug edit field on User/Profile edit page.
 *
 * Displays the Author slug edit field on User/Profile edit page.
 * Runs with the 'show_user_profile' and 'edit_user_profile' actions.
 *
 * @since 0.1.0
 *
 * @param object $user User data object.
 *
 * @uses ba_eas_can_edit_author_slug() To verify current user can edit the author slug.
 * @uses sanitize_title() To sanitize userdata into a new nicename.
 * @uses apply_filters() To call the 'ba_eas_show_user_nicename_options_list' hook.
 * @uses esc_html_e() To make sure we're safe to display.
 * @uses checked() To check that box.
 * @uses esc_attr() To make sure we're safe to display.
 */
function ba_eas_show_user_nicename( $user ) {

	// Return early if the user can't edit the author slug
	if ( ! ba_eas_can_edit_author_slug() ) {
		return false;
	}

	// Setup the nicename
	$nicename = '';
	if ( ! empty( $user->user_nicename ) ) {
		$nicename = $user->user_nicename;
	}

	// Setup options array
	$options = array();
	$options['username']    = trim( sanitize_title( $user->user_login   ) );
	$options['nickname']    = trim( sanitize_title( $user->nickname     ) );
	$options['displayname'] = trim( sanitize_title( $user->display_name ) );

	// Setup the first name
	if ( ! empty( $user->first_name ) ) {
		$options['firstname'] = trim( sanitize_title( $user->first_name ) );
	}

	// Setup the last name
	if ( ! empty( $user->last_name ) ) {
		$options['lastname'] = trim( sanitize_title( $user->last_name ) );
	}

	// Setup the first/last name combos
	if ( ! empty( $options['firstname'] ) && ! empty( $options['lastname'] ) ) {
		$options['firslast']  = $options['firstname'] . '-' . $options['lastname'];
		$options['lastfirst'] = $options['lastname'] . '-' . $options['firstname'];
	}

	// Setup a filterable list of nicename auto-update options,
	// then filter out any duplicates/empties
	$options = (array) apply_filters( 'ba_eas_show_user_nicename_options_list', $options );
	$options = array_unique( array_filter( array_map( 'trim', $options ) ) );

	// Set default for checked status
	$checked = true;
	?>

	<h3><?php esc_html_e( 'Edit Author Slug', 'edit-author-slug' ); ?></h3>
	<p><?php esc_html_e( 'Choose an Author Slug based on the above profile information, or create your own.', 'edit-author-slug' ); ?> <br /><span class="description"><?php esc_html_e( "ie. - 'user-name', 'firstname-lastname', or 'master-ninja'", 'edit-author-slug' ); ?></span></p>
	<table class="form-table">
		<tbody><tr>
			<th scope="row"><?php esc_html_e( 'Author Slug', 'edit-author-slug' ); ?></th>
			<td>
				<fieldset><legend class="screen-reader-text"><span><?php esc_html_e( 'Author Slug', 'edit-author-slug' ); ?></span></legend>
				<?php foreach ( (array) $options as $id => $item ) {

					// Checked?
					$checked_text = checked( $item, $nicename, false );

					// Flip the switch if we're checked to block custom from being checked
					if ( ! empty( $checked_text ) ) {
						$checked = false;
					}
				?>
				<label title="<?php echo esc_attr( $item ); ?>"><input type="radio" id="ba_eas_author_slug" name="ba_eas_author_slug" value="<?php echo esc_attr( $item ); ?>"<?php echo $checked_text; ?>> <span><?php echo esc_attr( $item ); ?></span></label><br>
				<?php } ?>
				<label title="<?php echo esc_attr( $nicename ); ?>"><input type="radio" id="ba_eas_author_slug_custom_radio" name="ba_eas_author_slug" value="\c\u\s\t\o\m"<?php checked( $checked ); ?>> <span><?php esc_html_e( 'Custom:', 'edit-author-slug' ); ?> </span></label> <input type="text" name="ba_eas_author_slug_custom" id="ba_eas_author_slug_custom" value="<?php echo esc_attr( $nicename ); ?>" class="regular-text" />
				</fieldset>
			</td>
		</tr></tbody>
	</table>

	<?php
}

/**
 * Prepare the user nicename for updating if applicable.
 *
 * The actual updating is handled by WP as long as no errors are thrown
 * by WP, some third party, or us.
 *
 * @since 0.1.0
 *
 * @param obj $errors WP_Errors object
 * @param bool $update Are we updating?
 * @param obj WP_User object
 *
 * @uses check_admin_referer() To verify the nonce and check referer.
 * @uses ba_eas_can_edit_author_slug() To verify current user can edit the author slug.
 * @uses get_userdata() To get the user data.
 * @uses WP_Errors::add() To add Edit Author Slug specific errors.
 * @uses sanitize_title() Used to sanitize user_nicename.
 * @uses remove_action() To remove the 'ba_eas_auto_update_user_nicename_single' and prevent looping.
 * @uses get_user_by() To see if the nicename is already in use.
 * @uses add_action() To add the 'ba_eas_auto_update_user_nicename_single' back.
 */
function ba_eas_update_user_nicename( $errors, $update, $user ) {

	// We shouldn't be here if we're not updating
	if ( ! $update ) {
		return;
	}

	// Check the nonce
	check_admin_referer( 'update-user_' . $user->ID );

	// Bail early if user can't edit the slug
	if ( ! ba_eas_can_edit_author_slug() ) {
		return;
	}

	// Validate the user_id
	if ( empty( $user->ID ) ) {
		return;
	}

	// Stash the original user object
	$_user = get_userdata( $user->ID );

	// Check for a custom author slug
	if ( ! empty( $_POST['ba_eas_author_slug'] ) && isset( $_POST['ba_eas_author_slug_custom'] ) && '\c\u\s\t\o\m' == stripslashes( $_POST['ba_eas_author_slug'] ) ) {
		$_POST['ba_eas_author_slug'] = $_POST['ba_eas_author_slug_custom'];
	}

	// Setup the author slug
	$author_slug = '';
	if ( isset( $_POST['ba_eas_author_slug'] ) ) {
		$author_slug = trim( stripslashes( $_POST['ba_eas_author_slug'] ) );
	}

	// Do we have an author slug?
	if ( empty( $author_slug ) ) {
		$errors->add( 'ba_edit_author_slug', __( '<strong>ERROR</strong>: An author slug cannot be blank. Please try again.', 'edit-author-slug' ) );
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
			$errors->add( 'ba_edit_author_slug', __( '<strong>ERROR</strong>: That author slug appears to be invalid. Please try something different.', 'edit-author-slug' ) );
			return;
		}

		// Does this author slug already exist?
		if ( get_user_by( 'slug', $author_slug ) && (int) get_user_by( 'slug', $author_slug )->ID !== $user->ID ) {
			$errors->add( 'ba_edit_author_slug', sprintf( __( '<strong>ERROR</strong>: The author slug, %1$s, already exists. Please try something different.', 'edit-author-slug' ), '<strong><em>' . esc_attr( $author_slug ) . '</em></strong>' ) );
			return;
		}

		// Looks like we made it, so let's update
		$user->user_nicename = $author_slug;

		// Update the nicename cache
		add_action( 'profile_update', 'ba_eas_update_nicename_cache', 10, 2 );
	}
}

/**
 * Can the current user edit the author slug?
 *
 * @since 0.8.0
 *
 * @uses is_super_admin() To check if super admin.
 * @uses current_user_can() To check for 'edit_users' and 'edit_author_slug' caps.
 * @uses apply_filters() To call 'ba_eas_can_edit_author_slug' hook.
 *
 * @return bool True if edit privileges. Defaults to false.
 */
function ba_eas_can_edit_author_slug() {

	// Default to false
	$retval = false;

	// True if user is allowed to edit the author slug
	if ( is_super_admin() || current_user_can( 'edit_users' ) || current_user_can( 'edit_author_slug' ) ) {
		$retval = true;
	}

	return (bool) apply_filters( 'ba_eas_can_edit_author_slug', $retval );
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
 * @uses esc_html__() To sanitize the author slug column title.
 *
 * @return array $defaults Array of current columns/column headings
 */
function ba_eas_author_slug_column( $defaults ) {

	// Set the new column name to "Author Slug"
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

	// Set row value to user_nicename if applicable
	if ( 'ba-eas-author-slug' == $column_name ) {
		$user = get_userdata( $user_id );

		if ( ! empty( $user->user_nicename ) ) {
			$default = esc_attr( $user->user_nicename );
		}
	}

	return $default;
}

/**
 * Add javascript to appropriate admin pages.
 *
 * Add javascript to user-edit.php and profile.php pages to
 * update custom field when other radio buttons are selected.
 *
 * Add javascript to Edit Author Slug settings page to
 * show/hide the role slugs.
 *
 * @since 0.9.0
 *
 * @uses get_current_screen() To determine our current location in the admin.
 * @uses ba_eas_can_edit_author_slug() To determine if the user can edit the author slug.
 */
function ba_eas_show_user_nicename_scripts() {

	// Get screen object
	$screen = get_current_screen();

	// Add nicename edit js
	if ( in_array( $screen->base, array( 'user-edit', 'profile' ) ) && ba_eas_can_edit_author_slug() ) {
?>

	<!-- Edit Author Slug nicename edit -->
	<script type="text/javascript">
	//<![CDATA[
		jQuery(document).ready(function($){
			$("input[name='ba_eas_author_slug']").click(function(){
				if ( "ba_eas_author_slug_custom_radio" != $(this).attr("id") ) {
					$("input[name='ba_eas_author_slug_custom']").val( $(this).val() ).siblings('.example').text( $(this).siblings('span').text() );
				}
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

	// Add role slug edit js
	if ( 'settings_page_edit-author-slug' === $screen->base && ba_eas_can_edit_author_slug() ) {
?>

	<!-- Edit Author Slug role slug edit -->
	<script type="text/javascript">
	//<![CDATA[
		jQuery(document).ready(function($){

			// Hide the slugs if we're not doing role-based
			if ( ! $("input[name='_ba_eas_do_role_based']").is(':checked') ) {
				$("input[name='_ba_eas_do_role_based']").parents('tr').next('tr').addClass('hidden');
			}

			// Watch for clicks on the role-based option
			$("input[name='_ba_eas_do_role_based']").on('click', function(){
				if ( $(this).is(':checked') ) {
					$(this).parents('tr').next('tr').fadeIn('slow', function(){$(this).removeClass('hidden');});
				} else {
					$(this).parents('tr').next('tr').fadeOut('fast', function(){$(this).addClass('hidden');});
				}
			});

			// Hide the slugs if we're not doing auto-update
			if ( ! $("input[name='_ba_eas_do_auto_update']").is(':checked') ) {
				$("input[name='_ba_eas_do_auto_update']").parents('tr').next('tr').addClass('hidden');
			}

			// Watch for clicks on the auto-update option
			$("input[name='_ba_eas_do_auto_update']").on('click', function(){
				if ( $(this).is(':checked') ) {
					$(this).parents('tr').next('tr').fadeIn('slow', function(){$(this).removeClass('hidden');});
				} else {
					$(this).parents('tr').next('tr').fadeOut('fast', function(){$(this).addClass('hidden');});
				}
			});
		});
	//]]>
	</script>
	<!-- end Edit Author Slug role slug edit -->

<?php
	}
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
 * @uses ba_eas() BA_Edit_Author_Slug object.
 * @uses sanitize_title() To sanitize the author base.
 * @uses update_option() To update author_base option.
 * @uses ba_eas_flush_rewrite_rules() To flush the rewrite rules in the db.
 */
function ba_eas_sanitize_author_base( $author_base ) {

	// Edit Author Slug instance
	$ba_eas = ba_eas();

	// Sanitize the author base
	$author_base = trim( sanitize_title( $author_base ) );

	// Make sure we have something
	if ( empty( $author_base ) ) {
		$author_base = 'author';
	}

	// Do we need to update the author_base
	if ( $author_base != $ba_eas->author_base ) {
		// Setup the new author_base global
		$ba_eas->author_base = $author_base;

		// Update options with new author_base
		update_option( '_ba_eas_author_base', $ba_eas->author_base );

		// Update the author_base in the WP_Rewrite object
		if ( ! empty( $ba_eas->author_base ) ) {
			$GLOBALS['wp_rewrite']->author_base = $ba_eas->author_base;
		}
	}

	// Courtesy flush
	ba_eas_flush_rewrite_rules();

	return $author_base;
}

/** Settings *****************************************************************/

/**
 * Add the Edit Author Slug Settings Menu.
 *
 * @since 0.9.0
 *
 * @uses add_options_page() To add the Edit Author Slug options page.
 */
function ba_eas_add_settings_menu() {
	add_options_page( __( 'Edit Author Slug Settings', 'edit-author-slug' ), __( 'Edit Author Slug', 'edit-author-slug' ), 'edit_users', 'edit-author-slug', 'ba_eas_settings_page_html' );
}

/**
 * Output HTML for settings page.
 *
 * @since 0.9.0
 *
 * @uses _e() To echo localized string.
 * @uses settings_fields() To output nonce, action, and option_page fields.
 * @uses do_settings_sections() To print out the setting sections/fields.
 * @uses submit_button() To display a nice submit button.
 */
function ba_eas_settings_page_html() {
?>

	<div class="wrap">

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
 * Add Author Base settings settings page.
 *
 * @since 0.9.0
 *
 * @uses add_settings_section() To add a settings section.
 * @uses add_settings_field() To add a settings field.
 * @uses register_setting() To add a settings to whitelist to be updated on submit.
 */
function ba_eas_register_admin_settings() {
	// Add the Author Base section
	add_settings_section( 'ba_eas_author_base', __( 'Author Base', 'edit-author-slug' ), 'ba_eas_admin_setting_callback_author_base_section', 'edit-author-slug' );

	// Author Base setting
	add_settings_field( '_ba_eas_author_base', __( 'Author Base', 'edit-author-slug' ), 'ba_eas_admin_setting_callback_author_base', 'edit-author-slug', 'ba_eas_author_base' );
	register_setting( 'edit-author-slug', '_ba_eas_author_base', 'ba_eas_sanitize_author_base' );

	// Role-Based Author Base setting
	add_settings_field( '_ba_eas_do_role_based', __( 'Role-Based Author Base', 'edit-author-slug' ), 'ba_eas_admin_setting_callback_do_role_based', 'edit-author-slug', 'ba_eas_author_base' );
	register_setting( 'edit-author-slug', '_ba_eas_do_role_based', 'intval' );

	// Role-Based Author Base slugs
	add_settings_field( '_ba_eas_role_slugs', __( 'Role Slugs', 'edit-author-slug' ), 'ba_eas_admin_setting_callback_role_slugs', 'edit-author-slug', 'ba_eas_author_base' );
	register_setting( 'edit-author-slug', '_ba_eas_role_slugs', 'ba_eas_admin_setting_sanitize_callback_role_slugs' );

	// Add the default user nicename section
	add_settings_section( 'ba_eas_auto_update', __( 'Automatic Author Slug Creation', 'edit-author-slug' ), 'ba_eas_admin_setting_callback_auto_update_section', 'edit-author-slug' );

	// Auto-update on/off
	add_settings_field( '_ba_eas_do_auto_update', __( 'Automatically Update', 'edit-author-slug' ), 'ba_eas_admin_setting_callback_do_auto_update', 'edit-author-slug', 'ba_eas_auto_update' );
	register_setting( 'edit-author-slug', '_ba_eas_do_auto_update', 'intval' );

	// Default user nicename setting
	add_settings_field( '_ba_eas_default_user_nicename', __( 'Author Slug', 'edit-author-slug' ), 'ba_eas_admin_setting_callback_default_user_nicename', 'edit-author-slug', 'ba_eas_auto_update' );
	register_setting( 'edit-author-slug', '_ba_eas_default_user_nicename' );
}

/**
 * Add Author Base settings section.
 *
 * @since 0.9.0
 *
 * @uses _e() To echo localized string.
 */
function ba_eas_admin_setting_callback_author_base_section() {
?>

		<p><?php _e( 'Change your author base to something more fun!', 'edit-author-slug' ); ?></p>

<?php
}

/**
 * Add default user nicename settings section.
 *
 * @since 0.9.0
 *
 * @uses _e() To echo localized string.
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
 * @uses ba_eas() BA_Edit_Author_Slug object
 * @uses apply_filters() To call 'editable_slug' hook
 * @uses esc_attr() To sanitize the author base
 */
function ba_eas_admin_setting_callback_author_base() {

	$author_base = apply_filters( 'editable_slug', ba_eas()->author_base );
?>

		<input id="_ba_eas_author_base" name="_ba_eas_author_base" type="text" value="<?php echo esc_attr( $author_base ); ?>" class="regular-text code" /> <em><?php _e( "Defaults to 'author'", 'edit-author-slug' ); ?></em>

<?php
}

/**
 * Add Role-Based Author Base checkbox.
 *
 * @since 1.0.0
 *
 * @uses ba_eas() BA_Edit_Author_Slug object
 * @uses checked() To display the checked attribute
 * @uses esc_html_e() To sanitize localized text for display
 */
function ba_eas_admin_setting_callback_do_role_based() {
?>

		<input name="_ba_eas_do_role_based" id="_ba_eas_do_role_based" value="1"<?php checked( ba_eas()->do_role_based, '1' ); ?> type="checkbox" />
		<label for="_ba_eas_do_role_based"><?php esc_html_e( 'Set user\'s Author Base according to their role. (The above "Author Base" setting will be used as a fallback.)', 'edit-author-slug' ); ?></label>

<?php
}

/**
 * Output the Role-Based Author Base slugs for editing.
 *
 * @since 1.0.0
 *
 * @uses ba_eas() To get the role slugs object.
 * @uses ba_eas_get_default_role_slugs() To get the editable roles.
 * @uses translate_user_role() To translate the impossible.
 * @uses sanitize_title() To sanitize the role slugs.
 * @uses esc_attr() To sanitize role slugs for display.
 * @uses esc_html() To sanitize localized text for display.
 */
function ba_eas_admin_setting_callback_role_slugs() {

	// Make sure we didn't pick up any dynamic roles between now and initialization
	$roles = array_replace_recursive( ba_eas_get_default_role_slugs(), ba_eas()->role_slugs );

	// Display the role slug customization fields
	foreach ( $roles as $role => $details ) {

		if ( empty( $details['name'] ) ) {
			continue;
		}

		// Check for empty slugs from picking up a dynamic role
		if ( empty( $details['slug'] ) ) {
			$details['slug'] = sanitize_title( translate_user_role( $details['name'] ) );
		}
?>

		<input name="_ba_eas_role_slugs[<?php echo esc_attr( $role ); ?>][slug]" id="_ba_eas_role_slugs[<?php echo esc_attr( $role ); ?>][slug]" type="text" value="<?php echo sanitize_title( $details['slug'] ); ?>" class="regular-text code" />
		<label for="_ba_eas_role_slugs[<?php echo esc_attr( $role ); ?>][slug]"><?php echo esc_html( translate_user_role( $details['name'] ) ); ?></label><br />

<?php
	}
}

/**
 * Sanitize the custom Role-Based Author Base slugs.
 *
 * @since 1.0.0
 *
 * @uses ba_eas_get_default_role_slugs() To get the editable roles.
 * @uses sanitize_title() To sanitize the slug.
 * @uses ba_eas() BA_Edit_Author_Slug object.
 */
function ba_eas_admin_setting_sanitize_callback_role_slugs( $role_slugs = array() ) {

	// Get default role slugs
	$defaults = ba_eas_get_default_role_slugs();

	// Sanitize the slugs passed via POST
	foreach ( $role_slugs as $role => $details ) {
		$slug = sanitize_title( $details['slug'] );

		// Make sure we have a slug
		if ( empty( $slug ) ) {
			$slug = ba_eas()->author_base;

			if ( ! empty( $defaults[ $role ]['slug'] ) ) {
				$slug = $defaults[ $role ]['slug'];
			}
		}

		$role_slugs[ $role ]['slug'] = $slug;
	}

	/*
	 * Merge our new settings with what's stored in the db. This is needed because
	 * the editable_roles filter may mean that lower level admins don't have access
	 * to all roles. This could lead to lower level admins stamping out
	 * customizations that only a higher level admin can (and has already) set.
	 */
	$role_slugs = array_replace_recursive( ba_eas()->role_slugs, $role_slugs );

	// Set BA_Edit_Author_Slug::role_slugs for later use
	ba_eas()->role_slugs = $role_slugs;

	return $role_slugs;
}

/**
 * Add auto-update checkbox.
 *
 * @since 0.9.0
 *
 * @uses ba_eas() BA_Edit_Author_Slug object.
 * @uses checked() To display the checked attribute.
 * @uses esc_html_e() To sanitize localized text for display.
 */
function ba_eas_admin_setting_callback_do_auto_update() {
?>

		<input name="_ba_eas_do_auto_update" id="_ba_eas_do_auto_update" value="1"<?php checked( ba_eas()->do_auto_update, '1' ); ?> type="checkbox" />
		<label for="_ba_eas_do_auto_update"><?php esc_html_e( 'Automatically update Author Slug when a user updates their profile.', 'edit-author-slug' ); ?></label>

<?php
}

/**
 * Add default user nicename options.
 *
 * @since 0.9.0
 *
 * @uses ba_eas() BA_Edit_Author_Slug object.
 * @uses apply_filters() To call 'ba_eas_default_user_nicename_options_list' hook.
 * @uses esc_attr() To sanitize the nicename options.
 * @uses selected() To determine if we're selected.
 */
function ba_eas_admin_setting_callback_default_user_nicename() {

	// Get the nicename structure
	$structure = ba_eas()->default_user_nicename;

	// Set to default nicename structure if needed
	if ( empty( $structure ) ) {
		$structure = 'username';
	}

	// Setup a filterable list of nicename auto-update options
	$options = (array) apply_filters( 'ba_eas_default_user_nicename_options_list', array(
		'username'    => __( 'username (Default)', 'edit-author-slug' ),
		'nickname'    => __( 'nickname',           'edit-author-slug' ),
		'displayname' => __( 'displayname',        'edit-author-slug' ),
		'firstname'   => __( 'firstname',          'edit-author-slug' ),
		'lastname'    => __( 'lastname',           'edit-author-slug' ),
		'firstlast'   => __( 'firstname-lastname', 'edit-author-slug' ),
		'lastfirst'   => __( 'lastname-firstname', 'edit-author-slug' ),
	) );

	// Filter out any duplicates/empties
	$options = array_unique( array_filter( array_map( 'trim', $options ) ) );
?>

		<select id="_ba_eas_default_user_nicename" name="_ba_eas_default_user_nicename">
		<?php foreach ( (array) $options as $id => $item ) { ?>
			<option id="<?php echo esc_attr( $id ); ?>" value="<?php echo esc_attr( $id ); ?>"<?php selected( $structure, $id ); ?>><?php echo esc_attr( $item ); ?></option>
		<?php } ?>
		</select>

<?php
}

/**
 * Add settings link to plugin listing.
 *
 * @since 0.9.1
 *
 * @param array $links Links array in which we would prepend our link.
 * @param string $file Current plugin basename.
 *
 * @uses ba_eas() BA_Edit_Author_Slug object.
 * @uses plugin_basename() To get the plugin basename.
 * @uses add_query_arg() To add the edit-author-slug query arg.
 * @uses admin_url() To get the admin url.
 */
function ba_eas_add_settings_link( $links, $file ) {

	if ( ba_eas()->plugin_basename == $file ) {
		$settings_link = '<a href="' . add_query_arg( array( 'page' => 'edit-author-slug' ), admin_url( 'options-general.php' ) ) . '">' . __( 'Settings', 'edit-author-slug' ) . '</a>';
		array_unshift( $links, $settings_link );
	}

	return $links;
}

/** Install/Upgrade ***********************************************************/

/**
 * Install Edit Author Slug.
 *
 * Add options on install to reduce calls to the db.
 *
 * @since 1.0.0
 *
 * @uses ba_eas() BA_Edit_Author_Slug object.
 * @uses add_option() To add Edit Author Slug options.
 */
function ba_eas_install() {

	// Edit Author Slug instance
	$ba_eas = ba_eas();

	// Bail if it's not a new install
	if ( 0 !== $ba_eas->current_db_version ) {
		return;
	}

	// Add the options
	add_option( '_ba_eas_author_base',           $ba_eas->author_base           );
	add_option( '_ba_eas_db_version',            $ba_eas->db_version            );
	add_option( '_ba_eas_do_auto_update',        $ba_eas->do_auto_update        );
	add_option( '_ba_eas_default_user_nicename', $ba_eas->default_user_nicename );
	add_option( '_ba_eas_do_role_based',         $ba_eas->do_role_based         );
	add_option( '_ba_eas_role_slugs',            $ba_eas->role_slugs            );
}

/**
 * Upgrade Edit Author Slug.
 *
 * Just cleans up some options for now.
 *
 * @since 0.8.0
 *
 * @global object WPDB object.
 * @uses WPDB::update() To rename the old Edit Author Slug options array.
 * @uses ba_eas() BA_Edit_Author_Slug object.
 * @uses add_option() To add new Edit Author Slug options.
 * @uses update_option() To update Edit Author Slug options.
 * @uses ba_eas_flush_rewrite_rules() To flush rewrite rules after version bump.
 */
function ba_eas_upgrade() {

	// Edit Author Slug instance
	$ba_eas = ba_eas();

	// We're up-to-date, so let's move on
	if ( $ba_eas->current_db_version === $ba_eas->db_version ) {
		return;
	}

	// 1.0.0
	if ( $ba_eas->current_db_version < 133 ) {
		add_option( '_ba_eas_do_auto_update',        $ba_eas->do_auto_update        );
		add_option( '_ba_eas_default_user_nicename', $ba_eas->default_user_nicename );
		add_option( '_ba_eas_do_role_based',         $ba_eas->do_role_based         );
		add_option( '_ba_eas_role_slugs',            $ba_eas->role_slugs            );
	}

	// 0.8.0
	if ( $ba_eas->current_db_version < 132 ) {
		// Add new options
		add_option( '_ba_eas_author_base', $ba_eas->author_base );

		// Rename the old option for safe keeping
		global $wpdb;
		$wpdb->update( $wpdb->options, array( 'option_name' => '_ba_eas_old_options' ), array( 'option_name' => 'ba_edit_author_slug' ) );
	}

	// Version bump
	update_option( '_ba_eas_db_version', $ba_eas->db_version );

	// Courtesy flush
	ba_eas_flush_rewrite_rules();
}
