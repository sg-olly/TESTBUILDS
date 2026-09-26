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
