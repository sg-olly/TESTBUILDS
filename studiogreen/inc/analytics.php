<?php
/**
 * Google Analytics, gated on cookie consent.
 *
 * The measurement ID is published as a meta tag rather than an inline script,
 * so the site's Content-Security-Policy can keep refusing inline JavaScript.
 * site.js loads gtag only once the visitor has accepted.
 *
 * @package StudioGreen
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The configured GA4 measurement ID.
 *
 * @return string Empty when unset or malformed.
 */
function sg_ga_id() {
	$id = defined( 'SG_GA_ID' ) ? SG_GA_ID : get_option( 'sg_ga_id', '' );

	/**
	 * Filter the GA4 measurement ID.
	 *
	 * @param string $id Measurement ID, e.g. G-XXXXXXXXXX.
	 */
	$id = (string) apply_filters( 'sg_ga_id', $id );

	return preg_match( '/^G-[A-Z0-9]{4,20}$/i', trim( $id ) ) ? strtoupper( trim( $id ) ) : '';
}

/**
 * Publish the measurement ID for site.js to find.
 */
function sg_ga_meta() {
	$id = sg_ga_id();

	if ( '' === $id || is_admin() ) {
		return;
	}

	printf( '<meta name="sg-ga-id" content="%s" />' . "\n", esc_attr( $id ) );
}
add_action( 'wp_head', 'sg_ga_meta', 3 );

/**
 * Let Google's endpoints through the CSP, but only when analytics is on.
 *
 * @param array $directives Directive name => list of sources.
 * @return array
 */
function sg_ga_csp( $directives ) {
	if ( '' === sg_ga_id() ) {
		return $directives;
	}

	$script  = array( 'https://www.googletagmanager.com' );
	$connect = array( 'https://www.google-analytics.com', 'https://analytics.google.com', 'https://*.analytics.google.com', 'https://www.googletagmanager.com' );
	$img     = array( 'https://www.google-analytics.com', 'https://www.googletagmanager.com' );

	foreach ( array( 'script-src' => $script, 'connect-src' => $connect, 'img-src' => $img ) as $name => $hosts ) {
		if ( ! isset( $directives[ $name ] ) ) {
			continue;
		}
		$directives[ $name ] = array_values( array_unique( array_merge( $directives[ $name ], $hosts ) ) );
	}

	return $directives;
}
add_filter( 'sg_csp_directives', 'sg_ga_csp' );

/**
 * A Customizer field for the ID, so it needs no code to change.
 *
 * @param WP_Customize_Manager $wp_customize Customizer.
 */
function sg_ga_customizer( $wp_customize ) {
	$wp_customize->add_section(
		'sg_analytics',
		array(
			'title'       => __( 'Analytics', 'studiogreen' ),
			'priority'    => 160,
			'description' => __( 'Measurement ID from Google Analytics, in the form G-XXXXXXXXXX. Analytics only loads for visitors who accept the cookie banner.', 'studiogreen' ),
		)
	);

	$wp_customize->add_setting(
		'sg_ga_id',
		array(
			'type'              => 'option',
			'default'           => '',
			'capability'        => 'manage_options',
			'sanitize_callback' => 'sg_sanitize_ga_id',
		)
	);

	$wp_customize->add_control(
		'sg_ga_id',
		array(
			'label'    => __( 'Google Analytics measurement ID', 'studiogreen' ),
			'section'  => 'sg_analytics',
			'type'     => 'text',
			'settings' => 'sg_ga_id',
		)
	);
}
add_action( 'customize_register', 'sg_ga_customizer' );

/**
 * Keep anything that is not a measurement ID out of the option.
 *
 * @param string $value Submitted value.
 * @return string
 */
function sg_sanitize_ga_id( $value ) {
	$value = strtoupper( trim( (string) $value ) );

	return preg_match( '/^G-[A-Z0-9]{4,20}$/', $value ) ? $value : '';
}
