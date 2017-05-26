<?php
/**
 * Plugin Name:     Edit Author Slug
 * Plugin URI:      https://github.com/thebrandonallen/edit-author-slug/
 * Description:     Allows an Admin (or capable user) to edit the author slug of a user, and change the Author Base. <em>i.e. - (WordPress default structure) http://example.com/author/username/ (Plugin allows) http://example.com/ninja/master-ninja/</em>
 * Author:          Brandon Allen
 * Author URI:      https://github.com/thebrandonallen/
 * Text Domain:     edit-author-slug
 * Domain Path:     /languages
 * Version:         1.4.1
 *
 * @package Edit_Author_Slug
 * @subpackage Main
 * @author Brandon Allen
 * @version 1.4.1
 */

/*
	Copyright (C) 2009-2017  Brandon Allen  (email : plugins ([at]) brandonallen ([dot]) me)

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

	https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Load the plugin class file.
require 'includes/classes/class-edit-author-slug.php';

/**
 * Runs on Edit Author Slug activation.
 *
 * @since 0.7.0
 *
 * @return void
 */
function ba_eas_activation() {

	/**
	 * Fires on Edit Author Slug activation.
	 *
	 * @since 0.7.0
	 */
	do_action( 'ba_eas_activation' );
}
register_activation_hook( __FILE__, 'ba_eas_activation' );

/**
 * Runs on Edit Author Slug deactivation.
 *
 * @since 0.7.0
 *
 * @return void
 */
function ba_eas_deactivation() {

	/**
	 * Fires on Edit Author Slug deactivation.
	 *
	 * @since 0.7.0
	 */
	do_action( 'ba_eas_deactivation' );
}
register_deactivation_hook( __FILE__, 'ba_eas_deactivation' );

/**
 * The main function responsible for returning the one true BA_Edit_Author_Slug
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $ba_eas = ba_eas(); ?>
 *
 * @return BA_Edit_Author_Slug The one true BA_Edit_Author_Slug Instance.
 */
function ba_eas() {
	return BA_Edit_Author_Slug::instance();
}

ba_eas();
ba_eas()->setup_actions();
