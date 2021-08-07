<?php
/**
 * Installs Edit Author Slug for the purpose of the unit-tests
 */
error_reporting( E_ALL & ~E_DEPRECATED & ~E_STRICT );

$config_file_path = $argv[1];
$tests_dir_path = $argv[2];

require_once $config_file_path;
require_once $tests_dir_path . '/includes/functions.php';

function _load_edit_author_slug() {
	require dirname( dirname( dirname( __FILE__ ) ) ) . '/edit-author-slug.php';
}
tests_add_filter( 'muplugins_loaded', '_load_edit_author_slug' );

$_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
$_SERVER['HTTP_HOST'] = WP_TESTS_DOMAIN;
$PHP_SELF = $GLOBALS['PHP_SELF'] = $_SERVER['PHP_SELF'] = '/index.php';

require_once ABSPATH . '/wp-settings.php';
