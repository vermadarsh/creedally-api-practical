jQuery( document ).ready( function( $ ) {
	'use strict';

	// Localized variables.
	var ajaxurl   = CAI_Public_JS_Obj.ajaxurl;
	var ajaxnonce = CAI_Public_JS_Obj.ajaxnonce;

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

	// Paginate the news items for next set of items.
	$( document ).on( 'click', '.news-pagination .next', function( evt ) {
		evt.preventDefault();
		var current_page = parseInt( $( '#current-news-items-page' ).val() );
		current_page     = ( 1 === is_valid_number( current_page ) ) ? current_page : 1;
		var page         = current_page + 1;

		fetch_paginated_news_items( page );
	} );

	// Paginate the news items for previous set of items.
	$( document ).on( 'click', '.news-pagination .prev', function( evt ) {
		evt.preventDefault();
		var current_page = parseInt( $( '#current-news-items-page' ).val() );
		current_page     = ( 1 === is_valid_number( current_page ) ) ? current_page : 1;
		var page         = current_page - 1;

		fetch_paginated_news_items( page );
	} );

	/**
	 * 
	 * @param {number} page Paged.
	 */
	function fetch_paginated_news_items( page ) {
		// Validate the page number.
		page = ( 1 === is_valid_number( page ) ) ? page : 1;

		// Shoot the AJAX to paginate the news items.
		$.ajax( {
			dataType: 'JSON',
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'paginate_news',
				page: page,
				nonce: ajaxnonce,
			},
			beforeSend: function() {
				// Block the image.
				block_element( $( '.news-listing-table' ) );
			},
			success: function ( response ) {
				// On the event of AJAX failure.
				if ( 'ajax-failed' === response.data.code ) {
					console.warn( response.data.error_message );
					return false;
				}

				// If the paginated posts are available.
				if ( 'paginated' === response.data.code ) {
					$( '.news-listing-table tbody' ).html( response.data.html ); // Set the html.
					$( '#current-news-items-page' ).val( parseInt( response.data.page ) ) // Set the current page.

					// Disable/enable the pagination links.
					var current_page = parseInt( $( '#current-news-items-page' ).val() );
					var total_pages  = parseInt( response.data.total_pages );

					if ( 1 < current_page ) {
						$( '.news-pagination .prev' ).removeClass( 'non-clickable' );
					}

					if ( 1 === current_page ) {
						$( '.news-pagination .prev' ).addClass( 'non-clickable' );
					}

					if ( current_page === total_pages ) {
						$( '.news-pagination .next' ).addClass( 'non-clickable' );
					}

					if ( current_page < total_pages ) {
						$( '.news-pagination .next' ).removeClass( 'non-clickable' );
					}
				}
			},
			error: function( xhr ) { // If error occured.
				console.warn( 'Error occured. Please try again.', 'Status: ' + xhr.statusText + ' Response: ' + xhr.responseText );
				unblock_element( $( '.news-listing-table' ) ); // Unblock the image.
			},
			complete: function() {
				unblock_element( $( '.news-listing-table' ) ); // Unblock the image.
			},
		} );
	}

	/**
	 * Check if a number is valid.
	 * 
	 * @param {number} data Concerned data.
	 */
	function is_valid_number( data ) {

		return ( '' === data || undefined === data || isNaN( data ) || 0 === data ) ? -1 :1;
	}

	/**
	 * Block element.
	 *
	 * @param {string} element DOM element.
	 */
	function block_element( element ) {
		element.addClass( 'non-clickable' );
	}

	/**
	 * Unblock element.
	 *
	 * @param {string} element DOM element.
	 */
	function unblock_element( element ) {
		element.removeClass( 'non-clickable' );
	}
} );
