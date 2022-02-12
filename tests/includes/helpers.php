<?php
/**
 * Edit Author Slug test helper functions.
 *
 * @package Edit_Author_Slug
 * @subpackage Tests
 */

/**
 * Returns the default test slugs.
 *
 * @since 1.0.4
 *
 * @return array
 */
function ba_eas_tests_slugs_default() {
	return array(
		'administrator' => array(
			'name' => 'Administrator',
			'slug' => 'administrator',
		),
		'editor'        => array(
			'name' => 'Editor',
			'slug' => 'editor',
		),
		'author'        => array(
			'name' => 'Author',
			'slug' => 'author',
		),
		'contributor'   => array(
			'name' => 'Contributor',
			'slug' => 'contributor',
		),
		'subscriber'    => array(
			'name' => 'Subscriber',
			'slug' => 'subscriber',
		),
	);
}

/**
 * Returns the custom test slugs.
 *
 * @since 1.0.4
 *
 * @return array
 */
function ba_eas_tests_slugs_custom() {
	$slugs                          = ba_eas_tests_slugs_default();
	$slugs['administrator']['slug'] = 'jonin';
	$slugs['editor']['slug']        = 'chunin';
	$slugs['author']['slug']        = 'mystic';
	$slugs['contributor']['slug']   = 'junior-genin';
	$slugs['subscriber']['slug']    = 'deshi';
	return $slugs;
}

/**
 * Returns the extra test slugs.
 *
 * @since 1.0.4
 *
 * @return array
 */
function ba_eas_tests_slugs_extra() {
	return ba_eas_tests_slugs_default() + array(
		'foot-soldier' => array(
			'name' => 'Foot Soldier',
			'slug' => 'foot-soldier',
		),
	);
}

/**
 * Returns the test roles.
 *
 * @since 1.0.4
 *
 * @param string $type The roles type.
 *
 * @return array
 */
function ba_eas_tests_roles( $type ) {

	$roles = array(
		'default' => array(
			'administrator' => array(
				'name' => 'Administrator',
			),
			'editor'        => array(
				'name' => 'Editor',
			),
			'author'        => array(
				'name' => 'Author',
			),
			'contributor'   => array(
				'name' => 'Contributor',
			),
			'subscriber'    => array(
				'name' => 'Subscriber',
			),
		),
	);

	$extra_role = array(
		'foot-soldier' => array(
			'name' => 'Foot Soldier',
		),
	);

	$roles['extra'] = $roles['default'] + $extra_role;

	return $roles[ $type ];
}

/**
 * Returns the default test roles.
 *
 * @since 1.0.4
 *
 * @return array
 */
function ba_eas_tests_roles_default() {
	return ba_eas_tests_roles( 'default' );
}

/**
 * Returns the custom test roles.
 *
 * @since 1.0.4
 *
 * @return array
 */
function ba_eas_tests_roles_custom() {
	return ba_eas_tests_roles( 'custom' );
}

/**
 * Returns the extra test roles.
 *
 * @since 1.0.4
 *
 * @return array
 */
function ba_eas_tests_roles_extra() {
	return ba_eas_tests_roles( 'extra' );
}
