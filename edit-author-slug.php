<?php
/*
Plugin Name: Edit Author Slug
Plugin URI: http://wordpress.org/extend/plugins/edit-author-slug/ 
Description: Allows user with with user editing capabilities to edit the author slug of a user. <em>i.e. - change http://example.com/author/username/ to http://example.com/author/user-name/</em>
Version: 0.1.2
Tested With: 2.8.6, 2.9-beta-1
Author: Brandon Allen
Author URI: http://brandonallen.org
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
if ( !class_exists( 'BA_Edit_Author_Slug' ) ) {
	class BA_Edit_Author_Slug {

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

				<h3>Edit Author Slug</h3>

				<table class="form-table">

					<tr>
						<th><label for="ba-edit-author-slug">Author Slug</label></th>

						<td>
							<input type="text" name="ba-edit-author-slug" id="ba-edit-author-slug" value="<?php echo sanitize_title_with_dashes( $user->user_nicename ); ?>" class="regular-text" /><br />
							<span class="description">only alphanumeric characters (A-Z, a-z, 0-9), underscores (_) and dashes (-)</span>
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
			global $user_id;
			
			if ( !empty( $_POST['action'] ) && ( $_POST['action'] === 'update' ) && !empty( $_POST['ba-edit-author-slug'] ) && ( $user->user_nicename != $_POST['ba-edit-author-slug'] ) ) {
				$new_user_nicename = sanitize_title_with_dashes( $_POST['ba-edit-author-slug'] );
				
				$userdata = array( 'ID' => $user_id, 'user_nicename' => $new_user_nicename );
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