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
