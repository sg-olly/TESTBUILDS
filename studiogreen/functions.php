<?php
/**
 * Studio Green theme bootstrap.
 *
 * @package StudioGreen
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SG_VERSION', '1.2.0' );
define( 'SG_DIR', get_template_directory() );
define( 'SG_URI', get_template_directory_uri() );

require_once SG_DIR . '/inc/setup.php';
require_once SG_DIR . '/inc/assets.php';
require_once SG_DIR . '/inc/template-tags.php';
require_once SG_DIR . '/inc/blog.php';
require_once SG_DIR . '/inc/fields-schema.php';
require_once SG_DIR . '/inc/fields.php';
require_once SG_DIR . '/inc/seo.php';
require_once SG_DIR . '/inc/seo-analysis.php';
require_once SG_DIR . '/inc/schema.php';
require_once SG_DIR . '/inc/security.php';
require_once SG_DIR . '/inc/redirects.php';
require_once SG_DIR . '/inc/install.php';
