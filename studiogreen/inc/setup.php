<?php
/**
 * Theme supports, menus and editor behaviour.
 *
 * @package StudioGreen
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register theme features and navigation locations.
 */
function sg_setup() {
	load_theme_textdomain( 'studiogreen', SG_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );

	// The static site had no custom logo control: the wordmark is a CSS mask so it
	// can recolour itself over dark sections. Left as a theme asset deliberately.
	register_nav_menus(
		array(
			'primary'      => __( 'Primary (header and mobile drawer)', 'studiogreen' ),
			'footer_pages' => __( 'Footer: pages', 'studiogreen' ),
			'footer_legal' => __( 'Footer: legal', 'studiogreen' ),
		)
	);
}
add_action( 'after_setup_theme', 'sg_setup' );

/**
 * Set the content width used by embeds and wide images.
 */
function sg_content_width() {
	$GLOBALS['content_width'] = 1480;
}
add_action( 'after_setup_theme', 'sg_content_width', 0 );

/**
 * Drop the core emoji scripts.
 *
 * They inject an inline script into every page, which the site's own
 * Content-Security-Policy (ported from the Netlify _headers file) does not
 * allow. Removing them keeps the policy strict and saves a request.
 */
function sg_disable_emojis() {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
	add_filter( 'emoji_svg_url', '__return_false' );
	add_filter(
		'tiny_mce_plugins',
		function ( $plugins ) {
			return is_array( $plugins ) ? array_diff( $plugins, array( 'wpemoji' ) ) : array();
		}
	);
}
add_action( 'init', 'sg_disable_emojis' );

/**
 * Remove the block library's default front-end CSS.
 *
 * The theme ships its own complete stylesheet and the templates emit no block
 * markup, so core's block styles would only add weight. Legal pages written in
 * the editor use plain paragraphs, headings, lists and tables, all of which the
 * theme styles itself in assets/css/legal.css.
 */
function sg_dequeue_block_styles() {
	wp_dequeue_style( 'wp-block-library' );
	wp_dequeue_style( 'wp-block-library-theme' );
	wp_dequeue_style( 'global-styles' );
	wp_dequeue_style( 'classic-theme-styles' );
}
add_action( 'wp_enqueue_scripts', 'sg_dequeue_block_styles', 100 );
