<?php
/**
 * Posts: listing, meta and navigation.
 *
 * @package StudioGreen
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether the current view belongs to the notes section.
 *
 * @return bool
 */
function sg_is_blog_context() {
	return is_home() || is_singular( 'post' ) || is_category() || is_tag() || is_author() || is_date();
}

/**
 * Comments are not used anywhere on this site.
 */
add_filter( 'comments_open', '__return_false' );
add_filter( 'pings_open', '__return_false' );

/**
 * A post's categories, ignoring the default term nothing was filed under.
 *
 * @param int|null $post_id Post ID.
 * @return WP_Term[]
 */
function sg_post_categories( $post_id = null ) {
	$terms = get_the_category( $post_id );

	if ( ! $terms || is_wp_error( $terms ) ) {
		return array();
	}

	$default = (int) get_option( 'default_category' );

	return array_values(
		array_filter(
			$terms,
			function ( $term ) use ( $default ) {
				// "Uncategorised" means nobody filed it, so it is not worth printing.
				return (int) $term->term_id !== $default;
			}
		)
	);
}

/**
 * Print the date, and the categories when a post has been filed.
 *
 * @param int|null $post_id Post ID.
 */
function sg_post_meta( $post_id = null ) {
	$parts = array(
		sprintf(
			'<time datetime="%s">%s</time>',
			esc_attr( get_the_date( 'c', $post_id ) ),
			esc_html( get_the_date( '', $post_id ) )
		),
	);

	$links = array();
	foreach ( sg_post_categories( $post_id ) as $term ) {
		$url = get_category_link( $term );
		if ( $url && ! is_wp_error( $url ) ) {
			$links[] = sprintf( '<a class="u-link" href="%s">%s</a>', esc_url( $url ), esc_html( $term->name ) );
		}
	}

	if ( $links ) {
		$parts[] = implode( ', ', $links );
	}

	echo implode( '<span class="post__meta-sep" aria-hidden="true"> / </span>', $parts ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * A listing excerpt, trimmed whether it was written by hand or generated.
 *
 * excerpt_length only reaches auto-generated excerpts, which would leave a
 * hand-written one running to full length beside its trimmed neighbours.
 *
 * @param int      $words   Maximum words.
 * @param int|null $post_id Post ID.
 * @return string
 */
function sg_card_excerpt( $words = 30, $post_id = null ) {
	$raw = wp_strip_all_tags( get_the_excerpt( $post_id ) );

	return wp_trim_words( $raw, $words, '…' );
}

/**
 * Paginate a listing.
 *
 * Built on paginate_links() rather than the_posts_pagination(), whose wrapper
 * hard-codes a .nav-links element that collides with the header nav.
 */
function sg_pagination() {
	$links = paginate_links(
		array(
			'type'      => 'list',
			'mid_size'  => 1,
			'prev_text' => __( 'Newer', 'studiogreen' ),
			'next_text' => __( 'Older', 'studiogreen' ),
		)
	);

	if ( ! $links ) {
		return;
	}

	printf(
		'<nav class="sg-pagination" aria-label="%s">%s</nav>',
		esc_attr__( 'Posts', 'studiogreen' ),
		wp_kses_post( $links )
	);
}

/**
 * Previous and next post links.
 *
 * the_post_navigation() is avoided for the same reason as the_posts_pagination().
 */
function sg_post_nav() {
	$previous = get_previous_post();
	$next     = get_next_post();

	if ( ! $previous && ! $next ) {
		return;
	}

	echo '<nav class="post-nav" aria-label="' . esc_attr__( 'More writing', 'studiogreen' ) . '">';

	foreach ( array( 'previous' => $previous, 'next' => $next ) as $rel => $post ) {
		if ( ! $post ) {
			continue;
		}

		printf(
			'<a class="post-nav__item post-nav__item--%1$s" href="%2$s" rel="%1$s">'
				. '<span class="post-nav__label">%3$s</span>'
				. '<span class="post-nav__title">%4$s</span>'
				. '</a>',
			esc_attr( $rel ),
			esc_url( (string) get_permalink( $post ) ),
			'previous' === $rel ? esc_html__( 'Previous', 'studiogreen' ) : esc_html__( 'Next', 'studiogreen' ),
			esc_html( get_the_title( $post ) )
		);
	}

	echo '</nav>';
}

/**
 * The heading for a listing view.
 *
 * @return string
 */
function sg_listing_title() {
	if ( is_search() ) {
		/* translators: %s: search term. */
		return sprintf( __( 'Results for %s', 'studiogreen' ), get_search_query() );
	}

	if ( is_home() ) {
		$id = (int) get_option( 'page_for_posts' );

		return $id ? get_the_title( $id ) : __( 'Notes', 'studiogreen' );
	}

	if ( is_archive() ) {
		return wp_strip_all_tags( get_the_archive_title() );
	}

	return __( 'Notes', 'studiogreen' );
}

/**
 * The standfirst for a listing view, taken from the posts page's excerpt.
 *
 * @return string
 */
function sg_listing_lead() {
	if ( is_home() ) {
		$id = (int) get_option( 'page_for_posts' );

		return $id ? wp_strip_all_tags( get_the_excerpt( $id ) ) : '';
	}

	if ( is_archive() ) {
		return wp_strip_all_tags( get_the_archive_description() );
	}

	return '';
}
