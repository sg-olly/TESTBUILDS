<?php
/**
 * Reading, editing and saving the content fields declared in fields-schema.php.
 *
 * @package StudioGreen
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Which schema applies to a page.
 *
 * @param int|null $post_id Page ID, defaults to the current post.
 * @return string Schema key.
 */
function sg_template_key( $post_id = null ) {
	$post_id = $post_id ? (int) $post_id : (int) get_the_ID();

	if ( $post_id && (int) get_option( 'page_on_front' ) === $post_id ) {
		return 'home';
	}

	$map = array(
		'templates/template-web.php'     => 'web',
		'templates/template-design.php'  => 'design',
		'templates/template-about.php'   => 'about',
		'templates/template-contact.php' => 'contact',
		'templates/template-service.php' => 'service',
	);

	$template = (string) get_page_template_slug( $post_id );

	return isset( $map[ $template ] ) ? $map[ $template ] : 'legal';
}

/**
 * Look up a single field definition.
 *
 * @param string $key           Field key.
 * @param string $template_key  Schema key.
 * @return array|null
 */
function sg_field_def( $key, $template_key ) {
	$schema = sg_schema();

	if ( ! isset( $schema[ $template_key ] ) ) {
		return null;
	}

	foreach ( $schema[ $template_key ]['groups'] as $group ) {
		foreach ( $group['fields'] as $field ) {
			if ( $field['key'] === $key ) {
				return $field;
			}
		}
	}

	return null;
}

/**
 * Every field definition for a schema, flattened.
 *
 * @param string $template_key Schema key.
 * @return array
 */
function sg_flat_fields( $template_key ) {
	$schema = sg_schema();
	$out    = array();

	if ( ! isset( $schema[ $template_key ] ) ) {
		return $out;
	}

	foreach ( $schema[ $template_key ]['groups'] as $group ) {
		foreach ( $group['fields'] as $field ) {
			$out[] = $field;
		}
	}

	return $out;
}

/**
 * Read a field's raw value, falling back to the wording from the static site.
 *
 * @param string   $key     Field key.
 * @param int|null $post_id Page ID, defaults to the current post.
 * @return string
 */
function sg_field( $key, $post_id = null ) {
	$post_id = $post_id ? (int) $post_id : (int) get_the_ID();
	$stored  = $post_id ? get_post_meta( $post_id, 'sg_' . $key, true ) : '';

	if ( is_string( $stored ) && '' !== trim( $stored ) ) {
		return $stored;
	}

	$def = sg_field_def( $key, sg_template_key( $post_id ) );

	return $def && isset( $def['default'] ) ? (string) $def['default'] : '';
}

/**
 * Echo a field, escaped according to where it is rendered.
 *
 * Fields flagged 'plain' end up inside elements whose text is re-split into
 * animated lines by JS, which would discard any markup, so they are escaped as
 * plain text. Everything else allows a small set of inline tags.
 *
 * @param string   $key     Field key.
 * @param int|null $post_id Page ID.
 */
function sg_the_field( $key, $post_id = null ) {
	$post_id = $post_id ? (int) $post_id : (int) get_the_ID();
	$value   = sg_field( $key, $post_id );
	$def     = sg_field_def( $key, sg_template_key( $post_id ) );

	if ( $def && ! empty( $def['plain'] ) ) {
		echo sg_marks( $value ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in helper.
		return;
	}

	echo wp_kses( $value, sg_inline_html() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by wp_kses.
}

/**
 * Read a list field as an array of lines.
 *
 * @param string   $key     Field key.
 * @param int|null $post_id Page ID.
 * @return string[]
 */
function sg_field_list( $key, $post_id = null ) {
	$raw   = sg_field( $key, $post_id );
	$lines = preg_split( '/\R/', $raw );

	if ( ! $lines ) {
		return array();
	}

	$lines = array_map( 'trim', $lines );

	return array_values( array_filter( $lines, 'strlen' ) );
}

/**
 * The page keys that {token} substitution recognises.
 *
 * Deliberately a fixed list. This runs over post content as well as URL
 * fields, so an unrecognised {something} has to be left alone rather than
 * quietly turning into a link to the home page.
 *
 * @return string[]
 */
function sg_token_keys() {
	return apply_filters(
		'sg_token_keys',
		array( 'home', 'web', 'design', 'about', 'contact', 'privacy', 'cookies' )
	);
}

/**
 * Replace {page} tokens with real permalinks.
 *
 * Lets the default links survive whatever slugs the pages end up with.
 *
 * @param string $text Text, possibly containing tokens.
 * @return string
 */
function sg_resolve_tokens( $text ) {
	if ( false === strpos( $text, '{' ) ) {
		return $text;
	}

	$keys = sg_token_keys();

	return preg_replace_callback(
		'/\{([a-z_]+)\}/',
		function ( $matches ) use ( $keys ) {
			if ( ! in_array( $matches[1], $keys, true ) ) {
				return $matches[0];
			}

			$id = sg_page_id( $matches[1] );

			// get_permalink() already carries whatever trailing form the site's
			// permalink structure uses. Forcing a slash on top of it breaks flat
			// URLs, such as the .html filenames the static export writes.
			return $id ? get_permalink( $id ) : home_url( '/' );
		},
		$text
	);
}

/**
 * Read a URL field, tokens resolved and ready to escape.
 *
 * @param string   $key     Field key.
 * @param int|null $post_id Page ID.
 * @return string
 */
function sg_field_url( $key, $post_id = null ) {
	return sg_resolve_tokens( sg_field( $key, $post_id ) );
}

/* -------------------------------------------------------------------------
 * Admin: meta boxes
 * ---------------------------------------------------------------------- */

/**
 * Templates whose layout comes entirely from fields.
 *
 * On these the main editor is dead weight: the template never calls
 * the_content(), so anything typed into the canvas is stored and never shown.
 *
 * @return string[]
 */
function sg_field_driven_templates() {
	return array( 'home', 'web', 'design', 'about', 'contact', 'service' );
}

/**
 * Whether a page's layout is built from fields rather than editor content.
 *
 * @param int $post_id Page ID.
 * @return bool
 */
function sg_is_field_driven( $post_id ) {
	return in_array( sg_template_key( $post_id ), sg_field_driven_templates(), true );
}

/**
 * Use the classic editor on the field-driven templates.
 *
 * The block editor puts meta boxes in a collapsed strip underneath an editing
 * canvas that, on these templates, does nothing at all. That combination reads
 * as "this page is not editable". The classic editor puts the content panel
 * straight under the title, where it belongs.
 *
 * Pages using the default template, and all posts, keep the block editor,
 * because their content really is the editor's content.
 *
 * @param bool    $use  Whether to use the block editor.
 * @param WP_Post $post Post being edited.
 * @return bool
 */
function sg_use_block_editor( $use, $post ) {
	if ( ! $post || 'page' !== $post->post_type ) {
		return $use;
	}

	return sg_is_field_driven( $post->ID ) ? false : $use;
}
add_filter( 'use_block_editor_for_post', 'sg_use_block_editor', 10, 2 );

/**
 * Register the content meta box for pages that have a schema.
 *
 * @param WP_Post $post Page being edited.
 */
function sg_add_meta_boxes( $post ) {
	$key    = sg_template_key( $post->ID );
	$schema = sg_schema();

	if ( ! isset( $schema[ $key ] ) ) {
		return;
	}

	// Hide the content editor where the template ignores it. Per-request only,
	// so nothing already written is touched, and the box returns the moment the
	// page is switched to a template that uses it.
	if ( sg_is_field_driven( $post->ID ) ) {
		remove_post_type_support( 'page', 'editor' );
	}

	add_meta_box(
		'sg-content',
		$schema[ $key ]['label'],
		'sg_render_meta_box',
		'page',
		'normal',
		'high',
		array( '__block_editor_compatible_meta_box' => true )
	);
}
add_action( 'add_meta_boxes_page', 'sg_add_meta_boxes' );

/**
 * Register the field meta keys.
 *
 * Gives the values a sanitiser and a capability check of their own, and exposes
 * them over REST, so they hold up whether they arrive from the meta box's form
 * post or from a REST request.
 */
function sg_register_meta() {
	$fields = array();

	foreach ( sg_schema() as $entry ) {
		foreach ( $entry['groups'] as $group ) {
			foreach ( $group['fields'] as $field ) {
				// A key such as hero_title appears on several templates with the
				// same type, so the last definition wins harmlessly.
				$fields[ 'sg_' . $field['key'] ] = $field;
			}
		}
	}

	foreach ( $fields as $meta_key => $field ) {
		register_post_meta(
			'page',
			$meta_key,
			array(
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true,
				'sanitize_callback' => function ( $value ) use ( $field ) {
					return sg_sanitize_field( $value, $field );
				},
				'auth_callback'     => function ( $allowed, $meta_key, $post_id ) {
					return current_user_can( 'edit_post', $post_id );
				},
			)
		);
	}

}
add_action( 'init', 'sg_register_meta' );

/**
 * Render the content meta box.
 *
 * @param WP_Post $post Page being edited.
 */
function sg_render_meta_box( $post ) {
	$key    = sg_template_key( $post->ID );
	$schema = sg_schema();

	if ( ! isset( $schema[ $key ] ) ) {
		return;
	}

	wp_nonce_field( 'sg_save_fields', 'sg_fields_nonce' );

	echo '<p class="sg-intro">';
	if ( sg_is_field_driven( $post->ID ) ) {
		echo esc_html__( 'This page is built from the fields below, which is why there is no main editor on it. Leave a field empty to keep the wording it launched with.', 'studiogreen' );
	} else {
		echo esc_html__( 'This page is written in the editor above. Each heading starts a new row. The fields below are extras.', 'studiogreen' );
	}
	echo '</p>';

	foreach ( $schema[ $key ]['groups'] as $group ) {
		echo '<div class="sg-group">';
		echo '<h3 class="sg-group__title">' . esc_html( $group['title'] ) . '</h3>';

		foreach ( $group['fields'] as $field ) {
			sg_render_field( $field, $post->ID );
		}

		echo '</div>';
	}
}

/**
 * Render one field control.
 *
 * @param array $field   Field definition.
 * @param int   $post_id Page ID.
 */
function sg_render_field( $field, $post_id ) {
	$name    = 'sg_' . $field['key'];
	$value   = (string) get_post_meta( $post_id, $name, true );
	$default = isset( $field['default'] ) ? (string) $field['default'] : '';
	$type    = isset( $field['type'] ) ? $field['type'] : 'text';

	echo '<p class="sg-field sg-field--' . esc_attr( $type ) . '">';
	echo '<label for="' . esc_attr( $name ) . '"><strong>' . esc_html( $field['label'] ) . '</strong></label>';

	if ( 'textarea' === $type || 'list' === $type ) {
		printf(
			'<textarea id="%1$s" name="%1$s" rows="%2$d" class="widefat" placeholder="%3$s">%4$s</textarea>',
			esc_attr( $name ),
			'list' === $type ? 5 : 3,
			esc_attr( $default ),
			esc_textarea( $value )
		);
	} else {
		printf(
			'<input type="text" id="%1$s" name="%1$s" value="%2$s" class="widefat" placeholder="%3$s" />',
			esc_attr( $name ),
			esc_attr( $value ),
			esc_attr( $default )
		);
	}

	if ( ! empty( $field['help'] ) ) {
		echo '<span class="sg-help description">' . esc_html( $field['help'] ) . '</span>';
	}

	if ( ! empty( $field['plain'] ) ) {
		echo '<span class="sg-help description">' . esc_html__( 'Plain text only: this line is animated, so any HTML would be stripped. Wrap a word in == to highlight it, like ==this==.', 'studiogreen' ) . '</span>';
	}

	echo '</p>';
}

/**
 * Sanitize and store the submitted fields.
 *
 * @param int     $post_id Page ID.
 * @param WP_Post $post    Page object.
 */
function sg_save_fields( $post_id, $post ) {
	if ( ! isset( $_POST['sg_fields_nonce'] ) ) {
		return;
	}

	if ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['sg_fields_nonce'] ) ), 'sg_save_fields' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	// The submitted form belongs to whichever template the page had when it was
	// opened; saving a template change alongside content edits is handled on the
	// next load, when the new schema's box renders.
	foreach ( sg_flat_fields( sg_template_key( $post_id ) ) as $field ) {
		$name = 'sg_' . $field['key'];

		if ( ! isset( $_POST[ $name ] ) ) {
			continue;
		}

		$raw   = wp_unslash( $_POST[ $name ] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized below per field type.
		$value = sg_sanitize_field( $raw, $field );

		if ( '' === trim( $value ) ) {
			delete_post_meta( $post_id, $name );
			continue;
		}

		update_post_meta( $post_id, $name, $value );
	}
}
add_action( 'save_post_page', 'sg_save_fields', 10, 2 );

/**
 * Sanitize a submitted value according to its field type.
 *
 * @param mixed $raw   Submitted value.
 * @param array $field Field definition.
 * @return string
 */
function sg_sanitize_field( $raw, $field ) {
	$raw  = is_string( $raw ) ? $raw : '';
	$type = isset( $field['type'] ) ? $field['type'] : 'text';

	if ( 'url' === $type ) {
		// Tokens such as {contact}#seo are stored verbatim; anything else is a URL.
		if ( preg_match( '/^\{[a-z_]+\}/', trim( $raw ) ) ) {
			return sanitize_text_field( $raw );
		}
		return esc_url_raw( trim( $raw ) );
	}

	if ( 'list' === $type ) {
		$lines = preg_split( '/\R/', $raw );
		$lines = $lines ? array_map( 'sanitize_text_field', $lines ) : array();
		$lines = array_values( array_filter( array_map( 'trim', $lines ), 'strlen' ) );
		return implode( "\n", $lines );
	}

	if ( ! empty( $field['plain'] ) ) {
		// Animated lines are rendered as text, so strip markup at the door too.
		return 'textarea' === $type
			? sanitize_textarea_field( $raw )
			: sanitize_text_field( $raw );
	}

	return trim( wp_kses( $raw, sg_inline_html() ) );
}

/**
 * Style the meta box.
 *
 * @param string $hook Current admin screen.
 */
function sg_admin_assets( $hook ) {
	if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
		return;
	}

	wp_enqueue_style( 'sg-admin', SG_URI . '/assets/css/admin.css', array(), sg_asset_version( 'assets/css/admin.css' ) );
}
add_action( 'admin_enqueue_scripts', 'sg_admin_assets' );
