<?php
/**
 * Security headers, ported from the site's Netlify _headers file.
 *
 * Netlify's _headers file does nothing on 20i, so the same policy is sent from
 * PHP instead. It is applied to the public front end only: wp-admin, the login
 * screen, the customizer preview and anyone signed in with editing rights are
 * skipped, because the block editor and customizer need to inline scripts and
 * frame the site, which this policy forbids.
 *
 * To turn the whole thing off, add this to wp-config.php:
 *
 *     define( 'SG_DISABLE_SECURITY_HEADERS', true );
 *
 * The equivalent rules can also be set at the server instead: see
 * extras/htaccess-snippets.txt in the project repository.
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

	// Signed-in editors get the admin bar, block editor previews and the
	// customizer, none of which survive a policy this strict.
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
		// 'unsafe-inline' is not granted to scripts: everything the theme runs is
		// a file. If a plugin adds an inline script, allow it with a hash or a
		// nonce rather than opening this up.
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
	 * Add a source like this from a child theme or a small plugin:
	 *
	 *     add_filter( 'sg_csp_directives', function ( $d ) {
	 *         $d['script-src'][] = 'https://plausible.io';
	 *         return $d;
	 *     } );
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

	// HSTS is only meaningful, and only safe, once the site is served over HTTPS.
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
