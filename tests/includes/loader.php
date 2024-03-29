<?php

require_once dirname( __FILE__ ) . '/define-constants.php';

system( WP_PHP_BINARY . ' ' . escapeshellarg( dirname( __FILE__ ) . '/install.php' ) . ' ' . escapeshellarg( WP_TESTS_CONFIG_PATH ) . ' ' . escapeshellarg( WP_TESTS_DIR ) );

// Bootstrap Edit Author Slug
require dirname( dirname( dirname( __FILE__ ) ) ) . '/edit-author-slug.php';

// Bail from redirects as they throw 'headers already sent' warnings.
tests_add_filter( 'wp_redirect', '__return_false' );
