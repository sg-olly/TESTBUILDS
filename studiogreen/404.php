<?php
/**
 * Not found.
 *
 * @package StudioGreen
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<header class="page-hero" data-screen-label="404 — Hero">
	<div class="wrap" data-reveal-group>
		<h1 class="t-display" data-lines><?php esc_html_e( 'That page has moved on.', 'studiogreen' ); ?></h1>
		<p class="t-manifesto page-hero__lead" data-lines><?php esc_html_e( 'The link you followed does not lead anywhere any more. Everything else is still where you left it.', 'studiogreen' ); ?></p>
	</div>
</header>

<section class="page-close" data-screen-label="404 — Closing">
	<div class="wrap" data-reveal-group>
		<a class="t-display page-close__link" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<?php echo sg_reveal_line( __( 'Back to the start', 'studiogreen' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</a>
	</div>
</section>

<?php
get_footer();
