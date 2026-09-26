<?php
/**
 * Template Name: Contact
 *
 * @package StudioGreen
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$sg_email    = sg_field( 'email' );
$sg_typeform = trim( sg_field( 'typeform_id' ) );
?>

<!-- ===== HERO ===== -->
<header class="page-hero" data-screen-label="Contact — Hero">
	<div class="wrap" data-reveal-group>
		<h1 class="t-display" data-lines><?php sg_the_field( 'hero_title' ); ?></h1>
		<p class="t-manifesto page-hero__lead" data-lines><?php sg_the_field( 'hero_lead' ); ?></p>
	</div>
</header>

<!-- ===== FORM ===== -->
<main class="enquire" data-screen-label="Contact — Form">
	<div class="wrap enquire__grid">
		<?php if ( '' !== $sg_typeform ) : ?>
			<div class="enquire__form">
				<div data-tf-live="<?php echo esc_attr( $sg_typeform ); ?>" style="width:100%;height:min(80vh,760px);"></div>
			</div>
		<?php endif; ?>
		<div class="enquire__direct">
			<p><?php sg_the_field( 'direct_intro' ); ?></p>
			<a class="t-manifesto enquire__email" href="mailto:<?php echo esc_attr( $sg_email ); ?>"><?php
				// <wbr> so a wrap breaks after the @, not mid-domain.
				echo str_replace( '@', '@<wbr>', esc_html( $sg_email ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				?></a>
			<p><?php sg_the_field( 'direct_note' ); ?></p>
		</div>
	</div>
</main>

<?php
get_footer();
