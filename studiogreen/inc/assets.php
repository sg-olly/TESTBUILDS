<?php
/**
 * Stylesheet and script registration.
 *
 * @package StudioGreen
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Version string for a theme asset, taken from the file's own timestamp.
 *
 * @param string $relative Path within the theme, e.g. 'assets/css/home.css'.
 * @return string
 */
function sg_asset_version( $relative ) {
	// Keyed on the file, not the theme version, which does not move per edit.
	$path  = SG_DIR . '/' . ltrim( $relative, '/' );
	$mtime = file_exists( $path ) ? filemtime( $path ) : 0;

	return $mtime ? (string) $mtime : SG_VERSION;
}

/**
 * Map the current request to its page-specific stylesheet handle and file.
 *
 * @return array{handle:string,file:string}|null Null when the page needs no extra CSS.
 */
function sg_page_stylesheet() {
	if ( is_front_page() ) {
		return array(
			'handle' => 'sg-home',
			'file'   => 'home.css',
		);
	}

	// The 404 page is built entirely from base styles.
	if ( is_404() ) {
		return null;
	}

	/*
	 * Before the is_page() branch below, which never matches a post, an archive
	 * or the posts page (where is_page() is false and is_home() is true).
	 */
	if ( is_singular( 'post' ) || is_home() || is_archive() || is_search() ) {
		return array(
			'handle' => 'sg-blog',
			'file'   => 'blog.css',
		);
	}

	$template = is_page() ? (string) get_page_template_slug() : '';

	$map = array(
		'templates/template-web.php'     => 'services.css',
		'templates/template-design.php'  => 'services.css',
		'templates/template-service.php' => 'services.css',
		'templates/template-about.php'   => 'about.css',
		'templates/template-contact.php' => 'contact.css',
	);

	if ( isset( $map[ $template ] ) ) {
		return array(
			'handle' => 'sg-' . basename( $map[ $template ], '.css' ),
			'file'   => $map[ $template ],
		);
	}

	return array(
		'handle' => 'sg-legal',
		'file'   => 'legal.css',
	);
}

/**
 * Enqueue front-end styles and scripts.
 */
function sg_enqueue_assets() {
	// Switzer, the display and body face, served by Fontshare.
	wp_enqueue_style(
		'sg-fonts',
		'https://api.fontshare.com/v2/css?f%5B%5D=switzer@400,500,600,700&display=swap',
		array(),
		null // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
	);

	wp_enqueue_style( 'sg-base', get_stylesheet_uri(), array( 'sg-fonts' ), sg_asset_version( 'style.css' ) );

	$page_css = sg_page_stylesheet();
	if ( $page_css ) {
		wp_enqueue_style(
			$page_css['handle'],
			SG_URI . '/assets/css/' . $page_css['file'],
			array( 'sg-base' ),
			sg_asset_version( 'assets/css/' . $page_css['file'] )
		);
	}

	wp_enqueue_script( 'sg-head', SG_URI . '/assets/js/head.js', array(), sg_asset_version( 'assets/js/head.js' ), false );

	/**
	 * Filter the Lenis smooth-scroll source.
	 *
	 * @param string $src Script URL, or '' to disable smooth scrolling.
	 */
	$lenis = apply_filters( 'sg_lenis_src', 'https://cdn.jsdelivr.net/npm/lenis@1.1.18/dist/lenis.min.js' );
	$deps  = array();
	if ( $lenis ) {
		wp_enqueue_script( 'sg-lenis', $lenis, array(), null, true ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
		$deps[] = 'sg-lenis';
	}

	wp_enqueue_script( 'sg-site', SG_URI . '/assets/js/site.js', $deps, sg_asset_version( 'assets/js/site.js' ), true );

	$contact_id = get_queried_object_id();
	if (
		is_page_template( 'templates/template-contact.php' )
		&& '' !== trim( sg_field( 'typeform_id', $contact_id ) )
	) {
		wp_enqueue_script( 'sg-typeform', 'https://embed.typeform.com/next/embed.js', array(), null, true ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
	}

	wp_localize_script(
		'sg-site',
		'SGData',
		array( 'cookiesUrl' => sg_cookies_url() )
	);
}
add_action( 'wp_enqueue_scripts', 'sg_enqueue_assets' );

/**
 * Preconnect to the Fontshare hosts, so the webfont starts loading sooner.
 *
 * @param array  $urls           URLs to print.
 * @param string $relation_type  Relation being processed.
 * @return array
 */
function sg_resource_hints( $urls, $relation_type ) {
	if ( 'preconnect' === $relation_type ) {
		$urls[] = array( 'href' => 'https://api.fontshare.com' );
		$urls[] = array(
			'href'        => 'https://cdn.fontshare.com',
			'crossorigin' => 'anonymous',
		);
	}
	return $urls;
}
add_filter( 'wp_resource_hints', 'sg_resource_hints', 10, 2 );

/**
 * Output the favicon set from the theme, unless a Site Icon is set in Customizer.
 */
function sg_favicons() {
	if ( has_site_icon() ) {
		return;
	}
	$img = SG_URI . '/assets/img';
	printf( '<link rel="icon" type="image/png" sizes="32x32" href="%s" />' . "\n", esc_url( $img . '/favicon-32.png' ) );
	printf( '<link rel="icon" type="image/png" sizes="192x192" href="%s" />' . "\n", esc_url( $img . '/favicon-192.png' ) );
	printf( '<link rel="icon" type="image/svg+xml" href="%s" />' . "\n", esc_url( $img . '/favicon.svg' ) );
	printf( '<link rel="apple-touch-icon" href="%s" />' . "\n", esc_url( $img . '/apple-touch-icon.png' ) );
	echo '<meta name="theme-color" content="#193117" />' . "\n";
}
add_action( 'wp_head', 'sg_favicons', 2 );
