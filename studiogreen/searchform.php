<?php
/**
 * Search form.
 *
 * @package StudioGreen
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$sg_id = wp_unique_id( 'sg-search-' );
?>
<form class="search-form" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="<?php echo esc_attr( $sg_id ); ?>">
		<?php esc_html_e( 'Search for:', 'studiogreen' ); ?>
	</label>
	<input
		type="search"
		id="<?php echo esc_attr( $sg_id ); ?>"
		class="search-form__field"
		name="s"
		value="<?php echo esc_attr( get_search_query() ); ?>"
		placeholder="<?php esc_attr_e( 'Search', 'studiogreen' ); ?>"
	/>
	<button type="submit" class="btn btn--ghost search-form__submit">
		<?php esc_html_e( 'Search', 'studiogreen' ); ?>
	</button>
</form>
