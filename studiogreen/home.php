<?php
/**
 * The notes index, i.e. the page assigned under Settings > Reading.
 *
 * @package StudioGreen
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

require SG_DIR . '/templates/parts/post-list.php';

get_footer();
