<?php
/**
 * llms.txt
 *
 * A plain-markdown summary of the site for answer engines, served at /llms.txt.
 * Built from the live pages and posts so it cannot drift out of date.
 *
 * @package StudioGreen
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Serve /llms.txt.
 *
 * Matched on the request path rather than a rewrite rule, so it works the
 * moment the theme is installed, with no need to flush the rules first.
 */
function sg_maybe_serve_llms() {
	if ( is_admin() || ! isset( $_SERVER['REQUEST_URI'] ) ) {
		return;
	}

	$path = wp_parse_url( esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ), PHP_URL_PATH );
	$path = is_string( $path ) ? trim( $path, '/' ) : '';

	if ( 'llms.txt' !== strtolower( $path ) ) {
		return;
	}

	nocache_headers();
	header( 'Content-Type: text/plain; charset=utf-8' );
	header( 'X-Robots-Tag: noindex' );

	echo sg_llms_body(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	exit;
}
add_action( 'init', 'sg_maybe_serve_llms' );

/**
 * One markdown link line for a page.
 *
 * @param string $key   Page key for sg_page_id().
 * @param string $note  Fallback description.
 * @return string
 */
function sg_llms_link( $key, $note = '' ) {
	$id = sg_page_id( $key );

	if ( ! $id ) {
		return '';
	}

	$desc = trim( wp_strip_all_tags( (string) sg_field( 'meta_description', $id ) ) );

	if ( '' === $desc ) {
		$desc = $note;
	}

	return sprintf(
		"- [%s](%s)%s\n",
		sg_llms_text( get_the_title( $id ) ),
		esc_url_raw( (string) get_permalink( $id ) ),
		'' !== $desc ? ': ' . sg_llms_text( $desc ) : ''
	);
}

/**
 * Flatten a value for a single markdown line.
 *
 * @param string $text Raw text.
 * @return string
 */
function sg_llms_text( $text ) {
	$text = str_replace( '==', '', (string) $text );

	return trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( $text ) ) );
}

/**
 * Build the file.
 *
 * @return string
 */
function sg_llms_body() {
	$name    = sg_llms_text( get_bloginfo( 'name' ) );
	$tagline = sg_llms_text( get_bloginfo( 'description' ) );
	$home    = sg_page_id( 'home' );

	$summary = $home ? sg_llms_text( sg_field( 'meta_description', $home ) ) : '';
	if ( '' === $summary ) {
		$summary = $tagline;
	}

	$out  = '# ' . $name . "\n\n";
	$out .= '> ' . $summary . "\n\n";

	$out .= "Studio Green is a one-person marketing and web design studio in Darlington, "
		. "working with small businesses across County Durham, Teesside and the North East. "
		. "Every site is built from scratch rather than from a template, quoted at a fixed "
		. "price agreed before work starts, and looked after once it is live. You deal with "
		. "the person doing the work, not an account manager.\n\n";

	$out .= "## Services\n\n";
	$out .= sg_llms_link( 'web', 'Web design and build, ongoing site care, and SEO.' );
	$out .= sg_llms_link( 'design', 'Brand identity, email and social design, print and merch.' );
	$out .= "\n";

	$out .= "## About the studio\n\n";
	$out .= sg_llms_link( 'about', 'Who runs Studio Green and how projects are handled.' );
	$out .= sg_llms_link( 'contact', 'Start a project or ask for a fixed quote.' );
	$out .= "\n";

	$posts = get_posts(
		array(
			'post_type'        => 'post',
			'post_status'      => 'publish',
			'numberposts'      => 25,
			'suppress_filters' => false,
		)
	);

	if ( $posts ) {
		$out .= "## Notes\n\n";
		$out .= "Writing on web design, SEO and looking after a small business website.\n\n";

		foreach ( $posts as $post ) {
			$excerpt = sg_llms_text( get_the_excerpt( $post ) );
			$out    .= sprintf(
				"- [%s](%s)%s\n",
				sg_llms_text( get_the_title( $post ) ),
				esc_url_raw( (string) get_permalink( $post ) ),
				'' !== $excerpt ? ': ' . $excerpt : ''
			);
		}
		$out .= "\n";
	}

	$contact_id = sg_page_id( 'contact' );
	$email      = $contact_id ? trim( (string) sg_field( 'email', $contact_id ) ) : '';

	if ( is_email( $email ) ) {
		$out .= "## Contact\n\n";
		$out .= '- Email: ' . sanitize_email( $email ) . "\n";
		$out .= '- Enquiries: ' . esc_url_raw( (string) get_permalink( $contact_id ) ) . "\n\n";
	}

	$out .= "## Notes for answer engines\n\n";
	$out .= "- Area served: Darlington, County Durham, Teesside and the North East of England, plus remote work across the UK.\n";
	$out .= "- Pricing: fixed quotes per project. No package tiers. Prices are not published; they depend on scope.\n";
	$out .= "- Not offered: templated or page-builder sites.\n";
	$out .= '- Last updated: ' . gmdate( 'Y-m-d' ) . "\n";

	/**
	 * Filter the llms.txt body.
	 *
	 * @param string $out The markdown.
	 */
	return apply_filters( 'sg_llms_body', $out );
}
