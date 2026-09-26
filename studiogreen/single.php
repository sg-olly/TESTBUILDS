<?php
/**
 * Single post.
 *
 * @package StudioGreen
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

	// Only a written excerpt, never a generated one, which would repeat the opening.
	$sg_lead = has_excerpt() ? get_the_excerpt() : '';
	$sg_tags = get_the_tags();
	?>

	<article <?php post_class(); ?>>
		<!-- ===== HERO ===== -->
		<header class="page-hero" data-screen-label="Post — Hero">
			<div class="wrap" data-reveal-group>
				<h1 class="t-display" data-lines><?php the_title(); ?></h1>
				<?php if ( '' !== trim( $sg_lead ) ) : ?>
					<p class="t-manifesto page-hero__lead" data-lines><?php echo esc_html( $sg_lead ); ?></p>
				<?php endif; ?>
				<p class="post__meta"><?php sg_post_meta(); ?></p>
			</div>
		</header>

		<?php if ( has_post_thumbnail() ) : ?>
			<div class="wrap">
				<figure class="post__cover">
					<?php
					the_post_thumbnail(
						'sg-wide',
						array(
							'loading'       => 'eager',
							'fetchpriority' => 'high',
						)
					);
					?>
					<?php $sg_caption = get_the_post_thumbnail_caption(); ?>
					<?php if ( $sg_caption ) : ?>
						<figcaption><?php echo esc_html( $sg_caption ); ?></figcaption>
					<?php endif; ?>
				</figure>
			</div>
		<?php endif; ?>

		<!-- ===== CONTENT ===== -->
		<main class="post" data-screen-label="Post — Content">
			<div class="wrap">
				<?php // No data-lines on prose: the splitter would discard links and emphasis. ?>
				<div class="post__body">
					<?php the_content(); ?>
					<?php
					wp_link_pages(
						array(
							'before' => '<nav class="post__pages">',
							'after'  => '</nav>',
						)
					);
					?>
				</div>

				<?php if ( $sg_tags && ! is_wp_error( $sg_tags ) ) : ?>
					<p class="post__tags">
						<?php foreach ( $sg_tags as $sg_tag ) : ?>
							<a class="u-link" href="<?php echo esc_url( (string) get_tag_link( $sg_tag ) ); ?>"><?php echo esc_html( $sg_tag->name ); ?></a>
						<?php endforeach; ?>
					</p>
				<?php endif; ?>

				<?php sg_post_nav(); ?>
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
					<?php echo sg_reveal_line( __( 'Start a project', 'studiogreen' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</a>
			</div>
		</section>
	<?php endif; ?>

	<?php
endwhile;

get_footer();
