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
			<?php // data-lines belongs on the anchor: the splitter rebuilds its host's contents, so a link nested inside would be discarded. ?>
			<a href="<?php the_permalink(); ?>" data-lines><?php the_title(); ?></a>
		</h2>

		<p class="post-card__excerpt"><?php echo esc_html( sg_card_excerpt() ); ?></p>

		<span class="post-card__more u-link" aria-hidden="true"><?php esc_html_e( 'Read', 'studiogreen' ); ?></span>
	</div>
</article>
