/* global YoastSEO, jQuery, SGAnalysis */
( function () {
	"use strict";

	var PLUGIN = "StudioGreenFields";

	if ( "undefined" === typeof SGAnalysis || ! SGAnalysis.roles ) {
		return;
	}

	var ids = Object.keys( SGAnalysis.roles );

	function escapeHtml( text ) {
		return text
			.replace( /&/g, "&amp;" )
			.replace( /</g, "&lt;" )
			.replace( />/g, "&gt;" );
	}

	/**
	 * An empty field renders its launch copy, which the placeholder holds.
	 */
	function valueOf( el ) {
		var value = "" !== el.value.trim() ? el.value : el.placeholder;

		// == wraps a highlight; the markers are not words.
		return value.replace( /==/g, "" ).trim();
	}

	function blockFor( id, role ) {
		var el = document.getElementById( id );

		if ( ! el ) {
			return "";
		}

		var value = valueOf( el );

		if ( "" === value ) {
			return "";
		}

		if ( "list" === role ) {
			return "<ul>" + value.split( /\n+/ ).map( function ( line ) {
				return "<li>" + escapeHtml( line.trim() ) + "</li>";
			} ).join( "" ) + "</ul>";
		}

		if ( "h2" === role ) {
			return "<h2>" + escapeHtml( value ) + "</h2>";
		}

		return value.split( /\n{2,}/ ).map( function ( para ) {
			return "<p>" + escapeHtml( para.trim() ) + "</p>";
		} ).join( "" );
	}

	/**
	 * The one H1, from however many fields the headline is split across. Only the
	 * first line of a multi-line field counts: hero_words cycles, and just one of
	 * its words is in the heading at a time.
	 */
	function heading() {
		var parts = [];

		ids.forEach( function ( id ) {
			if ( "h1" !== SGAnalysis.roles[ id ] ) {
				return;
			}

			var el = document.getElementById( id );

			if ( ! el ) {
				return;
			}

			var line = valueOf( el ).split( "\n" )[ 0 ].trim();

			if ( "" !== line ) {
				parts.push( line );
			}
		} );

		if ( ! parts.length ) {
			return "";
		}

		return "<h1>" + escapeHtml( parts.join( " " ) ) + "</h1>";
	}

	function assemble() {
		return heading() + ids.map( function ( id ) {
			if ( "h1" === SGAnalysis.roles[ id ] ) {
				return "";
			}

			return blockFor( id, SGAnalysis.roles[ id ] );
		} ).join( "" );
	}

	function addFieldContent( content ) {
		return content + assemble();
	}

	function reanalyse() {
		if ( "function" === typeof YoastSEO.app.pluginReloaded ) {
			YoastSEO.app.pluginReloaded( PLUGIN );
		} else if ( "function" === typeof YoastSEO.app.refresh ) {
			YoastSEO.app.refresh();
		}
	}

	function init() {
		if ( "undefined" === typeof YoastSEO || "undefined" === typeof YoastSEO.app ) {
			return;
		}

		YoastSEO.app.registerPlugin( PLUGIN, { status: "ready" } );
		YoastSEO.app.registerModification( "content", addFieldContent, PLUGIN, 5 );

		var timer = null;

		ids.forEach( function ( id ) {
			var el = document.getElementById( id );

			if ( ! el ) {
				return;
			}

			el.addEventListener( "input", function () {
				window.clearTimeout( timer );
				timer = window.setTimeout( reanalyse, 700 );
			} );
		} );

		reanalyse();
	}

	if ( "undefined" !== typeof YoastSEO && "undefined" !== typeof YoastSEO.app ) {
		init();
	} else if ( window.jQuery ) {
		jQuery( window ).on( "YoastSEO:ready", init );
	}
}() );
