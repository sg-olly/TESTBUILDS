<?php
/**
 * Template Name: About
 *
 * @package StudioGreen
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<!-- ===== HERO ===== -->
<header class="page-hero" data-screen-label="About — Hero">
	<div class="wrap" data-reveal-group>
		<h1 class="t-display" data-lines><?php sg_the_field( 'hero_title' ); ?></h1>
		<p class="t-manifesto page-hero__lead" data-lines><?php sg_the_field( 'hero_lead' ); ?></p>
	</div>
</header>

<!-- ===== STORY ===== -->
<section class="manifesto" data-screen-label="About — Story">
	<div class="wrap">
		<div class="manifesto__copy" data-reveal-group>
			<p class="t-manifesto" data-lines><?php sg_the_field( 'story_p1' ); ?></p>
			<p class="t-manifesto" data-lines><?php sg_the_field( 'story_p2' ); ?></p>
		</div>
		<?php sg_render_stats(); ?>
	</div>
</section>

<!-- ===== HOW I WORK ===== -->
<section class="how" data-screen-label="About — How I work">
	<div class="wrap">
		<ul class="how-index">
			<?php for ( $sg_i = 1; $sg_i <= 4; $sg_i++ ) : ?>
				<?php
				$sg_title = sg_field( "how_{$sg_i}_title" );
				if ( '' === trim( $sg_title ) ) {
					continue;
				}
				?>
				<li data-reveal-group>
					<div>
						<h2 class="t-display"><?php echo sg_reveal_line( $sg_title ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></h2>
						<p><?php sg_the_field( "how_{$sg_i}_text" ); ?></p>
					</div>
				</li>
			<?php endfor; ?>
		</ul>
	</div>
</section>

<!-- ===== CLOSING ===== -->
<section class="page-close" data-screen-label="About — Closing">
	<div class="wrap" data-reveal-group>
		<p class="t-manifesto page-close__line" data-lines><?php sg_the_field( 'close_line' ); ?></p>
		<a class="t-display page-close__link" href="<?php echo esc_url( sg_field_url( 'close_cta_url' ) ); ?>">
			<?php echo sg_reveal_line( sg_field( 'close_cta' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</a>
	</div>
</section>

<?php
get_footer();
