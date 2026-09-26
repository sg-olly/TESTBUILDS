<?php
/**
 * Editable content schema.
 *
 * @package StudioGreen
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The full content schema, keyed by template.
 *
 * @return array
 */
function sg_schema() {
	static $schema = null;

	if ( null !== $schema ) {
		return $schema;
	}

	$schema = array(
		'home'    => array(
			'label'  => __( 'Home page content', 'studiogreen' ),
			'groups' => array(
				array(
					'title'  => __( 'Hero', 'studiogreen' ),
					'fields' => array(
						array(
							'key'     => 'hero_line1',
							'label'   => __( 'Headline, first line', 'studiogreen' ),
							'type'    => 'text',
							'plain'   => true,
							'default' => 'Websites',
						),
						array(
							'key'     => 'hero_line2',
							'label'   => __( 'Headline, second line', 'studiogreen' ),
							'type'    => 'text',
							'plain'   => true,
							'default' => 'that',
							'help'    => __( 'The cycling word is appended after this.', 'studiogreen' ),
						),
						array(
							'key'     => 'hero_words',
							'label'   => __( 'Cycling words', 'studiogreen' ),
							'type'    => 'list',
							'default' => "grow.\nrank.\nconvert.\nsell.\nlast.",
							'help'    => __( 'One per line. They cycle in the headline every 2.6 seconds.', 'studiogreen' ),
						),
						array(
							'key'     => 'hero_lead',
							'label'   => __( 'Intro paragraph', 'studiogreen' ),
							'type'    => 'textarea',
							'plain'   => true,
							'default' => 'An independent marketing and web design studio. Every site built from scratch, then kept fast, fresh and easy to find.',
						),
					),
				),
				array(
					'title'  => __( 'Scrolling ticker', 'studiogreen' ),
					'fields' => array(
						array(
							'key'     => 'marquee_items',
							'label'   => __( 'Ticker items', 'studiogreen' ),
							'type'    => 'list',
							'default' => "HubSpot certified\nNo templates\nFixed quotes, agreed up front\nOne person, start to finish\nReplies within a working day\nBuilt to be found",
							'help'    => __( 'One per line. The list is repeated automatically so the scroll loops seamlessly.', 'studiogreen' ),
						),
					),
				),
				array(
					'title'  => __( 'Statement panel', 'studiogreen' ),
					'fields' => array(
						array(
							'key'     => 'statement_heading',
							'label'   => __( 'Statement', 'studiogreen' ),
							'type'    => 'text',
							'plain'   => true,
							'default' => 'Your website should be your ==hardest-working== team member',
							'help'    => __( 'Sits alone on the dark green panel between the ticker and the services list. Leave empty to drop the panel entirely.', 'studiogreen' ),
						),
					),
				),
				array(
					'title'  => __( 'Services index', 'studiogreen' ),
					'fields' => array_merge(
						array(
							array(
								'key'     => 'services_header',
								'label'   => __( 'Section header', 'studiogreen' ),
								'type'    => 'text',
								'plain'   => true,
								'default' => 'Web, SEO and brand, handled by one person',
								'help'    => __( 'Heads the lime block, one size step above the four rows beneath it.', 'studiogreen' ),
							),
						),
						sg_repeat_fields(
							'svc',
							4,
						array(
							array(
								'key'   => 'title',
								'label' => __( 'Title', 'studiogreen' ),
								'type'  => 'text',
								'plain' => true,
							),
							array(
								'key'   => 'text',
								'label' => __( 'Description', 'studiogreen' ),
								'type'  => 'textarea',
							),
							array(
								'key'   => 'url',
								'label' => __( 'Links to', 'studiogreen' ),
								'type'  => 'url',
							),
						),
						array(
							1 => array(
								'title' => 'Websites',
								'text'  => 'Built from scratch around what your business actually needs to do, not a theme with your logo dropped in.',
								'url'   => '{web}#web',
							),
							2 => array(
								'title' => 'Care & SEO',
								'text'  => 'Ongoing updates and SEMrush-certified SEO, so the site keeps working and people keep finding it.',
								'url'   => '{web}#updates',
							),
							3 => array(
								'title' => 'Brand identity',
								'text'  => 'Logo, colour, type, voice: the decisions that make you recognisable before anyone reads a word.',
								'url'   => '{design}#brand',
							),
							4 => array(
								'title' => 'Design everywhere',
								'text'  => 'The same brand applied everywhere it\'s seen, from inbox to print, so nothing looks like an afterthought.',
								'url'   => '{design}#social',
							),
						)
					)
					),
				),
				array(
					'title'  => __( 'Manifesto', 'studiogreen' ),
					'fields' => array_merge(
						array(
							array(
								'key'     => 'manifesto_p1',
								'label'   => __( 'First paragraph', 'studiogreen' ),
								'type'    => 'textarea',
								'plain'   => true,
								'default' => 'A website is usually the first impression someone gets of your business, before they phone, before they walk in. Get it wrong and it costs you the customer before you know they exist.',
							),
							array(
								'key'     => 'manifesto_p2',
								'label'   => __( 'Second paragraph', 'studiogreen' ),
								'type'    => 'textarea',
								'plain'   => true,
								'default' => 'A brochure is out of date the day it\'s printed. A website doesn\'t have to be, so long as somebody keeps it moving with the business. That\'s what I do after launch.',
							),
						),
						sg_stat_fields(
							array(
								1 => array(
									'value' => '5',
									'label' => 'Years in marketing',
								),
								2 => array(
									'value' => '100%',
									'label' => 'Custom, never templated',
								),
								3 => array(
									'value' => 'SEMrush',
									'label' => 'Certified in SEO',
								),
							)
						)
					),
				),
				array(
					'title'  => __( 'Closing', 'studiogreen' ),
					'fields' => array(
						array(
							'key'     => 'closing_line',
							'label'   => __( 'Closing line', 'studiogreen' ),
							'type'    => 'text',
							'plain'   => true,
							'default' => 'Got a business that deserves better than fine?',
						),
						array(
							'key'     => 'closing_email',
							'label'   => __( 'Email address', 'studiogreen' ),
							'type'    => 'text',
							'default' => 'hello@studiogreenmarketing.co.uk',
						),
						array(
							'key'     => 'closing_email_lines',
							'label'   => __( 'Email, split for display', 'studiogreen' ),
							'type'    => 'list',
							'default' => "hello@\nstudiogreen\nmarketing.co.uk",
							'help'    => __( 'One per line. Each line animates in separately at display size.', 'studiogreen' ),
						),
					),
				),
			),
		),

		'web'     => sg_service_page_schema(
			__( 'Web page content', 'studiogreen' ),
			array(
				'hero_title'     => 'A site that keeps earning its place.',
				'hero_lead'      => 'Most small business sites get built once and quietly rot. These three services exist so that doesn\'t happen to yours.',
				'services'       => array(
					1 => array(
						'id'        => 'web',
						'title'     => 'Web design and build',
						'kicker'    => 'Built around your business, not a theme.',
						'body'      => 'Page-builders and off-the-shelf themes are why so many small business sites look the same. Every build starts from what your business needs a visitor to do: book, buy, call, enquire, and the design and code get built around that goal.',
						'cta_label' => 'Brief a build',
						'cta_url'   => '{contact}',
						'list'      => "Discovery and strategy: what the site has to achieve\nBespoke design and prototyping\nResponsive, hand-built front end\nPerformance and accessibility built in, not bolted on",
					),
					2 => array(
						'id'        => 'updates',
						'title'     => 'Site updates and care',
						'kicker'    => 'The part everyone skips, and regrets.',
						'body'      => 'A launched site is a snapshot. The business keeps moving: new services, new prices, new stock, and a site that doesn\'t move with it starts working against you instead of for you.',
						'cta_label' => 'Set up ongoing care',
						'cta_url'   => '{contact}',
						'list'      => "Content and image edits\nNew sections and landing pages\nPlugin, security and CMS updates\nHosting, domains and uptime monitoring\nMonthly or as needed, whatever pace your business moves at",
					),
					3 => array(
						'id'        => 'seo',
						'title'     => 'SEO',
						'kicker'    => 'Built well and invisible is a wasted site.',
						'body'      => 'The best-designed website in your industry still won\'t matter on page four of Google. SEMrush-certified SEO means the work behind getting found is based on real search data for your industry and area, not guesswork.',
						'cta_label' => 'Get found',
						'cta_url'   => '{contact}',
						'list'      => "Technical and on-page audits\nKeyword and competitor research\nLocal SEO and Google Business\nContent optimisation\nReporting that tells you what actually changed, in plain English",
					),
				),
				'process_line'   => 'Four stages. No surprises.',
				'process'        => array(
					1 => array(
						'title' => 'Chat',
						'body'  => 'A short call about your goals, your audience and your timeline. No pitch, just questions.',
					),
					2 => array(
						'title' => 'Plan',
						'body'  => 'A fixed quote and a clear scope, so you know exactly what you\'re getting before anything starts.',
					),
					3 => array(
						'title' => 'Build',
						'body'  => 'Regular check-ins as it comes together, so you\'re never wondering what\'s happening.',
					),
					4 => array(
						'title' => 'Grow',
						'body'  => 'Launch is the start, not the finish. Updates, SEO and content keep it working after.',
					),
				),
				'close_line'     => 'Priced for what you actually need.',
				'close_body'     => 'Packaged pricing means paying for things you don\'t need. Tell me what you\'re after, a full build, an ongoing care plan, or both, and you\'ll get a fixed price back for your project.',
				'close_cta'      => 'Get a quote',
				'close_cta_url'  => '{contact}',
			)
		),

		'design'  => sg_service_page_schema(
			__( 'Design page content', 'studiogreen' ),
			array(
				'hero_title'     => 'The brand does the talking before you do.',
				'hero_lead'      => 'People form an opinion in seconds, usually before reading a word: off a logo, a colour, a font. This is the work that makes sure it\'s the right one.',
				'services'       => array(
					1 => array(
						'id'        => 'brand',
						'title'     => 'Brand identity',
						'kicker'    => 'The decisions everything else depends on.',
						'body'      => 'A brand identity isn\'t a logo. It\'s the full set of decisions that make a business instantly recognisable and consistently itself, on a website, a van, or an Instagram grid.',
						'cta_label' => 'Start your brand',
						'cta_url'   => '{contact}',
						'list'      => "Logo suite and marks\nColour palette and type system\nBrand guidelines: consistent even when others touch it\nTone of voice and messaging\nFull asset pack, ready for every use",
					),
					2 => array(
						'id'        => 'social',
						'title'     => 'Email and social',
						'kicker'    => 'Consistency is what makes a brand memorable.',
						'body'      => 'One good post doesn\'t build recognition. A hundred consistent ones do. These templates and assets come straight from your brand identity, so everything looks unmistakably you.',
						'cta_label' => 'Design my campaigns',
						'cta_url'   => '{contact}',
						'list'      => "Email and newsletter templates\nCampaign and launch assets\nSocial post and story templates\nProfile and banner design\nConsistent, without you having to think about it",
					),
					3 => array(
						'id'        => 'print',
						'title'     => 'Print and merch',
						'kicker'    => 'The brand still has to work off-screen.',
						'body'      => 'Business cards, signage, packaging: often the only physical thing a customer takes away, judged as harshly as the website. Delivered print-ready, so what turns up from the printer matches what you approved, first time.',
						'cta_label' => 'Get print-ready',
						'cta_url'   => '{contact}',
						'list'      => "Business cards and stationery\nFlyers, posters and signage\nBranded merch and packaging\nPrint-ready artwork, sized and set up correctly\nSupplier-ready files, no back-and-forth",
					),
				),
				'process_line'   => 'Four stages. No surprises.',
				'process'        => array(
					1 => array(
						'title' => 'Chat',
						'body'  => 'Understanding your business, your audience and the feeling you want the brand to give, before a single decision gets made.',
					),
					2 => array(
						'title' => 'Plan',
						'body'  => 'A fixed quote and a direction to agree on before any design starts.',
					),
					3 => array(
						'title' => 'Design',
						'body'  => 'Refined together, with regular check-ins, no disappearing for three weeks then reappearing off-brief.',
					),
					4 => array(
						'title' => 'Roll out',
						'body'  => 'Logo, templates and assets delivered ready to use, everywhere your brand needs to show up.',
					),
				),
				'close_line'     => 'Priced for what you actually need.',
				'close_body'     => 'A full identity, a set of social and email designs, or a stack of print: tell me what you\'re after and you\'ll get a fixed price back for exactly that, not a bundled package built for someone else\'s business.',
				'close_cta'      => 'Get a quote',
				'close_cta_url'  => '{contact}',
			)
		),

		'service' => sg_service_page_schema(
			__( 'Service page content', 'studiogreen' ),
			sg_blank_service_defaults()
		),

		'about'   => array(
			'label'  => __( 'About page content', 'studiogreen' ),
			'groups' => array(
				array(
					'title'  => __( 'Hero', 'studiogreen' ),
					'fields' => array(
						array(
							'key'     => 'hero_title',
							'label'   => __( 'Headline', 'studiogreen' ),
							'type'    => 'text',
							'plain'   => true,
							'default' => 'One person who actually cares about your site.',
						),
						array(
							'key'     => 'hero_lead',
							'label'   => __( 'Intro paragraph', 'studiogreen' ),
							'type'    => 'textarea',
							'plain'   => true,
							'default' => 'Studio Green is independent by design. When you hire me, you get me: planning, designing, building and growing your site, start to finish.',
						),
					),
				),
				array(
					'title'  => __( 'Story', 'studiogreen' ),
					'fields' => array_merge(
						array(
							array(
								'key'     => 'story_p1',
								'label'   => __( 'First paragraph', 'studiogreen' ),
								'type'    => 'textarea',
								'plain'   => true,
								'default' => 'Nearly five years in marketing and web design have taught me what gets a small business found, chosen and remembered. Studio Green is that experience applied directly, no agency layers in between.',
							),
							array(
								'key'     => 'story_p2',
								'label'   => __( 'Second paragraph', 'studiogreen' ),
								'type'    => 'textarea',
								'plain'   => true,
								'default' => 'You brief me once and I carry it through design, build, launch and whatever comes after. You\'ll always know what\'s happening, what it costs and when it\'s landing.',
							),
						),
						sg_stat_fields(
							array(
								1 => array(
									'value' => '5',
									'label' => 'Years of hands-on experience',
								),
								2 => array(
									'value' => '100%',
									'label' => 'Custom, never a template',
								),
								3 => array(
									'value' => 'SEMrush',
									'label' => 'Certified in SEO',
								),
							)
						)
					),
				),
				array(
					'title'  => __( 'How I work', 'studiogreen' ),
					'fields' => sg_repeat_fields(
						'how',
						4,
						array(
							array(
								'key'   => 'title',
								'label' => __( 'Title', 'studiogreen' ),
								'type'  => 'text',
								'plain' => true,
							),
							array(
								'key'   => 'text',
								'label' => __( 'Description', 'studiogreen' ),
								'type'  => 'textarea',
							),
						),
						array(
							1 => array(
								'title' => 'Built for you',
								'text'  => 'Every project starts from your business, not a layout that\'s been reused fifty times already.',
							),
							2 => array(
								'title' => 'Never waiting',
								'text'  => 'Small studio, no queue, no account manager relaying messages three days late.',
							),
							3 => array(
								'title' => 'Certified',
								'text'  => 'SEMrush-certified, so the SEO work is built to actually get you found, not just look finished.',
							),
							4 => array(
								'title' => 'Direct and honest',
								'text'  => 'Clear pricing, clear timelines and no telephone game between you and whoever\'s building it.',
							),
						)
					),
				),
				array(
					'title'  => __( 'Closing', 'studiogreen' ),
					'fields' => array(
						array(
							'key'     => 'close_line',
							'label'   => __( 'Closing line', 'studiogreen' ),
							'type'    => 'text',
							'plain'   => true,
							'default' => 'Sound like what you need?',
						),
						array(
							'key'     => 'close_cta',
							'label'   => __( 'Link label', 'studiogreen' ),
							'type'    => 'text',
							'plain'   => true,
							'default' => 'Start a project',
						),
						array(
							'key'     => 'close_cta_url',
							'label'   => __( 'Link URL', 'studiogreen' ),
							'type'    => 'url',
							'default' => '{contact}',
						),
					),
				),
			),
		),

		'contact' => array(
			'label'  => __( 'Contact page content', 'studiogreen' ),
			'groups' => array(
				array(
					'title'  => __( 'Hero', 'studiogreen' ),
					'fields' => array(
						array(
							'key'     => 'hero_title',
							'label'   => __( 'Headline', 'studiogreen' ),
							'type'    => 'text',
							'plain'   => true,
							'default' => 'Tell me what you\'re building.',
						),
						array(
							'key'     => 'hero_lead',
							'label'   => __( 'Intro paragraph', 'studiogreen' ),
							'type'    => 'textarea',
							'plain'   => true,
							'default' => 'A few details and I can get started properly. You\'ll hear back from me within one working day.',
						),
					),
				),
				array(
					'title'  => __( 'Direct contact', 'studiogreen' ),
					'fields' => array(
						array(
							'key'     => 'direct_intro',
							'label'   => __( 'Above the address', 'studiogreen' ),
							'type'    => 'textarea',
							'default' => 'Rather just email? That comes straight to me.',
						),
						array(
							'key'     => 'email',
							'label'   => __( 'Email address', 'studiogreen' ),
							'type'    => 'text',
							'default' => 'hello@studiogreenmarketing.co.uk',
							'help'    => __( 'Also used by the "Email instead" button in the header on this page.', 'studiogreen' ),
						),
						array(
							'key'     => 'direct_note',
							'label'   => __( 'Below the address', 'studiogreen' ),
							'type'    => 'textarea',
							'default' => 'No mailing list and no follow-up sequence. What you send stays between us.',
						),
					),
				),
				array(
					'title'  => __( 'Enquiry form', 'studiogreen' ),
					'fields' => array(
						array(
							'key'     => 'typeform_id',
							'label'   => __( 'Typeform form ID', 'studiogreen' ),
							'type'    => 'text',
							'default' => '01KWWTRWZQ68AXZJXNRGMVN10G',
							'help'    => __( 'The ID from your Typeform embed code. Leave empty to hide the form and show the email address on its own.', 'studiogreen' ),
						),
					),
				),
			),
		),

		'legal'   => array(
			'label'  => __( 'Page settings', 'studiogreen' ),
			'groups' => array(
				array(
					'title'  => __( 'Header', 'studiogreen' ),
					'fields' => array(
						array(
							'key'     => 'updated',
							'label'   => __( '"Last updated" date', 'studiogreen' ),
							'type'    => 'text',
							'default' => '',
							'help'    => __( 'Shown under the intro, for example "July 2026". Leave empty to use the date this page was last edited.', 'studiogreen' ),
						),
					),
				),
			),
		),
	);

	$descriptions = array(
		'home'    => 'Studio Green is an independent marketing studio. Engaging web design & build, ongoing site updates, SEO and content, no templates, fast turnaround.',
		'web'     => 'Web design and build, ongoing site care, and SEMrush-certified SEO. Built from scratch for one business, then kept up to date.',
		'design'  => 'Brand identity, email and social design, print and merch. A brand that looks like you meant it, on screen and off.',
		'about'   => 'Studio Green is an independent marketing and web design studio. Nearly five years in the trade, SEMrush-certified, and you deal with me directly.',
		'contact' => 'Tell Studio Green about your project. Web design & build, site updates, SEO and content for small businesses and independents.',
		'legal'   => '',
	);

	foreach ( $schema as $sg_key => $entry ) {
		$entry['groups'][]    = array(
			'title'  => __( 'Search and social', 'studiogreen' ),
			'fields' => array(
				array(
					'key'     => 'meta_description',
					'label'   => __( 'Meta description', 'studiogreen' ),
					'type'    => 'textarea',
					'plain'   => true,
					'default' => isset( $descriptions[ $sg_key ] ) ? $descriptions[ $sg_key ] : '',
					'help'    => __( 'Shown in search results and link previews. Around 150 characters. Ignored if an SEO plugin such as Yoast or Rank Math is active.', 'studiogreen' ),
				),
			),
		);
		$schema[ $sg_key ] = $entry;
	}

	/**
	 * Filter the editable content schema.
	 *
	 * @param array $schema Schema keyed by template.
	 */
	$schema = apply_filters( 'sg_schema', $schema );

	return $schema;
}

/**
 * An empty set of service-page defaults.
 *
 * @return array
 */
function sg_blank_service_defaults() {
	$services = array();
	for ( $i = 1; $i <= 3; $i++ ) {
		$services[ $i ] = array(
			'id'        => '',
			'title'     => '',
			'kicker'    => '',
			'body'      => '',
			'cta_label' => '',
			'cta_url'   => '{contact}',
			'list'      => '',
		);
	}

	$process = array();
	for ( $i = 1; $i <= 4; $i++ ) {
		$process[ $i ] = array(
			'title' => '',
			'body'  => '',
		);
	}

	return array(
		'hero_title'    => '',
		'hero_lead'     => '',
		'services'      => $services,
		'process_line'  => '',
		'process'       => $process,
		'close_line'    => '',
		'close_body'    => '',
		'close_cta'     => '',
		'close_cta_url' => '{contact}',
	);
}

/**
 * Build numbered field definitions for a repeating block.
 *
 * @param string $prefix    Key prefix, e.g. 'svc'.
 * @param int    $count     How many items.
 * @param array  $fields    Field definitions without keys or defaults.
 * @param array  $defaults  Defaults as index => array( sub-key => value ).
 * @return array
 */
function sg_repeat_fields( $prefix, $count, $fields, $defaults ) {
	$out = array();

	for ( $i = 1; $i <= $count; $i++ ) {
		foreach ( $fields as $field ) {
			$sub          = $field['key'];
			$field['key'] = "{$prefix}_{$i}_{$sub}";
			/* translators: 1: item number, 2: field label. */
			$field['label']   = sprintf( __( '%1$d. %2$s', 'studiogreen' ), $i, $field['label'] );
			$field['default'] = isset( $defaults[ $i ][ $sub ] ) ? $defaults[ $i ][ $sub ] : '';
			$out[]            = $field;
		}
	}

	return $out;
}

/**
 * Field definitions for the three-up statistics row.
 *
 * @param array $defaults Defaults as index => array( value, label ).
 * @return array
 */
function sg_stat_fields( $defaults ) {
	return sg_repeat_fields(
		'stat',
		3,
		array(
			array(
				'key'   => 'value',
				'label' => __( 'Figure', 'studiogreen' ),
				'type'  => 'text',
				'plain' => true,
				'help'  => __( 'Numbers count up on scroll. Text is shown as written.', 'studiogreen' ),
			),
			array(
				'key'   => 'label',
				'label' => __( 'Caption', 'studiogreen' ),
				'type'  => 'text',
			),
		),
		$defaults
	);
}

/**
 * Schema shared by the Web and Design pages, which have identical structure.
 *
 * @param string $label    Meta box title.
 * @param array  $defaults Default copy for this page.
 * @return array
 */
function sg_service_page_schema( $label, $defaults ) {
	return array(
		'label'  => $label,
		'groups' => array(
			array(
				'title'  => __( 'Hero', 'studiogreen' ),
				'fields' => array(
					array(
						'key'     => 'hero_title',
						'label'   => __( 'Headline', 'studiogreen' ),
						'type'    => 'text',
						'plain'   => true,
						'default' => $defaults['hero_title'],
					),
					array(
						'key'     => 'hero_lead',
						'label'   => __( 'Intro paragraph', 'studiogreen' ),
						'type'    => 'textarea',
						'plain'   => true,
						'default' => $defaults['hero_lead'],
					),
				),
			),
			array(
				'title'  => __( 'Services', 'studiogreen' ),
				'fields' => sg_repeat_fields(
					'svc',
					3,
					array(
						array(
							'key'   => 'title',
							'label' => __( 'Heading', 'studiogreen' ),
							'type'  => 'text',
							'plain' => true,
						),
						array(
							'key'   => 'id',
							'label' => __( 'Anchor', 'studiogreen' ),
							'type'  => 'text',
							'help'  => __( 'Used for deep links such as /web/#seo. Changing it breaks existing links.', 'studiogreen' ),
						),
						array(
							'key'   => 'kicker',
							'label' => __( 'Standfirst', 'studiogreen' ),
							'type'  => 'text',
							'plain' => true,
						),
						array(
							'key'   => 'body',
							'label' => __( 'Body copy', 'studiogreen' ),
							'type'  => 'textarea',
						),
						array(
							'key'   => 'cta_label',
							'label' => __( 'Link label', 'studiogreen' ),
							'type'  => 'text',
						),
						array(
							'key'   => 'cta_url',
							'label' => __( 'Link URL', 'studiogreen' ),
							'type'  => 'url',
						),
						array(
							'key'   => 'list',
							'label' => __( 'What it includes', 'studiogreen' ),
							'type'  => 'list',
							'help'  => __( 'One per line.', 'studiogreen' ),
						),
					),
					$defaults['services']
				),
			),
			array(
				'title'  => __( 'Process', 'studiogreen' ),
				'fields' => array_merge(
					array(
						array(
							'key'     => 'process_line',
							'label'   => __( 'Section heading', 'studiogreen' ),
							'type'    => 'text',
							'plain'   => true,
							'default' => $defaults['process_line'],
						),
					),
					sg_repeat_fields(
						'process',
						4,
						array(
							array(
								'key'   => 'title',
								'label' => __( 'Stage', 'studiogreen' ),
								'type'  => 'text',
								'plain' => true,
							),
							array(
								'key'   => 'body',
								'label' => __( 'Description', 'studiogreen' ),
								'type'  => 'textarea',
							),
						),
						$defaults['process']
					)
				),
			),
			array(
				'title'  => __( 'Closing', 'studiogreen' ),
				'fields' => array(
					array(
						'key'     => 'close_line',
						'label'   => __( 'Closing line', 'studiogreen' ),
						'type'    => 'text',
						'plain'   => true,
						'default' => $defaults['close_line'],
					),
					array(
						'key'     => 'close_body',
						'label'   => __( 'Closing paragraph', 'studiogreen' ),
						'type'    => 'textarea',
						'default' => $defaults['close_body'],
					),
					array(
						'key'     => 'close_cta',
						'label'   => __( 'Link label', 'studiogreen' ),
						'type'    => 'text',
						'plain'   => true,
						'default' => $defaults['close_cta'],
					),
					array(
						'key'     => 'close_cta_url',
						'label'   => __( 'Link URL', 'studiogreen' ),
						'type'    => 'url',
						'default' => $defaults['close_cta_url'],
					),
				),
			),
		),
	);
}
