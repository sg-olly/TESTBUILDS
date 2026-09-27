<?php
/**
 * One post in a listing.
 *
 * @package StudioGreen
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$sg_has_media = has_post_thumbnail();
?>

<article class="post-card<?php echo $sg_has_media ? ' post-card--has-media' : ''; ?>" data-reveal-group>
	<?php if ( $sg_has_media ) : ?>
		<a class="post-card__media" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
			<?php the_post_thumbnail( 'sg-card', array( 'loading' => 'lazy' ) ); ?>
		</a>
	<?php endif; ?>

	<div class="post-card__body">
		<p class="post-card__meta"><?php sg_post_meta(); ?></p>

		<h2 class="post-card__title t-manifesto">
			<?php
			// No data-lines here. The line reveal masks its text until an observer
			// adds .in-view, and this anchor is the card's only link, so a reveal
			// that does not fire leaves the card with nothing to click.
			?>
			<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
		</h2>

		<p class="post-card__excerpt"><?php echo esc_html( sg_card_excerpt() ); ?></p>

		<a class="post-card__more u-link" href="<?php the_permalink(); ?>">
			<?php esc_html_e( 'Read', 'studiogreen' ); ?>
			<span class="screen-reader-text"><?php the_title(); ?></span>
		</a>
	</div>
</article>
