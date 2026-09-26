<?php
/**
 * Security headers, ported from the site's Netlify _headers file.
 *
 * @package StudioGreen
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether headers should be sent for this request.
 *
 * @return bool
 */
function sg_should_send_headers() {
	if ( defined( 'SG_DISABLE_SECURITY_HEADERS' ) && SG_DISABLE_SECURITY_HEADERS ) {
		return false;
	}

	if ( is_admin() || is_customize_preview() ) {
		return false;
	}

	if ( is_user_logged_in() && current_user_can( 'edit_posts' ) ) {
		return false;
	}

	return true;
}

/**
 * The Content-Security-Policy value.
 *
 * @return string
 */
function sg_csp() {
	$directives = array(
		'default-src'               => array( "'self'" ),
		'script-src'                => array( "'self'", 'https://*.typeform.com', 'https://cdn.jsdelivr.net' ),
		'style-src'                 => array( "'self'", "'unsafe-inline'", 'https://api.fontshare.com' ),
		'font-src'                  => array( "'self'", 'https://cdn.fontshare.com' ),
		'img-src'                   => array( "'self'", 'data:', 'https://*.typeform.com' ),
		'frame-src'                 => array( 'https://*.typeform.com' ),
		'connect-src'               => array( "'self'", 'https://*.typeform.com', 'https://api.fontshare.com', 'https://cdn.fontshare.com' ),
		'worker-src'                => array( "'self'", 'blob:' ),
		'object-src'                => array( "'none'" ),
		'base-uri'                  => array( "'self'" ),
		'form-action'               => array( "'self'", 'https://*.typeform.com' ),
		'frame-ancestors'           => array( "'none'" ),
		'upgrade-insecure-requests' => array(),
	);

	/**
	 * Filter the Content-Security-Policy directives.
	 *
	 * @param array $directives Directive name => list of sources.
	 */
	$directives = apply_filters( 'sg_csp_directives', $directives );

	$parts = array();
	foreach ( $directives as $name => $sources ) {
		$parts[] = $sources ? $name . ' ' . implode( ' ', $sources ) : $name;
	}

	return implode( '; ', $parts );
}

/**
 * Send the security headers.
 *
 * @param array $headers Headers WordPress is about to send.
 * @return array
 */
function sg_security_headers( $headers ) {
	if ( ! sg_should_send_headers() ) {
		return $headers;
	}

	$own = array(
		'Content-Security-Policy'   => sg_csp(),
		'X-Frame-Options'           => 'DENY',
		'X-Content-Type-Options'    => 'nosniff',
		'Referrer-Policy'           => 'strict-origin-when-cross-origin',
		'Permissions-Policy'        => 'camera=(), microphone=(), geolocation=(), payment=(), usb=()',
		'Cross-Origin-Opener-Policy' => 'same-origin',
	);

	if ( is_ssl() ) {
		$own['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains';
	}

	/**
	 * Filter the security headers before they are sent.
	 *
	 * @param array $own Header name => value.
	 */
	$own = apply_filters( 'sg_security_headers', $own );

	return array_merge( $headers, $own );
}
add_filter( 'wp_headers', 'sg_security_headers' );
