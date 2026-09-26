<?php
/**
 * First-run setup.
 *
 * @package StudioGreen
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The pages the site is built from, in menu order.
 *
 * @return array
 */
function sg_page_blueprint() {
	return array(
		'home'    => array(
			'title'     => __( 'Home', 'studiogreen' ),
			'template'  => '',
			'menu'      => __( 'Home', 'studiogreen' ),
			'menus'     => array( 'primary', 'footer_pages' ),
		),
		'web'     => array(
			'title'    => __( 'Web', 'studiogreen' ),
			'template' => 'templates/template-web.php',
			'menu'     => __( 'Web', 'studiogreen' ),
			'menus'    => array( 'primary', 'footer_pages' ),
		),
		'design'  => array(
			'title'    => __( 'Design', 'studiogreen' ),
			'template' => 'templates/template-design.php',
			'menu'     => __( 'Design', 'studiogreen' ),
			'menus'    => array( 'primary', 'footer_pages' ),
		),
		'about'   => array(
			'title'    => __( 'About', 'studiogreen' ),
			'template' => 'templates/template-about.php',
			'menu'     => __( 'About', 'studiogreen' ),
			'menus'    => array( 'primary', 'footer_pages' ),
		),
		'notes'   => array(
			// The posts page: WordPress ignores its content, so it takes no template.
			'title'    => __( 'Notes', 'studiogreen' ),
			'template' => '',
			'menu'     => __( 'Notes', 'studiogreen' ),
			'menus'    => array( 'primary', 'footer_pages' ),
			'excerpt'  => 'Notes on web design, SEO and running a small studio. Written for the people paying for the work, not for other designers.',
			'meta'     => array(
				'sg_meta_description' => 'Notes from Studio Green on web design, SEO and looking after a small business website. Plain English, no jargon.',
			),
		),
		'contact' => array(
			'title'    => __( 'Contact', 'studiogreen' ),
			'template' => 'templates/template-contact.php',
			'menu'     => __( 'Contact', 'studiogreen' ),
			'menus'    => array( 'primary', 'footer_pages' ),
		),
		'privacy' => array(
			'title'    => __( 'Privacy policy', 'studiogreen' ),
			'template' => '',
			'menu'     => __( 'Privacy', 'studiogreen' ),
			'menus'    => array( 'footer_legal' ),
			'excerpt'  => 'How Studio Green collects, uses and looks after your personal information, and the rights you have over it.',
			'content'  => 'privacy',
			'meta'     => array(
				'sg_updated'          => 'July 2026',
				'sg_meta_description' => 'How Studio Green collects, uses and protects your personal data, and the rights you have under UK data protection law.',
			),
		),
		'cookies' => array(
			'title'    => __( 'Cookie policy', 'studiogreen' ),
			'template' => '',
			'menu'     => __( 'Cookies', 'studiogreen' ),
			'menus'    => array( 'footer_legal' ),
			'excerpt'  => 'The small files that help this site work, and how you stay in control of them.',
			'content'  => 'cookies',
			'meta'     => array(
				'sg_updated'          => 'July 2026',
				'sg_meta_description' => 'How Studio Green uses cookies and similar technologies, the ones we set, and how to control them.',
			),
		),
	);
}

/**
 * Run the one-time setup.
 */
function sg_install() {
	if ( get_option( 'sg_installed' ) ) {
		return;
	}

	$ids = array();

	foreach ( sg_page_blueprint() as $slug => $page ) {
		$ids[ $slug ] = sg_ensure_page( $slug, $page );
	}

	update_option( 'sg_page_ids', $ids );

	// Static front page.
	if ( ! empty( $ids['home'] ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $ids['home'] );
	}

	// After the front page is set, so the two cannot end up as the same page.
	sg_ensure_posts_page();

	if ( 'Just another WordPress site' === get_option( 'blogdescription' ) ) {
		update_option( 'blogdescription', 'Marketing & web design that grows your business' );
	}

	if ( '' === get_option( 'permalink_structure' ) ) {
		update_option( 'permalink_structure', '/%postname%/' );
	}

	sg_build_menus( $ids );

	flush_rewrite_rules();

	update_option( 'sg_installed', SG_VERSION );
}
add_action( 'after_switch_theme', 'sg_install' );

/**
 * Record a page ID without disturbing the others.
 *
 * @param string $key Blueprint key.
 * @param int    $id  Page ID.
 */
function sg_remember_page_id( $key, $id ) {
	$ids = get_option( 'sg_page_ids', array() );

	if ( ! is_array( $ids ) ) {
		$ids = array();
	}

	if ( isset( $ids[ $key ] ) && (int) $ids[ $key ] === (int) $id ) {
		return;
	}

	$ids[ $key ] = (int) $id;

	update_option( 'sg_page_ids', $ids );
}

/**
 * Make sure a posts page exists and is assigned. Safe to run repeatedly.
 *
 * @return int Page ID, or 0 if none was set.
 */
function sg_ensure_posts_page() {
	$existing = (int) get_option( 'page_for_posts' );

	// Already pointed at a real page: record it and leave the choice alone.
	if (
		$existing
		&& 'page' === get_post_type( $existing )
		&& 'publish' === get_post_status( $existing )
		&& (int) get_option( 'page_on_front' ) !== $existing
	) {
		sg_remember_page_id( 'notes', $existing );

		return $existing;
	}

	// WordPress ignores page_for_posts unless the front page is static.
	if ( 'page' !== get_option( 'show_on_front' ) ) {
		return 0;
	}

	$blueprint = sg_page_blueprint();

	if ( empty( $blueprint['notes'] ) ) {
		return 0;
	}

	$id = sg_ensure_page( 'notes', $blueprint['notes'] );

	if ( ! $id || (int) get_option( 'page_on_front' ) === $id ) {
		return 0;
	}

	update_option( 'page_for_posts', $id );
	sg_remember_page_id( 'notes', $id );

	return $id;
}

/**
 * Add a page to menus that already have items, without duplicating it.
 *
 * sg_build_menus() only fills a menu it created and left empty, so an existing
 * site needs this instead.
 *
 * @param int      $page_id   Page to link.
 * @param string   $label     Menu label.
 * @param string[] $locations Menu locations.
 */
function sg_add_page_to_menus( $page_id, $label, $locations ) {
	$assigned = get_nav_menu_locations();

	foreach ( $locations as $location ) {
		if ( empty( $assigned[ $location ] ) || ! is_nav_menu( $assigned[ $location ] ) ) {
			continue;
		}

		$term_id = (int) $assigned[ $location ];
		$items   = wp_get_nav_menu_items( $term_id );
		$items   = $items ? $items : array();
		$present = false;

		foreach ( $items as $item ) {
			if ( 'post_type' === $item->type && (int) $item->object_id === (int) $page_id ) {
				$present = true;
				break;
			}
		}

		if ( $present ) {
			continue;
		}

		// Appended rather than inserted, so a hand-ordered menu is not renumbered.
		wp_update_nav_menu_item(
			$term_id,
			0,
			array(
				'menu-item-title'     => $label,
				'menu-item-object'    => 'page',
				'menu-item-object-id' => (int) $page_id,
				'menu-item-type'      => 'post_type',
				'menu-item-status'    => 'publish',
				'menu-item-position'  => count( $items ) + 1,
			)
		);
	}
}

/**
 * Apply changes a site installed under an earlier version has not had.
 *
 * sg_install() only ever runs once, on theme switch, so anything added later
 * needs its own gate. Every step here is safe to repeat.
 */
function sg_maybe_upgrade() {
	$installed = get_option( 'sg_installed' );

	if ( ! $installed ) {
		return;
	}

	if ( version_compare( (string) $installed, SG_VERSION, '>=' ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}

	$notes_id = sg_ensure_posts_page();

	if ( $notes_id ) {
		sg_add_page_to_menus( $notes_id, __( 'Notes', 'studiogreen' ), array( 'primary', 'footer_pages' ) );
	}

	update_option( 'default_comment_status', 'closed' );
	update_option( 'default_ping_status', 'closed' );

	flush_rewrite_rules();

	update_option( 'sg_installed', SG_VERSION );
}
add_action( 'admin_init', 'sg_maybe_upgrade' );

/**
 * Create a page, or adopt an existing one with the same slug.
 *
 * @param string $slug Page slug.
 * @param array  $page Blueprint entry.
 * @return int Page ID, or 0 on failure.
 */
function sg_ensure_page( $slug, $page ) {
	$existing = get_page_by_path( $slug );

	if ( $existing ) {
		if ( ! empty( $page['template'] ) && ! get_page_template_slug( $existing->ID ) ) {
			update_post_meta( $existing->ID, '_wp_page_template', $page['template'] );
		}
		return (int) $existing->ID;
	}

	$content = '';
	if ( ! empty( $page['content'] ) ) {
		$content = sg_legal_content( $page['content'] );
	}

	$id = wp_insert_post(
		array(
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'post_title'     => $page['title'],
			'post_name'      => $slug,
			'post_excerpt'   => isset( $page['excerpt'] ) ? $page['excerpt'] : '',
			'post_content'   => $content,
			'comment_status' => 'closed',
			'ping_status'    => 'closed',
		),
		true
	);

	if ( is_wp_error( $id ) || ! $id ) {
		return 0;
	}

	if ( ! empty( $page['template'] ) ) {
		update_post_meta( $id, '_wp_page_template', $page['template'] );
	}

	if ( ! empty( $page['meta'] ) ) {
		foreach ( $page['meta'] as $key => $value ) {
			update_post_meta( $id, $key, $value );
		}
	}

	return (int) $id;
}

/**
 * Create the three menus and assign them to their locations.
 *
 * @param array $ids Page IDs keyed by slug.
 */
function sg_build_menus( $ids ) {
	$locations = get_nav_menu_locations();
	$names     = array(
		'primary'      => __( 'Primary', 'studiogreen' ),
		'footer_pages' => __( 'Footer pages', 'studiogreen' ),
		'footer_legal' => __( 'Footer legal', 'studiogreen' ),
	);

	foreach ( $names as $location => $name ) {
		if ( ! empty( $locations[ $location ] ) && is_nav_menu( $locations[ $location ] ) ) {
			continue;
		}

		$menu = wp_get_nav_menu_object( $name );
		if ( ! $menu ) {
			$menu_id = wp_create_nav_menu( $name );
			if ( is_wp_error( $menu_id ) ) {
				continue;
			}
			$menu = wp_get_nav_menu_object( $menu_id );
		}

		if ( ! $menu ) {
			continue;
		}

		// Only populate a menu we just created and left empty.
		if ( ! wp_get_nav_menu_items( $menu->term_id ) ) {
			$order = 1;
			foreach ( sg_page_blueprint() as $slug => $page ) {
				if ( ! in_array( $location, $page['menus'], true ) || empty( $ids[ $slug ] ) ) {
					continue;
				}

				wp_update_nav_menu_item(
					$menu->term_id,
					0,
					array(
						'menu-item-title'     => $page['menu'],
						'menu-item-object'    => 'page',
						'menu-item-object-id' => $ids[ $slug ],
						'menu-item-type'      => 'post_type',
						'menu-item-status'    => 'publish',
						'menu-item-position'  => $order,
					)
				);
				$order++;
			}
		}

		$locations[ $location ] = $menu->term_id;
	}

	set_theme_mod( 'nav_menu_locations', $locations );
}

/**
 * The starting copy for a legal page.
 *
 * @param string $which 'privacy' or 'cookies'.
 * @return string
 */
function sg_legal_content( $which ) {
	$file = SG_DIR . '/inc/content/' . $which . '.html';

	return file_exists( $file ) ? (string) file_get_contents( $file ) : ''; // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
}

/**
 * Turn {page} tokens inside page content into real permalinks.
 *
 * @param string $content Post content.
 * @return string
 */
function sg_content_tokens( $content ) {
	return sg_resolve_tokens( $content );
}
add_filter( 'the_content', 'sg_content_tokens', 9 );

/**
 * Point out what to do next, once, after the theme is first activated.
 */
function sg_admin_notice() {
	if ( ! get_option( 'sg_installed' ) || get_option( 'sg_notice_dismissed' ) ) {
		return;
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( isset( $_GET['sg-dismiss'] ) && check_admin_referer( 'sg_dismiss' ) ) {
		update_option( 'sg_notice_dismissed', 1 );
		return;
	}

	$dismiss = wp_nonce_url( add_query_arg( 'sg-dismiss', 1 ), 'sg_dismiss' );

	echo '<div class="notice notice-info"><p><strong>' . esc_html__( 'Studio Green is set up.', 'studiogreen' ) . '</strong> ';
	echo esc_html__( 'The pages, menus and front page have been created. Worth checking next: Settings > General for the site title and tagline, and Settings > Permalinks (open it once to flush the rules).', 'studiogreen' );
	echo ' <a href="' . esc_url( $dismiss ) . '">' . esc_html__( 'Dismiss', 'studiogreen' ) . '</a></p></div>';
}
add_action( 'admin_notices', 'sg_admin_notice' );
