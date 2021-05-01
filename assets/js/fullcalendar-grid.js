(function( window, undefined ) {
	'use strict';

	jQuery( function( $ ) {

		$( document ).ready( function() {

			// Run through each of these if there are multiple calendars on a page
			$( '.simcal-fullcalendar-grid .simcal-fullcal' ).each( function() {

				var fc = $( this );
				var currentView = '';

				var bubbleTrigger = fc.data( 'event-bubble-trigger' );

				fc.fullCalendar( {
					header: {
						left: fc.data( 'paging-buttons' ) + fc.data( 'today-button' ),
						center: 'title',
						right: fc.data( 'month-button' ) + fc.data( 'week-button' ) + fc.data( 'day-button' )
					},
					eventSources: {
						url: simcal_fullcal.settings.ajax_url,
						type: 'POST',
						data: {
							action: 'simcal_fullcal_load_events',
							calendar_id: fc.data( 'calendar-id' )
						},
						error: function( response ) {
							console.log( 'Error', response );
						},
						success: function( response ) {
						}
					},
					eventClick: function( calEvent, jsEvent, view ) {
						// Prevent the link from being followed
						event.preventDefault();
					},
					viewRender: function( view ) {
						currentView = view.name;
					},
					eventRender: function( event, element ) {
						element.qtip( {
							content: $( '.simcal-fullcal-qtip-id-' + event.qtip_id ).html(),
							position: {
								my: 'top center',
								at: 'bottom center',
								target: currentView === 'month' ? $( element ) : 'mouse',
								viewport: $( window ),
								adjust: {
									method: 'shift',
									scroll: false,
									mouse: false
								}
							},
							style: {
								def: false,
								classes: 'simcal-default-calendar simcal-event-bubble'
							},
							show: {
								solo: true,
								effect: false,
								event: bubbleTrigger == 'hover' ? 'mouseenter' : 'click'
							},
							hide: {
								fixed: true,
								effect: false,
								event: bubbleTrigger == 'click' ? 'unfocus' : 'mouseleave',
								delay: 100
							}
						} );
					},
					defaultView: fc.data( 'default-view' ),
					timezone: fc.data( 'timezone' ),
					firstDay: fc.data( 'week-starts' ),

					// TODO Pulling language from FC locale setting for now (instead of detecting WordPress setting).
					/*
					monthNames: simcal_fullcal.months.full,
					monthNamesShort: simcal_fullcal.months.short,
					dayNames: simcal_fullcal.days.full,
					dayNamesShort: simcal_fullcal.days.short,
					isRTL: simcal_fullcal.settings.is_rtl,
					*/

					fixedWeekCount: false,
					defaultDate: fc.data( 'start' ),
					eventLimit: fc.data( 'event-limit' ),

					loading: function( isLoading, view ) {
						// Target icon for fadeToggle animation.
						fc.siblings( '.simcal-ajax-loader' ).find( 'i' ).fadeToggle();
					},

					eventColor: fc.data( 'event-color' ),
					eventTextColor: fc.data( 'text-color' ),
					scrollTime: fc.data( 'default-start-time' ),
					minTime: fc.data( 'display-min-time' ),
					maxTime: fc.data( 'display-max-time' ),

					lang: fc.data( 'display-language' ),  // TODO Rename to "locale" for FC v3.
					timeFormat: fc.data( 'date-format' )
				} );

				// Since the height is not initially set (default FC) we do this check after setting up the calendar initially.
				if ( fc.data( 'calendar-height' ) != -1 ) {
					fc.fullCalendar( 'option', 'height', fc.data( 'calendar-height' ) );
				}
			} );
		} );
	} );
})( this );
