<?php
/**
 * Default page template: the prose layout used by the legal pages.
 *
 * @package StudioGreen
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

	// Written excerpts only; a generated one would repeat the opening paragraph.
	$sg_lead    = has_excerpt() ? get_the_excerpt() : '';
	$sg_updated = sg_updated_label();
	?>

	<!-- ===== HERO ===== -->
	<header class="page-hero" data-screen-label="<?php echo esc_attr( get_the_title() . ' — Hero' ); ?>">
		<div class="wrap" data-reveal-group>
			<h1 class="t-display" data-lines><?php the_title(); ?></h1>
			<?php if ( $sg_lead ) : ?>
				<p class="t-manifesto page-hero__lead" data-lines><?php echo esc_html( $sg_lead ); ?></p>
			<?php endif; ?>
			<?php if ( $sg_updated ) : ?>
				<p class="page-hero__updated">
					<?php
					printf(
						/* translators: %s: month and year. */
						esc_html__( 'Last updated: %s', 'studiogreen' ),
						esc_html( $sg_updated )
					);
					?>
				</p>
			<?php endif; ?>
		</div>
	</header>

	<!-- ===== CONTENT ===== -->
	<main class="law" data-screen-label="<?php echo esc_attr( get_the_title() . ' — Content' ); ?>">
		<div class="wrap">
			<?php
			echo sg_legal_rows( apply_filters( 'the_content', get_the_content() ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			?>
		</div>
	</main>

	<?php
endwhile;

get_footer();
