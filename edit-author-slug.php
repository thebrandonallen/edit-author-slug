<?php
/*
Plugin Name: Edit Author Slug
Plugin URI: http://brandonallen.org/wordpress/plugins/edit-author-slug/
Description: Allows user with with user editing capabilities to edit the author slug of a user. <em>i.e. - change http://example.com/author/username/ to http://example.com/author/user-name/</em>
Version: 0.3
Tested With: 2.8.6, 2.9.2
Author: Brandon Allen
Author URI: http://brandonallen.org/
*/

/*
* Copyright 2009  Brandon Allen  (email : wp_plugins_support@brandonallen.org)

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
 */
if ( ! class_exists( 'BA_Edit_Author_Slug' ) ) {
	class BA_Edit_Author_Slug {

		var $ba_current_author_slug = '';

		/**
		 * Automatically run actions using a function with the same name as it's class.
		 *
		 * Make sure we're in the backend first.
		 *
		 * @since 0.1
		 */
		function BA_Edit_Author_Slug() {
			if ( is_admin() ) {
				add_action( 'show_user_profile', array( &$this, 'show_user_nicename' ) );
				add_action( 'edit_user_profile', array( &$this, 'show_user_nicename' ) );
				add_action( 'personal_options_update', array( &$this, 'update_user_nicename' ) );
				add_action( 'edit_user_profile_update', array( &$this, 'update_user_nicename' ) );
				load_plugin_textdomain( 'ba-edit-author-slug', false, 'edit-author-slug/languages' );
			}
		}

		/**
		 * Display Author slug edit field on User/Profile edit page.
		 *
		 * Runs with the 'show_user_profile' and 'edit_user_profile' actions.
		 *
		 * @since 0.1
		 *
		 * @param object $user User data object
		 */
		function show_user_nicename( $user ) {
			global $user_id;
			
			if ( current_user_can( 'edit_users' ) ) {
				?>

				<h3><?php _e( 'Edit Author Slug', 'ba-edit-author-slug' ) ?></h3>

				<table class="form-table">

					<tr>
						<th><label for="ba-edit-author-slug"><?php _e( 'Author Slug', 'ba-edit-author-slug' ) ?></label></th>

						<td>
							<input type="text" name="ba-edit-author-slug" id="ba-edit-author-slug" value="<?php echo $user->user_nicename; ?>" class="regular-text" /><br />
							<span class="description"><?php _e( 'only alphanumeric characters (A-Z, a-z, 0-9), underscores (_) and dashes (-)', 'ba-edit-author-slug' ) ?></span>
						</td>
					</tr>

				</table>
			<?php }
		}

		/**
		 * Update user_nicename database option for corresponding user.
		 *
		 * Runs with 'personal_options_update' and 'edit_user_profile_update' actions.
		 * Only alphanumeric characters (A-Z, a-z, 0-9), underscores (_) and dashes (-)
		 * are allowed. Everything else will be stripped. Spaces will be replaced with dashes.
		 *
		 * @since 0.1
		 *
		 * @param object $user User data object
		 */
		function update_user_nicename( $user ) {
			global $user_id, $wpdb;

			$ba_user_to_update = get_userdata( $user_id );
			$ba_edit_author_slug = sanitize_title_with_dashes( $_POST['ba-edit-author-slug'] );
			$user_nicename_array = $wpdb->get_col( $wpdb->prepare( "SELECT user_nicename FROM $wpdb->users" ) );
			$wp_die_html_front = '<em><strong>';
			$wp_die_html_rear = '</strong></em>';

			if ( ! empty( $_POST['action'] ) && ( $_POST['action'] === 'update' ) && ! empty( $ba_edit_author_slug ) && ( $ba_user_to_update->user_nicename != $ba_edit_author_slug ) ) {
				if ( in_array( $ba_edit_author_slug, $user_nicename_array ) )
					wp_die( sprintf( __( "The author slug, '%s%s%s', is already in use. Please go back, and try again.", 'ba-edit-author-slug' ), $wp_die_html_front, $ba_edit_author_slug, $wp_die_html_rear ), __( 'Author Slug Already Exists - Please Try Again', 'ba-edit-author-slug' ) );
				
				$userdata = array( 'ID' => $user_id, 'user_nicename' => $ba_edit_author_slug );
				wp_update_user( $userdata );

				wp_cache_delete( $user->user_nicename, 'userslugs' );
			}
		}
	}
} //end class BA_Edit_Author_Slug

/**
 * Initialize BA_Edit_Author_Slug class
 */
if ( class_exists( 'BA_Edit_Author_Slug' ) ) {
	$ba_edit_author_slug = new BA_Edit_Author_Slug;
}

?>