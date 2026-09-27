<?php
/**
 * Service structured data.
 *
 * The service pages describe what the studio sells in fields Yoast cannot see,
 * so its graph gets Service nodes added to it rather than a second, competing
 * block of JSON-LD. Without an SEO plugin the same nodes are emitted directly.
 *
 * @package StudioGreen
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * A field value reduced to a plain schema string.
 *
 * @param string $text Raw field value.
 * @return string
 */
function sg_schema_text( $text ) {
	$text = str_replace( '==', '', (string) $text );

	return trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( $text ) ) );
}

/**
 * The area the studio serves.
 *
 * @return array
 */
function sg_schema_area() {
	/*
	 * Named areas rather than the whole country: the studio competes locally,
	 * and a service-area business needs no street address to say where it works.
	 */
	return apply_filters(
		'sg_schema_area_served',
		array(
			array(
				'@type' => 'City',
				'name'  => 'Darlington',
			),
			array(
				'@type' => 'AdministrativeArea',
				'name'  => 'County Durham',
			),
			array(
				'@type' => 'AdministrativeArea',
				'name'  => 'North East England',
			),
			array(
				'@type' => 'Country',
				'name'  => 'United Kingdom',
			),
		)
	);
}

/**
 * The services a page describes, read from its fields.
 *
 * @param int $post_id Page ID.
 * @return array[]
 */
function sg_page_services( $post_id ) {
	$template = sg_template_key( $post_id );

	if ( ! in_array( $template, array( 'web', 'design', 'service' ), true ) ) {
		return array();
	}

	$services = array();

	for ( $i = 1; $i <= 3; $i++ ) {
		$name = sg_schema_text( sg_field( "svc_{$i}_title", $post_id ) );

		if ( '' === $name ) {
			continue;
		}

		$services[] = array(
			'name'        => $name,
			'description' => sg_schema_text( sg_field( "svc_{$i}_body", $post_id ) ),
			'anchor'      => sanitize_title( sg_field( "svc_{$i}_id", $post_id ) ),
			'items'       => array_map( 'sg_schema_text', sg_field_list( "svc_{$i}_list", $post_id ) ),
		);
	}

	return $services;
}

/**
 * Build the Service nodes for a page.
 *
 * @param int    $post_id     Page ID.
 * @param string $provider_id The @id of the Organization offering them.
 * @return array[]
 */
function sg_service_nodes( $post_id, $provider_id ) {
	$services = sg_page_services( $post_id );

	if ( ! $services ) {
		return array();
	}

	$page_url = get_permalink( $post_id );
	$nodes    = array();

	foreach ( $services as $index => $service ) {
		$slug = '' !== $service['anchor'] ? $service['anchor'] : 'service-' . ( $index + 1 );

		$node = array(
			'@type'       => 'Service',
			'@id'         => $page_url . '#service-' . $slug,
			'name'        => $service['name'],
			'serviceType' => $service['name'],
			'url'         => $page_url . '#' . $slug,
			'areaServed'  => sg_schema_area(),
		);

		if ( '' !== $service['description'] ) {
			$node['description'] = $service['description'];
		}

		if ( '' !== $provider_id ) {
			$node['provider'] = array( '@id' => $provider_id );
		}

		if ( $service['items'] ) {
			$node['hasOfferCatalog'] = array(
				'@type'           => 'OfferCatalog',
				'name'            => $service['name'],
				'itemListElement' => array_map(
					function ( $item ) {
						return array(
							'@type'       => 'Offer',
							'itemOffered' => array(
								'@type' => 'Service',
								'name'  => $item,
							),
						);
					},
					$service['items']
				),
			);
		}

		$nodes[] = $node;
	}

	/**
	 * Filter the Service nodes for a page.
	 *
	 * @param array[] $nodes   Service nodes.
	 * @param int     $post_id Page ID.
	 */
	return apply_filters( 'sg_service_nodes', $nodes, $post_id );
}

/**
 * The @id of the Organization node in a graph, if there is one.
 *
 * @param array[] $graph Schema graph.
 * @return string
 */
function sg_graph_organization_id( $graph ) {
	foreach ( $graph as $node ) {
		if ( empty( $node['@type'] ) || empty( $node['@id'] ) ) {
			continue;
		}

		$types = (array) $node['@type'];

		foreach ( $types as $type ) {
			if ( 'Organization' === $type || 'ProfessionalService' === $type || 'LocalBusiness' === $type ) {
				return (string) $node['@id'];
			}
		}
	}

	return '';
}

/**
 * Add the Service nodes to Yoast's graph.
 *
 * @param array[] $graph Schema graph.
 * @return array[]
 */
function sg_yoast_schema_graph( $graph ) {
	if ( ! is_array( $graph ) || ! is_page() ) {
		return $graph;
	}

	$nodes = sg_service_nodes( get_queried_object_id(), sg_graph_organization_id( $graph ) );

	if ( ! $nodes ) {
		return $graph;
	}

	return array_merge( $graph, $nodes );
}
add_filter( 'wpseo_schema_graph', 'sg_yoast_schema_graph' );

/**
 * Emit the Service nodes when no SEO plugin is building a graph.
 */
function sg_service_structured_data() {
	if ( sg_seo_plugin_active() || ! is_page() ) {
		return;
	}

	$provider = home_url( '/' ) . '#organization';
	$nodes    = sg_service_nodes( get_queried_object_id(), $provider );

	if ( ! $nodes ) {
		return;
	}

	/*
	 * sg_structured_data only describes the organisation on the front page, and no
	 * service page is the front page, so the provider needs a node to point at.
	 */
	array_unshift(
		$nodes,
		array(
			'@type' => 'Organization',
			'@id'   => $provider,
			'name'  => get_bloginfo( 'name' ),
			'url'   => home_url( '/' ),
		)
	);

	printf(
		'<script type="application/ld+json">%s</script>' . "\n",
		wp_json_encode(
			array(
				'@context' => 'https://schema.org',
				'@graph'   => $nodes,
			),
			JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG
		)
	);
}
add_action( 'wp_head', 'sg_service_structured_data', 7 );
