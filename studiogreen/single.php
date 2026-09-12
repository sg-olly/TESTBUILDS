<?php
/**
 * Single post.
 *
 * Posts use the same prose layout as the legal pages: title and standfirst in
 * the hero, then the editor's content regrouped so each H2 opens a two-column
 * row. Nothing to configure, and a post written normally comes out in the
 * site's design.
 *
 * @package StudioGreen
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

	$sg_lead = get_the_excerpt();
	?>

	<article <?php post_class(); ?>>
		<!-- ===== HERO ===== -->
		<header class="page-hero" data-screen-label="Post — Hero">
			<div class="wrap" data-reveal-group>
				<h1 class="t-display" data-lines><?php the_title(); ?></h1>
				<?php if ( $sg_lead ) : ?>
					<p class="t-manifesto page-hero__lead" data-lines><?php echo esc_html( $sg_lead ); ?></p>
				<?php endif; ?>
				<p class="page-hero__updated">
					<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
				</p>
			</div>
		</header>

		<!-- ===== CONTENT ===== -->
		<main class="law" data-screen-label="Post — Content">
			<div class="wrap">
				<?php
				// Regroups already-filtered content into the row layout.
				echo sg_legal_rows( apply_filters( 'the_content', get_the_content() ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				?>
			</div>
		</main>
	</article>

	<!-- ===== CLOSING ===== -->
	<?php $sg_contact = sg_page_id( 'contact' ); ?>
	<?php if ( $sg_contact ) : ?>
		<section class="page-close" data-screen-label="Post — Closing">
			<div class="wrap" data-reveal-group>
				<p class="t-manifesto page-close__line" data-lines><?php esc_html_e( 'Got a business that deserves better than fine?', 'studiogreen' ); ?></p>
				<a class="t-display page-close__link" href="<?php echo esc_url( get_permalink( $sg_contact ) ); ?>">
					<?php echo sg_reveal_line( __( 'Start a project', 'studiogreen' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in helper. ?>
				</a>
			</div>
		</section>
	<?php endif; ?>

	<?php
endwhile;

get_footer();
