<?php
/**
 * Document head, fixed navigation and mobile drawer.
 *
 * @package StudioGreen
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$sg_contact_id = sg_page_id( 'contact' );
$sg_on_contact = $sg_contact_id && is_page( $sg_contact_id );

if ( $sg_on_contact ) {
	$sg_cta_url   = 'mailto:' . sg_field( 'email', $sg_contact_id );
	$sg_cta_label = __( 'Email instead', 'studiogreen' );
} else {
	$sg_cta_url   = $sg_contact_id ? get_permalink( $sg_contact_id ) : home_url( '/' );
	$sg_cta_label = __( 'Start a project', 'studiogreen' );
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

<!-- ===== NAV ===== -->
<nav class="nav" data-nav>
	<a class="brand" href="<?php echo esc_url( home_url( '/' ) ); ?>">
		<span class="brand-logo" role="img" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>"></span>
	</a>
	<?php sg_nav( 'primary', 'nav-links' ); ?>
	<div class="nav-cta">
		<a href="<?php echo esc_url( $sg_cta_url ); ?>" class="btn btn--solid" data-magnet><?php echo esc_html( $sg_cta_label ); ?></a>
		<button class="menu-btn" aria-label="<?php esc_attr_e( 'Open menu', 'studiogreen' ); ?>">
			<svg width="20" height="14" viewBox="0 0 20 14" fill="none" aria-hidden="true"><path d="M0 1h20M0 7h20M0 13h20" stroke="currentColor" stroke-width="1.6"/></svg>
		</button>
	</div>
</nav>

<?php sg_nav( 'primary', 'drawer', 'div', array( 'data-drawer' => '' ) ); ?>
