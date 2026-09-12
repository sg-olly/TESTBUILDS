<?php
/**
 * Studio Green theme bootstrap.
 *
 * The theme is a direct port of the hand-built static site. Layout lives in the
 * page templates; every piece of copy in those templates runs through sg_field(),
 * which falls back to the original wording until it is edited in wp-admin.
 *
 * @package StudioGreen
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SG_VERSION', '1.1.0' );
define( 'SG_DIR', get_template_directory() );
define( 'SG_URI', get_template_directory_uri() );

require_once SG_DIR . '/inc/setup.php';
require_once SG_DIR . '/inc/assets.php';
require_once SG_DIR . '/inc/template-tags.php';
require_once SG_DIR . '/inc/fields-schema.php';
require_once SG_DIR . '/inc/fields.php';
require_once SG_DIR . '/inc/seo.php';
require_once SG_DIR . '/inc/security.php';
require_once SG_DIR . '/inc/redirects.php';
require_once SG_DIR . '/inc/install.php';
