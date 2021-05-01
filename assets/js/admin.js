// Admin JS - SimpleCalendar - FullCalendar add-on

/* global simcalFullCalAdminGlobals */

(function( window, undefined ) {
	'use strict';

	jQuery( function( $ ) {

		$( document ).ready( function() {

			$.each( $.fullCalendar.langs, function( localeCode ) {
				$( '#_fullcalendar_display_language' ).append(
					$( '<option/>' )
						.attr( 'value', localeCode )
						.prop( 'selected', localeCode == simcalFullCalAdminGlobals.displayLanguage )
						.text( localeCode )
				);
			} );


		} );
	} );
})
( this );
