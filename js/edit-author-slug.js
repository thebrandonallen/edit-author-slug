/* jshint devel: true */

jQuery( document ).ready( function( $ ) {

	// Front show/hide.
	if ( $( 'input[name="_ba_eas_remove_front"]' ).prop( 'checked' ) ) {
		$( '.eas-demo-author-base-front' ).toggle();
	}

	// Watch for clicks on the `_ba_eas_remove_front` checkbox.
	$( 'input[name="_ba_eas_remove_front"]' ).on( 'click', function() {
		$( '.eas-demo-author-base-front' ).toggle();
	});

	// Hide the related fields if `eas-checkbox` is not checked.
	$( 'input[class="eas-checkbox"]' ).not( ':checked' ).parents( 'tr' ).next( 'tr' ).toggle();

	// Watch for clicks on the `eas-checkbox` options.
	$( 'input[class="eas-checkbox"]' ).on( 'click', function() {
		$( this ).parents( 'tr' ).next( 'tr' ).toggle();
	});

	// Make example nicenames clickable.
	$( 'input[name="ba_eas_author_slug"]' ).on( 'click', function() {
		if ( 'ba_eas_author_slug_custom_radio' !== this.id ) {
			$( 'input[name="ba_eas_author_slug_custom"]' ).val( $( this ).val() );
		}
	});

	// If focus moves to the custom author slug input, select the radio.
	$( 'input[name="ba_eas_author_slug_custom"]' ).on( 'focus', function() {
		$( '#ba_eas_author_slug_custom_radio' ).attr( 'checked', 'checked' );
	});
});
