<?php

function ba_eas_tests_slugs( $type ) {

	$slugs = array(
		'default' => array(
			'administrator' => array(
				'name' => 'Administrator',
				'slug' => 'administrator',
			),
			'editor' => array(
				'name' => 'Editor',
				'slug' => 'editor',
			),
			'author' => array(
				'name' => 'Author',
				'slug' => 'author',
			),
			'contributor' => array(
				'name' => 'Contributor',
				'slug' => 'contributor',
			),
			'subscriber' => array(
				'name' => 'Subscriber',
				'slug' => 'subscriber',
			),
		),
		'custom' => array(
			'administrator' => array(
				'name' => 'Administrator',
				'slug' => 'jonin',
			),
			'editor' => array(
				'name' => 'Editor',
				'slug' => 'chunin',
			),
			'author' => array(
				'name' => 'Author',
				'slug' => 'mystic',
			),
			'contributor' => array(
				'name' => 'Contributor',
				'slug' => 'junior-genin',
			),
			'subscriber' => array(
				'name' => 'Subscriber',
				'slug' => 'deshi',
			),
		),
	);

	$extra_role = array(
		'foot-soldier' => array(
			'name' => 'Foot Soldier',
			'slug' => 'foot-soldier',
		)
	);

	$slugs['extra'] = $slugs['default'] + $extra_role;

	return $slugs[ $type ];
}

function ba_eas_tests_slugs_default( $type ) {
	return ba_eas_tests_slugs( 'default' );
}

function ba_eas_tests_slugs_custom( $type ) {
	return ba_eas_tests_slugs( 'custom' );
}

function ba_eas_tests_slugs_extra( $type ) {
	return ba_eas_tests_slugs( 'extra' );
}

function ba_eas_tests_roles( $type ) {

	$roles = array(
		'default' => array(
			'administrator' => array(
				'name' => 'Administrator',
			),
			'editor' => array(
				'name' => 'Editor',
			),
			'author' => array(
				'name' => 'Author',
			),
			'contributor' => array(
				'name' => 'Contributor',
			),
			'subscriber' => array(
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

function ba_eas_tests_roles_default() {
	return ba_eas_tests_roles( 'default' );
}

function ba_eas_tests_roles_custom() {
	return ba_eas_tests_roles( 'custom' );
}

function ba_eas_tests_roles_extra() {
	return ba_eas_tests_roles( 'extra' );
}

function ba_eas_tests_return_null_string() {
	return 'null';
}

function ba_eas_tests_return_true_string() {
	return 'true';
}

function ba_eas_tests_return_false_string() {
	return 'false';
}

function ba_eas_tests_return_one_int() {
	return 1;
}

function ba_eas_tests_return_one_string() {
	return '1';
}

function ba_eas_tests_return_zero_string() {
	return '0';
}

function ba_eas_tests_return_number_alpha_string() {
	return '12ab4lk56';
}

function ba_eas_tests_return_alpha_number_string() {
	return 'ab4lk56';
}

function ba_eas_tests_return_sentence() {
	return 'This is a sentence.';
}

function ba_eas_tests_return_full_array() {
	return array(
		'key' => 'value',
	);
}

function ba_eas_tests_nicename_return_username() {
	return 'username';
}

function ba_eas_tests_nicename_return_nicename() {
	return 'nicename';
}

function ba_eas_tests_nicename_return_nickname() {
	return 'nickname';
}

function ba_eas_tests_nicename_return_displayname() {
	return 'displayname';
}

function ba_eas_tests_nicename_return_firstname() {
	return 'firstname';
}

function ba_eas_tests_nicename_return_lastname() {
	return 'lastname';
}

function ba_eas_tests_nicename_return_firstlast() {
	return 'firstlast';
}

function ba_eas_tests_nicename_return_lastfirst() {
	return 'lastfirst';
}

