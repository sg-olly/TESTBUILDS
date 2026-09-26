<?php
/**
 * Quiet single-row footer.
 *
 * @package StudioGreen
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<!-- ===== FOOTER ===== -->
<footer class="site-foot">
	<div class="wrap site-foot__inner">
		<span>
			<?php
			printf(
				esc_html__( '© %1$s %2$s', 'studiogreen' ),
				esc_html( gmdate( 'Y' ) ),
				esc_html( get_bloginfo( 'name' ) )
			);
			?>
		</span>
		<?php
		sg_nav( 'footer_pages', 'site-foot__pages', 'nav' );
		sg_nav( 'footer_legal', 'site-foot__legal', 'nav' );
		?>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
