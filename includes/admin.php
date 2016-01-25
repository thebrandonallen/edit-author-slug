<?php

/**
 * Edit Author Slug Admin Functions.
 *
 * @package Edit_Author_Slug
 * @subpackage Administration
 *
 * @author Brandon Allen
 */

// Exit if accessed directly.
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
 * @param WP_User $user The WP_User object.
 *
 * @uses ba_eas_can_edit_author_slug() To verify current user can edit the author slug.
 * @uses ba_eas_sanitize_nicename() To sanitize userdata into a new nicename.
 * @uses apply_filters() To call the `ba_eas_show_user_nicename_options_list` hook.
 * @uses ba_eas_trim_nicename() To trim the nicename to 50 characters via `array_map()`.
 * @uses esc_html_e() To sanitize localized string for display.
 * @uses checked() To check that box.
 * @uses ba_eas_esc_nicename() To escape the nicename for display.
 */
function ba_eas_show_user_nicename( $user ) {

	// Return early if the user can't edit the author slug.
	if ( ! ba_eas_can_edit_author_slug() ) {
		return;
	}

	// Setup the nicename.
	$nicename = '';
	if ( ! empty( $user->user_nicename ) ) {
		$nicename = $user->user_nicename;
	}

	// Setup options array.
	$options = array();
	$options['username']    = ba_eas_sanitize_nicename( $user->nickname     );
	$options['displayname'] = ba_eas_sanitize_nicename( $user->display_name );

	// Setup the first name.
	if ( ! empty( $user->first_name ) ) {
		$options['firstname'] = ba_eas_sanitize_nicename( $user->first_name );
	}

	// Setup the last name.
	if ( ! empty( $user->last_name ) ) {
		$options['lastname'] = ba_eas_sanitize_nicename( $user->last_name );
	}

	// Setup the first/last name combos.
	if ( ! empty( $options['firstname'] ) && ! empty( $options['lastname'] ) ) {
		$options['firslast']  = $options['firstname'] . '-' . $options['lastname'];
		$options['lastfirst'] = $options['lastname'] . '-' . $options['firstname'];
	}

	// Setup the last name.
	if ( ! empty( $user->ID ) ) {
		$options['id'] = ba_eas_sanitize_nicename( $user->ID );
	}
	/**
	 * Filters the array of user nicename options.
	 *
	 * @since 0.9.0
	 *
	 * @param array   $options An array of of user nicename options.
	 * @param WP_User $user    The WP_User object.
	 */
	$options = apply_filters( 'ba_eas_show_user_nicename_options_list', $options, $user );

	// Trim nicenames to 50 characters, and filter out any duplicates or empties.
	$options = array_map( 'ba_eas_trim_nicename', (array) $options );
	$options = array_unique( array_filter( $options ) );

	// Set default for checked status.
	$checked = true;
	?>

	<h2><?php esc_html_e( 'Edit Author Slug', 'edit-author-slug' ); ?></h2>
	<p><?php esc_html_e( 'Choose an Author Slug based on the above profile information, or create your own.', 'edit-author-slug' ); ?> <br /><span class="description"><?php esc_html_e( "ie. - 'user-name', 'firstname-lastname', or 'master-ninja'", 'edit-author-slug' ); ?></span></p>
	<table class="form-table">
		<tbody><tr>
			<th scope="row"><?php esc_html_e( 'Author Slug', 'edit-author-slug' ); ?></th>
			<td>
				<fieldset><legend class="screen-reader-text"><span><?php esc_html_e( 'Author Slug', 'edit-author-slug' ); ?></span></legend>
				<?php foreach ( (array) $options as $item ) {

					// Checked?
					$checked_text = checked( $item, $nicename, false );

					// Flip the switch if we're checked to block custom from being checked.
					if ( ! empty( $checked_text ) ) {
						$checked = false;
					}
				?>
				<label title="<?php echo ba_eas_esc_nicename( $item ); ?>">
					<input type="radio" id="ba_eas_author_slug" name="ba_eas_author_slug" value="<?php echo ba_eas_esc_nicename( $item ); ?>" autocapitalize="none" autocorrect="off" maxlength="50"<?php echo $checked_text; ?>>
					<span><?php echo ba_eas_esc_nicename( $item ); ?></span>
				</label><br />
				<?php } ?>
				<label for="ba_eas_author_slug_custom_radio">
					<input type="radio" id="ba_eas_author_slug_custom_radio" name="ba_eas_author_slug" value="\c\u\s\t\o\m" autocapitalize="none" autocorrect="off" maxlength="50"<?php checked( $checked ); ?>>
					<?php esc_html_e( 'Custom:', 'edit-author-slug' ); ?>
					<span class="screen-reader-text"><?php esc_html_e( 'Enter a custom author slug in the following field', 'edit-author-slug' ); ?></span>
				</label>
				<label for="ba_eas_author_slug_custom" class="screen-reader-text"><?php esc_html_e( 'Custom author slug:', 'edit-author-slug' ); ?></label>
				<input type="text" name="ba_eas_author_slug_custom" id="ba_eas_author_slug_custom" value="<?php echo ba_eas_esc_nicename( $nicename ); ?>" class="regular-text" />
				</fieldset>
			</td>
		</tr></tbody>
	</table>

	<?php
}

/**
 * Prepare the user nicename for updating if applicable.
 *
 * The actual updating is handled by WP as long as no errors are thrown by WP,
 * some third party, or us.
 *
 * @since 0.1.0
 *
 * @param WP_Errors $errors The WP_Errors object.
 * @param bool      $update Are we updating?
 * @param WP_User   $user   The WP_User object.
 *
 * @uses check_admin_referer() To verify the nonce and check referer.
 * @uses ba_eas_can_edit_author_slug() To verify current user can edit the author slug.
 * @uses get_userdata() To get the user data.
 * @uses WP_Errors::add() To add Edit Author Slug specific errors.
 * @uses ba_eas_sanitize_nicename() Used to sanitize user_nicename.
 * @uses remove_action() To remove the `ba_eas_auto_update_user_nicename` and prevent looping.
 * @uses get_user_by() To see if the nicename is already in use.
 * @uses esc_html() To sanitize author_slug for display.
 */
function ba_eas_update_user_nicename( $errors, $update, $user ) {

	// Bail early if user can't edit the slug.
	if ( ! ba_eas_can_edit_author_slug() ) {
		return;
	}

	// Don't run the auto-update if the current user can update their own nicename.
	remove_action( 'profile_update', 'ba_eas_auto_update_user_nicename' );

	// We shouldn't be here if we're not updating.
	if ( ! $update ) {
		return;
	}

	// Validate the user_id.
	if ( empty( $user->ID ) ) {
		return;
	}

	// Check the nonce.
	check_admin_referer( 'update-user_' . $user->ID );

	// Stash the original user object.
	$_user = get_userdata( $user->ID );

	// Check for a custom author slug.
	if ( isset( $_POST['ba_eas_author_slug'] ) && isset( $_POST['ba_eas_author_slug_custom'] ) && '\c\u\s\t\o\m' === stripslashes( $_POST['ba_eas_author_slug'] ) ) {
		$_POST['ba_eas_author_slug'] = $_POST['ba_eas_author_slug_custom'];
	}

	// Setup the author slug.
	$user_nicename = '';
	if ( ! empty( $_POST['ba_eas_author_slug'] ) ) {
		$user_nicename = trim( stripslashes( $_POST['ba_eas_author_slug'] ) );
	}

	// Do we have an author slug?
	if ( empty( $user_nicename ) ) {
		$errors->add(
			'user_nicename_empty',
			__( '<strong>ERROR</strong>: An author slug cannot be blank. Please try again.', 'edit-author-slug' )
		);
		return;
	}

	// Stash author slug as it was, mostly, passed.
	$raw_nicename = $user_nicename;

	// Check to see if the passed nicename contains any invalid characters.
	$ascii = ba_eas_nicename_is_ascii( $user_nicename );

	// Sanitize the author slug and cache the pre-filtered, sanitized version.
	$user_nicename = $raw_nicename_sanitized = ba_eas_sanitize_nicename( $user_nicename );

	/**
	 * Filters the sanitized user nicename before any final checks are run.
	 *
	 * @since 1.1.0
	 *
	 * @param string $user_nicename The sanitized user nicename.
	 * @param int    $user_id       The user id.
	 * @param string $raw_nicename  The un-sanitized user nicename.
	 * @param bool   $ascii         True if the nicename contains only characters
	 *                              that can be converted to allowed ASCII characters.
	 */
	$user_nicename = ba_eas_sanitize_nicename( apply_filters(
		'ba_eas_pre_update_user_nicename',
		$user_nicename,
		$user->ID,
		$raw_nicename,
		$ascii
	) );

	// Was the nicename filtered?
	$changed = ( $raw_nicename_sanitized !== $user_nicename );

	// Reset `$ascii` if the nicename was filtered.
	if ( $changed ) {
		$ascii = ba_eas_nicename_is_ascii( $user_nicename );
	}

	// Bail and throw an error if the nicename contains invalid characters.
	if ( ! $ascii ) {
		$errors->add(
			'user_nicename_invalid_characters',
			__( '<strong>ERROR</strong>: An author slug can only contain alphanumeric characters, underscores (_) and dashes (-).', 'edit-author-slug' )
		);
		return;
	}

	// Bail and throw an error if the nicename is empty after sanitization.
	if ( empty( $user_nicename ) ) {
		$errors->add(
			'user_nicename_invalid',
			__( '<strong>ERROR</strong>: That author slug appears to be invalid. Please try something different.', 'edit-author-slug' )
		);
		return;
	}

	// Bail and throw an error if the nicename contains more than 50 characters.
	if ( mb_strlen( $user_nicename ) > 50 ) {
		$errors->add(
			'user_nicename_too_long',
			__( '<strong>ERROR</strong>: An author slug may not be longer than 50 characters.', 'edit-author-slug' )
		);
		return;
	}

	// Make sure the passed nicename is different from the user's current nicename.
	if ( $user_nicename !== $_user->user_nicename ) {

		// Bail and throw an error if the nicename already exists.
		$exists = get_user_by( 'slug', $user_nicename );
		if ( $exists && (int) $exists->ID !== $user->ID ) {

			// Setup the error message.
			$message = __(
				'<strong>ERROR</strong>: The author slug, %1$s, already exists. Please try something different.',
				'edit-author-slug'
			);

			// Add the error message.
			$errors->add(
				'user_nicename_exists',
				sprintf(
					$message,
					'<strong><em>' . ba_eas_esc_nicename( $user_nicename ) . '</em></strong>'
				)
			);

			return;
		}

		// Looks like we made it, so let's update.
		$user->user_nicename = $user_nicename;

		// Update the nicename cache.
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
 * @uses apply_filters() To call `ba_eas_can_edit_author_slug` hook.
 *
 * @return bool True if edit privileges. Defaults to false.
 */
function ba_eas_can_edit_author_slug() {

	// Default to false.
	$retval = false;

	// True if user is allowed to edit the author slug.
	if ( is_super_admin() || current_user_can( 'edit_users' ) || current_user_can( 'edit_author_slug' ) ) {
		$retval = true;
	}

	/**
	 * Filters the return of `ba_eas_can_edit_author_slug()`.
	 *
	 * @since 0.8.0
	 *
	 * @param bool $retval True if a user can edit the author slug.
	 */
	return (bool) apply_filters( 'ba_eas_can_edit_author_slug', $retval );
}

/**
 * Adds the 'Author Slug' column and column heading to the users list table.
 *
 * @since 0.5.0
 *
 * @param array $defaults Array of current columns/column headings.
 *
 * @uses esc_html__() To escape and localize the author slug column title.
 *
 * @return array Array of current columns/column headings.
 */
function ba_eas_author_slug_column( $defaults ) {

	// Set the new column name to "Author Slug".
	$defaults['ba-eas-author-slug'] = esc_html__( 'Author Slug', 'edit-author-slug' );

	return $defaults;
}

/**
 * Fill in user_nicename for 'Author Slug' column.
 *
 * Adds the user's corresponding user_nicename to the 'Author Slug' column.
 *
 * @since 0.5.0
 *
 * @param string $default     Value for column data. Defaults to empty string.
 * @param string $column_name Column name currently being filtered.
 * @param int    $user_id     The user id.
 *
 * @uses get_userdata() To get the user data.
 * @uses esc_attr() To sanitize the user_nicename.
 *
 * @return string Value for column data. Defaults to empty string.
 */
function ba_eas_author_slug_custom_column( $default, $column_name, $user_id ) {

	// Set row value to user_nicename if applicable.
	if ( 'ba-eas-author-slug' === $column_name ) {
		$user = get_userdata( $user_id );

		if ( ! empty( $user->user_nicename ) ) {
			$default = ba_eas_esc_nicename( $user->user_nicename );
		}
	}

	return $default;
}

/**
 * Add javascript to appropriate admin pages.
 *
 * Add javascript to user-edit.php and profile.php pages to update custom field
 * when other radio buttons are selected.
 *
 * Add javascript to Edit Author Slug settings page to show/hide the role slugs.
 *
 * @since 0.9.0
 *
 * @uses ba_eas_can_edit_author_slug() To determine if the user can edit the author slug.
 * @uses wp_register_script() To register our js file.
 * @uses ba_eas() To get the `$plugin_url` and `$version` properties.
 * @uses wp_enqueue_script() To enqueue our js file.
 */
function ba_eas_show_user_nicename_scripts( $hook_suffix = '' ) {

	// Set an array of pages to add our js.
	$user_pages = array(
		'profile.php',
		'user-edit.php',
		'settings_page_edit-author-slug',
	);

	// Bail if we shouldn't add our js.
	if ( ! in_array( $hook_suffix, $user_pages ) || ! ba_eas_can_edit_author_slug() ) {
		return;
	}

	// Decide whether to load the dev version of the js.
	$min = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	// Add our js to the appropriate pages.
	wp_register_script(
		'edit-author-slug',
		ba_eas()->plugin_url . "js/edit-author-slug{$min}.js",
		array( 'jquery' ),
		ba_eas()->version
	);
	wp_enqueue_script( 'edit-author-slug' );
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
	add_options_page(
		__( 'Edit Author Slug Settings', 'edit-author-slug' ),
		__( 'Edit Author Slug', 'edit-author-slug' ),
		'edit_users',
		'edit-author-slug',
		'ba_eas_settings_page_html'
	);
}

/**
 * Output HTML for settings page.
 *
 * @since 0.9.0
 *
 * @uses esc_html_e() To sanitize localized string for display.
 * @uses settings_fields() To output nonce, action, and option_page fields.
 * @uses do_settings_sections() To output the setting sections/fields.
 * @uses submit_button() To output a nice submit button.
 */
function ba_eas_settings_page_html() {
?>

	<div class="wrap">

		<h1><?php esc_html_e( 'Edit Author Slug Settings', 'edit-author-slug' ); ?></h1>

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
	// Add the Author Base section.
	add_settings_section(
		'ba_eas_author_base',
		__( 'Author Base', 'edit-author-slug' ),
		'ba_eas_admin_setting_callback_author_base_section',
		'edit-author-slug'
	);

	// Author Base setting.
	add_settings_field(
		'_ba_eas_author_base',
		__( 'Author Base', 'edit-author-slug' ),
		'ba_eas_admin_setting_callback_author_base',
		'edit-author-slug',
		'ba_eas_author_base'
	);
	register_setting( 'edit-author-slug', '_ba_eas_author_base', 'ba_eas_admin_setting_sanitize_callback_author_base' );

	// Role-Based Author Base setting.
	add_settings_field(
		'_ba_eas_do_role_based',
		__( 'Role-Based Author Base', 'edit-author-slug' ),
		'ba_eas_admin_setting_callback_do_role_based',
		'edit-author-slug',
		'ba_eas_author_base'
	);
	register_setting( 'edit-author-slug', '_ba_eas_do_role_based', 'intval' );

	// Role-Based Author Base slugs.
	add_settings_field(
		'_ba_eas_role_slugs',
		__( 'Role Slugs', 'edit-author-slug' ),
		'ba_eas_admin_setting_callback_role_slugs',
		'edit-author-slug',
		'ba_eas_author_base'
	);
	register_setting( 'edit-author-slug', '_ba_eas_role_slugs', 'ba_eas_admin_setting_sanitize_callback_role_slugs' );

	// Add the default user nicename section.
	add_settings_section(
		'ba_eas_auto_update',
		__( 'Automatic Author Slug Creation', 'edit-author-slug' ),
		'ba_eas_admin_setting_callback_auto_update_section',
		'edit-author-slug'
	);

	// Auto-update on/off.
	add_settings_field(
		'_ba_eas_do_auto_update',
		__( 'Automatically Update', 'edit-author-slug' ),
		'ba_eas_admin_setting_callback_do_auto_update',
		'edit-author-slug',
		'ba_eas_auto_update'
	);
	register_setting( 'edit-author-slug', '_ba_eas_do_auto_update', 'intval' );

	// Default user nicename setting.
	add_settings_field(
		'_ba_eas_default_user_nicename',
		__( 'Author Slug Structure', 'edit-author-slug' ),
		'ba_eas_admin_setting_callback_default_user_nicename',
		'edit-author-slug',
		'ba_eas_auto_update'
	);
	register_setting( 'edit-author-slug', '_ba_eas_default_user_nicename', 'sanitize_key' );

	// Add the Bulk Update section.
	add_settings_section(
		'ba_eas_bulk_update',
		__( 'Bulk Update Author Slugs', 'edit-author-slug' ),
		'ba_eas_admin_setting_callback_bulk_update_section',
		'edit-author-slug'
	);

	// Bulk update.
	add_settings_field(
		'_ba_eas_bulk_update',
		__( 'Bulk Update', 'edit-author-slug' ),
		'ba_eas_admin_setting_callback_bulk_update',
		'edit-author-slug',
		'ba_eas_bulk_update'
	);
	register_setting( 'edit-author-slug', '_ba_eas_bulk_update', 'ba_eas_auto_update_user_nicename_bulk' );

	// Bulk update.
	add_settings_field(
		'_ba_eas_bulk_update_structure',
		__( 'Author Slug Structure', 'edit-author-slug' ),
		'ba_eas_admin_setting_callback_bulk_update_structure',
		'edit-author-slug',
		'ba_eas_bulk_update'
	);
	register_setting( 'edit-author-slug', '_ba_eas_bulk_update_structure', '__return_false' );
}

/**
 * Add Author Base settings section.
 *
 * @since 0.9.0
 *
 * @uses esc_html_e() To sanitize localized string for display.
 */
function ba_eas_admin_setting_callback_author_base_section() {
?>

		<p><?php esc_html_e( 'Change your author base to something more fun!', 'edit-author-slug' ); ?></p>

<?php
}

/**
 * Sanitize the author base and update options/globals where appropriate.
 *
 * Rewrite rules are also flushed.
 *
 * @since 1.2.0
 *
 * @param string $author_base Defaults to `author`.
 *
 * @return string The sanitized author base.
 */
function ba_eas_admin_setting_sanitize_callback_author_base( $author_base = 'author' ) {

	// Edit Author Slug instance.
	$ba_eas = ba_eas();

	// Sanitize the author base.
	$author_base = ba_eas_sanitize_author_base( $author_base );

	// Do we need to update the author_base.
	if ( $author_base !== $ba_eas->author_base ) {
		// Setup the new author_base global.
		$ba_eas->author_base = $author_base;

		// Update options with new author_base.
		update_option( '_ba_eas_author_base', $author_base );

		// Update the author_base in the WP_Rewrite object.
		if ( ! empty( $author_base ) ) {
			$GLOBALS['wp_rewrite']->author_base = $author_base;
		}
	}

	// Courtesy flush.
	ba_eas_flush_rewrite_rules();

	return $author_base;
}

/**
 * Add default user nicename settings section.
 *
 * @since 0.9.0
 *
 * @uses esc_html_e() To sanitize localized string for display.
 */
function ba_eas_admin_setting_callback_auto_update_section() {
?>

		<p><?php esc_html_e( "Allow Author Slugs to be automatically updated, and set the default Author Slug structure for users. Automatic updating will only occur when a user can't edit Author Slugs on their own.", 'edit-author-slug' ); ?> <br /><strong><em><?php esc_html_e( 'This could have SEO repercussions if users update their profiles frequently, and it will override any manual editing of the Author Slug you may have previously completed.', 'edit-author-slug' ); ?></em></strong></p>

<?php
}

/**
 * Add Author Base settings field.
 *
 * @since 0.9.0
 *
 * @uses ba_eas() To get the BA_Edit_Author_Slug object.
 * @uses apply_filters() To call `editable_slug` hook.
 * @uses esc_attr() To sanitize the author base.
 * @uses esc_html_e() To sanitize localized string for display.
 */
function ba_eas_admin_setting_callback_author_base() {

	$author_base = ba_eas_sanitize_author_base( ba_eas()->author_base );
?>

		<input id="_ba_eas_author_base" name="_ba_eas_author_base" type="text" value="<?php echo esc_attr( $author_base ); ?>" class="regular-text code" />
		<label><em><?php esc_html_e( "Defaults to 'author'", 'edit-author-slug' ); ?></em></label>

<?php
}

/**
 * Add Role-Based Author Base checkbox.
 *
 * @since 1.0.0
 *
 * @uses ba_eas() To get the BA_Edit_Author_Slug object.
 * @uses checked() To display the checked attribute.
 * @uses esc_html_e() To sanitize localized string for display.
 */
function ba_eas_admin_setting_callback_do_role_based() {
?>

		<input class="eas-checkbox" name="_ba_eas_do_role_based" id="_ba_eas_do_role_based" value="1"<?php checked( ba_eas()->do_role_based ); ?> type="checkbox" />
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

	// Get the default role slugs.
	$defaults = ba_eas_get_default_role_slugs();

	// Make sure we didn't pick up any dynamic roles between now and initialization.
	$roles = array_replace_recursive( $defaults, ba_eas()->role_slugs );

	// Display the role slug customization fields.
	foreach ( $roles as $role => $details ) {

		// Don't display a role slug if the role has been removed.
		if ( empty( $defaults[ $role ] ) ) {
			continue;
		}

		// Don't display a role slug, if the user can't see a name.
		if ( empty( $details['name'] ) ) {
			continue;
		}

		// Sanitize the slug.
		$details['slug'] = sanitize_title( $details['slug'] );

		// Check for empty slugs when picking up a dynamic role.
		if ( empty( $details['slug'] ) ) {
			$details['slug'] = sanitize_title( translate_user_role( $details['name'] ) );
		}
?>

		<input name="_ba_eas_role_slugs[<?php echo esc_attr( $role ); ?>][slug]" id="_ba_eas_role_slugs[<?php echo esc_attr( $role ); ?>][slug]" type="text" value="<?php echo ba_eas_esc_nicename( $details['slug'] ); ?>" class="regular-text code" />
		<label for="_ba_eas_role_slugs[<?php echo esc_attr( $role ); ?>][slug]"><?php echo esc_html( translate_user_role( $details['name'] ) ); ?></label><br />

<?php
	}
}

/**
 * Sanitize the custom Role-Based Author Base slugs.
 *
 * @since 1.0.0
 *
 * @param array $role_slugs An array of role slugs.
 *
 * @uses ba_eas_get_default_role_slugs() To get the editable roles.
 * @uses sanitize_title() To sanitize the slug.
 * @uses ba_eas() To get the BA_Edit_Author_Slug object.
 *
 * @return array An array of sanitized, role-based author slugs.
 */
function ba_eas_admin_setting_sanitize_callback_role_slugs( $role_slugs = array() ) {

	// Get default role slugs.
	$defaults = ba_eas_get_default_role_slugs();

	// Sanitize the slugs passed via POST.
	foreach ( $role_slugs as $role => $details ) {

		// If the role has been removed, we don't need to save it.
		if ( empty( $defaults[ $role ] ) ) {
			unset( $role_slugs[ $role ] );
			continue;
		}

		// Sanitize the passed role slug.
		$slug = sanitize_title( $details['slug'] );

		// Make sure we have a slug.
		if ( empty( $slug ) && ! empty( $defaults[ $role ]['slug'] ) ) {
			$slug = $defaults[ $role ]['slug'];
		}

		// Remove the role if we don't have a slug.
		if ( empty( $slug ) ) {
			unset( $role_slugs[ $role ] );

		// We made it through, so set the slug.
		} else {
			$role_slugs[ $role ]['slug'] = $slug;
		}
	}

	// Merge our changes to make sure we've got everything.
	$role_slugs = array_replace_recursive( $defaults, $role_slugs );

	// Set BA_Edit_Author_Slug::role_slugs for later use.
	ba_eas()->role_slugs = $role_slugs;

	return $role_slugs;
}

/**
 * Add auto-update checkbox.
 *
 * @since 0.9.0
 *
 * @uses ba_eas() To get the BA_Edit_Author_Slug object.
 * @uses checked() To display the checked attribute.
 * @uses esc_html_e() To sanitize localized string for display.
 */
function ba_eas_admin_setting_callback_do_auto_update() {
?>

		<input class="eas-checkbox" name="_ba_eas_do_auto_update" id="_ba_eas_do_auto_update" value="1"<?php checked( ba_eas()->do_auto_update ); ?> type="checkbox" />
		<label for="_ba_eas_do_auto_update"><?php esc_html_e( 'Automatically update Author Slug when a user updates their profile.', 'edit-author-slug' ); ?></label>

<?php
}

/**
 * Add default user nicename options.
 *
 * @since 0.9.0
 *
 * @uses ba_eas() To get the BA_Edit_Author_Slug object.
 * @uses apply_filters() To call `ba_eas_default_user_nicename_options_list` hook.
 * @uses esc_attr() To sanitize the nicename options.
 * @uses selected() To determine if we're selected.
 */
function ba_eas_admin_setting_callback_default_user_nicename() {

	// Get the nicename structure.
	$structure = ba_eas()->default_user_nicename;

	// Set to default nicename structure if needed.
	if ( empty( $structure ) ) {
		$structure = 'username';
	}

	/**
	 * Filters the array of user nicename structure options.
	 *
	 * @since 0.9.0
	 *
	 * @param array $options An array of of user nicename structure options.
	 */
	$options = (array) apply_filters( 'ba_eas_default_user_nicename_options_list', array(
		'username'    => __( 'username (Default)', 'edit-author-slug' ),
		'nickname'    => __( 'nickname',           'edit-author-slug' ),
		'displayname' => __( 'displayname',        'edit-author-slug' ),
		'firstname'   => __( 'firstname',          'edit-author-slug' ),
		'lastname'    => __( 'lastname',           'edit-author-slug' ),
		'firstlast'   => __( 'firstname-lastname', 'edit-author-slug' ),
		'lastfirst'   => __( 'lastname-firstname', 'edit-author-slug' ),
		'id'          => __( 'id',                 'edit-author-slug' ),
	) );

	// Filter out any duplicates/empties.
	$options = array_unique( array_filter( array_map( 'trim', $options ) ) );
?>

		<label><span class="screen-reader-text"><?php esc_html_e( 'Default author slug options', 'edit-author-slug' ); ?></span></label>
		<select id="_ba_eas_default_user_nicename" name="_ba_eas_default_user_nicename">
		<?php foreach ( (array) $options as $id => $item ) { ?>
			<option id="<?php echo esc_attr( $id ); ?>" value="<?php echo esc_attr( $id ); ?>"<?php selected( $structure, $id ); ?>><?php echo esc_html( $item ); ?></option>
		<?php } ?>
		</select>

<?php
}

/**
 * Add bulk update option.
 *
 * @since 1.1.0
 *
 * @uses esc_html_e() To escape the field label.
 *
 * @return void
 */
function ba_eas_admin_setting_callback_bulk_update_section() {
?>

		<p><?php esc_html_e( 'Update all users at once based on the specified Author Slug structure.', 'edit-author-slug' ); ?></p>

<?php
}

/**
 * Add bulk update option.
 *
 * @since 1.1.0
 *
 * @uses esc_html_e() To escape the field label.
 *
 * @return void
 */
function ba_eas_admin_setting_callback_bulk_update() {
?>

		<input class="eas-checkbox" name="_ba_eas_bulk_update" id="_ba_eas_bulk_update" value="1" type="checkbox" />
		<label for="_ba_eas_bulk_update"><?php esc_html_e( 'Update all users according to the below Author Slug setting. This will only be run after clicking "Save Changes".', 'edit-author-slug' ); ?></label>

<?php
}

/**
 * Add default user nicename options.
 *
 * @since 0.9.0
 *
 * @uses ba_eas() To get the BA_Edit_Author_Slug object.
 * @uses apply_filters() To call `ba_eas_default_user_nicename_options_list` hook.
 * @uses esc_attr() To sanitize the nicename options.
 * @uses selected() To determine if we're selected.
 */
function ba_eas_admin_setting_callback_bulk_update_structure() {

	// Get the nicename structure.
	$structure = ba_eas()->default_user_nicename;

	// Set to default nicename structure if needed.
	if ( empty( $structure ) ) {
		$structure = 'username';
	}

	/* Documented in `ba_eas_admin_setting_callback_default_user_nicename()` */
	$options = (array) apply_filters( 'ba_eas_default_user_nicename_options_list', array(
		'username'    => __( 'username (Default)', 'edit-author-slug' ),
		'nickname'    => __( 'nickname',           'edit-author-slug' ),
		'displayname' => __( 'displayname',        'edit-author-slug' ),
		'firstname'   => __( 'firstname',          'edit-author-slug' ),
		'lastname'    => __( 'lastname',           'edit-author-slug' ),
		'firstlast'   => __( 'firstname-lastname', 'edit-author-slug' ),
		'lastfirst'   => __( 'lastname-firstname', 'edit-author-slug' ),
		'id'          => __( 'id',                 'edit-author-slug' ),
	) );

	// Filter out any duplicates/empties.
	$options = array_unique( array_filter( array_map( 'trim', $options ) ) );
?>

		<label><span class="screen-reader-text"><?php esc_html_e( 'Default bulk update author slug options', 'edit-author-slug' ); ?></span></label>
		<select id="_ba_eas_bulk_update_structure" name="_ba_eas_bulk_update_structure">
		<?php foreach ( (array) $options as $id => $item ) { ?>
			<option id="<?php echo esc_attr( $id ); ?>" value="<?php echo esc_attr( $id ); ?>"<?php selected( $structure, $id ); ?>><?php echo esc_html( $item ); ?></option>
		<?php } ?>
		</select>

<?php
}

/**
 * Add settings link to plugin listing.
 *
 * @since 0.9.1
 *
 * @param array  $links Links array in which we would prepend our link.
 * @param string $file  Current plugin basename.
 *
 * @uses ba_eas() To get the BA_Edit_Author_Slug object.
 * @uses plugin_basename() To get the plugin basename.
 * @uses add_query_arg() To add the edit-author-slug query arg.
 * @uses admin_url() To get the admin url.
 *
 * @return string The array of plugin action links.
 */
function ba_eas_add_settings_link( $links, $file ) {

	if ( ba_eas()->plugin_basename === $file ) {
		$settings_link = '<a href="' . esc_url( add_query_arg( array( 'page' => 'edit-author-slug' ), admin_url( 'options-general.php' ) ) ) . '">' . esc_html__( 'Settings', 'edit-author-slug' ) . '</a>';
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
 * @uses ba_eas() To get the BA_Edit_Author_Slug object.
 * @uses add_option() To add Edit Author Slug options.
 */
function ba_eas_install() {

	// Edit Author Slug instance.
	$ba_eas = ba_eas();

	// Bail if it's not a new install.
	if ( 0 !== $ba_eas->current_db_version ) {
		return;
	}

	// Add the options.
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
 * @uses wpdb::update() To rename the old Edit Author Slug options array.
 * @uses ba_eas() To get the BA_Edit_Author_Slug object.
 * @uses add_option() To add new Edit Author Slug options.
 * @uses update_option() To update Edit Author Slug options.
 * @uses ba_eas_flush_rewrite_rules() To flush rewrite rules after version bump.
 */
function ba_eas_upgrade() {

	// Edit Author Slug instance.
	$ba_eas = ba_eas();

	// We're up-to-date, so let's move on.
	if ( $ba_eas->current_db_version === $ba_eas->db_version ) {
		return;
	}

	// < 0.8.0.
	if ( $ba_eas->current_db_version < 132 ) {
		// Add new options.
		add_option( '_ba_eas_author_base', $ba_eas->author_base );

		// Rename the old option for safe keeping.
		update_option( '_ba_eas_old_options', get_option( 'ba_edit_author_slug' ) );
		delete_option( 'ba_edit_author_slug' );
	}

	// < 1.0.0.
	if ( $ba_eas->current_db_version < 133 ) {
		add_option( '_ba_eas_do_auto_update',        $ba_eas->do_auto_update        );
		add_option( '_ba_eas_default_user_nicename', $ba_eas->default_user_nicename );
		add_option( '_ba_eas_do_role_based',         $ba_eas->do_role_based         );
		add_option( '_ba_eas_role_slugs',            $ba_eas->role_slugs            );
	}

	// Version bump.
	update_option( '_ba_eas_db_version', $ba_eas->db_version );

	// Courtesy flush.
	ba_eas_flush_rewrite_rules();
}
