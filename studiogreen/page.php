<?php
/**
 * Default page template: the prose layout used by the legal pages.
 *
 * Content is written normally in the editor. Each <h2> starts a new two-column
 * row, with the heading on the left and everything up to the next heading on
 * the right, matching the privacy and cookie pages on the static site.
 *
 * @package StudioGreen
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

	$sg_lead    = get_the_excerpt();
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
						/* translators: %s: month and year, for example "July 2026". */
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
			// sg_legal_rows() only regroups already-filtered content into the row
			// layout; the HTML inside it has been through the_content filters.
			echo sg_legal_rows( apply_filters( 'the_content', get_the_content() ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			?>
		</div>
	</main>

	<?php
endwhile;

get_footer();
