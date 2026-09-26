<?php
/**
 * Dump the theme's launch copy as JSON, for bin/copy-review.py.
 *
 * Runs the real schema rather than pattern-matching the source, so the Web and
 * Design pages (whose copy is passed into sg_service_page_schema) are included.
 */

define( 'ABSPATH', '/' );

function __( $s, $d = null ) { return $s; }
function _x( $s, $c, $d = null ) { return $s; }
function apply_filters( $tag, $value ) { return $value; }

$dir = dirname( __DIR__ ) . '/studiogreen';
require $dir . '/inc/fields-schema.php';

// Keys holding identifiers, URLs or settings rather than prose.
$skip = array( 'typeform_id', 'email', 'closing_email', 'closing_email_lines', 'updated', 'hero_words' );

$out = array();

foreach ( sg_schema() as $template => $entry ) {
	$fields = array();

	foreach ( $entry['groups'] as $group ) {
		foreach ( $group['fields'] as $field ) {
			$key = $field['key'];

			if ( in_array( $key, $skip, true ) ) {
				continue;
			}
			if ( preg_match( '/_(url|id)$/', $key ) ) {
				continue;
			}
			if ( 'url' === ( isset( $field['type'] ) ? $field['type'] : 'text' ) ) {
				continue;
			}

			$value = isset( $field['default'] ) ? (string) $field['default'] : '';

			if ( '' === trim( $value ) ) {
				continue;
			}

			$fields[] = array(
				'key'   => $key,
				'type'  => isset( $field['type'] ) ? $field['type'] : 'text',
				'group' => $group['title'],
				'text'  => $value,
			);
		}
	}

	if ( $fields ) {
		$out[ $template ] = $fields;
	}
}

echo json_encode( $out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
