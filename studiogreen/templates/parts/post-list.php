<?php
/**
 * Shared listing body for the notes index, archives and search.
 *
 * @package StudioGreen
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$sg_lead = sg_listing_lead();
?>

<header class="page-hero" data-screen-label="<?php echo esc_attr( sg_listing_title() . ' — Hero' ); ?>">
	<div class="wrap" data-reveal-group>
		<h1 class="t-display" data-lines><?php echo esc_html( sg_listing_title() ); ?></h1>
		<?php if ( '' !== trim( $sg_lead ) ) : ?>
			<p class="t-manifesto page-hero__lead" data-lines><?php echo esc_html( $sg_lead ); ?></p>
		<?php endif; ?>
		<?php if ( is_search() ) : ?>
			<?php get_search_form(); ?>
		<?php endif; ?>
	</div>
</header>

<main class="blog" data-screen-label="<?php echo esc_attr( sg_listing_title() . ' — List' ); ?>">
	<div class="wrap">
		<?php if ( have_posts() ) : ?>
			<div class="blog__list">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'templates/parts/post-card' );
				endwhile;
				?>
			</div>
			<?php sg_pagination(); ?>
		<?php else : ?>
			<div class="blog__empty" data-reveal-group>
				<p class="t-manifesto" data-lines>
					<?php
					if ( is_search() ) {
						esc_html_e( 'Nothing matched that.', 'studiogreen' );
					} else {
						esc_html_e( 'Nothing here yet.', 'studiogreen' );
					}
					?>
				</p>
				<p>
					<?php
					if ( is_search() ) {
						esc_html_e( 'Try a different word, or get in touch and ask me directly.', 'studiogreen' );
					} else {
						esc_html_e( 'The first piece is on its way.', 'studiogreen' );
					}
					?>
				</p>
				<?php if ( ! is_search() ) : ?>
					<?php $sg_contact = sg_page_id( 'contact' ); ?>
					<?php if ( $sg_contact ) : ?>
						<p>
							<a class="u-link" href="<?php echo esc_url( (string) get_permalink( $sg_contact ) ); ?>">
								<?php esc_html_e( 'Start a project', 'studiogreen' ); ?>
							</a>
						</p>
					<?php endif; ?>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
</main>
