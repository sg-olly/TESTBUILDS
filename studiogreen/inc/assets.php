<?php
/**
 * Stylesheet and script registration.
 *
 * The static site inlined a <style> block per page. Those blocks now live in
 * assets/css/*.css and are loaded only on the templates that use them, so no
 * page downloads rules it will not apply.
 *
 * @package StudioGreen
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Version string for a theme asset, taken from the file's own timestamp.
 *
 * The theme version alone is not enough: it changes once a release, while the
 * stylesheets change many times within one. Anything enqueued against a version
 * that has not moved keeps its old URL, so browsers and any caching layer in
 * front of the site go on serving the previous file and edits appear not to
 * have happened at all. Keyed on the file instead, the URL changes whenever the
 * file does, and only then.
 *
 * @param string $relative Path within the theme, e.g. 'assets/css/home.css'.
 * @return string
 */
function sg_asset_version( $relative ) {
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

	// Everything else (privacy, cookies, any new page) uses the legal/prose layout.
	return array(
		'handle' => 'sg-legal',
		'file'   => 'legal.css',
	);
}

/**
 * Enqueue front-end styles and scripts.
 */
function sg_enqueue_assets() {
	// Switzer, served by Fontshare exactly as the static site did.
	wp_enqueue_style(
		'sg-fonts',
		'https://api.fontshare.com/v2/css?f%5B%5D=switzer@400,500,600,700&display=swap',
		array(),
		null // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- Remote font CSS is versioned upstream.
	);

	// The design system: palette, type scale, nav, buttons, reveals, footer.
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

	// Adds .js-reveal before first paint so masked lines start hidden.
	wp_enqueue_script( 'sg-head', SG_URI . '/assets/js/head.js', array(), sg_asset_version( 'assets/js/head.js' ), false );

	/**
	 * Filter the Lenis smooth-scroll source.
	 *
	 * Defaults to the CDN build the static site used. To self-host, drop
	 * lenis.min.js into assets/js/ and return SG_URI . '/assets/js/lenis.min.js'
	 * from this filter (then remove cdn.jsdelivr.net from the CSP in
	 * inc/security.php). site.js checks for window.Lenis and simply skips smooth
	 * scrolling if the file does not load, so this is safe to change or empty.
	 *
	 * @param string $src Script URL, or '' to disable smooth scrolling.
	 */
	$lenis = apply_filters( 'sg_lenis_src', 'https://cdn.jsdelivr.net/npm/lenis@1.1.18/dist/lenis.min.js' );
	$deps  = array();
	if ( $lenis ) {
		wp_enqueue_script( 'sg-lenis', $lenis, array(), null, true ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- Version is pinned in the URL.
		$deps[] = 'sg-lenis';
	}

	wp_enqueue_script( 'sg-site', SG_URI . '/assets/js/site.js', $deps, sg_asset_version( 'assets/js/site.js' ), true );

	// Typeform's embed loader, only on the contact page and only if a form is set.
	$contact_id = get_queried_object_id();
	if (
		is_page_template( 'templates/template-contact.php' )
		&& '' !== trim( sg_field( 'typeform_id', $contact_id ) )
	) {
		wp_enqueue_script( 'sg-typeform', 'https://embed.typeform.com/next/embed.js', array(), null, true ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- Vendor loader, versioned upstream.
	}

	// The cookie banner is built in JS and needs a real permalink to link to.
	wp_localize_script(
		'sg-site',
		'SGData',
		array( 'cookiesUrl' => sg_cookies_url() )
	);
}
add_action( 'wp_enqueue_scripts', 'sg_enqueue_assets' );

/**
 * Preconnect to the Fontshare hosts, as the static site's <head> did.
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
 *
 * has_site_icon() wins so the client can override without touching files.
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
