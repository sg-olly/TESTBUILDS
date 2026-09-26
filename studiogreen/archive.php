<?php
/**
 * Category, tag, author and date archives.
 *
 * @package StudioGreen
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

require SG_DIR . '/templates/parts/post-list.php';

get_footer();
