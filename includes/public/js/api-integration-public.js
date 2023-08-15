jQuery( document ).ready( function( $ ) {
	'use strict';

	// Initiate the datepicker for the news dates field.
	$( '#news_date_from, #news_date_to' ).datepicker( {
		onSelect: function ( selected_date, instance ) {
			if ( 'news_date_from' === instance.id ) {
				// Min date for checkout should be on/after the checkin date.
				$( '#news_date_to' ).datepicker( 'option', 'minDate', selected_date );
				$( '#news_date_to' ).datepicker( 'option', 'maxDate', 0 );
				setTimeout( function() {
					$( '#news_date_to' ).datepicker( 'show' );
				}, 16 );
			}
		},
		dateFormat: 'yy-mm-dd',
		maxDate: 0,
	} );
} );
