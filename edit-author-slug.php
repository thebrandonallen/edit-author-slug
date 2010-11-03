<?php
/*
Plugin Name: Edit Author Slug
Plugin URI: http://brandonallen.org/wordpress/plugins/edit-author-slug/
Description: Allows an Admin to edit the author slug of any blog user, and change the Author Base. <em>i.e. - (WordPress default structure) http://example.com/author/username/ (Plugin allows) http://example.com/ninja/master-ninja/</em>
Version: 0.6
Tested With: 2.9.2, 3.0.1
Author: Brandon Allen
Author URI: http://brandonallen.org/
License: GPL2
*/

/*			Copyright 2010  Brandon Allen  (email : wp_plugins@brandonallen.org)

			This program is free software; you can redistribute it and/or modify
			it under the terms of the GNU General Public License as published by
			the Free Software Foundation; either version 2 of the License, or
			(at your option) any later version.

			This program is distributed in the hope that it will be useful,
			but WITHOUT ANY WARRANTY; without even the implied warranty of
			MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
			GNU General Public License for more details.

			You should have received a copy of the GNU General Public License
			along with this program; if not, write to the Free Software
			Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

			http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
*/

/**
 *
 * BA_Edit_Author_Slug is the class that handles ALL of the plugin functionality.
 * It helps us avoid name collisions.
 * http://codex.wordpress.org/Writing_a_Plugin#Avoiding_Function_Name_Collisions
 *
 * @package Edit Author Slug
 */
if ( !class_exists( 'BA_Edit_Author_Slug' ) ) {
	class BA_Edit_Author_Slug {

		/**
		 * Run necessary actions automagically.
		 *
		 * Runs necessary actions and localization by placing them inside a
		 * function with the same name as the Class name.
		 *
		 * @author Brandon Allen
		 *
		 * @since 0.1.0
		 * @uses is_admin() Only run actions when on admin pages.
		 */
		function BA_Edit_Author_Slug() {
			global $pagenow;

			if ( is_admin() ) {
				add_action( 'show_user_profile', array( &$this, 'show_user_nicename' ) );
				add_action( 'edit_user_profile', array( &$this, 'show_user_nicename' ) );
				add_action( 'personal_options_update', array( &$this, 'update_user_nicename' ) );
				add_action( 'edit_user_profile_update', array( &$this, 'update_user_nicename' ) );
				add_action( 'admin_init', array( &$this, 'add_author_base_settings_field' ) );
				load_plugin_textdomain( 'edit-author-slug', false, 'edit-author-slug/languages' );

				if ( isset( $pagenow ) && $pagenow == 'users.php' ) {
					add_filter( 'manage_users_columns', array( &$this, 'author_slug_column' ) );
					add_filter( 'manage_users_custom_column', array( &$this, 'author_slug_custom_column' ), 10, 3 );
				}
			}

			add_action( 'init', array( &$this, 'author_base_rewrite' ) );
		}

		/**
		 * Display Author slug edit field on User/Profile edit page.
		 *
		 * Displays the Author slug edit field on User/Profile edit page.
		 * Runs with the 'show_user_profile' and 'edit_user_profile' actions.
		 *
		 * @author Brandon Allen
		 *
		 * @since 0.1.0
		 * @global int $user_ID The ID of the user
		 * @uses current_user_can() To hide from unauthorized users.
		 *
		 * @param object $user User data object
		 */
		function show_user_nicename( $user ) {
			if ( current_user_can( 'edit_users' ) ) {
				?>

<h3><?php esc_html_e( 'Edit Author Slug', 'edit-author-slug' ) ?></h3>

<table class="form-table">
<tbody><tr>
	<th><label for="ba-edit-author-slug"><?php esc_html_e( 'Author Slug', 'edit-author-slug' ) ?></label></th>
	<td><input type="text" name="ba-edit-author-slug" id="ba-edit-author-slug" value="<?php echo esc_attr( $user->user_nicename ); ?>" class="regular-text" /><br /><span class="description"><?php esc_html_e( "ie. - 'user-name', 'firstname-lastname', or 'master-ninja'", 'edit-author-slug' ) ?></span></td>
</tr>
</tbody></table>
			<?php }
		}

		/**
		 * Update user_nicename for a given user.
		 *
		 * Runs with 'personal_options_update' and 'edit_user_profile_update' actions,
		 * and updates the user_nicename.
		 *
		 * @author Brandon Allen
		 *
		 * @since 0.1.0
		 * @global int $user_ID The ID of the user
		 * @global $wpdb WordPress database object for queries
		 * @uses sanitize_title() Used to sanitize Author Slug
		 */
		function update_user_nicename() {
			global $user_id, $wpdb;

			$userdata		= get_userdata( $user_id );
			$author_slug	= sanitize_title( $_POST['ba-edit-author-slug'] );

			// Get array of existing user_nicenames to compare against
			$user_nicenames = $wpdb->get_col( $wpdb->prepare( "SELECT user_nicename FROM $wpdb->users" ) );

			if ( !empty( $_POST['action'] ) && ( $_POST['action'] === 'update' ) && !empty( $author_slug ) && ( $userdata->user_nicename != $author_slug ) ) {
				if ( in_array( $author_slug, $user_nicenames ) ) {
					$stylized_author_slug = '<em><strong>' . esc_attr( $author_slug ) . '</strong></em>';
					$message = esc_html__( "The author slug, '[baeas_author_slug]', is already in use. Please go back, and try again.", 'edit-author-slug' );

					wp_die( str_replace( '[baeas_author_slug]', $stylized_author_slug, $message ) );
				}

				$new_userdata = array( 'ID' => $user_id, 'user_nicename' => $author_slug );
				wp_update_user( $new_userdata );

				wp_cache_delete( $userdata->user_nicename, 'userslugs' );
			}
		}

		/**
		 * Add Author Base settings field to 'Permalink' options page.
		 *
		 * Adds a settings field for Author Base in the 'Optional' settings
		 * section along with Category Base and Tag Base.
		 *
		 * @author Brandon Allen
		 *
		 * @since 0.4.0
		 */
		function add_author_base_settings_field() {
			// Register setting doesn't work on options-permalink.php
			// see Trac ticket #9296 (http://core.trac.wordpress.org/ticket/9296)
			// register_setting( 'ba_edit_author_slug', 'ba_edit_author_slug', array( &$this, 'sanitize_author_base' ) );

			$this->sanitize_author_base();

			add_settings_field( 'baeas_author_base', __( 'Author Base', 'edit-author-slug' ), array( &$this, 'author_base_settings_html' ), 'permalink', 'optional' );
		}

		/**
		 * Sanitize Author base and add to database.
		 *
		 * Sanitizes Author base, then adds it to the database.
		 * Contains workaround code until we see a fix to Trac
		 * ticket #9296 (http://core.trac.wordpress.org/ticket/9296)
		 *
		 * @author Brandon Allen
		 *
		 * @since 0.4.0
		 */
		function sanitize_author_base() {
			if ( isset( $_POST['permalink_structure'] ) || isset( $_POST['category_base'] ) ) {
				check_admin_referer('update-permalink');

				$options = get_option( 'ba_edit_author_slug' );

				if ( isset( $_POST['baeas_author_base'] ) ) {
					$author_base = $_POST['baeas_author_base'];
					if ( !empty( $author_base ) ) {
						$author_base = str_replace( '#', '', trim( $author_base ) );
						$author_base = _wp_filter_taxonomy_base( $author_base );
						$author_base = untrailingslashit( $author_base );
					}

					if ( $author_base != $options['author_base'] ) {
						$ba_edit_author_slug['author_base']				= $author_base;
						$ba_edit_author_slug['dont_forget_to_flush']	= 1;
						update_option( 'ba_edit_author_slug', $ba_edit_author_slug );
					}
				}
			}
		}

		/**
		 * Add Author Base settings html to Options > Permalinks.
		 *
		 * Adds a settings field for Author Base in the 'Optional'
		 * settings section along with Category Base and Tag Base.
		 *
		 * @author Brandon Allen
		 *
		 * @since 0.4.0
		 */
		function author_base_settings_html() {
			$options = get_option( 'ba_edit_author_slug' );

			echo '<input id="baeas_author_base" name="baeas_author_base" type="text" value="' . esc_attr( $options['author_base'] ) . '" class="regular-text code" />';
		}

		/**
		 * Rewrite Author Base according to user's setting.
		 *
		 * Rewrites Author Base to user's setting from the
		 * Author Base field on Options > Permalinks.
		 *
		 * @author Brandon Allen
		 *
		 * @since 0.4.0
		 * @global object $wp_rewrite Adds rewrite tags and permastructs.
		 */
		function author_base_rewrite() {
			global $wp_rewrite;

			$options = get_option( 'ba_edit_author_slug' );

			// Use this filter with caution! It could cause things to terribly ary
			// with your permalinks. If you use this filter, make sure you go to
			// your Permalink Options page, and click save. This will flush the old
			// rules, and update your permalinks.
			$remove_author_base = apply_filters( 'baeas_remove_author_base', false );

			if ( !empty( $options['author_base'] ) ) {
				$wp_rewrite->author_base = esc_attr( $options['author_base'] );
			} elseif ( empty( $options['author_base'] ) && $remove_author_base ) {
				$wp_rewrite->author_base = '';
				// We need to remove the extra backslash
				$wp_rewrite->author_structure = '/%author%';
			} else {
				return;
			}

			if ( isset( $options['dont_forget_to_flush'] ) && (int) $options['dont_forget_to_flush'] === 1 ) {
				flush_rewrite_rules( false );
				unset( $options['dont_forget_to_flush'] );
				update_option( 'ba_edit_author_slug', $options );
			}
		}

		/**
		 * Add 'Author Slug' column to Users page.
		 *
		 * Adds the 'Author Slug' column and column heading
		 * to the page Users > Authors & Users.
		 *
		 * @author Brandon Allen
		 *
		 * @since 0.5.0
		 *
		 * @param array $defaults Array of current columns/column headings
		 */
		function author_slug_column( $defaults ) {
			$defaults['baeas-author-slug'] = esc_html__( 'Author Slug', 'edit-author-base' );

			return $defaults;
		}

		/**
		 * Fill in user_nicename for 'Author Slug' column.
		 *
		 * Adds the user's corresponding user_nicename to the
		 * 'Author Slug' column.
		 *
		 * @author Brandon Allen
		 *
		 * @since 0.5.0
		 * @uses get_userdata() Used to retrieve user_nicename
		 *
		 * @param string $default Value for column data, defaults to ''
		 * @param string $column_name Column name currently being filtered
		 * @param int $user_id User ID
		 */
		function author_slug_custom_column( $default, $column_name, $user_id ) {
			if ( $column_name == 'baeas-author-slug') {
				$userdata = get_userdata( $user_id );

				return esc_attr( $userdata->user_nicename );
			}

			return $default;
		}
	}
} //end class BA_Edit_Author_Slug

/**
 * Initialize BA_Edit_Author_Slug class
 */
add_action( 'plugins_loaded', create_function( '', 'global $ba_edit_author_slug; $ba_edit_author_slug = new BA_Edit_Author_Slug();' ) );

?>