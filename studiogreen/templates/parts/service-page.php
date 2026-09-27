<?php
/**
 * Shared layout for the Web and Design pages.
 *
 * @package StudioGreen
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$sg_label = get_the_title();

$sg_hero_title = sg_field( 'hero_title' );
if ( '' === trim( $sg_hero_title ) ) {
	$sg_hero_title = $sg_label;
}
$sg_hero_lead = sg_field( 'hero_lead' );
?>

<!-- ===== HERO ===== -->
<header class="page-hero" data-screen-label="<?php echo esc_attr( $sg_label . ' — Hero' ); ?>">
	<div class="wrap" data-reveal-group>
		<h1 class="t-display" data-lines><?php echo sg_marks( $sg_hero_title ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></h1>
		<?php if ( '' !== trim( $sg_hero_lead ) ) : ?>
			<p class="t-manifesto page-hero__lead" data-lines><?php echo sg_marks( $sg_hero_lead ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
		<?php endif; ?>
	</div>
</header>

<!-- ===== SERVICES ===== -->
<?php for ( $sg_i = 1; $sg_i <= 3; $sg_i++ ) : ?>
	<?php
	$sg_title = sg_field( "svc_{$sg_i}_title" );
	if ( '' === trim( $sg_title ) ) {
		continue;
	}
	$sg_cta_label = sg_field( "svc_{$sg_i}_cta_label" );
	$sg_items     = sg_field_list( "svc_{$sg_i}_list" );
	?>
	<section class="svc" id="<?php echo esc_attr( sanitize_title( sg_field( "svc_{$sg_i}_id" ) ) ); ?>" data-screen-label="<?php echo esc_attr( $sg_label . ' — ' . $sg_title ); ?>">
		<div class="wrap svc__inner" data-reveal-group>
			<h2 class="t-display" data-lines><?php echo sg_marks( $sg_title ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></h2>
			<div class="svc__grid">
				<div class="svc__copy">
					<p class="t-manifesto" data-lines><?php sg_the_field( "svc_{$sg_i}_kicker" ); ?></p>
					<p class="svc__body"><?php sg_the_field( "svc_{$sg_i}_body" ); ?></p>
					<?php if ( '' !== trim( $sg_cta_label ) ) : ?>
						<a class="svc__cta" href="<?php echo esc_url( sg_field_url( "svc_{$sg_i}_cta_url" ) ); ?>"><?php echo esc_html( $sg_cta_label ); ?></a>
					<?php endif; ?>
				</div>
				<?php if ( $sg_items ) : ?>
					<ul class="svc__list">
						<?php foreach ( $sg_items as $sg_item ) : ?>
							<li><?php echo esc_html( $sg_item ); ?></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>
		</div>
	</section>
<?php endfor; ?>

<!-- ===== PROCESS ===== -->
<?php
$sg_process_line  = sg_field( 'process_line' );
$sg_process_steps = array();
for ( $sg_i = 1; $sg_i <= 4; $sg_i++ ) {
	$sg_stage = sg_field( "process_{$sg_i}_title" );
	if ( '' !== trim( $sg_stage ) ) {
		$sg_process_steps[] = array( $sg_stage, sg_field( "process_{$sg_i}_body" ) );
	}
}
if ( '' !== trim( $sg_process_line ) || $sg_process_steps ) :
	?>
	<section class="process" data-screen-label="<?php echo esc_attr( $sg_label . ' — Process' ); ?>">
		<div class="wrap">
			<?php if ( '' !== trim( $sg_process_line ) ) : ?>
				<h2 class="t-display process__line" data-lines data-reveal-group><?php echo sg_marks( $sg_process_line ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></h2>
			<?php endif; ?>
			<?php if ( $sg_process_steps ) : ?>
				<ul class="process__steps">
					<?php foreach ( $sg_process_steps as $sg_step ) : ?>
						<li data-reveal-group>
							<p class="t-manifesto" data-lines><?php echo sg_marks( $sg_step[0] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
							<p><?php echo wp_kses( $sg_step[1], sg_inline_html() ); ?></p>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
	</section>
<?php endif; ?>

<!-- ===== CLOSING ===== -->
<?php
$sg_close_line = sg_field( 'close_line' );
$sg_close_body = sg_field( 'close_body' );
$sg_close_cta  = sg_field( 'close_cta' );
if ( '' !== trim( $sg_close_line . $sg_close_body . $sg_close_cta ) ) :
	?>
	<section class="page-close" data-screen-label="<?php echo esc_attr( $sg_label . ' — Closing' ); ?>">
		<div class="wrap" data-reveal-group>
			<?php if ( '' !== trim( $sg_close_line ) ) : ?>
				<?php // A heading element, but kept at t-manifesto: it leads into the display-size CTA below, as the home page closing does. ?>
				<h2 class="t-manifesto page-close__line" data-lines><?php echo sg_marks( $sg_close_line ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></h2>
			<?php endif; ?>
			<?php if ( '' !== trim( $sg_close_body ) ) : ?>
				<p class="page-close__body"><?php echo wp_kses( $sg_close_body, sg_inline_html() ); ?></p>
			<?php endif; ?>
			<?php if ( '' !== trim( $sg_close_cta ) ) : ?>
				<a class="t-display page-close__link" href="<?php echo esc_url( sg_field_url( 'close_cta_url' ) ); ?>">
					<?php echo sg_reveal_line( $sg_close_cta ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</a>
			<?php endif; ?>
		</div>
	</section>
<?php endif; ?>

<?php
get_footer();
