jQuery(document).ready(function($){

	// Make example nicenames clickable.
	$('input[name="ba_eas_author_slug"]').click(function(){
		if ( 'ba_eas_author_slug_custom_radio' !== $(this).attr('id') ) {
			$('input[name="ba_eas_author_slug_custom"]').val( $(this).val() ).text( $(this).siblings('span').text() );
		}
	});

	// If focus moves to the custom author slug input, select the radio.
	$('input[name="ba_eas_author_slug_custom"]').focus(function(){
		$('#ba_eas_author_slug_custom_radio').attr('checked', 'checked');
	});

	// Hide the related fields if `eas-checkbox` is not checked.
	$('input[class="eas-checkbox"]').not(':checked').parents('tr').next('tr').addClass('hidden');

	// Watch for clicks on the `eas-checkbox` options.
	$('input[class="eas-checkbox"]').on('click', function(){
		if ( $(this).is(':checked') ) {
			$(this).parents('tr').next('tr').fadeIn('slow', function(){$(this).removeClass('hidden');});
		} else {
			$(this).parents('tr').next('tr').fadeOut('fast', function(){$(this).addClass('hidden');});
		}
	});
});
