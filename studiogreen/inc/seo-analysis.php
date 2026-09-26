<?php
/**
 * Hand the field content to Yoast's editor analysis.
 *
 * Field-driven pages keep their copy in post meta, so post_content is empty and
 * Yoast scores them as 0 words. This maps each field to a role in a document
 * Yoast can read, and the admin script assembles it from the live field values.
 *
 * @package StudioGreen
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Field keys that carry no prose worth analysing.
 *
 * @return string[]
 */
function sg_analysis_skip_keys() {
	return apply_filters(
		'sg_analysis_skip_keys',
		array(
			'meta_description',
			'typeform_id',
			'email',
			'closing_email',
			'closing_email_lines',
			'updated',
		)
	);
}

/**
 * Field keys whose values join to form the page's one H1.
 *
 * The home hero splits its headline over three fields, and hero_words holds the
 * cycling word: only its first line is in the heading at any moment.
 *
 * @return string[]
 */
function sg_analysis_h1_keys() {
	return apply_filters(
		'sg_analysis_h1_keys',
		array( 'hero_line1', 'hero_line2', 'hero_words', 'hero_title' )
	);
}

/**
 * Field keys that read as a subheading despite not ending in _title.
 *
 * @return string[]
 */
function sg_analysis_h2_keys() {
	return apply_filters(
		'sg_analysis_h2_keys',
		array( 'services_header', 'statement_heading', 'process_line', 'close_line', 'closing_line' )
	);
}

/**
 * How one field should read in the analysed document.
 *
 * @param array $field Field definition.
 * @return string One of 'h1', 'h2', 'list', 'text', 'skip'.
 */
function sg_analysis_role( $field ) {
	$key  = $field['key'];
	$type = isset( $field['type'] ) ? $field['type'] : 'text';

	if ( 'url' === $type || in_array( $key, sg_analysis_skip_keys(), true ) ) {
		return 'skip';
	}

	// Anchors and link targets, e.g. svc_1_id, close_cta_url.
	if ( preg_match( '/_(url|id)$/', $key ) ) {
		return 'skip';
	}

	// A bare figure such as "5" or "100%" is not prose; its caption is kept.
	if ( preg_match( '/^stat_\d+_value$/', $key ) ) {
		return 'skip';
	}

	if ( in_array( $key, sg_analysis_h1_keys(), true ) ) {
		return 'h1';
	}

	if ( in_array( $key, sg_analysis_h2_keys(), true ) || preg_match( '/_title$/', $key ) ) {
		return 'h2';
	}

	if ( 'list' === $type ) {
		return 'list';
	}

	return 'text';
}

/**
 * The analysable fields for a template, as input id => role, in template order.
 *
 * @param string $template_key Schema key.
 * @return array
 */
function sg_analysis_roles( $template_key ) {
	$roles = array();

	foreach ( sg_flat_fields( $template_key ) as $field ) {
		$role = sg_analysis_role( $field );

		if ( 'skip' !== $role ) {
			$roles[ 'sg_' . $field['key'] ] = $role;
		}
	}

	return $roles;
}

/**
 * Load the analysis bridge on pages the theme builds from fields.
 *
 * @param string $hook Current admin screen.
 */
function sg_analysis_assets( $hook ) {
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}

	// Only Yoast exposes the YoastSEO.app modification API this script uses.
	if ( ! defined( 'WPSEO_VERSION' ) ) {
		return;
	}

	$post = get_post();

	if ( ! $post || 'page' !== $post->post_type ) {
		return;
	}

	$roles = sg_analysis_roles( sg_template_key( $post->ID ) );

	if ( ! $roles ) {
		return;
	}

	wp_enqueue_script(
		'sg-admin-analysis',
		SG_URI . '/assets/js/admin-analysis.js',
		array( 'jquery' ),
		sg_asset_version( 'assets/js/admin-analysis.js' ),
		true
	);

	wp_localize_script( 'sg-admin-analysis', 'SGAnalysis', array( 'roles' => $roles ) );
}
add_action( 'admin_enqueue_scripts', 'sg_analysis_assets' );
