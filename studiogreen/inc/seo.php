<?php
/**
 * Meta description, Open Graph, canonical and structured data.
 *
 * The static site hand-wrote these tags into every page. They are reproduced
 * here, but the whole file stands down if an SEO plugin is active so the two
 * never both emit a description.
 *
 * @package StudioGreen
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether a plugin is already handling meta tags.
 *
 * @return bool
 */
function sg_seo_plugin_active() {
	$active = defined( 'WPSEO_VERSION' )          // Yoast SEO.
		|| defined( 'RANK_MATH_VERSION' )         // Rank Math.
		|| defined( 'SEOPRESS_VERSION' )          // SEOPress.
		|| defined( 'AIOSEO_VERSION' )            // All in One SEO.
		|| class_exists( 'The_SEO_Framework\\Load' );

	/**
	 * Filter whether the theme should stay out of the way of an SEO plugin.
	 *
	 * @param bool $active True to suppress the theme's own meta tags.
	 */
	return (bool) apply_filters( 'sg_seo_plugin_active', $active );
}

/**
 * Use a colon in document titles, as the static site did ("Web: Studio Green").
 *
 * @return string
 */
function sg_title_separator() {
	return ':';
}
add_filter( 'document_title_separator', 'sg_title_separator' );

/**
 * Title the front page after the studio rather than after the page.
 *
 * The static home page was titled "Studio Green: Marketing & web design that
 * grows your business". WordPress would otherwise use the page's own name, so
 * the site name and tagline are substituted here.
 *
 * @param array $parts Title parts.
 * @return array
 */
function sg_document_title_parts( $parts ) {
	if ( is_front_page() ) {
		$parts['title']   = get_bloginfo( 'name', 'display' );
		$parts['tagline'] = get_bloginfo( 'description', 'display' );
		unset( $parts['site'] );
	}

	return $parts;
}
add_filter( 'document_title_parts', 'sg_document_title_parts' );

/**
 * The description for the current view.
 *
 * @return string
 */
function sg_meta_description() {
	if ( is_front_page() ) {
		$description = sg_field( 'meta_description', (int) get_option( 'page_on_front' ) );
		return $description ? $description : get_bloginfo( 'description' );
	}

	if ( is_page() ) {
		$id          = get_queried_object_id();
		$description = sg_field( 'meta_description', $id );

		if ( '' === trim( $description ) ) {
			$description = get_the_excerpt( $id );
		}

		return $description;
	}

	if ( is_singular() ) {
		return get_the_excerpt();
	}

	return get_bloginfo( 'description' );
}

/**
 * The share image for the current view.
 *
 * @return string
 */
function sg_share_image() {
	if ( is_singular() && has_post_thumbnail() ) {
		$src = get_the_post_thumbnail_url( get_queried_object_id(), 'full' );
		if ( $src ) {
			return $src;
		}
	}

	/**
	 * Filter the default Open Graph image.
	 *
	 * @param string $url Image URL.
	 */
	return apply_filters( 'sg_default_share_image', SG_URI . '/assets/img/studiogreen_gradient_02.jpg' );
}

/**
 * Print the meta tags.
 */
function sg_meta_tags() {
	if ( sg_seo_plugin_active() ) {
		return;
	}

	$description = trim( wp_strip_all_tags( sg_meta_description() ) );
	$title       = wp_get_document_title();
	$url         = is_singular() ? get_permalink() : home_url( add_query_arg( array() ) );

	if ( $description ) {
		printf( '<meta name="description" content="%s" />' . "\n", esc_attr( $description ) );
	}

	// Pages are all "website", as they were on the static site; only real posts
	// are articles.
	$type = ( is_singular() && ! is_page() && ! is_front_page() ) ? 'article' : 'website';

	printf( '<meta property="og:type" content="%s" />' . "\n", esc_attr( $type ) );
	printf( '<meta property="og:site_name" content="%s" />' . "\n", esc_attr( get_bloginfo( 'name' ) ) );
	printf( '<meta property="og:title" content="%s" />' . "\n", esc_attr( $title ) );

	if ( $description ) {
		printf( '<meta property="og:description" content="%s" />' . "\n", esc_attr( $description ) );
	}

	printf( '<meta property="og:url" content="%s" />' . "\n", esc_url( $url ) );
	printf( '<meta property="og:image" content="%s" />' . "\n", esc_url( sg_share_image() ) );
	echo '<meta name="twitter:card" content="summary_large_image" />' . "\n";
}
add_action( 'wp_head', 'sg_meta_tags', 5 );

/**
 * Print the ProfessionalService structured data on the home page.
 */
function sg_structured_data() {
	if ( ! is_front_page() || sg_seo_plugin_active() ) {
		return;
	}

	$contact_id = sg_page_id( 'contact' );

	$data = array(
		'@context'    => 'https://schema.org',
		'@type'       => 'ProfessionalService',
		'name'        => get_bloginfo( 'name' ),
		'url'         => home_url( '/' ),
		'description' => wp_strip_all_tags( sg_meta_description() ),
		'areaServed'  => 'United Kingdom',
		'priceRange'  => '££',
	);

	if ( $contact_id ) {
		$email = sg_field( 'email', $contact_id );
		if ( $email ) {
			$data['email'] = $email;
		}
	}

	/**
	 * Filter the home page structured data.
	 *
	 * @param array $data Schema.org data, encoded to JSON-LD.
	 */
	$data = apply_filters( 'sg_structured_data', $data );

	// JSON_HEX_TAG keeps a stray "</script>" in any value from closing the block.
	printf(
		'<script type="application/ld+json">%s</script>' . "\n",
		wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG )
	);
}
add_action( 'wp_head', 'sg_structured_data', 6 );
