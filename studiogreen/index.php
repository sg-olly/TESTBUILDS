<?php
/**
 * Fallback template.
 *
 * The site is a set of static pages, so this is only reached by archives, the
 * blog index or search. It reuses the prose layout rather than introducing a
 * second visual language.
 *
 * @package StudioGreen
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<header class="page-hero" data-screen-label="Index — Hero">
	<div class="wrap" data-reveal-group>
		<h1 class="t-display" data-lines>
			<?php
			if ( is_search() ) {
				/* translators: %s: search term. */
				printf( esc_html__( 'Results for %s', 'studiogreen' ), esc_html( get_search_query() ) );
			} elseif ( is_archive() ) {
				echo esc_html( wp_strip_all_tags( get_the_archive_title() ) );
			} else {
				esc_html_e( 'Writing', 'studiogreen' );
			}
			?>
		</h1>
	</div>
</header>

<main class="law">
	<div class="wrap">
		<?php if ( have_posts() ) : ?>
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<section class="law__row" data-reveal-group>
					<h2 class="t-manifesto" data-lines><?php the_title(); ?></h2>
					<div class="law__body">
						<?php the_excerpt(); ?>
						<p><a href="<?php the_permalink(); ?>"><?php esc_html_e( 'Read more', 'studiogreen' ); ?></a></p>
					</div>
				</section>
				<?php
			endwhile;
			?>
			<?php the_posts_pagination(); ?>
		<?php else : ?>
			<section class="law__row" data-reveal-group>
				<h2 class="t-manifesto" data-lines><?php esc_html_e( 'Nothing here', 'studiogreen' ); ?></h2>
				<div class="law__body">
					<p><?php esc_html_e( 'There is nothing to show on this page yet.', 'studiogreen' ); ?></p>
				</div>
			</section>
		<?php endif; ?>
	</div>
</main>

<?php
get_footer();
