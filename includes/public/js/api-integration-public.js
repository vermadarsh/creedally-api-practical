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
		var this_link          = $( this );
		var current_page       = parseInt( this_link.siblings( '#current-news-items-page' ).val() );
		current_page           = ( 1 === is_valid_number( current_page ) ) ? current_page : 1;
		var page               = current_page + 1;
		var pagination_section = this_link.siblings( '#pagination-section' ).val();

		// Do the pagination.
		fetch_paginated_news_items( page, pagination_section, this_link );
	} );

	// Paginate the news items for previous set of items.
	$( document ).on( 'click', '.news-pagination .prev', function( evt ) {
		evt.preventDefault();
		var this_link          = $( this );
		var current_page       = parseInt( this_link.siblings( '#current-news-items-page' ).val() );
		current_page           = ( 1 === is_valid_number( current_page ) ) ? current_page : 1;
		var page               = current_page - 1;
		var pagination_section = this_link.siblings( '#pagination-section' ).val();

		// Do the pagination.
		fetch_paginated_news_items( page, pagination_section, this_link );
	} );

	/**
	 * 
	 * @param {number} page Paged.
	 * @param {string} pagination_section Pagination section.
	 * @param {string} this_link Anchor tag element.
	 */
	function fetch_paginated_news_items( page, pagination_section, this_link ) {
		// Shoot the AJAX to paginate the news items.
		$.ajax( {
			dataType: 'JSON',
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'paginate_news',
				page: page,
				section: pagination_section,
				nonce: ajaxnonce,
			},
			beforeSend: function() {
				// Block the html elements based on the section.
				if ( 'customer-portal' === pagination_section ) {
					block_element( $( '.news-listing-table' ) ); // Block the table.
				} else if ( 'widget' === pagination_section ) {
					block_element( $( '.api-integration-news-container.widget-container' ) ); // Block the widget container.
					block_element( $( '.api-integration-news-pagination' ) ); // Block the widget container.
				}
			},
			success: function ( response ) {
				// On the event of AJAX failure.
				if ( 'ajax-failed' === response.data.code ) {
					console.warn( response.data.error_message );
					return false;
				}

				// If the paginated posts are available.
				if ( 'paginated' === response.data.code ) {
					// Set the html based on the section.
					if ( 'customer-portal' === pagination_section ) {
						$( '.news-listing-table tbody' ).html( response.data.html );
					} else if ( 'widget' === pagination_section ) {
						$( '.api-integration-news-container.widget-container' ).html( response.data.html );
					}

					// Set the new page value.
					this_link.siblings( '#current-news-items-page' ).val( parseInt( response.data.page ) );

					// Disable/enable the pagination links.
					var current_page = parseInt( this_link.siblings( '#current-news-items-page' ).val() );
					var total_pages  = parseInt( response.data.total_pages );

					if ( 1 < current_page ) {
						this_link.parent( '.news-pagination' ).find( '.prev' ).removeClass( 'non-clickable' );
					}

					if ( 1 === current_page ) {
						this_link.parent( '.news-pagination' ).find( '.prev' ).addClass( 'non-clickable' );
					}

					if ( current_page === total_pages ) {
						this_link.parent( '.news-pagination' ).find( '.next' ).addClass( 'non-clickable' );
					}

					if ( current_page < total_pages ) {
						this_link.parent( '.news-pagination' ).find( '.next' ).removeClass( 'non-clickable' );
					}
				}
			},
			error: function( xhr ) { // If error occured.
				console.warn( 'Error occured. Please try again.', 'Status: ' + xhr.statusText + ' Response: ' + xhr.responseText );

				// Block the html elements based on the section.
				if ( 'customer-portal' === pagination_section ) {
					unblock_element( $( '.news-listing-table' ) ); // Unblock the table.
				} else if ( 'widget' === pagination_section ) {
					unblock_element( $( '.api-integration-news-container.widget-container' ) ); // Unblock the widget container.
					unblock_element( $( '.api-integration-news-pagination' ) ); // Unblock the widget container.
				}
			},
			complete: function() {
				// Block the html elements based on the section.
				if ( 'customer-portal' === pagination_section ) {
					unblock_element( $( '.news-listing-table' ) ); // Unblock the table.
				} else if ( 'widget' === pagination_section ) {
					unblock_element( $( '.api-integration-news-container.widget-container' ) ); // Unblock the widget container.
					unblock_element( $( '.api-integration-news-pagination' ) ); // Unblock the widget container.
				}
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
