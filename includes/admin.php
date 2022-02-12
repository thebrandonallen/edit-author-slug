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
defined( 'ABSPATH' ) || exit;

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
 */
function ba_eas_show_user_nicename( $user ) {

	// Return early if the user can't edit the author slug.
	if ( empty( $user->ID ) || ! ba_eas_can_edit_author_slug() ) {
		return;
	}

	// Setup the nicename.
	$nicename = $user->user_nicename;

	// Setup options array.
	$options = array(
		'username'    => ba_eas_get_nicename_by_structure( $user->ID, 'username' ),
		'nickname'    => ba_eas_get_nicename_by_structure( $user->ID, 'nickname' ),
		'displayname' => ba_eas_get_nicename_by_structure( $user->ID, 'displayname' ),
		'firstname'   => ba_eas_get_nicename_by_structure( $user->ID, 'firstname' ),
		'lastname'    => ba_eas_get_nicename_by_structure( $user->ID, 'lastname' ),
		'firstlast'   => ba_eas_get_nicename_by_structure( $user->ID, 'firstlast' ),
		'lastfirst'   => ba_eas_get_nicename_by_structure( $user->ID, 'lastfirst' ),
		'userid'      => ba_eas_get_nicename_by_structure( $user->ID, 'userid' ),
		'hash'        => ba_eas_get_nicename_by_structure( $user->ID, 'hash' ),
	);

	// Remove the username as a option if the user has requested it not be
	// available via iThemes force unique nicename.
	if ( ba_eas()->is_itsec_force_unique_nickname() ) {
		unset( $options['username'] );
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

	<h2><?php echo esc_html_x( 'Edit Author Slug', 'Plugin settings page heading', 'edit-author-slug' ); ?></h2>
	<p><?php esc_html_e( 'Choose an Author Slug based on the above profile information, or create your own.', 'edit-author-slug' ); ?> <br /><span class="description"><?php esc_html_e( "ie. - 'user-name', 'firstname-lastname', or 'master-ninja'", 'edit-author-slug' ); ?></span></p>
	<table class="form-table">
		<tbody><tr>
			<th scope="row"><?php esc_html_e( 'Author Slug', 'edit-author-slug' ); ?></th>
			<td>
				<fieldset><legend class="screen-reader-text"><span><?php esc_html_e( 'Author Slug', 'edit-author-slug' ); ?></span></legend>
				<?php
				foreach ( (array) $options as $item ) :

					// Checked?
					$checked_text = checked( $item, $nicename, false );

					// Flip the switch if we're checked to block custom from being checked.
					if ( ! empty( $checked_text ) ) {
						$checked = false;
					}
					?>
				<label title="<?php echo ba_eas_esc_nicename( $item ); ?>">
					<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<input type="radio" class="eas-author-slug" name="ba_eas_author_slug" value="<?php echo ba_eas_esc_nicename( $item ); ?>" autocapitalize="none" autocorrect="off" maxlength="50"<?php echo $checked_text; ?>>
					<span><?php echo ba_eas_esc_nicename( $item ); ?></span>
				</label><br />
				<?php endforeach; ?>
				<label for="ba_eas_author_slug_custom_radio">
					<input type="radio" class="eas-author-slug-custom-radio" name="ba_eas_author_slug" value="\c\u\s\t\o\m" autocapitalize="none" autocorrect="off" maxlength="50"<?php checked( $checked ); ?>>
					<?php esc_html_e( 'Custom:', 'edit-author-slug' ); ?>
					<span class="screen-reader-text"><?php esc_html_e( 'Enter a custom author slug in the following field', 'edit-author-slug' ); ?></span>
				</label>
				<label for="ba_eas_author_slug_custom" class="screen-reader-text"><?php esc_html_e( 'Custom author slug:', 'edit-author-slug' ); ?></label>
				<input type="text" name="ba_eas_author_slug_custom" class="eas-author-slug-custom" value="<?php echo ba_eas_esc_nicename( $nicename ); ?>" class="regular-text" />
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
 * @param WP_Error $errors The WP_Error object.
 * @param bool     $update True if user is being updated.
 * @param object   $user   An stdClass with user properties.
 */
function ba_eas_update_user_nicename( $errors, $update, $user ) {

	// We shouldn't be here if we're not updating.
	if ( ! $update ) {
		return;
	}

	// Validate the user_id.
	if ( empty( $user->ID ) ) {
		return;
	}

	// Bail if user can't edit the slug.
	if ( ! ba_eas_can_edit_author_slug() ) {
		return;
	}

	// Check the nonce.
	check_admin_referer( 'update-user_' . $user->ID );

	// Don't run the auto-update if the current user can update their own nicename.
	remove_action( 'profile_update', 'ba_eas_auto_update_user_nicename' );

	// Set some default variables.
	$old_user_nicename    = get_user_by( 'id', $user->ID )->user_nicename;
	$user_nicename        = '';
	$user_nicename_custom = '';

	if ( isset( $_POST['ba_eas_author_slug'] ) ) {
		$user_nicename = trim( wp_unslash( $_POST['ba_eas_author_slug'] ) );
	}

	if ( isset( $_POST['ba_eas_author_slug_custom'] ) ) {
		$user_nicename_custom = trim( wp_unslash( $_POST['ba_eas_author_slug_custom'] ) );
	}

	// Check for a custom author slug.
	if ( '\c\u\s\t\o\m' === $user_nicename ) {
		$user_nicename = $user_nicename_custom;
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
	$user_nicename          = ba_eas_sanitize_nicename( $user_nicename );
	$raw_nicename_sanitized = $user_nicename;

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
	$user_nicename = ba_eas_sanitize_nicename(
		apply_filters(
			'ba_eas_pre_update_user_nicename',
			$user_nicename,
			$user->ID,
			$raw_nicename,
			$ascii
		)
	);

	// Reset `$ascii` if the nicename was filtered.
	if ( $raw_nicename_sanitized !== $user_nicename ) {
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

	// iThemes Security's Force Unique Nickname needs special handling.
	if ( ba_eas()->is_itsec_force_unique_nickname() ) {
		// Unless there's an error, iThemes Security will always update the
		// nicename. We need to make sure it's reset back to the old nicename,
		// so it's not unexpectedly changed.
		$user->user_nicename = $old_user_nicename;

		// Bail and throw an error if the nicename is the same as the user login.
		if ( $user->user_login === $user_nicename ) {
			$errors->add(
				'user_nicename_itsec_block',
				__( '<strong>ERROR</strong>: Your iThemes settings prevent your author slug from being the same as your username.', 'edit-author-slug' )
			);
			return;
		}
	}

	// Bail if the nicename hasn't changed.
	if ( $user_nicename === $old_user_nicename ) {
		return;
	}

	// Bail and throw an error if the nicename already exists.
	if ( ba_eas_nicename_exists( $user_nicename, $user ) ) {

		// Setup the error message.
		/* translators: 1: author slug */
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
}

/**
 * Can the current user edit the author slug?
 *
 * @since 0.8.0
 *
 * @return bool True if edit privileges. Defaults to false.
 */
function ba_eas_can_edit_author_slug() {

	// Default to false.
	$retval = false;

	// True if user is allowed to edit the author slug.
	if ( current_user_can( 'edit_users' ) || current_user_can( 'edit_author_slug' ) ) {
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
 * @param string $hook_suffix The current admin page.
 *
 * @return void
 */
function ba_eas_show_user_nicename_scripts( $hook_suffix = '' ) {

	// Set an array of pages to add our js.
	$user_pages = array(
		'profile.php',
		'user-edit.php',
		'settings_page_edit-author-slug',
	);

	// Bail if we shouldn't add our js.
	if ( ! in_array( $hook_suffix, $user_pages, true ) || ! ba_eas_can_edit_author_slug() ) {
		return;
	}

	// Decide whether to load the dev version of the js.
	$min = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	// Add our js to the appropriate pages.
	wp_register_script(
		'edit-author-slug',
		ba_eas()->plugin_url . "js/edit-author-slug{$min}.js",
		array(),
		BA_Edit_Author_Slug::VERSION,
		true
	);
	wp_enqueue_script( 'edit-author-slug' );
}

/** Settings *****************************************************************/

/**
 * Add the Edit Author Slug Settings Menu.
 *
 * @since 0.9.0
 * @since 1.6.1 Only show the page if a user can edit author slugs.
 */
function ba_eas_add_settings_menu() {
	// If the user can edit author slugs, maybe show the options page.
	if ( ba_eas_can_edit_author_slug() ) {
		add_options_page(
			__( 'Edit Author Slug Settings', 'edit-author-slug' ),
			_x( 'Edit Author Slug', 'Settings menu item', 'edit-author-slug' ),
			'edit_users',
			'edit-author-slug',
			'ba_eas_settings_page_html'
		);
	}
}

/**
 * Output HTML for settings page.
 *
 * @since 0.9.0
 */
function ba_eas_settings_page_html() {
	?>

	<div class="wrap">

		<h1 id="edit-author-slug"><?php esc_html_e( 'Edit Author Slug Settings', 'edit-author-slug' ); ?></h1>

		<div class="notice notice-large">
			<a href="<?php echo esc_url( get_edit_profile_url() ); ?>"><?php esc_html_e( 'You can customize your own author slug by visiting your profile page. ', 'edit-author-slug' ); ?></a><?php esc_html_e( 'This also applies to other users.', 'edit-author-slug' ); ?>
		</div>

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
		'ba_eas_author_base',
		array(
			'label_for' => 'eas-author-base',
		)
	);
	register_setting( 'edit-author-slug', '_ba_eas_author_base', 'ba_eas_sanitize_author_base' );

	// Remove front setting.
	if ( ba_eas_has_front() ) {
		add_settings_field(
			'_ba_eas_remove_front',
			__( 'Remove Front', 'edit-author-slug' ),
			'ba_eas_admin_setting_callback_remove_front',
			'edit-author-slug',
			'ba_eas_author_base'
		);
		register_setting( 'edit-author-slug', '_ba_eas_remove_front', 'intval' );
	}

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
		'ba_eas_auto_update',
		array(
			'label_for' => 'eas-default-user-nicename',
		)
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
		'ba_eas_bulk_update',
		array(
			'label_for' => 'eas-bulk-update-structure',
		)
	);
	register_setting( 'edit-author-slug', '_ba_eas_bulk_update_structure', '__return_false' );
}

/**
 * Add Author Base settings section.
 *
 * @since 0.9.0
 */
function ba_eas_admin_setting_callback_author_base_section() {
	?>

		<p><?php esc_html_e( 'Change your author base to something more fun!', 'edit-author-slug' ); ?></p>

	<?php
}

/**
 * Add default user nicename settings section.
 *
 * @since 0.9.0
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
 */
function ba_eas_admin_setting_callback_author_base() {

	$author_base = ba_eas()->author_base;
	$front       = trim( $GLOBALS['wp_rewrite']->front, '/' );

	// Add the trailing slash back if `$front` isn't empty.
	if ( ! empty( $front ) ) {
		$front = trailingslashit( $front );
	}
	?>

		<input id="eas-author-base" name="_ba_eas_author_base" type="text" value="<?php echo esc_attr( $author_base ); ?>" class="regular-text code" />
		<br />
		<em><?php esc_html_e( "Defaults to 'author'", 'edit-author-slug' ); ?></em>
		<br /><br />
		<strong><?php esc_html_e( 'Demo:', 'edit-author-slug' ); ?></strong>
		<em>
		<?php
			echo sprintf(
				'%1$s%2$s%3$s%4$s',
				esc_url( home_url( '/' ) ),
				'<span class="eas-demo-author-base-front">' . esc_html( $front ) . '</span>',
				'<span class="eas-demo-author-base">' . esc_html( $author_base ) . '</span>',
				esc_html( user_trailingslashit( '/author-slug' ) )
			);
		?>
		</em>

	<?php
}

/**
 * Add the remove front settings field.
 *
 * @since 1.2.0
 *
 * @return void
 */
function ba_eas_admin_setting_callback_remove_front() {
	?>

		<input name="_ba_eas_remove_front" id="eas-remove-front" value="1"<?php checked( ba_eas()->remove_front ); ?> type="checkbox" />
		<label for="eas-remove-front">
			<?php esc_html_e( 'Remove the "front" portion of the author permalink structure.', 'edit-author-slug' ); ?>
		</label>

	<?php
}

/**
 * Add Role-Based Author Base checkbox.
 *
 * @since 1.0.0
 */
function ba_eas_admin_setting_callback_do_role_based() {
	?>

		<input class="eas-checkbox" name="_ba_eas_do_role_based" id="eas-do-role-based" value="1"<?php checked( ba_eas()->do_role_based ); ?> type="checkbox" />
		<label for="eas-do-role-based">
			<?php esc_html_e( "Set user's Author Base according to their role.", 'edit-author-slug' ); ?>
		</label>
		<br /><br />
		<?php
		echo sprintf(
			/* translators: 1: rewrite tag, 2: rewrite tag demo usage, 3: demo URL using rewrite tag */
			esc_html__(
				'Use the %1$s rewrite tag to customize the role-based author base. If you set the author base to "%2$s", the resulting author structure will be something like "%3$s".',
				'edit-author-slug'
			),
			'<code>%ba_eas_author_role%</code>',
			'<em>cool-people/&#37;ba_eas_author_role&#37;</em>',
			'<em>http://example.com/cool-people/role-slug/author-slug</em>'
		);
		?>

	<?php
}

/**
 * Output the Role-Based Author Base slugs for editing.
 *
 * @since 1.0.0
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

		<input name="_ba_eas_role_slugs[<?php echo esc_attr( $role ); ?>][slug]" id="eas-role-slugs-<?php echo esc_attr( $role ); ?>-slug" type="text" value="<?php echo ba_eas_esc_nicename( $details['slug'] ); ?>" class="regular-text code" />
		<label for="eas-role-slugs-<?php echo esc_attr( $role ); ?>-slug"><?php echo esc_html( translate_user_role( $details['name'] ) ); ?></label><br />

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

		// Set the role slug if it exists.
		if ( empty( $slug ) ) {
			unset( $role_slugs[ $role ] );
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
 */
function ba_eas_admin_setting_callback_do_auto_update() {
	?>

		<input class="eas-checkbox" name="_ba_eas_do_auto_update" id="eas-do-auto-update" value="1"<?php checked( ba_eas()->do_auto_update ); ?> type="checkbox" />
		<label for="eas-do-auto-update">
			<?php esc_html_e( 'Automatically update Author Slug when a user updates their profile.', 'edit-author-slug' ); ?>
		</label>

	<?php
}

/**
 * Add default user nicename options.
 *
 * @since 0.9.0
 */
function ba_eas_admin_setting_callback_default_user_nicename() {

	// Get the nicename structure.
	$structure = ba_eas()->default_user_nicename;

	// Set to default nicename structure if needed.
	if ( empty( $structure ) ) {
		$structure = 'username';
	}

	// Set up the class for the iThemes force unique nicename username error message.
	$class = 'username' === $structure ? '' : ' hidden';

	// Get the default nicename options.
	$options = ba_eas_default_user_nicename_options_list();
	?>

		<span class="screen-reader-text"><?php esc_html_e( 'Default author slug options', 'edit-author-slug' ); ?></span>
		<select id="eas-default-user-nicename" name="_ba_eas_default_user_nicename">
		<?php foreach ( (array) $options as $id => $item ) : ?>
			<option value="<?php echo esc_attr( $id ); ?>"<?php selected( $structure, $id ); ?>><?php echo esc_html( $item ); ?></option>
		<?php endforeach; ?>
		</select>

		<?php if ( ba_eas()->is_itsec_force_unique_nickname() ) : ?>
			<div class="eas-author-slug-select-error<?php echo esc_attr( $class ); ?>" style="background: #fff; border-left: 4px solid #fff; border-left-color: #dc3232; box-shadow: 0 1px 1px 0 rgba( 0, 0, 0, 0.1 ); margin: 10px 15px 2px 0; padding: 5px 12px 5px;">
				<?php
					printf(
						/* translators: 1: <code>username</code>, 2: <code>username</code> */
						esc_html__( 'Your iThemes settings suggest you don\'t want the %1$s being used as an author slug. Leaving it set to %2$s will still work, but you may wish to change to something different.', 'edit-author-slug' ),
						'<code>username</code>',
						'<code>username</code>'
					);
				?>
			</div>
		<?php endif; ?>

	<?php
}

/**
 * Add bulk update option.
 *
 * @since 1.1.0
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
 * @return void
 */
function ba_eas_admin_setting_callback_bulk_update() {
	?>

		<input class="eas-checkbox" name="_ba_eas_bulk_update" id="eas-bulk-update" value="1" type="checkbox" />
		<label for="eas-bulk-update">
			<?php esc_html_e( 'Update all users according to the below Author Slug setting. This will only be run after clicking "Save Changes".', 'edit-author-slug' ); ?>
		</label>

	<?php
}

/**
 * Add default user nicename options.
 *
 * @since 0.9.0
 */
function ba_eas_admin_setting_callback_bulk_update_structure() {

	// Get the nicename structure.
	$structure = ba_eas()->default_user_nicename;

	// Set to default nicename structure if needed.
	if ( empty( $structure ) ) {
		$structure = 'username';
	}

	// Set up the class for the iThemes force unique nicename username error message.
	$class = 'username' === $structure ? '' : ' hidden';

	// Get the default nicename options.
	$options = ba_eas_default_user_nicename_options_list();
	?>

		<span class="screen-reader-text"><?php esc_html_e( 'Default bulk update author slug options', 'edit-author-slug' ); ?></span>
		<select id="eas-bulk-update-structure" name="_ba_eas_bulk_update_structure">
		<?php foreach ( (array) $options as $id => $item ) : ?>
			<option value="<?php echo esc_attr( $id ); ?>"<?php selected( $structure, $id ); ?>><?php echo esc_html( $item ); ?></option>
		<?php endforeach; ?>
		</select>

		<?php if ( ba_eas()->is_itsec_force_unique_nickname() ) : ?>
			<div class="eas-author-slug-select-error<?php echo esc_attr( $class ); ?>" style="background: #fff; border-left: 4px solid #fff; border-left-color: #dc3232; box-shadow: 0 1px 1px 0 rgba( 0, 0, 0, 0.1 ); margin: 10px 15px 2px 0; padding: 5px 12px 5px;">
				<?php
					printf(
						/* translators: 1: <code>username</code>, 2: <code>username</code> */
						esc_html__( 'Your iThemes settings suggest you don\'t want the %1$s being used as an author slug. Leaving it set to %2$s will still work, but you may wish to change to something different.', 'edit-author-slug' ),
						'<code>username</code>',
						'<code>username</code>'
					);
				?>
			</div>
		<?php endif; ?>

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
 * @return string The array of plugin action links.
 */
function ba_eas_add_settings_link( $links, $file ) {

	if ( ba_eas()->plugin_basename === $file ) {

		$settings_url = add_query_arg(
			array(
				'page' => 'edit-author-slug',
			),
			admin_url( 'options-general.php' )
		);

		$settings_link = '<a href="' . esc_url( $settings_url ) . '">' . esc_html__( 'Settings', 'edit-author-slug' ) . '</a>';
		array_unshift( $links, $settings_link );
	}

	return $links;
}

/**
 * Returns the default nicename options list.
 *
 * @since 1.2.0
 *
 * @return array
 */
function ba_eas_default_user_nicename_options_list() {

	/**
	 * Filters the array of user nicename structure options.
	 *
	 * @since 0.9.0
	 * @since 1.2.0 Moved filter into it's own wrapper function.
	 *
	 * @param array $options An array of of user nicename structure options.
	 */
	$options = apply_filters(
		'ba_eas_default_user_nicename_options_list',
		array(
			'username'    => __( 'username (Default)', 'edit-author-slug' ),
			'nickname'    => __( 'nickname', 'edit-author-slug' ),
			'displayname' => __( 'displayname', 'edit-author-slug' ),
			'firstname'   => __( 'firstname', 'edit-author-slug' ),
			'lastname'    => __( 'lastname', 'edit-author-slug' ),
			'firstlast'   => __( 'firstname-lastname', 'edit-author-slug' ),
			'lastfirst'   => __( 'lastname-firstname', 'edit-author-slug' ),
			'userid'      => __( 'userid', 'edit-author-slug' ),
			'hash'        => __( 'hash', 'edit-author-slug' ),
		)
	);

	return (array) $options;
}

/**
 * Checks to see that we are updating Edit Author Slug settings. If so, we call
 * `ba_eas_settings_updated` hook.
 *
 * This function is called by `admin_action_update` which tells us that the
 * *intention* is to update. Unfortunately, we can't tell if there are errors at
 * this point, and there are no (good) hooks to determine this. The benefit to
 * method, however, is that we can check the nonce for better security than
 * the other methods. This way, the `ba_eas_settings_updated` is only called if
 * it's safe to do so.
 *
 * @since 1.2.0
 *
 * @return void
 */
function ba_eas_settings_updated() {

	// Check that a valid nonce was passed.
	if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'edit-author-slug-options' ) ) {
		return;
	}

	// Make sure we're on the Edit Author Slug settings page.
	if ( ! isset( $_REQUEST['option_page'] ) || 'edit-author-slug' !== $_REQUEST['option_page'] ) {
		return;
	}

	/**
	 * Fires when `$POST['action'] = update` on our settings page.
	 *
	 * @since 1.2.0
	 */
	do_action( 'ba_eas_settings_updated' );
}

/** Install/Upgrade ***********************************************************/

/**
 * Install Edit Author Slug.
 *
 * Add options on install to reduce calls to the db.
 *
 * @since 1.0.0
 */
function ba_eas_install() {

	// Edit Author Slug instance.
	$ba_eas = ba_eas();

	// Bail if it's not a new install.
	if ( 0 !== $ba_eas->current_db_version ) {
		return;
	}

	// Add the options.
	add_option( '_ba_eas_author_base', $ba_eas->author_base );
	add_option( '_ba_eas_db_version', BA_Edit_Author_Slug::DB_VERSION );
	add_option( '_ba_eas_do_auto_update', (int) $ba_eas->do_auto_update );
	add_option( '_ba_eas_default_user_nicename', $ba_eas->default_user_nicename );
	add_option( '_ba_eas_do_role_based', (int) $ba_eas->do_role_based );
	add_option( '_ba_eas_role_slugs', $ba_eas->role_slugs );
	add_option( '_ba_eas_remove_front', (int) $ba_eas->remove_front );
}

/**
 * Upgrade Edit Author Slug.
 *
 * Just cleans up some options for now.
 *
 * @since 0.8.0
 */
function ba_eas_upgrade() {

	// Edit Author Slug instance.
	$ba_eas = ba_eas();

	// We're up-to-date, so let's move on.
	if ( BA_Edit_Author_Slug::DB_VERSION === $ba_eas->current_db_version ) {
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
		add_option( '_ba_eas_do_auto_update', (int) $ba_eas->do_auto_update );
		add_option( '_ba_eas_default_user_nicename', $ba_eas->default_user_nicename );
		add_option( '_ba_eas_do_role_based', (int) $ba_eas->do_role_based );
		add_option( '_ba_eas_role_slugs', $ba_eas->role_slugs );
	}

	// < 1.2.0.
	if ( $ba_eas->current_db_version < 411 ) {
		add_option( '_ba_eas_remove_front', (int) $ba_eas->remove_front );
	}

	// Version bump.
	update_option( '_ba_eas_db_version', BA_Edit_Author_Slug::DB_VERSION );

	// Courtesy flush.
	ba_eas_flush_rewrite_rules();
}
