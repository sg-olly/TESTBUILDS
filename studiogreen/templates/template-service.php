<?php
/**
 * Template Name: Service page
 * Template Post Type: page
 *
 * The Web and Design layout, ready for a new page: hero, up to three service
 * blocks, an optional four-stage process band and a closing call to action.
 *
 * Every field starts empty. The hero falls back to the page's own title, and
 * any block left blank is skipped, so a new page can start with a heading and
 * grow into the rest.
 *
 * @package StudioGreen
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require SG_DIR . '/templates/parts/service-page.php';
