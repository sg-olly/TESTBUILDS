<?php
/**
 * Markup helpers shared by the templates.
 *
 * @package StudioGreen
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Inline HTML permitted inside body copy fields.
 *
 * @return array
 */
function sg_inline_html() {
	return array(
		'a'      => array(
			'href'   => array(),
			'title'  => array(),
			'target' => array(),
			'rel'    => array(),
		),
		'strong' => array(),
		'b'      => array(),
		'em'     => array(),
		'i'      => array(),
		'br'     => array(),
		'span'   => array( 'class' => array() ),
	);
}

/**
 * Look up a page created by the theme installer.
 *
 * @param string $key Page key: home, web, design, about, contact, privacy, cookies.
 * @return int Page ID, or 0 if not found.
 */
function sg_page_id( $key ) {
	/*
	 * Settings > Reading is authoritative for the posts page, so the nav and
	 * {notes} keep working if it is changed or given a different slug.
	 */
	if ( 'notes' === $key ) {
		$posts_page = (int) get_option( 'page_for_posts' );

		if ( $posts_page && 'publish' === get_post_status( $posts_page ) ) {
			return $posts_page;
		}
	}

	$ids = get_option( 'sg_page_ids', array() );

	if ( ! empty( $ids[ $key ] ) && 'publish' === get_post_status( (int) $ids[ $key ] ) ) {
		return (int) $ids[ $key ];
	}

	$page = get_page_by_path( $key );

	return $page ? (int) $page->ID : 0;
}

/**
 * Permalink of the cookie policy, used by the consent banner.
 *
 * @return string
 */
function sg_cookies_url() {
	$id = sg_page_id( 'cookies' );

	return $id ? get_permalink( $id ) : home_url( '/' );
}

/**
 * Collect the items for a nav menu location.
 *
 * @param string $location Registered menu location.
 * @return array<int,array{url:string,title:string,current:bool}>
 */
function sg_nav_items( $location ) {
	$items    = array();
	$locations = get_nav_menu_locations();

	if ( ! empty( $locations[ $location ] ) ) {
		$menu_items = wp_get_nav_menu_items( $locations[ $location ] );

		if ( $menu_items ) {
			$notes_id = sg_page_id( 'notes' );

			foreach ( $menu_items as $item ) {
				if ( (int) $item->menu_item_parent ) {
					continue;
				}

				// A post or a category archive is still "in" the notes section.
				$current = sg_is_current_url( $item->url )
					|| ( $notes_id
						&& 'post_type' === $item->type
						&& (int) $item->object_id === $notes_id
						&& sg_is_blog_context() );

				$items[] = array(
					'url'     => $item->url,
					'title'   => $item->title,
					'current' => $current,
				);
			}
			return $items;
		}
	}

	$fallback = ( 'footer_legal' === $location )
		? array( 'privacy', 'cookies' )
		: array( 'home', 'web', 'design', 'about', 'notes', 'contact' );

	foreach ( $fallback as $key ) {
		$id = sg_page_id( $key );
		if ( ! $id ) {
			continue;
		}
		$items[] = array(
			'url'     => get_permalink( $id ),
			'title'   => get_the_title( $id ),
			'current' => is_page( $id )
				|| ( 'home' === $key && is_front_page() )
				|| ( 'notes' === $key && sg_is_blog_context() ),
		);
	}

	return $items;
}

/**
 * Whether a menu URL points at the page being viewed.
 *
 * @param string $url Menu item URL.
 * @return bool
 */
function sg_is_current_url( $url ) {
	$current = trailingslashit( home_url( add_query_arg( array() ) ) );

	return untrailingslashit( $current ) === untrailingslashit( $url );
}

/**
 * Render a flat row of navigation anchors.
 *
 * @param string $location Registered menu location.
 * @param string $class    Class for the wrapping element.
 * @param string $tag      Wrapper tag name.
 * @param array  $attrs    Extra attributes for the wrapper, as name => value.
 */
function sg_nav( $location, $class, $tag = 'div', $attrs = array() ) {
	$items = sg_nav_items( $location );

	if ( ! $items ) {
		return;
	}

	$rendered = '';
	foreach ( $attrs as $name => $value ) {
		$rendered .= sprintf( ' %s="%s"', esc_attr( $name ), esc_attr( $value ) );
	}

	printf( '<%s class="%s"%s>', esc_attr( $tag ), esc_attr( $class ), $rendered ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	foreach ( $items as $item ) {
		printf(
			'<a href="%s"%s>%s</a>',
			esc_url( $item['url'] ),
			$item['current'] ? ' class="active" aria-current="page"' : '',
			esc_html( $item['title'] )
		);
	}

	printf( '</%s>', esc_attr( $tag ) );
}

/**
 * Escape plain text and turn ==markers== into highlight spans.
 *
 * @param string $text Plain text, possibly containing ==highlights==.
 * @return string Escaped HTML.
 */
function sg_marks( $text ) {
	$escaped = esc_html( $text );

	// == is untouched by escaping, so this is safe to run afterwards.
	return preg_replace(
		'/==(.+?)==/u',
		'<span class="mark">$1</span>',
		$escaped
	);
}

/**
 * A single masked reveal line.
 *
 * @param string $text  Line text.
 * @param bool   $plain Escape as plain text (true) or allow inline HTML (false).
 * @return string
 */
function sg_reveal_line( $text, $plain = true ) {
	$inner = $plain ? sg_marks( $text ) : wp_kses( $text, sg_inline_html() );

	return '<span class="reveal-line"><span>' . $inner . '</span></span>';
}

/**
 * Render the three-up statistics row shared by the home and about pages.
 */
function sg_render_stats() {
	echo '<div class="manifesto__stats" data-reveal-group>';

	for ( $i = 1; $i <= 3; $i++ ) {
		$value = sg_field( "stat_{$i}_value" );
		$label = sg_field( "stat_{$i}_label" );

		if ( '' === trim( $value ) && '' === trim( $label ) ) {
			continue;
		}

		if ( preg_match( '/^(\d+(?:\.\d+)?)(.*)$/', trim( $value ), $matches ) ) {
			$figure = sprintf(
				'<span data-count="%s">0</span>%s',
				esc_attr( $matches[1] ),
				esc_html( $matches[2] )
			);
		} else {
			$figure = esc_html( $value );
		}

		printf(
			'<div class="stat-item"><span class="t-display"><span class="reveal-line"><span>%s</span></span></span><span>%s</span></div>',
			$figure, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			esc_html( $label )
		);
	}

	echo '</div>';
}

/**
 * Rebuild editor content into the legal pages' two-column rows.
 *
 * @param string $content Rendered post content.
 * @return string
 */
function sg_legal_rows( $content ) {
	$parts = preg_split(
		'/(<h2\b[^>]*>.*?<\/h2>)/is',
		$content,
		-1,
		PREG_SPLIT_DELIM_CAPTURE
	);

	if ( ! $parts ) {
		return '';
	}

	$out     = '';
	$intro   = trim( (string) array_shift( $parts ) );
	$heading = null;

	if ( '' !== $intro ) {
		$out .= '<section class="law__row" data-reveal-group><div></div><div class="law__body">' . $intro . '</div></section>';
	}

	foreach ( $parts as $part ) {
		if ( null === $heading && preg_match( '/^<h2\b/i', trim( $part ) ) ) {
			$heading = wp_strip_all_tags( $part );
			continue;
		}

		if ( null !== $heading ) {
			$out .= '<section class="law__row" data-reveal-group>'
				. '<h2 class="t-manifesto" data-lines>' . esc_html( $heading ) . '</h2>'
				. '<div class="law__body">' . trim( $part ) . '</div>'
				. '</section>';
			$heading = null;
		}
	}

	// A trailing heading with no body after it.
	if ( null !== $heading ) {
		$out .= '<section class="law__row" data-reveal-group>'
			. '<h2 class="t-manifesto" data-lines>' . esc_html( $heading ) . '</h2>'
			. '<div class="law__body"></div>'
			. '</section>';
	}

	return $out;
}

/**
 * The "Last updated" line for a legal page.
 *
 * @param int|null $post_id Page ID.
 * @return string
 */
function sg_updated_label( $post_id = null ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$value   = trim( (string) get_post_meta( $post_id, 'sg_updated', true ) );

	if ( '' !== $value ) {
		return $value;
	}

	return get_the_modified_date( 'F Y', $post_id );
}
