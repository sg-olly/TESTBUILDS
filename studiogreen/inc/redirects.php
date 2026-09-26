<?php
/**
 * Redirects from the old static URLs.
 *
 * @package StudioGreen
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Old path (no leading slash) => page key.
 *
 * @return array
 */
function sg_legacy_urls() {
	return apply_filters(
		'sg_legacy_urls',
		array(
			'index.html'    => 'home',
			'services.html' => 'web',
			'design.html'   => 'design',
			'about.html'    => 'about',
			'contact.html'  => 'contact',
			'privacy.html'  => 'privacy',
			'cookies.html'  => 'cookies',
		)
	);
}

/**
 * Send a 301 when a request matches an old static URL.
 */
function sg_legacy_redirect() {
	if ( ! is_404() || ! isset( $_SERVER['REQUEST_URI'] ) ) {
		return;
	}

	$request = wp_parse_url( esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) );
	$path    = isset( $request['path'] ) ? trim( $request['path'], '/' ) : '';

	if ( '' === $path ) {
		return;
	}

	$fragment = isset( $request['query'] ) && '' !== $request['query'] ? '?' . $request['query'] : '';
	$map      = sg_legacy_urls();

	if ( isset( $map[ $path ] ) ) {
		$id = sg_page_id( $map[ $path ] );
		if ( $id ) {
			wp_safe_redirect( get_permalink( $id ) . $fragment, 301 );
			exit;
		}
	}

	if ( 0 === strpos( $path, 'assets/' ) ) {
		$file = basename( $path );
		if ( preg_match( '/^[A-Za-z0-9._-]+\.(jpe?g|png|webp|svg|gif)$/', $file )
			&& file_exists( SG_DIR . '/assets/img/' . $file ) ) {
			wp_redirect( SG_URI . '/assets/img/' . $file, 301 );
			exit;
		}
	}
}
add_action( 'template_redirect', 'sg_legacy_redirect' );
