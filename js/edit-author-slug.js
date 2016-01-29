jQuery( document ).ready(function( $ ) {

	// Front show/hide.
	if ( $( 'input[name="_ba_eas_remove_front"]' ).prop( 'checked' ) ) {
		$( 'span[class="eas-demo-author-base-front"]' ).addClass( 'hidden' );
	}

	// Watch for clicks on the `_ba_eas_remove_front` checkbox.
	$( 'input[name="_ba_eas_remove_front"]' ).on( 'click', function() {
		if ( $( this ).prop( 'checked' ) ) {
			$( 'span[class="eas-demo-author-base-front"]' ).fadeOut( 'fast', function() {
$( this ).addClass( 'hidden' );
});
		} else {
			$( 'span[class="eas-demo-author-base-front hidden"]' ).fadeIn( 'slow', function() {
$( this ).removeClass( 'hidden' );
});
		}
	});

	// Make example nicenames clickable.
	$( 'input[name="ba_eas_author_slug"]' ).click(function() {
		if ( 'ba_eas_author_slug_custom_radio' !== $( this ).attr( 'id' ) ) {
			$( 'input[name="ba_eas_author_slug_custom"]' ).val( $( this ).val() ).text( $( this ).siblings( 'span' ).text() );
		}
	});

	// If focus moves to the custom author slug input, select the radio.
	$( 'input[name="ba_eas_author_slug_custom"]' ).focus(function() {
		$( '#ba_eas_author_slug_custom_radio' ).attr( 'checked', 'checked' );
	});

	// Hide the related fields if `eas-checkbox` is not checked.
	$( 'input[class="eas-checkbox"]' ).not( ':checked' ).parents( 'tr' ).next( 'tr' ).addClass( 'hidden' );

	// Watch for clicks on the `eas-checkbox` options.
	$( 'input[class="eas-checkbox"]' ).on( 'click', function() {
		if ( $( this ).prop( 'checked' ) ) {
			$( this ).parents( 'tr' ).next( 'tr' ).fadeIn( 'slow', function() {
$( this ).removeClass( 'hidden' );
});
		} else {
			$( this ).parents( 'tr' ).next( 'tr' ).fadeOut( 'fast', function() {
$( this ).addClass( 'hidden' );
});
		}
	});
});
