<?php
/**
 * Home page.
 *
 * @package StudioGreen
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$sg_words   = sg_field_list( 'hero_words' );
$sg_ticker  = sg_field_list( 'marquee_items' );
?>

<!-- ===== HERO ===== -->
<header class="hero" data-screen-label="Home — Hero">
	<div class="wrap hero__inner" data-reveal-group>
		<h1 class="t-display hero__title">
			<?php echo sg_reveal_line( sg_field( 'hero_line1' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<span class="reveal-line"><span><?php sg_the_field( 'hero_line2' ); ?>
				<?php if ( $sg_words ) : ?>
					<span class="cycle-word" data-cycle="<?php echo esc_attr( wp_json_encode( $sg_words ) ); ?>"><?php echo esc_html( $sg_words[0] ); ?></span>
				<?php endif; ?>
			</span></span>
		</h1>
		<p class="t-manifesto hero__lead" data-lines><?php sg_the_field( 'hero_lead' ); ?></p>
	</div>
</header>

<?php if ( $sg_ticker ) : ?>
<!-- ===== TICKER ===== -->
<div class="marquee" aria-hidden="true">
	<div class="marquee__track">
		<?php
		for ( $sg_pass = 0; $sg_pass < 2; $sg_pass++ ) {
			foreach ( $sg_ticker as $sg_item ) {
				echo '<span>' . esc_html( $sg_item ) . '</span>';
			}
		}
		?>
	</div>
</div>
<?php endif; ?>

<!-- ===== STATEMENT ===== -->
<?php
$sg_statement = sg_field( 'statement_heading' );
if ( '' !== trim( $sg_statement ) ) :
	?>
<section class="statement" data-screen-label="Home — Statement">
	<div class="wrap">
		<h2 class="t-display statement__line" data-lines data-reveal-group><?php echo sg_marks( $sg_statement ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></h2>
	</div>
</section>
<?php endif; ?>

<!-- ===== SERVICES INDEX ===== -->
<section class="services" id="services" data-screen-label="Home — Services">
	<div class="wrap">
		<?php
		$sg_services_header = sg_field( 'services_header' );
		if ( '' !== trim( $sg_services_header ) ) :
			?>
			<h2 class="t-display services__header" data-lines data-reveal-group><?php echo sg_marks( $sg_services_header ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></h2>
		<?php endif; ?>
		<ul class="svc-index">
			<?php for ( $sg_i = 1; $sg_i <= 4; $sg_i++ ) : ?>
				<?php
				$sg_title = sg_field( "svc_{$sg_i}_title" );
				if ( '' === trim( $sg_title ) ) {
					continue;
				}
				?>
				<li data-reveal-group>
					<a href="<?php echo esc_url( sg_field_url( "svc_{$sg_i}_url" ) ); ?>">
						<div>
							<?php // h3: these now sit under the section header above. ?>
							<h3 class="t-display"><?php echo sg_reveal_line( $sg_title ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></h3>
							<p><?php sg_the_field( "svc_{$sg_i}_text" ); ?></p>
						</div>
						<span class="svc-more"><?php esc_html_e( 'more', 'studiogreen' ); ?></span>
					</a>
				</li>
			<?php endfor; ?>
		</ul>
	</div>
</section>

<!-- ===== MANIFESTO ===== -->
<section class="manifesto" data-screen-label="Home — Manifesto">
	<div class="wrap">
		<div class="manifesto__copy" data-reveal-group>
			<p class="t-manifesto" data-lines><?php sg_the_field( 'manifesto_p1' ); ?></p>
			<p class="t-manifesto" data-lines><?php sg_the_field( 'manifesto_p2' ); ?></p>
		</div>
		<?php sg_render_stats(); ?>
	</div>
</section>

<!-- ===== CLOSING ===== -->
<section class="closing" data-screen-label="Home — Closing">
	<div class="wrap closing__inner" data-reveal-group>
		<p class="t-manifesto closing__line" data-lines><?php sg_the_field( 'closing_line' ); ?></p>
		<a class="t-display closing__mail" href="mailto:<?php echo esc_attr( sg_field( 'closing_email' ) ); ?>">
			<?php
			foreach ( sg_field_list( 'closing_email_lines' ) as $sg_line ) {
				echo sg_reveal_line( $sg_line ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			?>
		</a>
	</div>
</section>

<?php
get_footer();
